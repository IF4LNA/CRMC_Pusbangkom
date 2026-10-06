<?php

namespace Tests\Feature\Beranda;

use App\Models\Bidang;
use App\Models\GaleriSarana;
use App\Models\GambarStruktur;
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
 * unggahan gambar bisa diperiksa tanpa menyentuh storage yang sebenarnya.
 *
 * Catatan: section "Struktur Organize" diuji lewat gambar bagan, bukan lewat
 * kartu orang. Form datanya sudah tidak ada lagi (lihat HomeController).
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

    public function test_navbar_beranda_sama_dengan_navigasi_dashboard(): void
    {
        // Navigasi utama diambil dari satu partial, jadi halaman Beranda dan
        // dashboard CRMC harus memuat tautan yang sama persis.
        $ambil = fn (string $html) => $this->parseNavLinks($html);

        $beranda = $ambil($this->get('/home')->getContent());
        $dashboard = $ambil($this->get('/')->getContent());

        $this->assertNotEmpty($beranda);
        $this->assertSame($beranda, $dashboard);
    }

    public function test_navbar_memuat_tautan_sop(): void
    {
        $res = $this->get('/home');

        $res->assertOk();
        $res->assertSee('href="' . route('sop.index') . '"', false);
    }

    public function test_dashboard_lama_tetap_di_slash(): void
    {
        $this->get('/')->assertOk();
    }

    /**
     * Kumpulkan href dari elemen nav utama (#navUtama) supaya dua halaman
     * bisa dibandingkan tanpa ikut menghitung markup di luar navigasi.
     */
    private function parseNavLinks(string $html): array
    {
        if (!preg_match('/<nav id="navUtama".*?<\/nav>/s', $html, $blok)) {
            return [];
        }

        preg_match_all('/href="([^"]+)"/', $blok[0], $cocok);

        return $cocok[1];
    }

    // ====================
    // Gambar struktur organisasi
    // ====================

    public function test_bagan_kosong_menampilkan_pesan(): void
    {
        $res = $this->get('/home');

        $res->assertOk();
        $res->assertSee('Bagan Belum Diunggah');
    }

    public function test_bagan_yang_diunggah_ditampilkan(): void
    {
        GambarStruktur::create([
            'path' => 'struktur-organisasi/bagan.jpg',
            'keterangan' => 'Bagan per 1 Januari 2026',
        ]);

        $res = $this->get('/home');

        $res->assertOk();
        $res->assertDontSee('Bagan Belum Diunggah');
        $res->assertSee('struktur-organisasi/bagan.jpg');
        $res->assertSee('Bagan per 1 Januari 2026');
    }

    public function test_halaman_beranda_tidak_lagi_menampilkan_form_peran(): void
    {
        $res = $this->get('/home');

        $res->assertOk();
        $res->assertDontSee('Tambah Peran');
        $res->assertDontSee('modalStruktur', false);
    }

    // ====================
    // Endpoint admin: gambar struktur
    // ====================

    public function test_admin_bisa_mengunggah_gambar_struktur(): void
    {
        $res = $this->actingAs($this->admin)->from('/home')->post('/admin/beranda/struktur/gambar', [
            'gambar' => UploadedFile::fake()->image('bagan.jpg', 1600, 1200),
            'keterangan' => 'Bagan awal',
        ]);

        $res->assertRedirect('/home');
        $res->assertSessionHasNoErrors();

        $this->assertDatabaseCount('gambar_struktur', 1);
        $this->assertDatabaseHas('gambar_struktur', ['keterangan' => 'Bagan awal']);

        Storage::disk('public')->assertExists(GambarStruktur::first()->path);
    }

    public function test_gambar_struktur_wajib_diisi(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/beranda/struktur/gambar', [])
            ->assertSessionHasErrors('gambar');

        $this->assertDatabaseCount('gambar_struktur', 0);
    }

    public function test_gambar_struktur_bukan_gambar_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/beranda/struktur/gambar', [
                'gambar' => UploadedFile::fake()->create('bagan.pdf', 20, 'application/pdf'),
            ])
            ->assertSessionHasErrors('gambar');

        $this->assertDatabaseCount('gambar_struktur', 0);
    }

    public function test_menggambar_struktur_menggantikan_yang_lama(): void
    {
        $this->actingAs($this->admin)->from('/home')->post('/admin/beranda/struktur/gambar', [
            'gambar' => UploadedFile::fake()->image('lama.jpg', 800, 600),
        ]);
        $pathLama = GambarStruktur::first()->path;

        $this->actingAs($this->admin)->from('/home')->post('/admin/beranda/struktur/gambar', [
            'gambar' => UploadedFile::fake()->image('baru.jpg', 800, 600),
            'keterangan' => 'Bagan baru',
        ])->assertRedirect('/home');

        // Hanya satu bagan yang boleh aktif, dan file lamanya harus hilang.
        $this->assertDatabaseCount('gambar_struktur', 1);
        Storage::disk('public')->assertMissing($pathLama);
        Storage::disk('public')->assertExists(GambarStruktur::first()->path);
        $this->assertSame('Bagan baru', GambarStruktur::first()->keterangan);
    }

    public function test_admin_bisa_menghapus_gambar_struktur_dan_berkasnya(): void
    {
        $this->actingAs($this->admin)->from('/home')->post('/admin/beranda/struktur/gambar', [
            'gambar' => UploadedFile::fake()->image('hapus.jpg', 800, 600),
        ]);
        $path = GambarStruktur::first()->path;

        $this->actingAs($this->admin)
            ->from('/home')->delete('/admin/beranda/struktur/gambar')
            ->assertRedirect('/home');

        $this->assertDatabaseCount('gambar_struktur', 0);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_pegawai_tidak_boleh_mengelola_gambar_struktur(): void
    {
        GambarStruktur::create(['path' => 'struktur-organisasi/ada.jpg']);

        $this->actingAs($this->pegawai)
            ->post('/admin/beranda/struktur/gambar', [
                'gambar' => UploadedFile::fake()->image('x.jpg', 100, 100),
            ])
            ->assertForbidden();

        $this->actingAs($this->pegawai)
            ->from('/home')->delete('/admin/beranda/struktur/gambar')
            ->assertForbidden();

        $this->assertDatabaseCount('gambar_struktur', 1);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->post('/admin/beranda/struktur/gambar', [
            'gambar' => UploadedFile::fake()->image('x.jpg', 100, 100),
        ])->assertRedirect('/login');
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
        $res->assertSee('Unggah Gambar');
        $res->assertSee('formGambarStruktur', false);
        $res->assertSee('bukaModalGaleri', false);
    }

    public function test_pegawai_tidak_melihat_tombol_kelola(): void
    {
        $res = $this->actingAs($this->pegawai)->get('/home');

        $res->assertOk();
        $res->assertDontSee('formGambarStruktur', false);
        $res->assertDontSee('bukaModalGaleri', false);
    }
}
