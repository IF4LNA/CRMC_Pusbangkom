<?php

namespace Tests\Feature\Crmc;

use App\Models\Bidang;
use App\Models\SubMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tombol melayang di halaman 8 Komponen harus bisa melompat ke setiap
 * komponen, jadi anchor 1-8 dan daftar tautannya perlu selalu dirender.
 */
class NavigasiCepatKomponenTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_komponen_punya_navigasi_cepat_ke_semua_komponen(): void
    {
        $bidang = Bidang::create(['nama_bidang' => 'Bidang Uji Navigasi']);

        SubMenu::create([
            'bidang_id' => $bidang->id,
            'nama_sub_menu' => 'Manajemen Risiko',
        ]);

        $res = $this->get('/crmc/manajemen-risiko');

        $res->assertOk()
            ->assertSee('id="navCepatKomponen"', false)
            ->assertSee('id="tombolCepatKomponen"', false)
            ->assertSee('lompatKeKomponen', false);

        foreach (range(1, 8) as $nomor) {
            $res->assertSee('id="komponen-' . $nomor . '"', false);
        }

        foreach ([
            'Identitas Pegawai',
            'Risk Register Spesifik Acuan',
            'Standar Operasional Prosedur (SOP)',
            'Formulir Pengendalian / Daftar Periksa',
            'Jadwal Rencana Pelaksanaan',
            'Bukti Pelaksanaan',
            'Status Residu Risiko',
            'Evaluasi & Rencana Perbaikan',
        ] as $judul) {
            $res->assertSeeText($judul);
        }
    }
}
