<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Mekanisme notifikasi popup status upload + pertahanan posisi scroll.
 *
 * Pesan flash Laravel (success/error) dan hasil validasi diterjemahkan
 * layout `layouts.app` menjadi popup toast di sudut kanan atas layar,
 * sementara posisi scroll disimpan ke sessionStorage sebelum form upload
 * disubmit dan dipulihkan setelah redirect selesai.
 */
class NotifikasiUploadDokumenTest extends TestCase
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

    private function buatUser(): User
    {
        return User::create([
            'name' => 'Pegawai Notifikasi',
            'nip' => '19900101202609999',
            'jabatan' => 'Staf',
            'email' => 'pegawai.notifikasi@pu.go.id',
            'password' => 'rahasia123',
            'role' => 'pegawai',
        ]);
    }

    public function test_upload_berhasil_mengirim_flash_sukses_ke_popup_dan_instrumen_scroll_ikut_termuat()
    {
        Storage::fake('public');
        $this->actingAs($this->buatUser());
        $this->buatSubBidang();

        $response = $this->post('/crmc/manajemen-risiko/upload-dokumen', [
            'kategori_komponen' => 'sop',
            'tahun_pelaksanaan' => 2026,
            'files' => [UploadedFile::fake()->create('SOP-Aman.pdf', 64, 'application/pdf')],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $response->assertSessionMissing('error');

        // Halaman tujuan redirect: wadah popup ada dan pesan sukses benar-
        // benar dijejali ke pemanggilan showToast (bukan hanya pita pesan
        // biasa di paling atas halaman).
        $halaman = $this->get($response->headers->get('Location'));
        $halaman->assertOk();
        $halaman->assertSee('id="toastWrap"', false);

        // Pesan sukses dijejali sebagai argumen showToast() di payload JS,
        // bukan hanya tampil sebagai pita pesan biasa.
        $this->assertStringContainsString(
            "['success', \"1 dokumen berhasil diunggah",
            $halaman->getContent()
        );

        // Mekanisme pertahanan posisi scroll ikut termuat di halaman ini.
        $this->assertStringContainsString('crmc:posisi-scroll', $halaman->getContent());
    }

    public function test_validasi_gagal_menampilkan_pesan_error_pada_popup_tanpa_pesan_sukses()
    {
        $this->actingAs($this->buatUser());
        $this->buatSubBidang();

        // Redirect diikuti dalam satu rangkaian request. Kalau assertion sesi
        // dipanggil di antara POST dan GET, session store milik framework
        // dimulai ulang dan error bag ikut ter-marshal dua kali (kosong).
        // Yang diuji di sini adalah hasil akhir yang dilihat pengguna:
        // halaman tujuan redirect beserta payload popup-nya.
        $halaman = $this->followingRedirects()->from('/crmc/manajemen-risiko')->post(
            '/crmc/manajemen-risiko/upload-dokumen',
            [
                'kategori_komponen' => 'sop',
                'tahun_pelaksanaan' => 2026,
                'files' => [UploadedFile::fake()->create('shell.php', 10, 'application/octet-stream')],
            ]
        );

        $halaman->assertOk();
        $halaman->assertSee('id="toastWrap"', false);

        $isi = $halaman->getContent();

        // Pesan validasi Laravel ikut ter-encode ke dalam payload popup merah.
        $this->assertStringContainsString(
            'const kesalahan = ["Format file tidak diizinkan.',
            $isi
        );

        // Kegagalan validasi tidak boleh pernah memicu popup sukses.
        $this->assertStringNotContainsString("['success', \"", $isi);
    }

    public function test_penyimpanan_file_gagal_mengirim_flash_error_bukan_sukses()
    {
        $this->actingAs($this->buatUser());
        $this->buatSubBidang();

        $berkas = tempnam(sys_get_temp_dir(), 'crmc-gagal');
        file_put_contents($berkas, "%PDF-1.4\n");

        // Subclass UploadedFile yang mensimulasikan kegagalan penulisan ke
        // disk (storeAs() mengembalikan false) tanpa menyentuh disk asli.
        $gagalDisimpan = new class($berkas, 'laporan.pdf', 'application/pdf', null, true) extends UploadedFile
        {
            public function storeAs($path, $name = null, $options = [])
            {
                return false;
            }
        };

        $response = $this->post('/crmc/manajemen-risiko/upload-dokumen', [
            'kategori_komponen' => 'sop',
            'tahun_pelaksanaan' => 2026,
            'files' => [$gagalDisimpan],
        ]);

        @unlink($berkas);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        // Penyimpanan gagal -> tidak boleh ada pesan sukses sama sekali.
        $response->assertSessionMissing('success');
        $this->assertDatabaseCount('lampiran_crmc', 0);
    }
}
