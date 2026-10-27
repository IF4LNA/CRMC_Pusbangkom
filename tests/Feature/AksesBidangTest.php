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
 * Matriks akses per bidang.
 *
 * Aturan yang dijaga:
 *   - tamu tetap boleh membaca (perilaku lama, banyak tautan dibagikan
 *     tanpa login);
 *   - admin boleh membaca & mengelola semua bidang;
 *   - pegawai hanya bidang yang ditugaskan, termasuk sub-bidang, dokumen,
 *     dan berkasnya;
 *   - pegawai tanpa penugasan tidak mendapat bidang apa pun;
 *   - pemeriksaan dilakukan di server, bukan hanya menyembunyikan tombol.
 */
class AksesBidangTest extends TestCase
{
    use RefreshDatabase;

    private Bidang $bidangA;

    private Bidang $bidangB;

    private SubMenu $subA;

    private SubMenu $subB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bidangA = Bidang::create(['nama_bidang' => 'Bidang Alpha']);
        $this->bidangB = Bidang::create(['nama_bidang' => 'Bidang Beta']);

        $this->subA = SubMenu::create([
            'bidang_id' => $this->bidangA->id,
            'nama_sub_menu' => 'Manajemen Risiko Alpha',
        ]);

        $this->subB = SubMenu::create([
            'bidang_id' => $this->bidangB->id,
            'nama_sub_menu' => 'Perencanaan Beta',
        ]);
    }

    private function pengguna(string $role = 'pegawai', array $bidangIds = []): User
    {
        static $urut = 0;
        $urut++;

        $user = User::create([
            'name' => ucfirst($role).' '.$urut,
            'nip' => '1990010120260'.str_pad((string) $urut, 5, '0', STR_PAD_LEFT),
            'jabatan' => $role === 'admin' ? 'Administrator' : 'Staf',
            'email' => "akses{$urut}@pu.go.id",
            'password' => 'rahasia123',
            'role' => $role,
        ]);

        if ($bidangIds !== []) {
            $user->bidang()->attach($bidangIds);
        }

        return $user;
    }

    private function buatLampiran(SubMenu $subMenu, string $nama = 'dokumen.pdf'): LampiranCrmc
    {
        $dokumen = DokumenCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
        ]);

        return LampiranCrmc::create([
            'dokumen_crmc_id' => $dokumen->id,
            'kategori_komponen' => 'sop',
            'nama_file' => $nama,
            'file_path' => 'crmc/2026/'.$subMenu->id.'/sop/'.$nama,
            'tipe_file' => 'pdf',
        ]);
    }

    // ======================================================
    // VISIBILITAS DASHBOARD, NAVIGASI, DAN PENCARIAN
    // ======================================================

    public function test_admin_melihat_semua_bidang_di_dashboard_dan_navigasi(): void
    {
        $admin = $this->pengguna('admin');

        $html = $this->actingAs($admin)->get('/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('Bidang Alpha', $html);
        $this->assertStringContainsString('Bidang Beta', $html);
        $this->assertStringContainsString($this->subA->nama_sub_menu, $html);
        $this->assertStringContainsString($this->subB->nama_sub_menu, $html);
    }

    public function test_pegawai_hanya_melihat_bidang_yang_ditugaskan(): void
    {
        $pegawai = $this->pengguna('pegawai', [$this->bidangA->id]);

        $html = $this->actingAs($pegawai)->get('/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('Bidang Alpha', $html);
        $this->assertStringContainsString($this->subA->nama_sub_menu, $html);
        $this->assertStringNotContainsString('Bidang Beta', $html);
        $this->assertStringNotContainsString($this->subB->nama_sub_menu, $html);
    }

    public function test_pegawai_dengan_banyak_bidang_melihat_semua_yang_ditugaskan(): void
    {
        $pegawai = $this->pengguna('pegawai', [$this->bidangA->id, $this->bidangB->id]);

        $html = $this->actingAs($pegawai)->get('/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('Bidang Alpha', $html);
        $this->assertStringContainsString('Bidang Beta', $html);
        $this->assertStringContainsString($this->subA->nama_sub_menu, $html);
        $this->assertStringContainsString($this->subB->nama_sub_menu, $html);
    }

    public function test_pegawai_tanpa_penugasan_mendapat_dashboard_kosong_yang_jelas(): void
    {
        $pegawai = $this->pengguna('pegawai');

        $html = $this->actingAs($pegawai)->get('/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('Belum Ada Bidang yang Ditugaskan', $html);
        $this->assertStringNotContainsString('Bidang Alpha', $html);
        $this->assertStringNotContainsString('Bidang Beta', $html);

        $this->actingAs($pegawai)->get('/crmc/'.$this->subA->slug)->assertForbidden();
        $this->actingAs($pegawai)->get('/crmc/'.$this->subB->slug)->assertForbidden();

        $this->actingAs($pegawai)
            ->get('/sop')
            ->assertOk()
            ->assertSee('Belum Ada Bidang yang Ditugaskan');
    }

    public function test_pencarian_sop_tidak_membocorkan_bidang_lain(): void
    {
        $this->buatLampiran($this->subA, 'SOP-Alpha.pdf');
        $this->buatLampiran($this->subB, 'SOP-Beta.pdf');

        $pegawai = $this->pengguna('pegawai', [$this->bidangA->id]);

        // Filter bidang lain dari URL diabaikan, dan hasilnya tetap hanya
        // SOP dari bidang yang ditugaskan.
        $html = $this->actingAs($pegawai)
            ->get('/sop?bidang='.$this->bidangB->id)
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('SOP-Alpha.pdf', $html);
        $this->assertStringNotContainsString('SOP-Beta.pdf', $html);
    }

    // ======================================================
    // AKSES LANGSUNG KE URL
    // ======================================================

    public function test_pegawai_boleh_membuka_subbidang_sendiri_tetapi_ditolak_di_subbidang_lain(): void
    {
        $pegawai = $this->pengguna('pegawai', [$this->bidangA->id]);

        $this->actingAs($pegawai)
            ->get('/crmc/'.$this->subA->slug)
            ->assertOk();

        $this->actingAs($pegawai)
            ->get('/crmc/'.$this->subB->slug)
            ->assertForbidden();
    }

    public function test_admin_boleh_membuka_semua_subbidang(): void
    {
        $admin = $this->pengguna('admin');

        $this->actingAs($admin)->get('/crmc/'.$this->subA->slug)->assertOk();
        $this->actingAs($admin)->get('/crmc/'.$this->subB->slug)->assertOk();
    }

    // ======================================================
    // BERKAS: PRATINJAU, UNDUH, DAN UNGGAH
    // ======================================================

    public function test_berkas_hanya_bisa_dibaca_oleh_pihak_yang_berhak(): void
    {
        Storage::fake('local');

        $lampiran = $this->buatLampiran($this->subA);
        Storage::disk('local')->put($lampiran->storage_path, '%PDF-1.4 isi uji');

        $url = route('crmc.lampiran.berkas', ['id' => $lampiran->id]);

        // Tamu tidak lagi boleh membaca dokumen CRMC (hanya Beranda dan
        // Dasar Hukum yang publik).
        $this->get($url)->assertForbidden();

        $this->actingAs($this->pengguna('pegawai', [$this->bidangA->id]))
            ->get($url)
            ->assertOk();

        $this->actingAs($this->pengguna('pegawai', [$this->bidangB->id]))
            ->get($url)
            ->assertForbidden();

        $this->actingAs($this->pengguna('pegawai'))
            ->get($url)
            ->assertForbidden();

        $this->actingAs($this->pengguna('admin'))
            ->get($url)
            ->assertOk();
    }

    public function test_unduhan_berkas_tetap_memeriksa_akses_bidang(): void
    {
        Storage::fake('local');

        $lampiran = $this->buatLampiran($this->subA);
        Storage::disk('local')->put($lampiran->storage_path, '%PDF-1.4 isi uji');

        $url = route('crmc.lampiran.berkas', ['id' => $lampiran->id, 'unduh' => 1]);

        $this->actingAs($this->pengguna('pegawai', [$this->bidangA->id]))
            ->get($url)
            ->assertOk()
            ->assertHeader('content-disposition');

        $this->actingAs($this->pengguna('pegawai', [$this->bidangB->id]))
            ->get($url)
            ->assertForbidden();
    }

    public function test_pegawai_hanya_bisa_mengunggah_pada_bidangnya(): void
    {
        Storage::fake('local');

        $pegawai = $this->pengguna('pegawai', [$this->bidangA->id]);

        $payload = [
            'kategori_komponen' => 'sop',
            'tahun_pelaksanaan' => 2026,
            'files' => [UploadedFile::fake()->create('SOP-Baru.pdf', 40, 'application/pdf')],
        ];

        $this->actingAs($pegawai)
            ->post('/crmc/'.$this->subA->slug.'/upload-dokumen', $payload)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('lampiran_crmc', ['nama_file' => 'SOP-Baru.pdf']);

        $this->actingAs($pegawai)
            ->post('/crmc/'.$this->subB->slug.'/upload-dokumen', [
                'kategori_komponen' => 'sop',
                'tahun_pelaksanaan' => 2026,
                'files' => [UploadedFile::fake()->create('SOP-Terlarang.pdf', 40, 'application/pdf')],
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('lampiran_crmc', ['nama_file' => 'SOP-Terlarang.pdf']);
    }

    public function test_tamu_tidak_bisa_mengunggah_berkas(): void
    {
        Storage::fake('local');

        $this->post('/crmc/'.$this->subA->slug.'/upload-dokumen', [
            'kategori_komponen' => 'sop',
            'tahun_pelaksanaan' => 2026,
            'files' => [UploadedFile::fake()->create('SOP-Tamu.pdf', 40, 'application/pdf')],
        ])->assertForbidden();

        $this->assertDatabaseCount('lampiran_crmc', 0);
    }

    // ======================================================
    // MENGELOLA AKUN, PERAN, DAN PENUGASAN
    // ======================================================

    public function test_pegawai_tidak_bisa_mengelola_akun_mengubah_peran_atau_penugasan(): void
    {
        $pegawai = $this->pengguna('pegawai', [$this->bidangA->id]);

        $this->actingAs($pegawai)->get('/admin/pegawai')->assertForbidden();

        $this->actingAs($pegawai)->post('/admin/pegawai', [
            'name' => 'Akun Palsu',
            'nip' => '000000000000000001',
            'jabatan' => 'Staf',
            'email' => 'palsu@pu.go.id',
            'password' => 'rahasia123',
            'role' => 'admin',
            'bidang_ids' => [$this->bidangB->id],
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'palsu@pu.go.id']);

        $this->actingAs($pegawai)->put('/admin/pegawai/'.$pegawai->id, [
            'name' => $pegawai->name,
            'nip' => $pegawai->nip,
            'jabatan' => $pegawai->jabatan,
            'email' => $pegawai->email,
            'role' => 'admin',
            'bidang_ids' => [$this->bidangB->id],
        ])->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $pegawai->id,
            'role' => 'pegawai',
        ]);
        $this->assertDatabaseHas('bidang_user', [
            'user_id' => $pegawai->id,
            'bidang_id' => $this->bidangA->id,
        ]);
        $this->assertDatabaseMissing('bidang_user', [
            'user_id' => $pegawai->id,
            'bidang_id' => $this->bidangB->id,
        ]);
    }

    public function test_admin_dapat_menetapkan_banyak_bidang_dan_mengosongkannya_lagi(): void
    {
        $admin = $this->pengguna('admin');
        $pegawai = $this->pengguna('pegawai');

        $this->actingAs($admin)->put('/admin/pegawai/'.$pegawai->id, [
            'name' => $pegawai->name,
            'nip' => $pegawai->nip,
            'jabatan' => $pegawai->jabatan,
            'email' => $pegawai->email,
            'role' => 'pegawai',
            'bidang_ids' => [$this->bidangA->id, $this->bidangB->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('bidang_user', ['user_id' => $pegawai->id, 'bidang_id' => $this->bidangA->id]);
        $this->assertDatabaseHas('bidang_user', ['user_id' => $pegawai->id, 'bidang_id' => $this->bidangB->id]);

        // Mengosongkan centang berarti mencabut semua akses.
        $this->actingAs($admin)->put('/admin/pegawai/'.$pegawai->id, [
            'name' => $pegawai->name,
            'nip' => $pegawai->nip,
            'jabatan' => $pegawai->jabatan,
            'email' => $pegawai->email,
            'role' => 'pegawai',
        ])->assertRedirect();

        $this->assertDatabaseCount('bidang_user', 0);
    }

    public function test_akun_baru_tanpa_pilihan_bidang_tidak_mendapat_akses(): void
    {
        $admin = $this->pengguna('admin');

        $this->actingAs($admin)->post('/admin/pegawai', [
            'name' => 'Pegawai Baru',
            'nip' => '19900101202608888',
            'jabatan' => 'Staf',
            'email' => 'baru@pu.go.id',
            'password' => 'rahasia123',
            'role' => 'pegawai',
        ])->assertRedirect();

        $baru = User::where('email', 'baru@pu.go.id')->firstOrFail();

        $this->assertSame([], $baru->bidangIds());
        $this->actingAs($baru)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Belum Ada Bidang yang Ditugaskan');
    }

    public function test_menghapus_pegawai_ikut_menghapus_baris_penugasan_bidang(): void
    {
        $pegawai = $this->pengguna('pegawai', [$this->bidangA->id]);

        $pegawai->delete();

        $this->assertDatabaseMissing('bidang_user', ['user_id' => $pegawai->id]);
    }

    // ======================================================
    // KEBIJAKAN UBAH DATA: PEGAWAI HANYA UNGGAH
    // ======================================================

    public function test_pegawai_tidak_bisa_mengubah_ringkasan_residu_dan_evaluasi(): void
    {
        $pegawai = $this->pengguna('pegawai', [$this->bidangA->id]);

        $this->actingAs($pegawai)->post('/crmc/'.$this->subA->slug, [
            'residu' => 'waspada_i',
            'evaluasi' => 'Tidak boleh tersimpan.',
            'tahun_pelaksanaan' => 2026,
        ])->assertForbidden();

        $this->assertDatabaseMissing('dokumen_crmc', [
            'sub_menu_id' => $this->subA->id,
            'status_residu_risiko' => 'waspada_i',
        ]);

        $admin = $this->pengguna('admin');

        $this->actingAs($admin)->post('/crmc/'.$this->subA->slug, [
            'residu' => 'waspada_i',
            'evaluasi' => 'Boleh disimpan admin.',
            'tahun_pelaksanaan' => 2026,
        ])->assertRedirect();

        $this->assertDatabaseHas('dokumen_crmc', [
            'sub_menu_id' => $this->subA->id,
            'status_residu_risiko' => 'waspada_i',
            'evaluasi_dan_rencana' => 'Boleh disimpan admin.',
        ]);
    }

    public function test_pegawai_tidak_bisa_mengubah_keterangan_atau_menghapus_lampiran(): void
    {
        Storage::fake('local');

        $lampiran = $this->buatLampiran($this->subA);
        Storage::disk('local')->put($lampiran->storage_path, 'isi');

        $pegawai = $this->pengguna('pegawai', [$this->bidangA->id]);

        $this->actingAs($pegawai)
            ->patch('/crmc/lampiran/'.$lampiran->id.'/keterangan', ['keterangan' => 'Diubah pegawai'])
            ->assertForbidden();

        $this->actingAs($pegawai)
            ->delete('/crmc/lampiran/'.$lampiran->id)
            ->assertForbidden();

        $this->assertDatabaseHas('lampiran_crmc', [
            'id' => $lampiran->id,
            'keterangan' => null,
        ]);

        $admin = $this->pengguna('admin');

        $this->actingAs($admin)
            ->patch('/crmc/lampiran/'.$lampiran->id.'/keterangan', ['keterangan' => 'Diubah admin'])
            ->assertRedirect();

        $this->assertDatabaseHas('lampiran_crmc', [
            'id' => $lampiran->id,
            'keterangan' => 'Diubah admin',
        ]);
    }

    // ======================================================
    // REGRESI: TAMU TETAP BISA MEMBACA
    // ======================================================

    public function test_tamu_hanya_bisa_membaca_beranda_dan_dasar_hukum(): void
    {
        $this->buatLampiran($this->subA, 'SOP-Umum.pdf');

        // Dashboard tetap bisa dibuka untuk tab Dasar Hukum, tetapi tidak
        // memuat bidang milik siapa pun.
        $this->get('/dashboard')->assertOk();

        // Dokumen dan SOP tidak boleh dibuka tamu.
        $this->get('/crmc/'.$this->subA->slug)->assertForbidden();

        $this->get('/sop')->assertForbidden();
    }

    public function test_pivot_bidang_user_unik_dan_terhubung_dua_arah(): void
    {
        $pegawai = $this->pengguna('pegawai', [$this->bidangA->id, $this->bidangB->id]);

        $this->assertSame(2, DB::table('bidang_user')->where('user_id', $pegawai->id)->count());
        $this->assertTrue($pegawai->fresh()->memilikiBidang($this->bidangA->id));
        $this->assertTrue($this->bidangB->fresh()->pengguna->contains($pegawai->id));
    }
}
