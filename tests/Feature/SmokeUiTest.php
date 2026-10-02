<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_publik_render()
    {
        $this->seedStrukturOrganisasi();

        foreach (['/', '/home', '/login'] as $url) {
            $res = $this->get($url);
            $this->assertTrue(
                $res->isOk() || $res->isRedirect(),
                "Gagal render {$url}: HTTP {$res->getStatusCode()}"
            );
        }
    }

    public function test_halaman_terlogin_render()
    {
        $this->seedStrukturOrganisasi();

        $user = \App\Models\User::create([
            'name' => 'Uji Coba',
            'nip' => '198001011994031001',
            'jabatan' => 'Penguji',
            'email' => 'uji@crmc.test',
            'password' => 'rahasia123',
            'role' => 'admin',
        ]);
        $this->actingAs($user);

        $urls = [
            // Dashboard CRMC + tiap tabnya (tab hanya tersembunyi lewat JS,
            // jadi seluruh partial tetap dirender di server).
            '/',
            '/?tab=dasar-hukum',
            '/?tab=umum-tu',
            '/?tab=sda',
            '/?tab=ckps',
            // Halaman beranda
            '/home',
            // Admin
            '/admin/pegawai',
            // Halaman 8 komponen per sub-bidang
            '/crmc/manajemen-risiko',
        ];

        foreach ($urls as $url) {
            $res = $this->get($url);
            $this->assertTrue(
                $res->isOk() || $res->isRedirect(),
                "Gagal render {$url}: HTTP {$res->getStatusCode()}"
            );
        }
    }

    /**
     * Isi tabel struktur organisasi supaya cabang org chart yang "berisi"
     * ikut teruji, bukan hanya tampilan kosongnya.
     */
    private function seedStrukturOrganisasi(): void
    {
        $buatUser = function (string $nip, string $nama): \App\Models\User {
            return \App\Models\User::create([
                'name' => $nama,
                'nip' => $nip,
                'jabatan' => 'Staf',
                'email' => $nip . '@crmc.test',
                'password' => 'rahasia123',
                'role' => 'pegawai',
            ]);
        };

        $bidang = \App\Models\Bidang::create(['nama_bidang' => 'Bidang Uji']);

        // Sub-bilang + dokumen, supaya halaman 8 komponen ikut teruji pada
        // jalur yang benar-benar punya data (bukan cuma fallback slug).
        $subMenu = \App\Models\SubMenu::create([
            'bidang_id' => $bidang->id,
            'nama_sub_menu' => 'Manajemen Risiko',
        ]);

        $dokumen = \App\Models\DokumenCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
            'residu' => 'Rendah',
        ]);

        \App\Models\LampiranCrmc::create([
            'dokumen_crmc_id' => $dokumen->id,
            'kategori_komponen' => 'risk_register',
            'nama_file' => 'Risk Register Uji.pdf',
            'file_path' => 'lampiran/uji.pdf',
            'tipe_file' => 'pdf',
            'keterangan' => 'Keterangan uji',
        ]);

        // Dua gambar supaya carousel ikut teruji (kode ini hanya berjalan
        // kalau slide lebih dari satu).
        foreach (['Ruang Uji', 'Perangkat Uji'] as $i => $judul) {
            \App\Models\GaleriSarana::create([
                'path' => 'galeri/uji-' . $i . '.jpg',
                'judul' => $judul,
                'keterangan' => 'Keterangan ' . $judul,
                'urutan' => $i + 1,
            ]);
        }

        $struktur = [
            ['198001010000000001', 'Pemilik Risiko Uji', 'pemilik_risiko', null],
            ['198001010000000002', 'Mutu Uji', 'pengendali_mutu', $bidang->id],
            ['198001010000000003', 'Risiko Uji A', 'pengendali_risiko', $bidang->id],
            ['198001010000000004', 'Risiko Uji B', 'pengendali_risiko', null],
        ];

        foreach ($struktur as $i => [$nip, $nama, $peran, $bidangId]) {
            \App\Models\StrukturOrganisasi::create([
                'user_id' => $buatUser($nip, $nama)->id,
                'peran' => $peran,
                'nama_jabatan' => $peran,
                'bidang_id' => $bidangId,
                'urutan' => $i + 1,
            ]);
        }
    }
}
