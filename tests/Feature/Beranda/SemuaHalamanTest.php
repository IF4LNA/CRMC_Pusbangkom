<?php

namespace Tests\Feature\Beranda;

use App\Models\Bidang;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Uji asap untuk seluruh halaman yang ada.
 *
 * Tujuannya bukan memeriksa isi tiap halaman, melainkan memastikan setiap
 * halaman masih bisa dirender tanpa error setelah perubahan bersama,
 * terutama pada layouts/app.blade.php yang dipakai hampir semua halaman.
 */
class SemuaHalamanTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_yang_sama_lagi_tetap_terbuka(): void
    {
        // Dashboard pindah ke "/dashboard"; "/" kini halaman Beranda.
        $this->get('/dashboard')->assertOk();
    }

    /**
     * Halaman yang dibuka pertama kali harus Beranda, bukan dashboard.
     */
    public function test_halaman_awal_adalah_beranda(): void
    {
        $this->get('/')->assertOk()->assertSee('Tentang CRMC');
    }

    public function test_formulir_login_terbuka(): void
    {
        $this->get('/login')->assertOk()->assertSee('name="email"', false);
    }

    /**
     * Foto gedung pada halaman login harus benar-benar terlihat.
     *
     * <body> memakai `bg-blue-950` yang opaque, jadi lapisan foto tidak
     * boleh memakai z-index negatif. Nilai seperti -z-10 digambar di
     * belakang warna body sehingga fotonya hilang sama sekali.
     */
    public function test_foto_gedung_pada_halaman_login_tidak_tertutup_lapis_warna(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();

        $this->assertStringContainsString('images/gedung_pusbangkom.jpg', $html);

        // Wrapper foto harus berada di stack yang sama atau di atas
        // background body, bukan di bawahnya.
        $this->assertDoesNotMatchRegularExpression(
            '/fixed inset-0 -z-\d+/',
            $html,
            'Lapis foto tidak boleh memakai z-index negatif karena body punya background opaque.'
        );

        // Isi halaman (header, main, footer) harus di atas foto.
        $this->assertMatchesRegularExpression(
            '/<header class="relative z-10 /',
            $html,
            'Header harus di atas lapisan foto.'
        );
        $this->assertMatchesRegularExpression(
            '/<main class="relative z-10 /',
            $html,
            'Kartu login harus di atas lapisan foto.'
        );
    }

    public function test_setiap_halaman_komponen_terbuka(): void
    {
        // Komponen dibuat lebih dulu supaya controller punya sub-bidang
        // sungguhan untuk dirender, bukan hanya fallback nama.
        $bidang = Bidang::create(['nama_bidang' => 'Bagian Umum Program & Tata Usaha']);

        foreach (['Manajemen Risiko', 'Pengajuan Ls', 'Rencana Anggaran'] as $nama) {
            SubMenu::create([
                'bidang_id' => $bidang->id,
                'nama_sub_menu' => $nama,
            ]);
        }

        foreach (SubMenu::all() as $sub) {
            $this->get('/crmc/' . Str::slug($sub->nama_sub_menu))
                ->assertOk();
        }
    }

    public function test_halaman_beranda_terbuka_untuk_admin(): void
    {
        $this->actingAs($this->admin())->get('/home')->assertOk();
    }

    public function test_halaman_kelola_pegawai_terbuka_untuk_admin(): void
    {
        $this->actingAs($this->admin())->get('/admin/pegawai')->assertOk();
    }

    public function test_halaman_admin_menuntut_login(): void
    {
        $this->get('/admin/pegawai')->assertRedirect('/login');
    }

    private function admin(): User
    {
        static $urut = 0;
        $urut++;

        return User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin Asap ' . $urut,
            'nip' => 'ASAP' . str_pad((string) $urut, 8, '0', STR_PAD_LEFT),
            'jabatan' => 'Administrator',
        ]);
    }
}
