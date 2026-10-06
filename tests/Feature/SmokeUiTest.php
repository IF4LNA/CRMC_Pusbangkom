<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\DokumenCrmc;
use App\Models\LampiranCrmc;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_publik_render()
    {
        $this->seedData();

        foreach (['/', '/home', '/login', '/sop'] as $url) {
            $res = $this->get($url);
            $this->assertTrue(
                $res->isOk() || $res->isRedirect(),
                "Gagal render {$url}: HTTP {$res->getStatusCode()}"
            );
        }
    }

    public function test_halaman_terlogin_render()
    {
        $bidang = $this->seedData();

        $user = User::create([
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
            // jadi seluruh partial tetap dirender di server). Tab bidang
            // memakai id bidang, mis. "bidang-1".
            '/',
            '/?tab=dasar-hukum',
            '/?tab=bidang-' . $bidang->id,
            // Halaman beranda
            '/home',
            // Kumpulan SOP
            '/sop',
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
     * Isi tabel inti supaya halaman yang diuji punya data sungguhan, bukan
     * hanya cabang tampilan kosong.
     *
     * @return \App\Models\Bidang bidang yang dipakai pada tab dashboard
     */
    private function seedData(): Bidang
    {
        $bidang = Bidang::create(['nama_bidang' => 'Bidang Uji']);

        // Sub-bidang + dokumen, supaya halaman 8 komponen ikut teruji pada
        // jalur yang benar-benar punya data (bukan cuma fallback slug).
        $subMenu = SubMenu::create([
            'bidang_id' => $bidang->id,
            'nama_sub_menu' => 'Manajemen Risiko',
        ]);

        $dokumen = DokumenCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
        ]);

        LampiranCrmc::create([
            'dokumen_crmc_id' => $dokumen->id,
            'kategori_komponen' => 'sop',
            'nama_file' => 'SOP Uji.pdf',
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

        return $bidang;
    }
}
