<?php

namespace Tests\Feature\Crmc;

use App\Models\Bidang;
use App\Models\SubMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Navigasi "Sebelumnya"/"Berikutnya" antar sub-bidang pada halaman
 * 8 komponen (crmc.show).
 *
 * Aturan yang dijaga:
 *  - berpindah hanya di antara sub-bidang dalam bidang yang sama;
 *  - urutan mengikuti id (sama seperti daftar sub-bidang di dashboard);
 *  - sub-bidang di ujung daftar dirender sebagai tombol nonaktif;
 *  - bidang yang hanya punya satu sub-bidang tidak menampilkan navigasi;
 *  - tahun yang sedang dibuka ikut dibawa ke tautan tetangga.
 */
class NavigasiSubBidangTest extends TestCase
{
    use RefreshDatabase;

    private Bidang $bidangUmum;

    private Bidang $bidangSda;

    private \App\Models\User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bidangUmum = Bidang::create(['nama_bidang' => 'Bagian Umum Program & Tata Usaha']);
        $this->bidangSda = Bidang::create(['nama_bidang' => 'Bidang SDA']);

        $this->admin = \App\Models\User::create([
            'name' => 'Admin Uji',
            'nip' => '199001012026011234',
            'jabatan' => 'Administrator',
            'email' => 'admin.navsub@pu.go.id',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);
    }

    private function buatSubBidang(Bidang $bidang, string $nama): SubMenu
    {
        return SubMenu::create([
            'bidang_id' => $bidang->id,
            'nama_sub_menu' => $nama,
        ]);
    }

    public function test_sub_bidang_tengah_punya_tautan_sebelumnya_dan_berikutnya(): void
    {
        $this->buatSubBidang($this->bidangUmum, 'Perencanaan');
        $this->buatSubBidang($this->bidangUmum, 'Manajemen Risiko');
        $this->buatSubBidang($this->bidangUmum, 'Evaluasi');

        $response = $this->actingAs($this->admin)->get('/crmc/manajemen-risiko');

        $response->assertOk();
        $response->assertSee('data-peran="navigasi-sub-bidang"', false);

        // Sebelumnya -> Perencanaan, Berikutnya -> Evaluasi.
        $response->assertSee(route('crmc.show', ['slug' => 'perencanaan']), false);
        $response->assertSee(route('crmc.show', ['slug' => 'evaluasi']), false);
        $response->assertSee('Sub-bidang sebelumnya: Perencanaan', false);
        $response->assertSee('Sub-bidang berikutnya: Evaluasi', false);
    }

    public function test_sub_bidang_pertama_punya_berikutnya_tapi_sebelumnya_nonaktif(): void
    {
        $this->buatSubBidang($this->bidangUmum, 'Perencanaan');
        $this->buatSubBidang($this->bidangUmum, 'Manajemen Risiko');

        $response = $this->actingAs($this->admin)->get('/crmc/perencanaan');

        $response->assertOk();
        $response->assertSee(route('crmc.show', ['slug' => 'manajemen-risiko']), false);

        // Tidak ada tautan ke sub-bidang sebelum Perencanaan.
        $response->assertSee('Sudah di sub-bidang pertama', false);
    }

    public function test_sub_bidang_terakhir_punya_sebelumnya_tapi_berikutnya_nonaktif(): void
    {
        $this->buatSubBidang($this->bidangUmum, 'Perencanaan');
        $this->buatSubBidang($this->bidangUmum, 'Manajemen Risiko');

        $response = $this->actingAs($this->admin)->get('/crmc/manajemen-risiko');

        $response->assertOk();
        $response->assertSee(route('crmc.show', ['slug' => 'perencanaan']), false);
        $response->assertSee('Sudah di sub-bidang terakhir', false);
    }

    public function test_navigasi_tidak_menyeberang_ke_bidang_lain(): void
    {
        // Manajemen Risiko adalah sub-bidang terakhir di bidang umum; bidang
        // SDA punya sub-bidang sendiri. Tombol "Berikutnya" tidak boleh
        // menunjuk ke sub-bidang bidang SDA.
        $this->buatSubBidang($this->bidangUmum, 'Perencanaan');
        $this->buatSubBidang($this->bidangUmum, 'Manajemen Risiko');
        $this->buatSubBidang($this->bidangSda, 'Pengelolaan SDA');

        $response = $this->actingAs($this->admin)->get('/crmc/manajemen-risiko');

        $response->assertOk();
        $response->assertSee(route('crmc.show', ['slug' => 'perencanaan']), false);
        $response->assertDontSee(route('crmc.show', ['slug' => 'pengelolaan-sda']), false);
        $response->assertSee('Sudah di sub-bidang terakhir', false);
    }

    public function test_bidang_dengan_satu_sub_bidang_tidak_menampilkan_navigasi(): void
    {
        $this->buatSubBidang($this->bidangSda, 'Manajemen Risiko');

        $response = $this->actingAs($this->admin)->get('/crmc/manajemen-risiko');

        $response->assertOk();
        $response->assertDontSee('data-peran="navigasi-sub-bidang"', false);
    }

    public function test_tahun_yang_dibuka_ikut_dibawa_ke_tautan_tetangga(): void
    {
        $this->buatSubBidang($this->bidangUmum, 'Perencanaan');
        $this->buatSubBidang($this->bidangUmum, 'Manajemen Risiko');

        $response = $this->actingAs($this->admin)->get('/crmc/perencanaan?tahun=2027');

        $response->assertOk();
        $response->assertSee(
            route('crmc.show', ['slug' => 'manajemen-risiko', 'tahun' => 2027]),
            false
        );
    }
}
