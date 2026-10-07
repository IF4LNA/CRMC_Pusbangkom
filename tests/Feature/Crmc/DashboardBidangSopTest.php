<?php

namespace Tests\Feature\Crmc;

use App\Models\Bidang;
use App\Models\DokumenCrmc;
use App\Models\LampiranCrmc;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Uji dashboard CRMC: tab per bidang, pengelolaan sub-bidang oleh admin,
 * dan halaman kumpulan SOP.
 */
class DashboardBidangSopTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $pegawai;

    private Bidang $bidangUmum;

    private Bidang $bidangSda;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = $this->buatUser('admin', 'Admin Uji');
        $this->pegawai = $this->buatUser('pegawai', 'Pegawai Uji');

        $this->bidangUmum = Bidang::create(['nama_bidang' => 'Bagian Umum Program & Tata Usaha']);
        $this->bidangSda = Bidang::create(['nama_bidang' => 'Bidang SDA']);
    }

    private function buatUser(string $role, string $nama): User
    {
        static $urut = 0;
        $urut++;

        return User::factory()->create([
            'role' => $role,
            'name' => $nama,
            'nip' => 'NIP' . str_pad((string) $urut, 8, '0', STR_PAD_LEFT),
            'jabatan' => 'Jabatan Uji',
        ]);
    }

    // ====================
    // Dashboard: tab per bidang
    // ====================

    public function test_setiap_bidang_punya_tab_bernomor(): void
    {
        SubMenu::create(['bidang_id' => $this->bidangUmum->id, 'nama_sub_menu' => 'Manajemen Risiko']);
        SubMenu::create(['bidang_id' => $this->bidangSda->id, 'nama_sub_menu' => 'Bangkom SDA']);

        $res = $this->get('/dashboard');

        $res->assertOk();
        $res->assertSee('id="tab-bidang-' . $this->bidangUmum->id . '"', false);
        $res->assertSee('id="tab-bidang-' . $this->bidangSda->id . '"', false);
        $res->assertSee('Manajemen Risiko');
        $res->assertSee('Bangkom SDA');
    }

    public function test_navbar_menampilkan_nama_bidang_dan_jumlah_sub_bidang(): void
    {
        SubMenu::create(['bidang_id' => $this->bidangSda->id, 'nama_sub_menu' => 'Bangkom SDA']);
        SubMenu::create(['bidang_id' => $this->bidangSda->id, 'nama_sub_menu' => 'Kurikulum SDA']);

        $res = $this->get('/dashboard');

        $res->assertOk();
        $res->assertSee('switchTab(\'bidang-' . $this->bidangSda->id . '\')', false);
        $res->assertSee('id="nav-bidang-' . $this->bidangSda->id . '"', false);
    }

    public function test_sub_bidang_tanpa_gambar_latar_tetap_ditampilkan(): void
    {
        SubMenu::create(['bidang_id' => $this->bidangSda->id, 'nama_sub_menu' => 'Tanpa Gambar']);

        $res = $this->get('/dashboard');

        $res->assertOk();
        $res->assertSee('Tanpa Gambar');
        // Tidak ada gambar latarnya, jadi tidak boleh ada <img> untuk kartu itu.
        $res->assertDontSee('latar-sub-bidang', false);
    }

    // ====================
    // Admin: kelola sub-bidang
    // ====================

    public function test_admin_bisa_menambah_sub_bidang(): void
    {
        $res = $this->actingAs($this->admin)->from('/dashboard')->post('/bidang/sub-bidang', [
            'bidang_id' => $this->bidangSda->id,
            'nama_sub_menu' => 'Monitoring  E-Learning',
        ]);

        $res->assertRedirect('/dashboard');
        $res->assertSessionHasNoErrors();

        $this->assertDatabaseHas('sub_menu', [
            'bidang_id' => $this->bidangSda->id,
            // Spasi ganda harus dirapatkan supaya slug-nya konsisten.
            'nama_sub_menu' => 'Monitoring E-Learning',
        ]);
    }

    public function test_admin_bisa_menambah_gambar_latar_sub_bidang(): void
    {
        $this->actingAs($this->admin)->from('/dashboard')->post('/bidang/sub-bidang', [
            'bidang_id' => $this->bidangSda->id,
            'nama_sub_menu' => 'Dengan Gambar',
            'gambar_latar' => UploadedFile::fake()->image('latar.jpg', 1200, 800),
        ])->assertRedirect('/dashboard');

        $sub = SubMenu::firstWhere('nama_sub_menu', 'Dengan Gambar');
        $this->assertNotNull($sub);
        $this->assertNotNull($sub->gambar_latar);
        Storage::disk('public')->assertExists($sub->gambar_latar);

        $res = $this->actingAs($this->admin)->get('/dashboard');
        $res->assertOk();
        $res->assertSee($sub->gambar_latar);
    }

    public function test_gambar_latar_bukan_gambar_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->post('/bidang/sub-bidang', [
                'bidang_id' => $this->bidangSda->id,
                'nama_sub_menu' => 'Salah Ekstensi',
                'gambar_latar' => UploadedFile::fake()->create('dok.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors('gambar_latar');

        $this->assertDatabaseCount('sub_menu', 0);
    }

    public function test_bidang_tak_dikenal_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->post('/bidang/sub-bidang', ['bidang_id' => 9999, 'nama_sub_menu' => 'X'])
            ->assertSessionHasErrors('bidang_id');
    }

    public function test_nama_sub_bidang_wajib_diisi(): void
    {
        $this->actingAs($this->admin)
            ->post('/bidang/sub-bidang', ['bidang_id' => $this->bidangSda->id])
            ->assertSessionHasErrors('nama_sub_menu');
    }

    public function test_mengubah_sub_bidang_tanpa_gambar_baru_mempertahankan_gambar_lama(): void
    {
        $this->actingAs($this->admin)->from('/dashboard')->post('/bidang/sub-bidang', [
            'bidang_id' => $this->bidangSda->id,
            'nama_sub_menu' => 'Awal',
            'gambar_latar' => UploadedFile::fake()->image('lama.jpg', 800, 600),
        ]);

        $sub = SubMenu::first();
        $pathLama = $sub->gambar_latar;

        $this->actingAs($this->admin)->from('/dashboard')->put('/bidang/sub-bidang/' . $sub->id, [
            'bidang_id' => $this->bidangSda->id,
            'nama_sub_menu' => 'Diubah',
        ])->assertRedirect('/dashboard');

        $sub->refresh();
        $this->assertSame('Diubah', $sub->nama_sub_menu);
        $this->assertSame($pathLama, $sub->gambar_latar);
        Storage::disk('public')->assertExists($pathLama);
    }

    public function test_mengganti_gambar_latar_menghapus_file_lama(): void
    {
        $this->actingAs($this->admin)->from('/dashboard')->post('/bidang/sub-bidang', [
            'bidang_id' => $this->bidangSda->id,
            'nama_sub_menu' => 'Awal',
            'gambar_latar' => UploadedFile::fake()->image('lama.jpg', 800, 600),
        ]);

        $sub = SubMenu::first();
        $pathLama = $sub->gambar_latar;

        $this->actingAs($this->admin)->from('/dashboard')->put('/bidang/sub-bidang/' . $sub->id, [
            'bidang_id' => $this->bidangSda->id,
            'nama_sub_menu' => 'Awal',
            'gambar_latar' => UploadedFile::fake()->image('baru.jpg', 800, 600),
        ])->assertRedirect('/dashboard');

        Storage::disk('public')->assertMissing($pathLama);
        Storage::disk('public')->assertExists($sub->fresh()->gambar_latar);
    }

    public function test_menghapus_sub_bidang_juga_menghapus_berkas_lampirannya(): void
    {
        $sub = SubMenu::create([
            'bidang_id' => $this->bidangSda->id,
            'nama_sub_menu' => 'Akan Dihapus',
        ]);

        $dokumen = DokumenCrmc::create([
            'sub_menu_id' => $sub->id,
            'tahun_pelaksanaan' => 2026,
        ]);

        $path = 'crmc/2026/' . $sub->id . '/sop/uji.pdf';
        Storage::disk('public')->put($path, 'isi');

        $lampiran = LampiranCrmc::create([
            'dokumen_crmc_id' => $dokumen->id,
            'kategori_komponen' => 'sop',
            'nama_file' => 'uji.pdf',
            'file_path' => Storage::disk('public')->url($path),
            'tipe_file' => 'pdf',
        ]);

        $this->actingAs($this->admin)
            ->from('/dashboard')->delete('/bidang/sub-bidang/' . $sub->id)
            ->assertRedirect('/dashboard');

        $this->assertDatabaseMissing('sub_menu', ['id' => $sub->id]);
        // Cascade di database tidak menghapus file, jadi file fisik ikut
        // harus hilang agar tidak tertinggal sampah di storage.
        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('lampiran_crmc', ['id' => $lampiran->id]);
    }

    public function test_pegawai_tidak_boleh_mengelola_sub_bidang(): void
    {
        $sub = SubMenu::create([
            'bidang_id' => $this->bidangSda->id,
            'nama_sub_menu' => 'Tidak Boleh',
        ]);

        $this->actingAs($this->pegawai)
            ->post('/bidang/sub-bidang', ['bidang_id' => $this->bidangSda->id, 'nama_sub_menu' => 'X'])
            ->assertForbidden();

        $this->actingAs($this->pegawai)
            ->put('/bidang/sub-bidang/' . $sub->id, ['bidang_id' => $this->bidangSda->id, 'nama_sub_menu' => 'X'])
            ->assertForbidden();

        $this->actingAs($this->pegawai)
            ->delete('/bidang/sub-bidang/' . $sub->id)
            ->assertForbidden();

        $this->assertDatabaseHas('sub_menu', ['id' => $sub->id, 'nama_sub_menu' => 'Tidak Boleh']);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->post('/bidang/sub-bidang', ['bidang_id' => $this->bidangSda->id, 'nama_sub_menu' => 'X'])
            ->assertRedirect('/login');
    }

    public function test_tombol_kelola_sub_bidang_hanya_untuk_admin(): void
    {
        SubMenu::create(['bidang_id' => $this->bidangSda->id, 'nama_sub_menu' => 'Contoh']);

        $this->actingAs($this->admin)->get('/dashboard')
            ->assertOk()
            ->assertSee('Tambah Sub-Bidang');

        $this->actingAs($this->pegawai)->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Tambah Sub-Bidang');
    }

    // ====================
    // Slug sub-bidang bertanda baca
    // ====================

    public function test_slug_dengan_tanda_baca_tetap_ketemu(): void
    {
        SubMenu::create([
            'bidang_id' => $this->bidangSda->id,
            'nama_sub_menu' => 'Kerjasama Pendidikan (MSS)',
        ]);

        // Slug yang dibentuk view (= normalisasiSlug) harus bisa membuka
        // halaman, bukan 404 atau membuat sub-bidang baru.
        $res = $this->get('/crmc/kerjasama-pendidikan-mss');
        $res->assertOk();
        $res->assertSee('Kerjasama Pendidikan (MSS)');

        $this->assertSame(1, SubMenu::count());
    }

    // ====================
    // Halaman SOP
    // ====================

    public function test_halaman_sop_terbuka_tanpa_login(): void
    {
        $this->get('/sop')->assertOk()->assertSee('Kumpulan Standar Operasional Prosedur');
    }

    public function test_halaman_sop_kosong_menampilkan_pesan(): void
    {
        $this->get('/sop')->assertOk()->assertSee('Belum Ada Dokumen SOP');
    }

    public function test_halaman_sop_menampilkan_dokumen_sop_dari_semua_sub_bidang(): void
    {
        $subA = SubMenu::create(['bidang_id' => $this->bidangUmum->id, 'nama_sub_menu' => 'Sub A']);
        $subB = SubMenu::create(['bidang_id' => $this->bidangSda->id, 'nama_sub_menu' => 'Sub B']);

        $this->buatSop($subA, 2026, 'SOP A.pdf');
        $this->buatSop($subB, 2025, 'SOP B.pdf');

        $res = $this->get('/sop');

        $res->assertOk();
        $res->assertSee('SOP A.pdf');
        $res->assertSee('SOP B.pdf');
        $res->assertSee('Sub A');
        $res->assertSee('Sub B');
    }

    public function test_halaman_sop_tidak_menampilkan_komponen_lain(): void
    {
        $sub = SubMenu::create(['bidang_id' => $this->bidangSda->id, 'nama_sub_menu' => 'Sub A']);

        $dokumen = DokumenCrmc::create(['sub_menu_id' => $sub->id, 'tahun_pelaksanaan' => 2026]);
        LampiranCrmc::create([
            'dokumen_crmc_id' => $dokumen->id,
            'kategori_komponen' => 'bukti_pelaksanaan',
            'nama_file' => 'bukti.jpg',
            'file_path' => 'x.jpg',
            'tipe_file' => 'jpg',
        ]);

        $this->get('/sop')->assertOk()->assertDontSee('bukti.jpg');
    }

    public function test_filter_sop_berdasarkan_bidang(): void
    {
        $subA = SubMenu::create(['bidang_id' => $this->bidangUmum->id, 'nama_sub_menu' => 'Sub A']);
        $subB = SubMenu::create(['bidang_id' => $this->bidangSda->id, 'nama_sub_menu' => 'Sub B']);

        $this->buatSop($subA, 2026, 'SOP Umum.pdf');
        $this->buatSop($subB, 2026, 'SOP SDA.pdf');

        $res = $this->get('/sop?bidang=' . $this->bidangSda->id);

        $res->assertOk();
        $res->assertSee('SOP SDA.pdf');
        $res->assertDontSee('SOP Umum.pdf');
    }

    public function test_filter_sop_berdasarkan_tahun(): void
    {
        $sub = SubMenu::create(['bidang_id' => $this->bidangSda->id, 'nama_sub_menu' => 'Sub A']);

        $this->buatSop($sub, 2025, 'SOP 2025.pdf');
        $this->buatSop($sub, 2026, 'SOP 2026.pdf');

        $res = $this->get('/sop?tahun=2025');

        $res->assertOk();
        $res->assertSee('SOP 2025.pdf');
        $res->assertDontSee('SOP 2026.pdf');
    }

    public function test_pencarian_sop_mencakup_keterangan(): void
    {
        $sub = SubMenu::create(['bidang_id' => $this->bidangSda->id, 'nama_sub_menu' => 'Sub A']);

        $dokumen = DokumenCrmc::create(['sub_menu_id' => $sub->id, 'tahun_pelaksanaan' => 2026]);

        LampiranCrmc::create([
            'dokumen_crmc_id' => $dokumen->id,
            'kategori_komponen' => 'sop',
            'nama_file' => 'dokumen.pdf',
            'keterangan' => 'Acuan Testing Bangkom',
            'file_path' => 'x.pdf',
            'tipe_file' => 'pdf',
        ]);

        $res = $this->get('/sop?q=Testing+Bangkom');

        $res->assertOk();
        $res->assertSee('dokumen.pdf');
    }

    public function test_pencarian_sop_mencakup_nama_sub_bidang(): void
    {
        $sub = SubMenu::create(['bidang_id' => $this->bidangSda->id, 'nama_sub_menu' => 'Kurikulum Unik']);

        $this->buatSop($sub, 2026, 'sop-ada.pdf');
        $subLain = SubMenu::create(['bidang_id' => $this->bidangUmum->id, 'nama_sub_menu' => 'Lain']);
        $this->buatSop($subLain, 2026, 'sop-tidak-cocok.pdf');

        $res = $this->get('/sop?q=Kurikulum');

        $res->assertOk();
        $res->assertSee('sop-ada.pdf');
        $res->assertDontSee('sop-tidak-cocok.pdf');
    }

    public function test_navbar_menampilkan_jumlah_sop(): void
    {
        $sub = SubMenu::create(['bidang_id' => $this->bidangSda->id, 'nama_sub_menu' => 'Sub A']);
        $this->buatSop($sub, 2026, 'sop-1.pdf');
        $this->buatSop($sub, 2026, 'sop-2.pdf');

        $res = $this->get('/dashboard');

        $res->assertOk();
        // Badge jumlah SOP berada tepat setelah label "SOP" di navigasi.
        $res->assertSee('SOP</span>' . "\n        <span class=\"count\">2</span>", false);
    }

    private function buatSop(SubMenu $sub, int $tahun, string $namaFile): LampiranCrmc
    {
        $dokumen = DokumenCrmc::firstOrCreate(
            ['sub_menu_id' => $sub->id, 'tahun_pelaksanaan' => $tahun]
        );

        return LampiranCrmc::create([
            'dokumen_crmc_id' => $dokumen->id,
            'kategori_komponen' => 'sop',
            'nama_file' => $namaFile,
            'file_path' => 'crmc/' . $tahun . '/' . $sub->id . '/sop/' . $namaFile,
            'tipe_file' => 'pdf',
        ]);
    }
}
