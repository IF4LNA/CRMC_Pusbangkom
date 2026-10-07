<?php

namespace Tests\Feature\Crmc;

use App\Models\Bidang;
use App\Models\PenugasanCrmc;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Uji dua fitur yang saling terkait dengan data yang sudah ada:
 *
 *  1. Pencarian di halaman Kelola Akun Pegawai.
 *  2. Penugasan Komponen 1 yang disimpan per tahun, karena identitas
 *     pegawai beserta PIC-nya berbeda tiap tahun.
 */
class KelolaPegawaiPenugasanTahunanTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $pegawai;

    private SubMenu $subMenu;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Siti Aminah',
            'nip' => '197001012026011001',
            'jabatan' => 'Kepala Pusat',
        ]);

        $this->pegawai = User::factory()->create([
            'role' => 'pegawai',
            'name' => 'Budi Santoso',
            'nip' => '198501012026011002',
            'jabatan' => 'Staf Pelaksana',
        ]);

        $bidang = Bidang::create(['nama_bidang' => 'Bagian Umum Program & Tata Usaha']);

        $this->subMenu = SubMenu::create([
            'bidang_id' => $bidang->id,
            'nama_sub_menu' => 'Manajemen Risiko',
        ]);
    }

    // ====================
    // Kelola pegawai: pencarian
    // ====================

    // Catatan: nama admin yang sedang login selalu muncul di navbar, jadi
    // pengujian tidak boleh memakai assertDontSee atas namanya. Yang dipakai
    // adalah jumlah baris hasil filter pada badge "X dari Y".

    public function test_halaman_kelola_pegawai_mencari_berdasarkan_nama(): void
    {
        $res = $this->actingAs($this->admin)->get('/admin/pegawai?q=Budi');

        $res->assertStatus(200);
        $res->assertSee('Budi Santoso');
        $res->assertSee('1 dari 2');
    }

    public function test_pencarian_kelola_pegawai_mencakup_nip_jabatan_dan_email(): void
    {
        $res = $this->actingAs($this->admin)->get('/admin/pegawai?q=19850101');
        $res->assertStatus(200)->assertSee('Budi Santoso')->assertSee('1 dari 2');

        $res = $this->actingAs($this->admin)->get('/admin/pegawai?q=' . urlencode('Staf Pelaksana'));
        $res->assertStatus(200)->assertSee('Budi Santoso')->assertSee('1 dari 2');

        $email = substr((string) $this->pegawai->email, 0, 6);
        $res = $this->actingAs($this->admin)->get('/admin/pegawai?q=' . urlencode($email));
        $res->assertStatus(200)->assertSee('Budi Santoso')->assertSee('1 dari 2');
    }

    public function test_pencarian_kelola_pegawai_bisa_disaring_per_role(): void
    {
        $res = $this->actingAs($this->admin)->get('/admin/pegawai?peran=admin');

        $res->assertStatus(200);
        $res->assertSee('1 dari 2');

        $res = $this->actingAs($this->admin)->get('/admin/pegawai?peran=pegawai');
        $res->assertStatus(200);
        $res->assertSee('Budi Santoso');
        $res->assertSee('1 dari 2');
    }

    public function test_tanpa_kata_kunci_seluruh_pegawai_tampil(): void
    {
        $res = $this->actingAs($this->admin)->get('/admin/pegawai');

        $res->assertStatus(200);
        $res->assertSee('Siti Aminah');
        $res->assertSee('Budi Santoso');
    }

    public function test_kolom_statistik_tetap_menampilkan_seluruh_pegawai_saat_pencarian(): void
    {
        $res = $this->actingAs($this->admin)->get('/admin/pegawai?q=Budi');

        $res->assertStatus(200);

        // Baris tabel boleh terfilter (1 dari 2), tapi kartu statistik
        // harus tetap melaporkan seluruh pegawai (2 orang).
        $res->assertSee('1 dari 2');

        $html = $res->getContent();
        $this->assertMatchesRegularExpression(
            '/Total Pegawai<\/p>\s*<p class="stat-value[^"]*">\s*2\s*</',
            $html,
            'Kartu "Total Pegawai" harus tetap 2 walau pencarian aktif.'
        );
    }

    public function test_pencarian_tidak_memperlukai_penggunaan_wildcard_like(): void
    {
        // "%" harus dicari apa adanya, bukan jadi pola LIKE yang
        // mengembalikan semua baris.
        $res = $this->actingAs($this->admin)->get('/admin/pegawai?q=' . urlencode('%'));

        $res->assertStatus(200);
        $res->assertSee('Tidak ada pegawai yang cocok');
    }

    public function test_pegawai_tidak_bisa_membuka_halaman_kelola_pegawai(): void
    {
        $this->actingAs($this->pegawai)->get('/admin/pegawai')->assertStatus(403);
    }

    // ====================
    // Komponen 1: penugasan per tahun
    // ====================

    private function simpanPenugasan(int $tahun, array $pilihan = []): \Illuminate\Testing\TestResponse
    {
        $data = array_merge([
            'pemilik_risiko_id' => $this->admin->id,
            'pengendali_mutu_id' => $this->admin->id,
            'pengendali_risiko_ids' => [$this->pegawai->id],
            'tahun_pelaksanaan' => $tahun,
        ], $pilihan);

        return $this->actingAs($this->admin)
            ->post('/crmc/manajemen-risiko/penugasan', $data);
    }

    public function test_penugasan_tersimpan_dengan_tahunnya(): void
    {
        $res = $this->simpanPenugasan(2026);

        $res->assertStatus(302);

        $this->assertGreaterThan(
            0,
            PenugasanCrmc::tahun(2026)->count(),
            'Penugasan harus tersimpan dengan tahun 2026.'
        );
        $this->assertSame(
            0,
            PenugasanCrmc::tahun(2025)->count(),
            'Mengisi 2026 tidak boleh membuat baris tahun lain.'
        );
    }

    public function test_penugasan_tahun_baru_tidak_menghapus_tahun_lama(): void
    {
        $this->simpanPenugasan(2026);
        $jumlah2026 = PenugasanCrmc::tahun(2026)->count();

        $this->simpanPenugasan(2027);

        $this->assertSame(
            $jumlah2026,
            PenugasanCrmc::tahun(2026)->count(),
            'Penugasan 2026 harus tetap utuh setelah mengisi 2027.'
        );
        $this->assertGreaterThan(0, PenugasanCrmc::tahun(2027)->count());
    }

    public function test_komponen_satu_menampilkan_penugasan_tahun_yang_dibuka(): void
    {
        $this->simpanPenugasan(2026, [
            'pemilik_risiko_id' => $this->admin->id,
            'pengendali_risiko_ids' => [$this->pegawai->id],
        ]);

        // Tahun 2027 sengaja belum diisi: kartu Komponen 1 harus
        // menampilkan empty state, bukan penugasan 2026. Daftar nama di
        // form penugasan admin memang memuat seluruh pegawai, jadi yang
        // diperiksa adalah EMPTY STATE-nya.
        $res = $this->get('/crmc/manajemen-risiko?tahun=2027');

        $res->assertStatus(200);
        $res->assertSee('Tahun 2027');
        $res->assertSee('Pemilik Risiko belum ditugaskan');
        $res->assertSee('Pengendali Mutu belum ditugaskan');
        $res->assertSee('Belum ada staf pengendali risiko yang ditugaskan');

        // Dan penugasan tahun lain tidak ikut terbaca.
        $this->assertSame(0, PenugasanCrmc::tahun(2027)->count());
    }

    public function test_halaman_menampilkan_penugasan_lama_pada_tahunnya(): void
    {
        $this->simpanPenugasan(2026, ['pengendali_risiko_ids' => [$this->pegawai->id]]);

        $res = $this->get('/crmc/manajemen-risiko?tahun=2026');

        $res->assertStatus(200);
        $res->assertSee('Budi Santoso');
        $res->assertSee('Tahun 2026');
        $res->assertSee('Tahun Pelaksanaan Penugasan');
    }

    public function test_form_penugasan_menampilkan_kolom_tahun(): void
    {
        $res = $this->actingAs($this->admin)->get('/crmc/manajemen-risiko');

        $res->assertStatus(200);
        $res->assertSee('Tahun Pelaksanaan Penugasan');
        $res->assertSee('name="tahun_pelaksanaan"', false);
    }

    public function test_tahun_penugasan_masuk_daftar_tahun_halaman(): void
    {
        // 2035 tidak ada dokumen, tidak ada tahun manual, dan bukan tahun
        // berjalan: satu-satunya sumbernya adalah penugasan.
        $this->simpanPenugasan(2035);

        $res = $this->get('/crmc/manajemen-risiko?tahun=2035');

        $res->assertStatus(200);
        $res->assertSee('2035');
        $res->assertSee('Budi Santoso');
    }

    public function test_penugasan_menolak_tahun_tidak_valid(): void
    {
        $res = $this->simpanPenugasan(1800);

        $res->assertSessionHasErrors('tahun_pelaksanaan');
        $this->assertSame(0, PenugasanCrmc::count());
    }

    public function test_pegawai_tidak_boleh_mengubah_penugasan(): void
    {
        $res = $this->actingAs($this->pegawai)->post('/crmc/manajemen-risiko/penugasan', [
            'pemilik_risiko_id' => $this->admin->id,
            'pengendali_mutu_id' => $this->admin->id,
            'pengendali_risiko_ids' => [$this->pegawai->id],
            'tahun_pelaksanaan' => 2026,
        ]);

        $res->assertStatus(403);
        $this->assertSame(0, PenugasanCrmc::count());
    }

    public function test_menyimpan_dua_kali_tahun_sama_tidak_menggandakan_baris(): void
    {
        $this->simpanPenugasan(2026);
        $jumlahPertama = PenugasanCrmc::tahun(2026)->count();

        $this->simpanPenugasan(2026);

        $this->assertSame(
            $jumlahPertama,
            PenugasanCrmc::tahun(2026)->count(),
            'Simpan ulang tahun yang sama tidak boleh menambah baris.'
        );
    }

    // ====================
    // Kartu sub-bidang: gaya banner & urutan
    // ====================

    public function test_kartu_sub_bidang_berwarna_biru_sesuai_banner(): void
    {
        $res = $this->actingAs($this->admin)->get('/dashboard');

        $res->assertStatus(200);
        $res->assertSee('sub-bidang-banner', false);
    }

    public function test_kartu_sub_bidang_tidak_lagi_memakai_tombol_form_input_edit(): void
    {
        $res = $this->actingAs($this->admin)->get('/dashboard');

        $res->assertStatus(200);
        $res->assertDontSee('openCRMCModal');
        // Dua aksi yang tersisa: ubah dan hapus.
        $res->assertSee('bukaModalSubBidang', false);
        $res->assertSee('konfirmasiHapusSubBidang', false);
    }

    public function test_urutan_kartu_sub_bidang_mengikuti_urutan_database(): void
    {
        // Nama sengaja dibuat terbalik secara alfabetis agar urutannya
        // terlihat jelas salah kalau diurutkan berdasarkan nama.
        $zulu = SubMenu::create([
            'bidang_id' => $this->subMenu->bidang_id,
            'nama_sub_menu' => 'Zulu Manajemen',
        ]);
        $alpha = SubMenu::create([
            'bidang_id' => $this->subMenu->bidang_id,
            'nama_sub_menu' => 'Alpha exceedingly',
        ]);

        $res = $this->actingAs($this->admin)->get('/dashboard');

        $res->assertStatus(200);

        $html = $res->getContent();
        $posisiZulu = strpos($html, 'Zulu Manajemen');
        $posisiAlpha = strpos($html, 'Alpha exceedingly');

        $this->assertNotFalse($posisiZulu, 'Kartu "Zulu Manajemen" harus tampil.');
        $this->assertNotFalse($posisiAlpha, 'Kartu "Alpha exceedingly" harus tampil.');
        $this->assertLessThan(
            $posisiAlpha,
            $posisiZulu,
            'Sub-bidang harus tampil sesuai urutan database (id), bukan alfabetis.'
        );
    }

    public function test_mengganti_gambar_latar_sub_bidang(): void
    {
        $awal = UploadedFile::fake()->image('awal.jpg');
        $res = $this->actingAs($this->admin)->put('/bidang/sub-bidang/' . $this->subMenu->id, [
            'bidang_id' => $this->subMenu->bidang_id,
            'nama_sub_menu' => 'Manajemen Risiko',
            'gambar_latar' => $awal,
        ]);

        $res->assertStatus(302);
        $this->subMenu->refresh();
        $this->assertNotNull($this->subMenu->gambar_latar);
        Storage::disk('public')->assertExists($this->subMenu->gambar_latar);

        $pathPertama = $this->subMenu->gambar_latar;

        // Ganti dengan gambar lain: file lama harus ikut terhapus.
        $baru = UploadedFile::fake()->image('baru.jpg');
        $this->actingAs($this->admin)->put('/bidang/sub-bidang/' . $this->subMenu->id, [
            'bidang_id' => $this->subMenu->bidang_id,
            'nama_sub_menu' => 'Manajemen Risiko',
            'gambar_latar' => $baru,
        ])->assertStatus(302);

        $this->subMenu->refresh();
        $this->assertNotSame($pathPertama, $this->subMenu->gambar_latar);
        Storage::disk('public')->assertMissing($pathPertama);
        Storage::disk('public')->assertExists($this->subMenu->gambar_latar);
    }

    public function test_menghapus_gambar_latar_sub_bidang(): void
    {
        $this->actingAs($this->admin)->put('/bidang/sub-bidang/' . $this->subMenu->id, [
            'bidang_id' => $this->subMenu->bidang_id,
            'nama_sub_menu' => 'Manajemen Risiko',
            'gambar_latar' => UploadedFile::fake()->image('awal.jpg'),
        ])->assertStatus(302);

        $this->subMenu->refresh();
        $pathLama = $this->subMenu->gambar_latar;

        $this->actingAs($this->admin)->put('/bidang/sub-bidang/' . $this->subMenu->id, [
            'bidang_id' => $this->subMenu->bidang_id,
            'nama_sub_menu' => 'Manajemen Risiko',
            'hapus_gambar_latar' => 1,
        ])->assertStatus(302);

        $this->subMenu->refresh();
        $this->assertNull($this->subMenu->gambar_latar);
        Storage::disk('public')->assertMissing($pathLama);
    }

    public function test_mengosongkan_kolom_gambar_tetap_mempertahankan_gambar_lama(): void
    {
        $this->actingAs($this->admin)->put('/bidang/sub-bidang/' . $this->subMenu->id, [
            'bidang_id' => $this->subMenu->bidang_id,
            'nama_sub_menu' => 'Manajemen Risiko',
            'gambar_latar' => UploadedFile::fake()->image('awal.jpg'),
        ])->assertStatus(302);

        $this->subMenu->refresh();
        $pathLama = $this->subMenu->gambar_latar;

        $this->actingAs($this->admin)->put('/bidang/sub-bidang/' . $this->subMenu->id, [
            'bidang_id' => $this->subMenu->bidang_id,
            'nama_sub_menu' => 'Manajemen Risiko',
        ])->assertStatus(302);

        $this->subMenu->refresh();
        $this->assertSame($pathLama, $this->subMenu->gambar_latar);
        Storage::disk('public')->assertExists($pathLama);
    }

    public function test_pratinjau_gambar_latar_ada_di_modal_ubah(): void
    {
        $this->actingAs($this->admin)->put('/bidang/sub-bidang/' . $this->subMenu->id, [
            'bidang_id' => $this->subMenu->bidang_id,
            'nama_sub_menu' => 'Manajemen Risiko',
            'gambar_latar' => UploadedFile::fake()->image('awal.jpg'),
        ])->assertStatus(302);

        $res = $this->actingAs($this->admin)->get('/dashboard');

        $res->assertStatus(200);
        $res->assertSee('pratinjauLatarSubBidang', false);
        $res->assertSee('hapus_gambar_latar', false);
    }

    // ==================================================
    // Tombol pada kartu: JSON di dalam onclick
    // ==================================================

    /**
     * Menempelkan JSON ke atribut onclick membuatnya mati: parser HTML
     * menutup atribut di kutip pertama, dan sisa JSON diperlakukan sebagai
     * atribut lain. Tombolnya masih terlihat, jadi salahnya tidak terlihat
     * sampai admin benar-benar mengklik.
     */
    public function test_atribut_onclick_tombol_kartu_tidak_memuat_json(): void
    {
        $res = $this->actingAs($this->admin)->get('/dashboard');

        $res->assertStatus(200);

        preg_match_all('/onclick="[^"]*"/', $res->getContent(), $cocok);

        $denganJson = [];
        foreach ($cocok[0] as $atribut) {
            if (!str_contains($atribut, 'bukaModalSubBidang') && !str_contains($atribut, 'konfirmasiHapusSubBidang')) {
                continue;
            }
            if (str_contains($atribut, '{"') || str_contains($atribut, '{&quot;')) {
                $denganJson[] = $atribut;
            }
        }

        $this->assertSame(
            [],
            $denganJson,
            "Ada tombol yang menyisipkan JSON ke dalam onclick:\n" . implode("\n", $denganJson)
        );
    }

    public function test_tombol_kartu_membaca_data_dari_atribut_data(): void
    {
        $res = $this->actingAs($this->admin)->get('/dashboard');

        $res->assertStatus(200);
        // Data sub-bidang dibaca lewat dataset, bukan dari onclick.
        $res->assertSee('data-sub-id="' . $this->subMenu->id . '"', false);
        $res->assertSee('data-sub-nama="Manajemen Risiko"', false);
        $res->assertSee('bukaModalSubBidangSub', false);
    }

    public function test_pencarian_pegawai_ada_di_modal_identitas(): void
    {
        $res = $this->actingAs($this->admin)->get('/crmc/manajemen-risiko');

        $res->assertStatus(200);
        $res->assertSee('cariPegawaiPenugasan', false);
        $res->assertSee('daftarTimPengendali', false);
        $res->assertSee('bersihkanCariPegawai', false);
        $res->assertSee('data-cari=', false);
    }

    /**
     * Pencarian sengaja hanya untuk Lapis 3 (Tim Pengendali Risiko).
     * Lapis 1 dan 2 masing-masing memilih satu orang, jadi tidak perlu
     * filter; menyaringnya justru berisiko membuat admin tak sadar
     * menyimpan nama yang tertutup filter.
     */
    public function test_pencarian_pegawai_hanya_untuk_lapis_3(): void
    {
        $res = $this->actingAs($this->admin)->get('/crmc/manajemen-risiko');

        $res->assertStatus(200);

        $html = $res->getContent();
        $posisiLapis3 = strpos($html, '3. Tim Pengendali Risiko');
        $posisiCari = strpos($html, 'cariPegawaiPenugasan');

        $this->assertNotFalse($posisiLapis3);
        $this->assertNotFalse($posisiCari);
        $this->assertGreaterThan(
            $posisiLapis3,
            $posisiCari,
            'Kotak pencarian harus berada di bagian Lapis 3, bukan di atas Lapis 1.'
        );

        // Script tidak boleh lagi menyentuh dua <select>.
        $this->assertStringNotContainsString('const select = [', $html);
        $this->assertStringNotContainsString('inputPemilikRisiko").options', $html);
    }

    public function test_kartu_sub_bidang_gelap_dengan_teks_terang(): void
    {
        $res = $this->actingAs($this->admin)->get('/dashboard');

        $res->assertStatus(200);
        $this->assertMatchesRegularExpression(
            '/\.sub-bidang-banner\s*\{[^}]*background-color:\s*#0f172a/',
            $res->getContent(),
            'Dasar kartu harus gelap.'
        );
        $this->assertMatchesRegularExpression(
            '/text-sm font-bold text-white leading-snug/',
            $res->getContent(),
            'Teks kartu harus terang.'
        );
        $this->assertStringNotContainsString('from-amber-100', $res->getContent());
    }

    public function test_hover_navigasi_memakai_kuning(): void
    {
        $res = $this->get('/dashboard');

        $res->assertStatus(200);
        $this->assertMatchesRegularExpression(
            '/\.nav-dark \.nav-link:hover[^{]*\{[^}]*#fbbf24/',
            $res->getContent(),
            'Hover navigasi harus memakai warna kuning.'
        );
    }

    /**
     * Tombol navigasi berbasis <button> menahan status fokus setelah
     * diklik. Kalau :focus tidak diberi perlakuan sama seperti :hover,
     * tombolnya tetap menyala begitu kursor berpindah.
     */
    public function test_hover_navigasi_juga_menutupi_fokus(): void
    {
        $res = $this->get('/dashboard');

        $res->assertStatus(200);
        $this->assertMatchesRegularExpression(
            '/\.nav-dark \.nav-link:focus\s*\{[^}]*#fbbf24/',
            $res->getContent(),
            ':focus harus punya gaya yang sama dengan :hover.'
        );
    }
}