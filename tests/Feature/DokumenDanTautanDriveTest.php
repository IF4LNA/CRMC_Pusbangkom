<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\DokumenCrmc;
use App\Models\LampiranCrmc;
use App\Models\SubMenu;
use App\Models\TautanDriveCrmc;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Berkas pendukung Komponen 1, navigasi "file berikutnya" pada tiap
 * komponen, dan tautan Google Drive per Komponen 1,2,3,4,5,6,8.
 */
class DokumenDanTautanDriveTest extends TestCase
{
    use RefreshDatabase;

    private function buatSubBidang(): SubMenu
    {
        $bidang = Bidang::first() ?: Bidang::create(['nama_bidang' => 'Bidang Uji']);

        return SubMenu::create([
            'bidang_id' => $bidang->id,
            'nama_sub_menu' => 'Manajemen Risiko',
        ]);
    }

    private function buatUser(string $role = 'pegawai'): User
    {
        static $urut = 0;
        $urut++;

        return User::create([
            'name' => 'Pegawai '.$urut,
            'nip' => '1990010120260'.str_pad((string) $urut, 5, '0', STR_PAD_LEFT),
            'jabatan' => 'Staf',
            'email' => "pegawai{$urut}@pu.go.id",
            'password' => 'rahasia123',
            'role' => $role,
        ]);
    }

    // ======================================================
    // KOMPONEN 1: UNGGAH BERKAS PENDUKUNG IDENTITAS PEGAWAI
    // ======================================================

    public function test_pegawai_bisa_mengunggah_berkas_pada_komponen_1()
    {
        Storage::fake('public');
        $this->actingAs($this->buatUser());
        $this->buatSubBidang();

        $response = $this->post('/crmc/manajemen-risiko/upload-dokumen', [
            'kategori_komponen' => 'identitas_pegawai',
            'tahun_pelaksanaan' => 2026,
            'files' => [UploadedFile::fake()->create('SK-Kapus.pdf', 120, 'application/pdf')],
            'keterangan' => [0 => 'SK Kepala Pusat tahun 2026'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('lampiran_crmc', [
            'kategori_komponen' => 'identitas_pegawai',
            'nama_file' => 'SK-Kapus.pdf',
            'keterangan' => 'SK Kepala Pusat tahun 2026',
        ]);
    }

    /**
     * .xlsx adalah arsip zip. Di sebagian lingkungan (mis. Windows/Laragon)
     * fileinfo melaporkannya sebagai "application/zip" sehingga rule bawaan
     * "mimes" menolak berkas yang sebenarnya sah. Rule BerkasDokumen harus
     * tetap menerimanya lewat ekstensi aslinya.
     */
    public function test_xlsx_yang_terdeteksi_sebagai_zip_tetap_bisa_diunggah()
    {
        Storage::fake('public');
        $this->actingAs($this->buatUser());
        $this->buatSubBidang();

        $response = $this->post('/crmc/manajemen-risiko/upload-dokumen', [
            'kategori_komponen' => 'risk_register',
            'tahun_pelaksanaan' => 2026,
            'files' => [UploadedFile::fake()->create('risk-register-2026.xlsx', 80, 'application/zip')],
            'keterangan' => [0 => 'Risk register tahun 2026'],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $lampiran = LampiranCrmc::where('nama_file', 'risk-register-2026.xlsx')->first();

        $this->assertNotNull($lampiran);
        $this->assertSame('risk_register', $lampiran->kategori_komponen);
        $this->assertSame('xlsx', $lampiran->tipe_file);
        Storage::disk('public')->assertExists($lampiran->storage_path);
    }

    public function test_berkas_berekstensi_berbahaya_tetap_ditolak()
    {
        Storage::fake('public');
        $this->actingAs($this->buatUser());
        $this->buatSubBidang();

        $response = $this->post('/crmc/manajemen-risiko/upload-dokumen', [
            'kategori_komponen' => 'risk_register',
            'tahun_pelaksanaan' => 2026,
            'files' => [UploadedFile::fake()->create('shell.php', 10, 'application/octet-stream')],
        ]);

        $response->assertSessionHasErrors('files.0');
        $this->assertDatabaseCount('lampiran_crmc', 0);
    }

    public function test_komponen_1_menampilkan_berkas_yang_diunggah()
    {
        Storage::fake('public');
        $pegawai = $this->buatUser();
        $subMenu = $this->buatSubBidang();

        $dokumen = DokumenCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
        ]);

        LampiranCrmc::create([
            'dokumen_crmc_id' => $dokumen->id,
            'kategori_komponen' => 'identitas_pegawai',
            'nama_file' => 'SK-Kapus.pdf',
            'file_path' => 'lampiran/sk.pdf',
            'tipe_file' => 'pdf',
        ]);

        $response = $this->actingAs($pegawai)->get('/crmc/manajemen-risiko?tahun=2026');

        $response->assertOk();
        $response->assertSee('SK-Kapus.pdf');
        $response->assertSee('Identitas Pegawai');
    }

    // ======================================================
    // NAVIGASI FILE BERIKUTNYA (TIDAK MENUMPUK KE BAWAH)
    // ======================================================

    public function test_komponen_dengan_lebih_dari_satu_file_memakai_navigasi_file_berikutnya()
    {
        $pegawai = $this->buatUser();
        $subMenu = $this->buatSubBidang();

        $dokumen = DokumenCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
        ]);

        foreach (['SOP-1.pdf', 'SOP-2.pdf', 'SOP-3.pdf'] as $nama) {
            LampiranCrmc::create([
                'dokumen_crmc_id' => $dokumen->id,
                'kategori_komponen' => 'sop',
                'nama_file' => $nama,
                'file_path' => 'lampiran/'.$nama,
                'tipe_file' => 'pdf',
            ]);
        }

        $response = $this->actingAs($pegawai)->get('/crmc/manajemen-risiko?tahun=2026');

        $response->assertOk();
        // Penomoran file + tombol next/previous ada (akar carousel memakai
        // id "dok-" + kategori, jadi tidak bentrok dengan kode JS-nya).
        $response->assertSee('File 1 dari 3');
        $response->assertSee('id="dok-sop"', false);
        $response->assertSee('onclick="geserDokumen(', false);
        $response->assertSee('onclick="tampilDokumen(', false);
        // Ketiga file tetap ikut dirender, tidak ada yang dibuang.
        foreach (['SOP-1.pdf', 'SOP-2.pdf', 'SOP-3.pdf'] as $nama) {
            $response->assertSee($nama);
        }
    }

    public function test_komponen_tanpa_dokumen_tidak_menampilkan_navigasi_kosong()
    {
        $this->buatSubBidang();

        $response = $this->get('/crmc/manajemen-risiko');

        $response->assertOk();
        $response->assertSee('Belum ada dokumen tahun', false);
        // Tanpa berkas tidak ada carousel sama sekali, jadi tidak ada
        // tombol next/previous yang tidak berguna.
        $response->assertDontSee('id="dok-sop"', false);
        $response->assertDontSee('onclick="geserDokumen(', false);
        $response->assertDontSee('File 1 dari', false);
    }

    // ======================================================
    // TAUTAN GOOGLE DRIVE PER KOMPONEN
    // ======================================================

    public function test_pegawai_bisa_menyimpan_tautan_google_drive()
    {
        $pegawai = $this->buatUser();
        $subMenu = $this->buatSubBidang();

        $response = $this->actingAs($pegawai)->post('/crmc/manajemen-risiko/tautan-drive', [
            'kategori_komponen' => 'sop',
            'tahun_pelaksanaan' => 2026,
            'url' => 'https://drive.google.com/drive/folders/abc123',
            'label' => 'Folder SOP 2026',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tautan_drive_crmc', [
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
            'kategori_komponen' => 'sop',
            'url' => 'https://drive.google.com/drive/folders/abc123',
            'label' => 'Folder SOP 2026',
            'user_id' => $pegawai->id,
        ]);
    }

    public function test_tautan_di_luar_google_ditolak()
    {
        $pegawai = $this->buatUser();
        $this->buatSubBidang();

        $response = $this->actingAs($pegawai)->post('/crmc/manajemen-risiko/tautan-drive', [
            'kategori_komponen' => 'sop',
            'tahun_pelaksanaan' => 2026,
            'url' => 'https://contoh-internal.go.id/dokumen',
        ]);

        $response->assertSessionHasErrors('url');
        $this->assertDatabaseCount('tautan_drive_crmc', 0);
    }

    public function test_tautan_tanpa_skema_di_lengkap_dengan_https()
    {
        $this->actingAs($this->buatUser());
        $this->buatSubBidang();

        $this->post('/crmc/manajemen-risiko/tautan-drive', [
            'kategori_komponen' => 'evaluasi',
            'tahun_pelaksanaan' => 2026,
            'url' => 'docs.google.com/spreadsheets/d/xyz/edit',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tautan_drive_crmc', [
            'url' => 'https://docs.google.com/spreadsheets/d/xyz/edit',
        ]);
    }

    public function test_menyimpan_ulang_menimpa_tautan_sebelumnya()
    {
        $this->actingAs($this->buatUser());
        $subMenu = $this->buatSubBidang();

        TautanDriveCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
            'kategori_komponen' => 'sop',
            'url' => 'https://drive.google.com/drive/folders/lama',
        ]);

        $this->post('/crmc/manajemen-risiko/tautan-drive', [
            'kategori_komponen' => 'sop',
            'tahun_pelaksanaan' => 2026,
            'url' => 'https://drive.google.com/drive/folders/baru',
        ])->assertSessionHasNoErrors();

        // Satu komponen satu tautan per tahun: baris lama diperbarui,
        // bukan ditumpuk dengan baris baru.
        $this->assertDatabaseCount('tautan_drive_crmc', 1);
        $this->assertDatabaseHas('tautan_drive_crmc', [
            'url' => 'https://drive.google.com/drive/folders/baru',
        ]);
    }

    public function test_komponen_7_tidak_bisa_mempunya_tautan_drive()
    {
        $this->actingAs($this->buatUser());
        $this->buatSubBidang();

        $this->post('/crmc/manajemen-risiko/tautan-drive', [
            'kategori_komponen' => 'residu',
            'tahun_pelaksanaan' => 2026,
            'url' => 'https://drive.google.com/drive/folders/abc',
        ])->assertSessionHasErrors('kategori_komponen');

        $this->assertDatabaseCount('tautan_drive_crmc', 0);
    }

    public function test_tamu_tidak_bisa_menyimpan_tautan_drive()
    {
        $this->buatSubBidang();

        $this->post('/crmc/manajemen-risiko/tautan-drive', [
            'kategori_komponen' => 'sop',
            'tahun_pelaksanaan' => 2026,
            'url' => 'https://drive.google.com/drive/folders/abc',
        ])->assertForbidden();

        $this->assertDatabaseCount('tautan_drive_crmc', 0);
    }

    public function test_tautan_dapat_dihapus_oleh_pembuatnya()
    {
        $pegawai = $this->buatUser();
        $subMenu = $this->buatSubBidang();

        $tautan = TautanDriveCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
            'kategori_komponen' => 'sop',
            'url' => 'https://drive.google.com/drive/folders/abc',
            'user_id' => $pegawai->id,
        ]);

        $this->actingAs($pegawai)
            ->delete("/crmc/tautan-drive/{$tautan->id}")
            ->assertRedirect();

        $this->assertDatabaseCount('tautan_drive_crmc', 0);
    }

    public function test_pegawai_lain_tidak_bisa_menghapus_tautan_bukan_miliknya()
    {
        $pemilik = $this->buatUser();
        $lain = $this->buatUser();
        $subMenu = $this->buatSubBidang();

        $tautan = TautanDriveCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
            'kategori_komponen' => 'sop',
            'url' => 'https://drive.google.com/drive/folders/abc',
            'user_id' => $pemilik->id,
        ]);

        $this->actingAs($lain)
            ->delete("/crmc/tautan-drive/{$tautan->id}")
            ->assertForbidden();

        $this->actingAs($this->buatUser('admin'))
            ->delete("/crmc/tautan-drive/{$tautan->id}")
            ->assertRedirect();

        $this->assertDatabaseCount('tautan_drive_crmc', 0);
    }

    // ======================================================
    // STRUKTUR MODAL (AGAR PENYIMPANAN TIDAK GAGAL DI BROWSER)
    // ======================================================

    /**
     * Form hapus tidak boleh berada di dalam form simpan.
     *
     * HTML tidak mengizinkan dua <form> bersarang. Kalau form hapus
     * diletakkan di dalam form simpan, browser mengabaikan tag <form>
     * kedua tetapi tetap memasukkan input @method('DELETE') ke form simpan.
     * Akibatnya tombol "Simpan Tautan" mengirim permintaan DELETE dan
     * permintaan DELETE dan controller menganggapnya tidak cocok route,
     * sehingga tautan tidak pernah tersimpan. Uji ini menjaga form hapus
     * tetap berdiri sendiri.
     */
    public function test_form_hapus_tautan_berdiri_sendiri_di_luar_form_simpan()
    {
        $pegawai = $this->buatUser();
        $subMenu = $this->buatSubBidang();

        TautanDriveCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
            'kategori_komponen' => 'sop',
            'url' => 'https://drive.google.com/drive/folders/abc',
        ]);

        $html = $this->actingAs($pegawai)->get('/crmc/manajemen-risiko?tahun=2026')->getContent();

        $mulai = strpos($html, 'id="tautanDriveModal"');
        $selesai = strpos($html, 'id="keteranganModal"');
        $this->assertNotFalse($mulai, 'Modal tautan Drive tidak ditemukan di halaman.');
        $this->assertNotFalse($selesai, 'Modal keterangan tidak ditemukan di halaman.');

        $blok = substr($html, $mulai, $selesai - $mulai);

        $bukaSimpan = strpos($blok, 'id="tautanDriveForm"');
        $tutupSimpan = strpos($blok, '</form>', $bukaSimpan);
        $bukaHapus = strpos($blok, 'id="tautanDriveHapusForm"');

        $this->assertNotFalse($bukaSimpan);
        $this->assertNotFalse($tutupSimpan);
        $this->assertNotFalse($bukaHapus);

        $this->assertGreaterThan(
            $tutupSimpan,
            $bukaHapus,
            'Form hapus harus berada setelah form simpan ditutup, bukan bersarang di dalamnya.'
        );

        // Isi form simpan tidak boleh membawa _method (bisa membatalkan simpan).
        $isiFormSimpan = substr($blok, $bukaSimpan, $tutupSimpan - $bukaSimpan);
        $this->assertStringNotContainsString(
            '_method',
            $isiFormSimpan,
            'Form simpan tidak boleh berisi input _method.'
        );
    }

    public function test_kolom_tautan_menerima_input_tanpa_skema_dari_browser()
    {
        $pegawai = $this->buatUser();
        $this->buatSubBidang();

        $html = $this->actingAs($pegawai)->get('/crmc/manajemen-risiko')->getContent();

        // type="url" akan menolak tautan tanpa skema sebelum sampai ke server,
        // padahal normalisasi tautan di server justru yang menambalnya.
        $this->assertStringContainsString('id="tautanDriveUrl"', $html);
        $this->assertStringNotContainsString('type="url"', $html);
    }

    // ======================================================
    // TAMPILAN PADA HALAMAN 8 KOMPONEN
    // ======================================================

    public function test_tautan_drive_tampil_sebagai_tombol_pada_komponen()
    {
        $pegawai = $this->buatUser();
        $subMenu = $this->buatSubBidang();

        TautanDriveCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
            'kategori_komponen' => 'identitas_pegawai',
            'url' => 'https://drive.google.com/drive/folders/sk2026',
            'label' => 'Folder SK 2026',
        ]);

        $response = $this->actingAs($pegawai)->get('/crmc/manajemen-risiko?tahun=2026');

        $response->assertOk();
        $response->assertSee('https://drive.google.com/drive/folders/sk2026');
        $response->assertSee('Folder SK 2026');
    }

    public function test_halaman_tamu_menampilkan_tombol_drive_tanpa_form_isi()
    {
        $subMenu = $this->buatSubBidang();

        TautanDriveCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
            'kategori_komponen' => 'sop',
            'url' => 'https://drive.google.com/drive/folders/abc',
        ]);

        $response = $this->get('/crmc/manajemen-risiko?tahun=2026');

        $response->assertOk();
        $response->assertSee('Buka Google Drive');
        // Pengunjung tidak boleh mendapat tombol maupun form untuk mengisi
        // tautan; yang tersedia hanya tombol pembuka tautannya.
        $response->assertDontSee('Ubah Link Drive');
        $response->assertDontSee('id="tautanDriveForm"', false);
        $response->assertDontSee('id="tautanDriveHapusForm"', false);
    }

    // ======================================================
    // PRATINJAU BERKAS KANTOR PADA HALAMAN 8 KOMPONEN
    // ======================================================

    /**
     * Berkas Word/Excel modern dipratinjau di peramban. Server cukup
     * menulis wadah pratinjau berisi URL berkas; kotak "tidak tersedia"
     * hanya dipakai untuk format yang benar-benar tak bisa dibaca.
     */
    public function test_dokumen_kantor_tampil_dengan_wadah_pratinjau()
    {
        $pegawai = $this->buatUser();
        $subMenu = $this->buatSubBidang();

        $dokumen = DokumenCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
        ]);

        $lampiran = LampiranCrmc::create([
            'dokumen_crmc_id' => $dokumen->id,
            'kategori_komponen' => 'risk_register',
            'nama_file' => 'risk-register-2026.xlsx',
            'file_path' => 'lampiran/risk-register-2026.xlsx',
            'tipe_file' => 'xlsx',
        ]);

        $html = $this->actingAs($pegawai)->get('/crmc/manajemen-risiko?tahun=2026')->getContent();

        $this->assertStringContainsString('data-pratinjau-kantor', $html);
        $this->assertStringContainsString('data-tipe="xlsx"', $html);
        // Berkas di sini disimpan dengan jalur relatif murni (tanpa awalan
        // "/storage/"), jadi URL tampilan mengikuti nilai apa adanya.
        $this->assertStringContainsString('data-src="lampiran/risk-register-2026.xlsx"', $html);
        $this->assertStringNotContainsString('Pratinjau tidak tersedia untuk format XLSX', $html);
    }

    /**
     * URL berkas yang tersimpan bisa memuat host lama (mis. host yang
     * dipakai saat berkas diunggah dulu). Pratinjau memakai jalur
     * relatifnya supaya fetch() selalu menuju host yang sedang dibuka, bukan
     * lintas-asal yang diblokir peramban.
     */
    public function test_wadah_pratinjau_memakai_jalur_relatif()
    {
        $pegawai = $this->buatUser();
        $subMenu = $this->buatSubBidang();

        $dokumen = DokumenCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
        ]);

        LampiranCrmc::create([
            'dokumen_crmc_id' => $dokumen->id,
            'kategori_komponen' => 'risk_register',
            'nama_file' => 'register-risiko.docx',
            'file_path' => 'http://crmc_pusbangkom.test/storage/lampiran/register-risiko.docx',
            'tipe_file' => 'docx',
        ]);

        $html = $this->actingAs($pegawai)->get('/crmc/manajemen-risiko?tahun=2026')->getContent();

        $this->assertStringContainsString('data-src="/storage/lampiran/register-risiko.docx"', $html);
        $this->assertStringNotContainsString('data-src="http://crmc_pusbangkom.test', $html);
    }

    /**
     * DOC lama (format biner Word 97-2003) tidak bisa dibaca library
     * peramban, jadi tetap harus memakai kotak unduh.
     */
    public function test_doc_lama_tetap_memakai_kotak_unduh()
    {
        $pegawai = $this->buatUser();
        $subMenu = $this->buatSubBidang();

        $dokumen = DokumenCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
        ]);

        LampiranCrmc::create([
            'dokumen_crmc_id' => $dokumen->id,
            'kategori_komponen' => 'sop',
            'nama_file' => 'sop-lama.doc',
            'file_path' => 'lampiran/sop-lama.doc',
            'tipe_file' => 'doc',
        ]);

        $html = $this->actingAs($pegawai)->get('/crmc/manajemen-risiko?tahun=2026')->getContent();

        $this->assertStringContainsString('Pratinjau tidak tersedia untuk format DOC', $html);
        $this->assertStringContainsString('Perlu diunduh', $html);
        $this->assertStringNotContainsString('data-pratinjau-kantor', $html);
    }

    /**
     * Skrip pratinjau sisi klien harus dimuat SEKALI per halaman, walau
     * ada banyak berkas kantor. Kalau tidak, halaman menarik ulang library
     * yang sama untuk setiap berkas.
     */
    public function test_skrip_pratinjau_dimuat_sekali_walau_banyak_berkas()
    {
        $pegawai = $this->buatUser();
        $subMenu = $this->buatSubBidang();

        $dokumen = DokumenCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
        ]);

        foreach (['a.docx', 'b.xlsx', 'c.xls'] as $nama) {
            LampiranCrmc::create([
                'dokumen_crmc_id' => $dokumen->id,
                'kategori_komponen' => 'risk_register',
                'nama_file' => $nama,
                'file_path' => 'lampiran/'.$nama,
                'tipe_file' => pathinfo($nama, PATHINFO_EXTENSION),
            ]);
        }

        $html = $this->actingAs($pegawai)->get('/crmc/manajemen-risiko?tahun=2026')->getContent();

        // Tiga berkas kantor menghasilkan tiga wadah pratinjau...
        $this->assertSame(3, substr_count($html, 'data-tipe="'));

        // ...tetapi setiap library hanya ditulis sekali.
        $this->assertSame(1, substr_count($html, 'jszip.min.js'));
        $this->assertSame(1, substr_count($html, 'docx-preview.min.js'));
        $this->assertSame(1, substr_count($html, 'xlsx.full.min.js'));
    }
}
