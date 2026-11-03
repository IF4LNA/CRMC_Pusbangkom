<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\DokumenCrmc;
use App\Models\LampiranCrmc;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PemulihanDokumenTest extends TestCase
{
    use RefreshDatabase;

    private Bidang $bidang;

    private SubMenu $subMenu;

    private DokumenCrmc $dokumen;

    private User $admin;

    private User $pegawai;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->bidang = Bidang::create(['nama_bidang' => 'Bidang Uji']);
        $this->subMenu = SubMenu::create([
            'bidang_id' => $this->bidang->id,
            'nama_sub_menu' => 'Sub Uji',
        ]);
        $this->dokumen = DokumenCrmc::create([
            'sub_menu_id' => $this->subMenu->id,
            'tahun_pelaksanaan' => 2026,
        ]);

        $this->admin = User::create([
            'name' => 'Admin Uji',
            'nip' => '198001011994031001',
            'jabatan' => 'Administrator',
            'email' => 'adminuji@pu.go.id',
            'password' => 'rahasia123',
            'role' => 'admin',
        ]);

        $this->pegawai = User::create([
            'name' => 'Pegawai Uji',
            'nip' => '198001011994031002',
            'jabatan' => 'Staf',
            'email' => 'pegawaii@pu.go.id',
            'password' => 'rahasia123',
            'role' => 'pegawai',
        ]);
    }

    private function buatFile(string $nama = 'berkas.pdf', ?int $subMenuId = null, int $tahun = 2026, string $kategori = 'sop'): string
    {
        $sub = $subMenuId ?? $this->subMenu->id;
        $path = "crmc/{$tahun}/{$sub}/{$kategori}/{$nama}";
        Storage::disk('local')->put($path, 'isi berkas uji');

        return $path;
    }

    public function test_halaman_pemulihan_render_untuk_admin(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('admin.pemulihan.index'))->assertOk();
    }

    public function test_pegawai_tidak_boleh_akses(): void
    {
        $this->actingAs($this->pegawai);
        $this->get(route('admin.pemulihan.index'))->assertForbidden();
        $this->post(route('admin.pemulihan.recover'), ['paths' => ['crmc/2026/1/sop/x.pdf']])->assertForbidden();
    }

    public function test_file_yang_belum_terdaftar_siap_dipulihkan(): void
    {
        $path = $this->buatFile('siap.pdf');
        $this->actingAs($this->admin);

        $res = $this->get(route('admin.pemulihan.index'));
        $res->assertOk();
        $res->assertSee('siap.pdf');
    }

    public function test_pulihkan_satu_file_membuat_record_dengan_relasi_benar(): void
    {
        $path = $this->buatFile('pulih.pdf');

        $this->actingAs($this->admin);
        $response = $this->post(route('admin.pemulihan.recover'), ['paths' => [$path]]);
        $response->assertRedirect(route('admin.pemulihan.index'));

        $this->assertDatabaseHas('lampiran_crmc', [
            'dokumen_crmc_id' => $this->dokumen->id,
            'kategori_komponen' => 'sop',
            'file_path' => $path,
            'tipe_file' => 'pdf',
        ]);

        // File fisik tidak berubah.
        $this->assertTrue(Storage::disk('local')->exists($path));
    }

    public function test_pulihkan_tidak_membuat_duplikat(): void
    {
        $path = $this->buatFile('duplikat.pdf');

        // Daftarkan manual dulu.
        LampiranCrmc::create([
            'dokumen_crmc_id' => $this->dokumen->id,
            'kategori_komponen' => 'sop',
            'nama_file' => 'duplikat.pdf',
            'keterangan' => null,
            'file_path' => $path,
            'tipe_file' => 'pdf',
        ]);

        $jumlahAwal = LampiranCrmc::count();

        $this->actingAs($this->admin);
        $this->post(route('admin.pemulihan.recover'), ['paths' => [$path]]);

        $this->assertSame($jumlahAwal, LampiranCrmc::count());
    }

    public function test_file_tanpa_dokumen_induk_tidak_dipulihkan(): void
    {
        // sub_menu tidak punya dokumen_crmc
        $subLain = SubMenu::create([
            'bidang_id' => $this->bidang->id,
            'nama_sub_menu' => 'Sub Lain',
        ]);
        $path = $this->buatFile('orphan.pdf', $subLain->id);

        $this->actingAs($this->admin);
        $this->post(route('admin.pemulihan.recover'), ['paths' => [$path]]);

        $this->assertDatabaseMissing('lampiran_crmc', ['file_path' => $path]);
    }

    public function test_pemulihan_batch_menangani_kegagalan_tanpa_duplikat(): void
    {
        $pathOk = $this->buatFile('ok.pdf');
        $subLain = SubMenu::create([
            'bidang_id' => $this->bidang->id,
            'nama_sub_menu' => 'Sub Lain 2',
        ]);
        $pathBad = $this->buatFile('bad.pdf', $subLain->id);

        $this->actingAs($this->admin);
        $this->post(route('admin.pemulihan.recover'), ['paths' => [$pathOk, $pathBad]]);

        $this->assertDatabaseHas('lampiran_crmc', ['file_path' => $pathOk]);
        $this->assertDatabaseMissing('lampiran_crmc', ['file_path' => $pathBad]);
    }

    public function test_path_traversal_ditolak(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post(route('admin.pemulihan.recover'), ['paths' => ['../secret.txt']]);
        $response->assertRedirect(route('admin.pemulihan.index'));
        $this->assertDatabaseMissing('lampiran_crmc', ['file_path' => '../secret.txt']);
    }
}
