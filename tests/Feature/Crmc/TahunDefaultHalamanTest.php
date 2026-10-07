<?php

namespace Tests\Feature\Crmc;

use App\Models\Bidang;
use App\Models\DokumenCrmc;
use App\Models\SubMenu;
use App\Models\TahunAnggaran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Uji tahun default pada halaman 8 Komponen.
 *
 * `daftarTahun()` sengaja memuat tahun berjalan + 1 tahun ke depan supaya
 * admin bisa merencanakan tahun berikutnya. Tapi halaman harus dibuka di
 * tahun berjalan, bukan di tahun paling akhir pada daftar, karena tahun
 * berikutnya belum punya dokumen maupun penugasan apa pun.
 */
class TahunDefaultHalamanTest extends TestCase
{
    use RefreshDatabase;

    private SubMenu $subMenu;

    private int $tahun;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tahun = (int) date('Y');

        $bidang = Bidang::create(['nama_bidang' => 'Bagian Umum Program & Tata Usaha']);

        $this->subMenu = SubMenu::create([
            'bidang_id' => $bidang->id,
            'nama_sub_menu' => 'Manajemen Risiko',
        ]);
    }

    public function test_tahun_berjalan_tetap_ada_di_pilihan_tahun(): void
    {
        // Tahun depan tetap boleh dipilih untuk perencanaan, jadi daftar
        // tahun tidak boleh ikut diperkecil.
        $res = $this->get('/crmc/manajemen-risiko');

        $res->assertOk();
        $res->assertSee('value="'.($this->tahun + 1).'"', false);
        $res->assertSee('value="'.$this->tahun.'"', false);
    }

    /**
     * Tanpa parameter ?tahun, halaman harus terbuka di tahun berjalan.
     */
    public function test_halaman_terbuka_di_tahun_berjalan_secara_bawaan(): void
    {
        $res = $this->get('/crmc/manajemen-risiko');

        $res->assertOk();
        $res->assertSee('Tahun: <strong class="text-white">'.$this->tahun.'</strong>', false);
    }

    /**
     * Walaupun dokumen untuk tahun berikutnya sudah ada, tahun default
     * tetap harus tahun berjalan.
     */
    public function test_tahun_default_bukan_tahun_berikutnya_walau_ada_dokumennya(): void
    {
        DokumenCrmc::create([
            'sub_menu_id' => $this->subMenu->id,
            'tahun_pelaksanaan' => $this->tahun + 1,
        ]);

        $res = $this->get('/crmc/manajemen-risiko');

        $res->assertOk();
        $res->assertSee('Tahun: <strong class="text-white">'.$this->tahun.'</strong>', false);
    }

    /**
     * Tahun yang ditanyakan lewat URL tetap dihormati, supaya admin masih
     * bisa membuka tahun lampau untuk membandingkan.
     */
    public function test_tahun_yang_diminta_lewat_url_tetap_dihormati(): void
    {
        $res = $this->get('/crmc/manajemen-risiko?tahun='.($this->tahun + 1));

        $res->assertOk();
        $res->assertSee('Tahun: <strong class="text-white">'.($this->tahun + 1).'</strong>', false);
    }

    /**
     * Tahun yang tidak ada di daftar harus jatuh ke tahun berjalan, bukan
     * tampil hampa.
     */
    public function test_tahun_tak_dikenal_kembali_ke_tahun_berjalan(): void
    {
        $res = $this->get('/crmc/manajemen-risiko?tahun=1800');

        $res->assertOk();
        $res->assertSee('Tahun: <strong class="text-white">'.$this->tahun.'</strong>', false);
    }

    /**
     * Tahun yang sengaja ditambahkan admin harus bisa dibuka lewat URL,
     * dan default halaman tetap tahun berjalan.
     */
    public function test_tahun_dari_admin_tetap_bisa_dibuka(): void
    {
        TahunAnggaran::create(['tahun' => 2035]);

        $res = $this->get('/crmc/manajemen-risiko');

        $res->assertOk();
        $res->assertSee('value="2035"', false);
        $res->assertSee('Tahun: <strong class="text-white">'.$this->tahun.'</strong>', false);

        $this->get('/crmc/manajemen-risiko?tahun=2035')
            ->assertOk()
            ->assertSee('Tahun: <strong class="text-white">2035</strong>', false);
    }
}
