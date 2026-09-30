<?php

namespace Tests\Feature\Beranda;

use App\Models\Bidang;
use App\Models\GaleriSarana;
use App\Models\StrukturOrganisasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Uji halaman Beranda (/home) beserta endpoint adminnya.
 *
 * Berjalan di atas database in-memory bawaan PHPUnit, jadi tidak menyentuh
 * data pengembangan. Penyimpanan berkas disorot ke disk "public" semu agar
 * unggahan galeri bisa diperiksa tanpa menyentuh storage yang sebenarnya.
 */
class HalamanBerandaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $pegawai;

    private array $bidang;

    /**
     * Membuat user uji. Kolom `nip` bersifat unik dan wajib, begitu pula
     * `jabatan`, jadi keduanya diisi di sini walau tidak ada di factory.
     */
    private function buatUser(string $role, ?string $nama = null): User
    {
        static $urut = 0;
        $urut++;

        return User::factory()->create([
            'role' => $role,
            'name' => $nama ?? ('User Uji ' . $urut),
            'nip' => 'NIP' . str_pad((string) $urut, 8, '0', STR_PAD_LEFT),
            'jabatan' => 'Jabatan Uji',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = $this->buatUser('admin', 'Admin Uji');
        $this->pegawai = $this->buatUser('pegawai', 'Pegawai Uji');

        $this->bidang = [];
        foreach (['Bidang Umum', 'Bidang SDA', 'Bidang CKPS'] as $nama) {
            $bidang = Bidang::create(['nama_bidang' => $nama]);
            $this->bidang[$nama] = $bidang->id;
        }
    }

    // ====================
    // Halaman publik
    // ====================

    public function test_halaman_beranda_dapat_diakses_tanpa_login(): void
    {
        $res = $this->get('/home');

        $res->assertOk();
        $res->assertSee('Tentang CRMC');
        $res->assertSee('Struktur Organisasi');
    }

    public function test_halaman_beranda_menampilkan_teks_penjelasan_lengkap(): void
    {
        $res = $this->get('/home');

        $res->assertOk();
        $res->assertSee('Pembagian Tugas Komitmen');
        $res->assertSee('Reminder Komitmen');
        $res->assertSee('Evaluasi Efektifitas');
        $res->assertSee('Continuous Monitoring');
    }

    public function test_halaman_beranda_menampilkan_koordinat_peta(): void
    {
        $res = $this->get('/home');

        $res->assertOk();
        $res->assertSee('6&deg;53&apos;58.0&quot;S 107&deg;39&apos;46.5&quot;E', false);
        $res->assertSee('id="petaCrmc"', false);
        $res->assertSee('data-lat="-6.899444"', false);
        $res->assertSee('data-lng="107.662917"', false);
    }

    public function test_halaman_beranda_memuat_footer(): void
    {
        $res = $this->get('/home');

        $res->assertOk();
        $res->assertSee('Hak cipta dilindungi undang-undang.');
    }

    public function test_navbar_memuat_tautan_ke_beranda(): void
    {
        $res = $this->get('/home');

        $res->assertOk();
        $res->assertSee('href="' . route('beranda') . '"', false);
    }

    public function test_dashboard_lama_tetap_di_slash(): void
    {
        $this->get('/')->assertOk();
    }

    // ====================
    // Org chart
    // ====================

    public function test_org_chart_kosong_menampilkan_pesan(): void
    {
        $res = $this->get('/home');

        $res->assertOk();
        $res->assertSee('Belum Ada Struktur Organisasi');
    }

    public function test_org_chart_menampilkan_ketiga_tingkat(): void
    {
        $pemilik = $this->buatUser('pegawai', 'Bapak Pemilik');
        $mutu = $this->buatUser('pegawai', 'Bapak Mutu');
        $risiko = $this->buatUser('pegawai', 'Bapak Risiko');

        StrukturOrganisasi::create([
            'peran' => 'pemilik_risiko',
            'nama_jabatan' => 'Pemilik Risiko',
            'user_id' => $pemilik->id,
        ]);
        StrukturOrganisasi::create([
            'peran' => 'pengendali_mutu',
            'nama_jabatan' => 'Pengendali Mutu',
            'bidang_id' => $this->bidang['Bidang SDA'],
            'user_id' => $mutu->id,
        ]);
        StrukturOrganisasi::create([
            'peran' => 'pengendali_risiko',
            'nama_jabatan' => 'Pengendali Risiko',
            'bidang_id' => $this->bidang['Bidang SDA'],
            'user_id' => $risiko->id,
        ]);

        $res = $this->get('/home');

        $res->assertOk();
        $res->assertSee('Tingkat 1');
        $res->assertSee('Tingkat 2');
        $res->assertSee('Tingkat 3');
        $res->assertSee('Bapak Pemilik');
        $res->assertSee('Bapak Mutu');
        $res->assertSee('Bapak Risiko');
        // Pengendali mutu pengendali risiko harus berada dalam satu blok bidang.
        $res->assertSee('Tim Pengendali Risiko');
    }

    public function test_kursi_kosong_ditampilkan_sebagai_belum_ditunjuk(): void
    {
        StrukturOrganisasi::create([
            'peran' => 'pemilik_risiko',
            'nama_jabatan' => 'Pemilik Risiko',
        ]);

        $res = $this->get('/home');

        $res->assertOk();
        $res->assertSee('Belum Ditunjuk');
        $res->assertSee('Kursi kosong');
    }

    public function test_pengendali_risiko_tanpa_pengendali_mutu_tetap_ditampilkan(): void
    {
        // Tidak ada pengendali mutu sama sekali, jadi pengendali risiko
        // tidak boleh hilang dari halaman.
        StrukturOrganisasi::create([
            'peran' => 'pengendali_risiko',
            'nama_jabatan' => 'Pengendali Risiko',
            'bidang_id' => $this->bidang['Bidang SDA'],
        ]);

        $res = $this->get('/home');

        $res->assertOk();
        $res->assertSee('Pengendali Risiko Belum Ditempatkan');
    }

    public function test_bidang_tanpa_pengendali_mutu_muncul_sebagai_kartu(): void
    {
        $mutu = $this->buatUser('pegawai', 'Mutu SDA');
        StrukturOrganisasi::create([
            'peran' => 'pengendali_mutu',
            'nama_jabatan' => 'Pengendali Mutu SDA',
            'bidang_id' => $this->bidang['Bidang SDA'],
            'user_id' => $mutu->id,
        ]);

        $res = $this->get('/home');

        $res->assertOk();
        $res->assertSee('Bidang SDA');
        $res->assertSee('Mutu SDA');
        // Bidang lain tidak punya pengendali mutu, jadi tidak boleh muncul
        // sebagai blok bidang tersendiri.
        $res->assertDontSee('Mutu CKPS');
    }

    // ====================
    // Endpoint admin: struktur
    // ====================

    public function test_admin_bisa_menambah_struktur(): void
    {
        $user = $this->buatUser('pegawai', 'Pegawai Termasuk');

        $res = $this->actingAs($this->admin)->from('/home')->post('/admin/beranda/struktur', [
            'peran' => 'pengendali_mutu',
            'nama_jabatan' => 'Pengendali Mutu SDA',
            'bidang_id' => $this->bidang['Bidang SDA'],
            'user_id' => $user->id,
        ]);

        $res->assertRedirect('/home');
        $this->assertDatabaseHas('struktur_organisasi', [
            'peran' => 'pengendali_mutu',
            'nama_jabatan' => 'Pengendali Mutu SDA',
            'bidang_id' => $this->bidang['Bidang SDA'],
        ]);
    }

    public function test_pemilik_risiko_tidak_bisa_punya_bidang(): void
    {
        $this->actingAs($this->admin)->from('/home')->post('/admin/beranda/struktur', [
            'peran' => 'pemilik_risiko',
            'nama_jabatan' => 'Pemilik Risiko',
            'bidang_id' => $this->bidang['Bidang SDA'],
        ])->assertRedirect('/home');

        $this->assertDatabaseHas('struktur_organisasi', [
            'peran' => 'pemilik_risiko',
            'bidang_id' => null,
        ]);
    }

    public function test_admin_bisa_mengubah_struktur(): void
    {
        $row = StrukturOrganisasi::create([
            'peran' => 'pengendali_risiko',
            'nama_jabatan' => 'Pengendali Risiko',
            'bidang_id' => $this->bidang['Bidang SDA'],
        ]);

        $this->actingAs($this->admin)->from('/home')->put('/admin/beranda/struktur/' . $row->id, [
            'peran' => 'pengendali_mutu',
            'nama_jabatan' => 'Pengendali Mutu CKPS',
            'bidang_id' => $this->bidang['Bidang CKPS'],
        ])->assertRedirect('/home');

        $this->assertDatabaseHas('struktur_organisasi', [
            'id' => $row->id,
            'peran' => 'pengendali_mutu',
            'bidang_id' => $this->bidang['Bidang CKPS'],
        ]);
    }

    public function test_admin_bisa_menghapus_struktur(): void
    {
        $row = StrukturOrganisasi::create([
            'peran' => 'pemilik_risiko',
            'nama_jabatan' => 'Pemilik Risiko',
        ]);

        $this->actingAs($this->admin)
            ->from('/home')->delete('/admin/beranda/struktur/' . $row->id)
            ->assertRedirect('/home');

        $this->assertDatabaseMissing('struktur_organisasi', ['id' => $row->id]);
    }

    public function test_admin_bisa_mengurutkan_ulang_struktur(): void
    {
        $a = StrukturOrganisasi::create(['peran' => 'pemilik_risiko', 'nama_jabatan' => 'A', 'urutan' => 1]);
        $b = StrukturOrganisasi::create(['peran' => 'pemilik_risiko', 'nama_jabatan' => 'B', 'urutan' => 2]);
        $c = StrukturOrganisasi::create(['peran' => 'pemilik_risiko', 'nama_jabatan' => 'C', 'urutan' => 3]);

        $this->actingAs($this->admin)->from('/home')->post('/admin/beranda/struktur/urutan', [
            'urutan' => [$c->id, $a->id, $b->id],
        ])->assertRedirect('/home');

        $this->assertSame(1, StrukturOrganisasi::find($c->id)->urutan);
        $this->assertSame(2, StrukturOrganisasi::find($a->id)->urutan);
        $this->assertSame(3, StrukturOrganisasi::find($b->id)->urutan);
    }

    public function test_peran_tak_dikenal_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/beranda/struktur', ['peran' => 'ketua', 'nama_jabatan' => 'X'])
            ->assertSessionHasErrors('peran');

        $this->assertDatabaseCount('struktur_organisasi', 0);
    }

    public function test_nama_jabatan_wajib_diisi(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/beranda/struktur', ['peran' => 'pemilik_risiko'])
            ->assertSessionHasErrors('nama_jabatan');
    }

    public function test_bidang_tak_dikenal_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/beranda/struktur', [
                'peran' => 'pengendali_mutu',
                'nama_jabatan' => 'X',
                'bidang_id' => 9999,
            ])
            ->assertSessionHasErrors('bidang_id');
    }

    public function test_pegawai_tidak_boleh_mengubah_struktur(): void
    {
        $row = StrukturOrganisasi::create([
            'peran' => 'pemilik_risiko',
            'nama_jabatan' => 'Pemilik Risiko',
        ]);

        $this->actingAs($this->pegawai)
            ->post('/admin/beranda/struktur', ['peran' => 'pemilik_risiko', 'nama_jabatan' => 'X'])
            ->assertForbidden();

        $this->actingAs($this->pegawai)
            ->from('/home')->put('/admin/beranda/struktur/' . $row->id, [
                'peran' => 'pemilik_risiko',
                'nama_jabatan' => 'X',
            ])
            ->assertForbidden();

        $this->actingAs($this->pegawai)
            ->from('/home')->delete('/admin/beranda/struktur/' . $row->id)
            ->assertForbidden();

        $this->actingAs($this->pegawai)
            ->post('/admin/beranda/struktur/urutan', ['urutan' => [$row->id]])
            ->assertForbidden();

        $this->assertDatabaseHas('struktur_organisasi', ['id' => $row->id, 'nama_jabatan' => 'Pemilik Risiko']);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->post('/admin/beranda/struktur', ['peran' => 'pemilik_risiko', 'nama_jabatan' => 'X'])
            ->assertRedirect('/login');
    }

    // ====================
    // Endpoint admin: galeri
    // ====================

    public function test_admin_bisa_mengunggah_gambar_galeri(): void
    {
        $file = UploadedFile::fake()->image('ruang.jpg', 1600, 1200);

        $res = $this->actingAs($this->admin)->from('/home')->post('/admin/beranda/galeri', [
            'judul' => 'Ruang Rapat',
            'keterangan' => 'Ruang rapat bidang',
            'gambar' => $file,
        ]);

        $res->assertRedirect('/home');
        $res->assertSessionHasNoErrors();

        $this->assertDatabaseCount('galeri_sarana', 1);
        $this->assertDatabaseHas('galeri_sarana', ['judul' => 'Ruang Rapat']);

        $galeri = GaleriSarana::first();
        Storage::disk('public')->assertExists($galeri->path);
    }

    public function test_galeri_kosong_menampilkan_pesan(): void
    {
        $this->get('/home')->assertSee('Belum Ada Gambar');
    }

    public function test_carousel_menampilkan_semua_gambar(): void
    {
        GaleriSarana::create(['path' => 'galeri-sarana/a.jpg', 'judul' => 'Ruang Rapat', 'urutan' => 1]);
        GaleriSarana::create(['path' => 'galeri-sarana/b.jpg', 'judul' => 'Perangkat Komputer', 'urutan' => 2]);

        $res = $this->get('/home');

        $res->assertOk();
        $res->assertSee('data-total="2"', false);
        $res->assertSee('Ruang Rapat');
        $res->assertSee('Perangkat Komputer');
    }

    public function test_gambar_wajib_diisi_saat_menambah(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/beranda/galeri', ['judul' => 'Tanpa Gambar'])
            ->assertSessionHasErrors('gambar');

        $this->assertDatabaseCount('galeri_sarana', 0);
    }

    public function test_gambar_bukan_gambar_ditolak(): void
    {
        $file = UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf');

        $this->actingAs($this->admin)
            ->post('/admin/beranda/galeri', ['judul' => 'X', 'gambar' => $file])
            ->assertSessionHasErrors('gambar');
    }

    public function test_admin_bisa_mengubah_judul_tanpa_mengganti_gambar(): void
    {
        $file = UploadedFile::fake()->image('lama.jpg', 800, 600);
        $this->actingAs($this->admin)->from('/home')->post('/admin/beranda/galeri', [
            'judul' => 'Judul Lama',
            'gambar' => $file,
        ]);
        $galeri = GaleriSarana::first();
        $pathLama = $galeri->path;

        $this->actingAs($this->admin)->from('/home')->put('/admin/beranda/galeri/' . $galeri->id, [
            'judul' => 'Judul Baru',
        ])->assertRedirect('/home');

        $galeri->refresh();
        $this->assertSame('Judul Baru', $galeri->judul);
        // Hanya judul yang berubah, berkas gambar lama harus tetap utuh.
        $this->assertSame($pathLama, $galeri->path);
        Storage::disk('public')->assertExists($pathLama);
    }

    public function test_mengganti_gambar_menghapus_file_lama(): void
    {
        $this->actingAs($this->admin)->from('/home')->post('/admin/beranda/galeri', [
            'judul' => 'Awal',
            'gambar' => UploadedFile::fake()->image('lama.jpg', 800, 600),
        ]);
        $galeri = GaleriSarana::first();
        $pathLama = $galeri->path;

        $this->actingAs($this->admin)->from('/home')->put('/admin/beranda/galeri/' . $galeri->id, [
            'judul' => 'Awal',
            'gambar' => UploadedFile::fake()->image('baru.jpg', 800, 600),
        ])->assertRedirect('/home');

        $galeri->refresh();
        Storage::disk('public')->assertMissing($pathLama);
        Storage::disk('public')->assertExists($galeri->path);
    }

    public function test_admin_bisa_menghapus_gambar_dan_berkasnya(): void
    {
        $this->actingAs($this->admin)->from('/home')->post('/admin/beranda/galeri', [
            'judul' => 'Akan dihapus',
            'gambar' => UploadedFile::fake()->image('hapus.jpg', 800, 600),
        ]);
        $galeri = GaleriSarana::first();
        $path = $galeri->path;

        $this->actingAs($this->admin)
            ->from('/home')->delete('/admin/beranda/galeri/' . $galeri->id)
            ->assertRedirect('/home');

        $this->assertDatabaseCount('galeri_sarana', 0);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_admin_bisa_mengurutkan_ulang_galeri(): void
    {
        $a = GaleriSarana::create(['path' => 'galeri-sarana/a.jpg', 'judul' => 'A', 'urutan' => 1]);
        $b = GaleriSarana::create(['path' => 'galeri-sarana/b.jpg', 'judul' => 'B', 'urutan' => 2]);

        $this->actingAs($this->admin)->from('/home')->post('/admin/beranda/galeri/urutan', [
            'urutan' => [$b->id, $a->id],
        ])->assertRedirect('/home');

        $this->assertSame(1, GaleriSarana::find($b->id)->urutan);
        $this->assertSame(2, GaleriSarana::find($a->id)->urutan);
    }

    public function test_pegawai_tidak_boleh_mengelola_galeri(): void
    {
        $galeri = GaleriSarana::create(['path' => 'galeri-sarana/a.jpg', 'judul' => 'A']);

        $this->actingAs($this->pegawai)
            ->post('/admin/beranda/galeri', [
                'judul' => 'X',
                'gambar' => UploadedFile::fake()->image('x.jpg', 100, 100),
            ])
            ->assertForbidden();

        $this->actingAs($this->pegawai)
            ->from('/home')->delete('/admin/beranda/galeri/' . $galeri->id)
            ->assertForbidden();

        $this->actingAs($this->pegawai)
            ->post('/admin/beranda/galeri/urutan', ['urutan' => [$galeri->id]])
            ->assertForbidden();

        $this->assertDatabaseHas('galeri_sarana', ['id' => $galeri->id, 'judul' => 'A']);
    }

    // ====================
    // Tampilan khusus admin
    // ====================

    public function test_admin_melihat_tombol_kelola(): void
    {
        $res = $this->actingAs($this->admin)->get('/home');

        $res->assertOk();
        $res->assertSee('Tambah Peran');
        $res->assertSee('Unggah Gambar');
        $res->assertSee('bukaModalStruktur');
    }

    public function test_pegawai_tidak_melihat_tombol_kelola(): void
    {
        $res = $this->actingAs($this->pegawai)->get('/home');

        $res->assertOk();
        $res->assertDontSee('Tambah Peran');
        $res->assertDontSee('bukaModalStruktur');
        $res->assertDontSee('modalStruktur', false);
    }
}
