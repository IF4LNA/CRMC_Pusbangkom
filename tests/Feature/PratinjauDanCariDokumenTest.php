<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\DokumenCrmc;
use App\Models\LampiranCrmc;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Optimasi pratinjau lazy per slide berlaku untuk semua komponen
 * berkarusel, sedangkan pencarian dan filter dokumen hanya dirender untuk
 * Komponen 6 — Bukti Pelaksanaan (kategori_komponen = "bukti_pelaksanaan").
 */
class PratinjauDanCariDokumenTest extends TestCase
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

    private function buatPengguna(): User
    {
        static $urut = 0;
        $urut++;

        $user = User::create([
            'name' => 'Pegawai '.$urut,
            'nip' => '1990010120260'.str_pad((string) $urut, 5, '0', STR_PAD_LEFT),
            'jabatan' => 'Staf',
            'email' => "pegawai{$urut}@pu.go.id",
            'password' => 'rahasia123',
            'role' => 'pegawai',
        ]);

        // Halaman 8 Komponen hanya boleh dibuka pegawai pada bidang yang
        // ditugaskan. Pasang bidang default agar pengujian pratinjau tetap
        // menguji pratinjau, bukan mendapat 403.
        $bidang = Bidang::first() ?: Bidang::create(['nama_bidang' => 'Bidang Uji']);
        $user->bidang()->attach($bidang->id);

        return $user;
    }

    private function buatLampiran(
        SubMenu $subMenu,
        string $kategori,
        string $nama,
        string $path,
        string $tipe,
        ?string $keterangan = null,
    ): LampiranCrmc {
        $dokumen = DokumenCrmc::firstOrCreate([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => 2026,
        ]);

        return LampiranCrmc::create([
            'dokumen_crmc_id' => $dokumen->id,
            'kategori_komponen' => $kategori,
            'nama_file' => $nama,
            'keterangan' => $keterangan,
            'file_path' => $path,
            'tipe_file' => $tipe,
        ]);
    }

    private function ambilHalaman(SubMenu $subMenu): string
    {
        return $this->actingAs($this->buatPengguna())
            ->get('/crmc/manajemen-risiko?tahun=2026')
            ->getContent();
    }

    public function test_semua_komponen_memakai_pratinjau_lazy_per_slide(): void
    {
        $subMenu = $this->buatSubBidang();
        $this->buatLampiran($subMenu, 'identitas_pegawai', 'identitas.pdf', 'lampiran/identitas.pdf', 'pdf');
        $risiko = $this->buatLampiran($subMenu, 'risk_register', 'risiko.pdf', 'lampiran/risiko.pdf', 'pdf');
        $sop = $this->buatLampiran($subMenu, 'sop', 'sop.png', 'lampiran/sop.png', 'png');
        $this->buatLampiran($subMenu, 'bukti_pelaksanaan', 'bukti.pdf', 'lampiran/bukti.pdf', 'pdf');
        $this->buatLampiran($subMenu, 'evaluasi', 'evaluasi.xlsx', 'lampiran/evaluasi.xlsx', 'xlsx');

        $html = $this->ambilHalaman($subMenu);

        // Semua carousel komponen memperoleh penanda pratinjau lazy.
        foreach (['identitas_pegawai', 'risk_register', 'sop', 'bukti_pelaksanaan', 'evaluasi'] as $kategori) {
            preg_match('/<div id="dok-'.$kategori.'"[^>]*>/', $html, $akar);
            $this->assertNotEmpty($akar, 'Carousel '.$kategori.' tidak ditemukan.');
            $this->assertStringContainsString('data-lazy-pratinjau="1"', $akar[0]);
        }

        // PDF dan gambar tidak lagi memakai src langsung. URL berkas kini
        // melewati route ber-otorisasi, bukan jalur "/storage/...".
        $this->assertStringContainsString('data-src="'.$risiko->url.'#view=FitH"', $html);
        $this->assertDoesNotMatchRegularExpression('/<iframe src="\/crmc\/lampiran\//', $html);
        $this->assertStringContainsString('data-src="'.$sop->url.'"', $html);
        $this->assertDoesNotMatchRegularExpression('/<img src="\/crmc\/lampiran\//', $html);

        // Berkas kantor juga baru diproses saat slide aktif, dengan retry.
        $this->assertStringContainsString('data-pratinjau-lazy="1"', $html);
        $this->assertStringContainsString('data-pratinjau-retry="1"', $html);
        $this->assertStringContainsString('data-tipe="xlsx"', $html);

        // Skrip pemuat hanya dipasang sekali per halaman.
        $this->assertSame(1, substr_count($html, 'window.crmcMuatSlide = muatSlide;'));
    }

    public function test_pencarian_dan_filter_hanya_dirender_di_komponen_6(): void
    {
        $subMenu = $this->buatSubBidang();
        $this->buatLampiran(
            $subMenu,
            'bukti_pelaksanaan',
            'Laporan Kegiatan Mei.pdf',
            'lampiran/laporan.pdf',
            'pdf',
            'Laporan lengkap'
        );
        $this->buatLampiran(
            $subMenu,
            'bukti_pelaksanaan',
            'Foto Rapat.png',
            'lampiran/foto.png',
            'png',
            'Dokumentasi rapat'
        );
        $this->buatLampiran($subMenu, 'risk_register', 'risiko.pdf', 'lampiran/risiko.pdf', 'pdf');

        $html = $this->ambilHalaman($subMenu);

        // Alat cari hanya satu, yaitu milik Komponen 6.
        $this->assertSame(1, preg_match_all('/<div[^>]*data-peran="alat-cari"/', $html));
        $this->assertStringContainsString('data-peran="cari-dokumen"', $html);
        $this->assertStringContainsString('data-peran="filter-dokumen"', $html);
        $this->assertStringContainsString('Cari nama atau keterangan dokumen', $html);
        $this->assertSame(1, preg_match_all('/<div[^>]*data-peran="kosong-cari"/', $html));
        $this->assertStringContainsString('Dokumen tidak ditemukan', $html);

        preg_match('/<div id="dok-bukti_pelaksanaan"[^>]*>/', $html, $akarBukti);
        $this->assertNotEmpty($akarBukti);
        $this->assertStringContainsString('data-khusus-cari="1"', $akarBukti[0]);

        preg_match('/<div id="dok-risk_register"[^>]*>/', $html, $akarRisiko);
        $this->assertNotEmpty($akarRisiko);
        $this->assertStringNotContainsString('data-khusus-cari', $akarRisiko[0]);

        // Pencarian memakai nama + keterangan dalam huruf kecil, dan filter
        // memakai kelompok format yang tersedia.
        $this->assertStringContainsString('data-cari="laporan kegiatan mei.pdf laporan lengkap"', $html);
        $this->assertStringContainsString('data-cari="foto rapat.png dokumentasi rapat"', $html);
        $this->assertStringContainsString('data-kelompok="pdf"', $html);
        $this->assertStringContainsString('data-kelompok="gambar"', $html);
        $this->assertStringContainsString('<option value="pdf">PDF</option>', $html);
        $this->assertStringContainsString('<option value="gambar">Gambar</option>', $html);
    }

    public function test_komponen_lain_tetap_lazy_tanpa_pencarian_dan_filter(): void
    {
        $subMenu = $this->buatSubBidang();
        $risiko = $this->buatLampiran($subMenu, 'risk_register', 'risiko.pdf', 'lampiran/risiko.pdf', 'pdf');
        $this->buatLampiran($subMenu, 'risk_register', 'risiko.xlsx', 'lampiran/risiko.xlsx', 'xlsx');

        $html = $this->ambilHalaman($subMenu);

        $this->assertSame(0, preg_match_all('/<div[^>]*data-peran="alat-cari"/', $html));
        $this->assertSame(0, preg_match_all('/<div[^>]*data-peran="kosong-cari"/', $html));
        $this->assertSame(0, preg_match_all('/<div[^>]*data-khusus-cari/', $html));

        preg_match('/<div id="dok-risk_register"[^>]*>/', $html, $akar);
        $this->assertNotEmpty($akar);
        $this->assertStringContainsString('data-lazy-pratinjau="1"', $akar[0]);

        // Pratinjau komponen lain tetap lazy, termasuk berkas kantor.
        $this->assertStringContainsString('data-src="'.$risiko->url.'#view=FitH"', $html);
        $this->assertStringContainsString('data-pratinjau-lazy="1"', $html);
        $this->assertStringContainsString('data-pratinjau-retry="1"', $html);
    }
}
