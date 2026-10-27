<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\DokumenCrmc;
use App\Models\LampiranCrmc;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Penyimpanan berkas yang aman saat pindah server:
 * - unggahan baru menyimpan jalur relatif (bukan URL berisi host/IP lokal);
 * - perintah "dokumen:migrasi-path" menormalkan data lama tanpa menyentuh
 *   berkas fisik maupun baris yang tidak sesuai pola.
 */
class PenyimpananPathDokumenTest extends TestCase
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

        $user = User::create([
            'name' => 'Pegawai '.$urut,
            'nip' => '1990010120260'.str_pad((string) $urut, 5, '0', STR_PAD_LEFT),
            'jabatan' => 'Staf',
            'email' => "pegawai{$urut}@pu.go.id",
            'password' => 'rahasia123',
            'role' => $role,
        ]);

        // Pembatasan akses per bidang: pegawai harus punya bidang agar
        // unggahan/penghapusan yang diuji di sini tidak ditolak 403.
        if ($role === 'pegawai') {
            $bidang = Bidang::first() ?: Bidang::create(['nama_bidang' => 'Bidang Uji']);
            $user->bidang()->attach($bidang->id);
        }

        return $user;
    }

    private function buatLampiran(string $filePath, string $nama = 'uji.pdf'): LampiranCrmc
    {
        $subMenu = $this->buatSubBidang();

        $dokumen = DokumenCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
        ]);

        return LampiranCrmc::create([
            'dokumen_crmc_id' => $dokumen->id,
            'kategori_komponen' => 'sop',
            'nama_file' => $nama,
            'file_path' => $filePath,
            'tipe_file' => 'pdf',
        ]);
    }

    // ======================================================
    // UNGGAHAN BARU
    // ======================================================

    public function test_unggahan_baru_menyimpan_jalur_tanpa_host()
    {
        Storage::fake('local');
        $this->actingAs($this->buatUser());
        $this->buatSubBidang();

        $response = $this->post('/crmc/manajemen-risiko/upload-dokumen', [
            'kategori_komponen' => 'sop',
            'tahun_pelaksanaan' => 2026,
            'files' => [UploadedFile::fake()->create('SOP-2026.pdf', 120, 'application/pdf')],
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $lampiran = LampiranCrmc::where('nama_file', 'SOP-2026.pdf')->firstOrFail();

        // Berkas baru disimpan sebagai jalur relatif murni di disk privat.
        // URL tampilan memakai route ber-otorisasi, bukan "/storage/...".
        $this->assertStringNotContainsString('http', $lampiran->file_path);
        $this->assertStringStartsWith('crmc/', $lampiran->file_path);
        $this->assertSame(
            route('crmc.lampiran.berkas', ['id' => $lampiran->id], false),
            (string) $lampiran->url
        );
        Storage::disk('local')->assertExists($lampiran->storage_path);
    }

    /**
     * Regresi: berkas yang baru diunggah pernah muncul sebagai 404 karena
     * file_path disimpan tanpa awalan "/storage/" sehingga URL pratinjau
     * menjadi relatif terhadap halaman (mis. "/crmc/crmc/2026/...pdf").
     * Sekarang pratinjau memakai route berkas ber-otorisasi.
     */
    public function test_pratinjau_berkas_baru_menunjuk_jalur_storage_yang_benar()
    {
        Storage::fake('local');
        $this->actingAs($this->buatUser());
        $this->buatSubBidang();

        $this->post('/crmc/manajemen-risiko/upload-dokumen', [
            'kategori_komponen' => 'sop',
            'tahun_pelaksanaan' => 2026,
            'files' => [UploadedFile::fake()->create('SOP-Baru.pdf', 80, 'application/pdf')],
        ])->assertSessionHasNoErrors();

        $lampiran = LampiranCrmc::where('nama_file', 'SOP-Baru.pdf')->firstOrFail();

        $html = $this->actingAs($this->buatUser())
            ->get('/crmc/manajemen-risiko?tahun=2026')
            ->getContent();

        $url = route('crmc.lampiran.berkas', ['id' => $lampiran->id], false);

        $this->assertStringContainsString('data-src="'.$url.'#view=FitH"', $html);
        $this->assertStringNotContainsString('src="/crmc/crmc/', $html);
        $this->assertStringNotContainsString('/storage/crmc/', $html);
    }

    public function test_url_lampiran_dibentuk_dinamis_dari_disk_public()
    {
        Storage::fake('public');

        // Data lama berupa URL host lain harus tetap menghasilkan URL
        // memakai host yang sedang dibuka, bukan host lamanya.
        $lampiran = $this->buatLampiran('http://172.10.10.168:8000/storage/crmc/2026/1/sop/uji.pdf');

        $this->assertSame('crmc/2026/1/sop/uji.pdf', $lampiran->storage_path);
        $this->assertStringNotContainsString('172.10.10.168', (string) $lampiran->url);
        $this->assertSame(
            route('crmc.lampiran.berkas', ['id' => $lampiran->id], false),
            (string) $lampiran->url
        );

        // Jalur relatif (format penyimpanan baru) juga menghasilkan URL benar.
        $baru = $this->buatLampiran('/storage/crmc/2026/1/sop/baru.pdf', 'baru.pdf');

        $this->assertSame('crmc/2026/1/sop/baru.pdf', $baru->storage_path);
        $this->assertSame(
            route('crmc.lampiran.berkas', ['id' => $baru->id], false),
            (string) $baru->url
        );
    }

    public function test_penghapusan_memakai_jalur_yang_benar_untuk_kedua_format()
    {
        Storage::fake('public');

        // Format lama: URL penuh dengan host.
        $this->assertSame(
            'crmc/2026/1/sop/lama.pdf',
            (new LampiranCrmc(['file_path' => 'http://172.10.10.168:8000/storage/crmc/2026/1/sop/lama.pdf']))->storage_path
        );

        // Format baru: jalur relatif berawalan "/storage/".
        $this->assertSame(
            'crmc/2026/1/sop/baru.pdf',
            (new LampiranCrmc(['file_path' => '/storage/crmc/2026/1/sop/baru.pdf']))->storage_path
        );

        // Data sangat lama berprefiks "public/".
        $this->assertSame(
            'crmc/2026/1/sop/kuno.pdf',
            (new LampiranCrmc(['file_path' => 'public/crmc/2026/1/sop/kuno.pdf']))->storage_path
        );
    }

    // ======================================================
    // PERINTAH MIGRASI
    // ======================================================

    public function test_perintah_dry_run_tidak_mengubah_data()
    {
        Storage::fake('public');
        Storage::disk('public')->put('crmc/2026/1/sop/uji.pdf', 'isi');

        $lampiran = $this->buatLampiran('http://172.10.10.168:8000/storage/crmc/2026/1/sop/uji.pdf');

        $this->artisan('dokumen:migrasi-path')
            ->expectsOutputToContain('PERLU DIUBAH (dry-run)')
            ->assertExitCode(0);

        $this->assertSame(
            'http://172.10.10.168:8000/storage/crmc/2026/1/sop/uji.pdf',
            DB::table('lampiran_crmc')->where('id', $lampiran->id)->value('file_path')
        );
    }

    public function test_perintah_apply_mengubah_url_menjadi_path_relatif()
    {
        Storage::fake('public');
        Storage::disk('public')->put('crmc/2026/1/sop/uji.pdf', 'isi');

        $lampiran = $this->buatLampiran('http://172.10.10.168:8000/storage/crmc/2026/1/sop/uji.pdf');

        $this->artisan('dokumen:migrasi-path', ['--apply' => true, '--force' => true])
            ->assertExitCode(0);

        $this->assertDatabaseHas('lampiran_crmc', [
            'id' => $lampiran->id,
            'file_path' => 'crmc/2026/1/sop/uji.pdf',
        ]);

        // Berkas fisik tetap ada dan tetap bisa ditemukan lewat jalur baru.
        Storage::disk('public')->assertExists('crmc/2026/1/sop/uji.pdf');
        $this->assertSame('crmc/2026/1/sop/uji.pdf', $lampiran->fresh()->storage_path);
    }

    public function test_baris_yang_sudah_relatif_tidak_disentuh()
    {
        Storage::fake('public');

        $lampiran = $this->buatLampiran('crmc/2026/1/sop/sudah-relatif.pdf');

        $this->artisan('dokumen:migrasi-path', ['--apply' => true, '--force' => true])
            ->expectsOutputToContain('0 baris DIUBAH')
            ->assertExitCode(0);

        $this->assertDatabaseHas('lampiran_crmc', [
            'id' => $lampiran->id,
            'file_path' => 'crmc/2026/1/sop/sudah-relatif.pdf',
        ]);
    }

    public function test_url_tanpa_segmen_storage_dilaporkan_dan_dibiarkan()
    {
        Storage::fake('public');

        $lampiran = $this->buatLampiran('https://drive.google.com/file/d/abc123/view');

        $this->artisan('dokumen:migrasi-path', ['--apply' => true, '--force' => true])
            ->expectsOutputToContain('anomali')
            ->assertExitCode(0);

        $this->assertDatabaseHas('lampiran_crmc', [
            'id' => $lampiran->id,
            'file_path' => 'https://drive.google.com/file/d/abc123/view',
        ]);
    }

    public function test_berkas_hilang_tetap_dinormalkan_dan_dilaporkan()
    {
        Storage::fake('public');

        // URL benar tetapi berkas fisiknya tidak ada di disk.
        $lampiran = $this->buatLampiran('http://172.10.10.168:8000/storage/crmc/2026/1/sop/hilang.pdf', 'hilang.pdf');

        $this->artisan('dokumen:migrasi-path', ['--apply' => true, '--force' => true])
            ->expectsOutputToContain('berkas hilang')
            ->assertExitCode(0);

        $this->assertDatabaseHas('lampiran_crmc', [
            'id' => $lampiran->id,
            'file_path' => 'crmc/2026/1/sop/hilang.pdf',
        ]);
    }

    public function test_path_dasar_hukum_ikut_dimigrasikan()
    {
        Storage::fake('public');
        Storage::disk('public')->put('dasar-hukum/uu-17-2003.pdf', 'isi');

        DB::table('dokumen_dasar_hukum')->insert([
            'kode' => 'UU-17-2003',
            'jenis' => 'Undang-Undang',
            'penerbit' => 'Pemerintah',
            'nomor' => '17',
            'tentang' => 'Keuangan Negara',
            'urutan' => 1,
            'path' => 'http://crmc_pusbangkom.test/storage/dasar-hukum/uu-17-2003.pdf',
            'nama_file' => 'uu-17-2003.pdf',
            'tipe_file' => 'pdf',
            'ukuran' => 1024,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('dokumen:migrasi-path', ['--apply' => true, '--force' => true])
            ->assertExitCode(0);

        $this->assertDatabaseHas('dokumen_dasar_hukum', [
            'kode' => 'UU-17-2003',
            'path' => 'dasar-hukum/uu-17-2003.pdf',
        ]);
    }

    // ======================================================
    // TAMPILAN & PEMBERSIHAN BERKAS
    // ======================================================

    public function test_pratinjau_data_lama_memakai_jalur_relatif_pada_host_aktif()
    {
        Storage::fake('public');

        $lampiran = $this->buatLampiran('http://172.10.10.168:8000/storage/crmc/2026/1/sop/uji.pdf');

        $html = $this->actingAs($this->buatUser())
            ->get('/crmc/manajemen-risiko?tahun=2026')
            ->getContent();

        $url = route('crmc.lampiran.berkas', ['id' => $lampiran->id], false);

        $this->assertStringContainsString('data-src="'.$url.'#view=FitH"', $html);
        $this->assertStringNotContainsString('172.10.10.168', $html);
    }

    public function test_menghapus_lampiran_lama_tidak_menyentuh_berkas_lain()
    {
        Storage::fake('public');
        Storage::disk('public')->put('crmc/2026/1/sop/dihapus.pdf', 'isi');
        Storage::disk('public')->put('crmc/2026/1/sop/dipertahankan.pdf', 'isi');

        $lampiran = $this->buatLampiran('http://172.10.10.168:8000/storage/crmc/2026/1/sop/dihapus.pdf', 'dihapus.pdf');

        $this->actingAs($this->buatUser('admin'))
            ->delete('/crmc/lampiran/'.$lampiran->id)
            ->assertRedirect();

        Storage::disk('public')->assertMissing('crmc/2026/1/sop/dihapus.pdf');
        Storage::disk('public')->assertExists('crmc/2026/1/sop/dipertahankan.pdf');
        $this->assertDatabaseMissing('lampiran_crmc', ['id' => $lampiran->id]);
    }
}
