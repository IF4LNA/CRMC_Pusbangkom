<?php

namespace Tests\Feature\Crmc;

use App\Models\Bidang;
use App\Models\DokumenDasarHukum;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Uji tab "Dasar Hukum": daftar regulasi dari database, hak unggah berkas
 * admin, dan pratinjau yang langsung tampil setelah diunggah.
 */
class DasarHukumDokumenTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $pegawai;

    private DokumenDasarHukum $se;

    private DokumenDasarHukum $kep;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin Dasar Hukum',
            'nip' => 'NIP0000000001',
            'jabatan' => 'Administrator',
        ]);

        $this->pegawai = User::factory()->create([
            'role' => 'pegawai',
            'name' => 'Pegawai Dasar Hukum',
            'nip' => 'NIP0000000002',
            'jabatan' => 'Staf',
        ]);

        // Dua naskah: satu Surat Edaran, satu Keputusan.
        $this->se = DokumenDasarHukum::create([
            'kode' => 'se-12-2024',
            'jenis' => 'Surat Edaran',
            'penerbit' => 'Menteri Pekerjaan Umum dan Perumahan Rakyat',
            'nomor' => '12/SE/M/2024',
            'tentang' => 'Pedoman Penerapan Manajemen Risiko di Kementerian Pekerjaan Umum dan Perumahan Rakyat',
            'urutan' => 1,
        ]);

        $this->kep = DokumenDasarHukum::create([
            'kode' => 'kep-08-2026',
            'jenis' => 'Keputusan',
            'penerbit' => 'Kepala Pusat Pengembangan Kompetensi Sumber Daya Air, Cipta Karya dan Prasarana Strategis',
            'nomor' => '08/KPTS/Ma/2026',
            'tentang' => 'Penetapan Tim Satuan Tugas Pengendalian Gratifikasi',
            'urutan' => 2,
        ]);

        $bidang = Bidang::create(['nama_bidang' => 'Bagian Umum Program & Tata Usaha']);
        SubMenu::create(['bidang_id' => $bidang->id, 'nama_sub_menu' => 'Manajemen Risiko']);
    }

    // ====================
    // Daftar regulasi
    // ====================

    public function test_dashboard_menampilkan_semua_regulasi(): void
    {
        $res = $this->get('/dashboard');

        $res->assertOk();
        $res->assertSee('NOMOR: 12/SE/M/2024');
        $res->assertSee('NOMOR: 08/KPTS/Ma/2026');
        $res->assertSee('Pedoman Penerapan Manajemen Risiko');
        $res->assertSee('Penetapan Tim Satuan Tugas Pengendalian Gratifikasi');
    }

    /**
     * Naskah tanpa berkas tetap tampil lengkap: identitas regulasinya
     * tidak boleh hilang hanya karena admin belum mengunggah salinannya.
     */
    public function test_regulasi_tanpa_berkas_tetap_tampil_lengkap(): void
    {
        $res = $this->get('/dashboard');

        $res->assertOk();
        $res->assertSee('Dokumen belum diunggah');
        $this->assertSame(2, substr_count($res->getContent(), 'Dokumen belum diunggah'));
    }

    public function test_kartu_menampilkan_jumlah_dokumen_terunggah(): void
    {
        $this->se->update([
            'path' => 'dasar-hukum/contoh.pdf',
            'nama_file' => 'contoh.pdf',
            'tipe_file' => 'pdf',
            'ukuran' => 1024,
        ]);

        $res = $this->get('/dashboard');

        $res->assertOk();
        $res->assertSee('1 Dokumen Terunggah');
        $res->assertSee('2 Peraturan Utama Terkait');
    }

    // ====================
    // Unggah berkas oleh admin
    // ====================

    public function test_admin_bisa_mengunggah_dokumen_pdf(): void
    {
        $res = $this->actingAs($this->admin)->post(
            "/admin/dasar-hukum/{$this->se->id}/berkas",
            ['berkas' => UploadedFile::fake()->create('se-12-2024.pdf', 200, 'application/pdf')]
        );

        $res->assertRedirect();
        $res->assertSessionHasNoErrors();

        $this->se->refresh();

        $this->assertNotNull($this->se->path);
        Storage::disk('public')->assertExists($this->se->path);
    }

    /**
     * After an upload, the dashboard should show the preview, not just
     * the upload form.
     */
    public function test_pratinjau_tampil_langsung_setelah_diunggah(): void
    {
        $this->actingAs($this->admin)->post(
            "/admin/dasar-hukum/{$this->se->id}/berkas",
            ['berkas' => UploadedFile::fake()->create('se-12-2024.pdf', 200, 'application/pdf')]
        );

        $html = $this->get('/dashboard')->assertOk()->getContent();

        $this->se->refresh();

        // PDF dirender lewat iframe viewer bawaan browser.
        $this->assertStringContainsString('<iframe', $html);
        $this->assertStringContainsString($this->se->url, $html);
        $this->assertStringContainsString('se-12-2024.pdf', $html);
    }

    public function test_gambar_dipakai_untuk_pratinjau(): void
    {
        $this->actingAs($this->admin)->post(
            "/admin/dasar-hukum/{$this->se->id}/berkas",
            ['berkas' => UploadedFile::fake()->image('scan.jpg', 800, 1200)]
        );

        $html = $this->get('/dashboard')->assertOk()->getContent();

        $this->se->refresh();

        $this->assertMatchesRegularExpression(
            '/<img src="'.preg_quote($this->se->url, '/').'"/',
            $html,
            'Gambar harus ditampilkan lewat <img>, bukan iframe.'
        );
    }

    /**
     * Format kantor tidak bisa dirender browser. Halaman harus tetap
     * memberi jalan keluar (unduh), bukan kotak kosong.
     */
    public function test_format_kantor_masih_bisa_diunduh(): void
    {
        $this->actingAs($this->admin)->post(
            "/admin/dasar-hukum/{$this->kep->id}/berkas",
            ['berkas' => UploadedFile::fake()->create('kep-08-2026.docx', 100)]
        );

        $html = $this->get('/dashboard')->assertOk()->getContent();

        $this->kep->refresh();

        $this->assertStringContainsString('Pratinjau tidak tersedia untuk format DOCX', $html);
        $this->assertStringContainsString('Unduh Berkas', $html);
    }

    public function test_unggah_baru_mengganti_berkas_lama(): void
    {
        $this->actingAs($this->admin)->post(
            "/admin/dasar-hukum/{$this->se->id}/berkas",
            ['berkas' => UploadedFile::fake()->create('lama.pdf', 100, 'application/pdf')]
        );

        $pathLama = $this->se->fresh()->path;

        $this->actingAs($this->admin)->post(
            "/admin/dasar-hukum/{$this->se->id}/berkas",
            ['berkas' => UploadedFile::fake()->create('baru.pdf', 100, 'application/pdf')]
        );

        $pathBaru = $this->se->fresh()->path;

        $this->assertNotSame($pathLama, $pathBaru);
        Storage::disk('public')->assertMissing($pathLama);
        Storage::disk('public')->assertExists($pathBaru);
    }

    // ====================
    // Validasi & otorisasi
    // ====================

    public function test_berkas_wajib_diisi(): void
    {
        $this->actingAs($this->admin)
            ->post("/admin/dasar-hukum/{$this->se->id}/berkas", [])
            ->assertSessionHasErrors('berkas');

        $this->assertNull($this->se->fresh()->path);
    }

    public function test_format_tidak_didukung_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->post("/admin/dasar-hukum/{$this->se->id}/berkas", [
                'berkas' => UploadedFile::fake()->create('virus.exe', 10, 'application/octet-stream'),
            ])
            ->assertSessionHasErrors('berkas');

        $this->assertNull($this->se->fresh()->path);
    }

    public function test_pegawai_tidak_boleh_mengunggah_dokumen(): void
    {
        $this->actingAs($this->pegawai)
            ->post("/admin/dasar-hukum/{$this->se->id}/berkas", [
                'berkas' => UploadedFile::fake()->create('se-12-2024.pdf', 100, 'application/pdf'),
            ])
            ->assertForbidden();

        $this->assertNull($this->se->fresh()->path);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->post("/admin/dasar-hukum/{$this->se->id}/berkas", [])->assertRedirect('/login');
    }

    public function test_form_unggah_hanya_tampil_untuk_admin(): void
    {
        $this->actingAs($this->admin)->get('/dashboard')
            ->assertOk()
            ->assertSee('admin/dasar-hukum/'.$this->se->id.'/berkas', false);

        $this->actingAs($this->pegawai)->get('/dashboard')
            ->assertOk()
            ->assertDontSee('admin/dasar-hukum/'.$this->se->id.'/berkas', false);
    }

    // ====================
    // Hapus berkas
    // ====================

    public function test_admin_bisa_menghapus_berkas_tanpa_menghapus_regulasi(): void
    {
        $this->actingAs($this->admin)->post(
            "/admin/dasar-hukum/{$this->se->id}/berkas",
            ['berkas' => UploadedFile::fake()->create('se-12-2024.pdf', 100, 'application/pdf')]
        );

        $path = $this->se->fresh()->path;

        $this->actingAs($this->admin)
            ->delete("/admin/dasar-hukum/{$this->se->id}/berkas")
            ->assertRedirect();

        $this->se->refresh();

        $this->assertNull($this->se->path);
        Storage::disk('public')->assertMissing($path);

        // Naskahnya tetap ada supaya bisa diunggah ulang.
        $this->assertSame('12/SE/M/2024', $this->se->nomor);
        $this->assertDatabaseCount('dokumen_dasar_hukum', 2);
    }

    public function test_pegawai_tidak_boleh_menghapus_berkas(): void
    {
        Storage::disk('public')->put('dasar-hukum/contoh.pdf', 'isi');
        $this->se->update(['path' => 'dasar-hukum/contoh.pdf']);

        $this->actingAs($this->pegawai)
            ->delete("/admin/dasar-hukum/{$this->se->id}/berkas")
            ->assertForbidden();

        Storage::disk('public')->assertExists('dasar-hukum/contoh.pdf');
    }
}
