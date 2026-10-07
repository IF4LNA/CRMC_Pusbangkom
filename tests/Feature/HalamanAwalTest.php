<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Uji halaman awal: "/" harus membuka Beranda, bukan dashboard.
 *
 * Dashboard pindah ke "/dashboard" supaya pengunjung yang baru mengetik
 * alamat situs langsung membaca penjelasan CRMC, struktur, dan galeri.
 */
class HalamanAwalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $bidang = Bidang::create(['nama_bidang' => 'Bagian Umum Program & Tata Usaha']);
        SubMenu::create(['bidang_id' => $bidang->id, 'nama_sub_menu' => 'Manajemen Risiko']);
    }

    public function test_halaman_awal_membuka_beranda(): void
    {
        $res = $this->get('/');

        $res->assertOk();
        // Penanda khas Beranda, bukan dashboard.
        $res->assertSee('Tentang CRMC');
        $res->assertSee('Continuous Monitoring');
    }

    public function test_halaman_awal_bukan_dashboard(): void
    {
        $beranda = $this->get('/')->getContent();

        // Tab dashboard hanya dirender di "/dashboard".
        $this->assertStringNotContainsString('id="tab-dashboard"', $beranda);
        $this->assertStringNotContainsString('Ringkasan Personel', $beranda);
    }

    public function test_dashboard_berpindah_ke_dashboard(): void
    {
        $res = $this->get('/dashboard');

        $res->assertOk();
        $res->assertSee('id="tab-dashboard"', false);
    }

    /**
     * Tautan lama "/home" harus tetap hidup supaya bookmark dan tautan yang
     * sudah dibagikan tidak buntu.
     */
    public function test_alias_home_masih_membuka_beranda(): void
    {
        $this->get('/home')->assertOk()->assertSee('Tentang CRMC');
    }

    public function test_tautan_dashboard_dari_beranda_mengarah_ke_dashboard(): void
    {
        $res = $this->get('/');

        $res->assertOk();
        $res->assertSee('href="'.route('crmc.dashboard').'"', false);
    }

    /**
     * Setelah login, pengguna langsung masuk ke dashboard: di sanalah
     * instrumen yang perlu diisi.
     */
    public function test_setelah_login_diarahkan_ke_dashboard(): void
    {
        $user = User::create([
            'name' => 'Pegawai Uji',
            'nip' => '199001012026011234',
            'jabatan' => 'Staf',
            'email' => 'pegawai.uji@pu.go.id',
            'password' => bcrypt('password123'),
            'role' => 'pegawai',
        ]);

        $this->post('/login', [
            'email' => 'pegawai.uji@pu.go.id',
            'password' => 'password123',
        ])->assertRedirect(route('crmc.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_logout_kembali_ke_beranda(): void
    {
        $user = User::factory()->create([
            'role' => 'pegawai',
            'nip' => Str::random(12),
            'jabatan' => 'Staf',
        ]);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('beranda'));
    }

    /**
     * Hero dashboard memakai foto crmc_hero.jpg sebagai latar.
     *
     * Foto harus berada di dalam hero (bukan di luar grid), lalu teksnya
     * diletakkan pada elemen `relative` supaya tidak tertimpa gradasi.
     */
    public function test_hero_dashboard_memakai_foto_crmc_hero(): void
    {
        $html = $this->get('/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('images/crmc_hero.jpg', $html);

        // Berkas harus benar-benar ada, bukan hanya disebut di markup.
        $this->assertFileExists(public_path('images/crmc_hero.jpg'));

        $this->assertMatchesRegularExpression(
            '/<img src="[^"]*images\/crmc_hero\.jpg"[^>]*class="absolute inset-0 h-full w-full object-cover/',
            $html,
            'Foto hero harus menutupi area hero.'
        );

        $this->assertMatchesRegularExpression(
            '/images\/crmc_hero\.jpg.*?bg-gradient-to-r.*?<div class="relative flex flex-col h-full p-5 sm:p-6">/s',
            $html,
            'Teks hero harus berada pada elemen relative di atas foto dan gradiennya.'
        );
    }

    /**
     * Tombol "Mulai Kelola CRMC" memakai warna kuning.
     *
     * Tombol biru di atas gradien biru hero tidak menonjol, sementara
     * kuning sudah dipakai sebagai warna aksi pada navigasi.
     */
    public function test_tombol_mulai_kelola_hero_memakai_warna_kuning(): void
    {
        $html = $this->get('/dashboard')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<button[^>]*onclick="switchTab\(\'umum-tu\'\)"[^>]*class="[^"]*btn-kuning[^"]*"/',
            $html,
            'Tombol "Mulai Kelola CRMC" harus memakai kelas btn-kuning.'
        );

        $this->assertMatchesRegularExpression(
            '/\.btn-kuning\s*\{[^}]*background:\s*#fbbf24/',
            $html,
            'Kelas btn-kuning harus berwarna kuning.'
        );

        // Teks tombol harus navy: putih di atas kuning sulit dibaca.
        $this->assertMatchesRegularExpression(
            '/\.btn-kuning\s*\{[^}]*color:\s*var\(--navy\)/',
            $html,
            'Teks tombol kuning harus berwarna navy.'
        );
    }

    /**
     * Tombol harus berada di baris paling bawah hero, didorong ke dasar
     * lewat `mt-auto` di dalam kolom fleks yang setinggi hero.
     */
    public function test_tombol_mulai_kelola_berada_di_bawah_hero(): void
    {
        $html = $this->get('/dashboard')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<div class="mt-auto pt-5[^"]*">.*?Mulai Kelola CRMC/s',
            $html,
            'Baris tombol harus memakai mt-auto agar terdorong ke dasar hero.'
        );

        // Tombol masih di dalam hero, tidak keluar ke kartu sebelah.
        $posisiTombol = strpos($html, 'Mulai Kelola CRMC');
        $this->assertNotFalse($posisiTombol);
        $this->assertLessThan(
            strpos($html, 'Ringkasan Personel'),
            $posisiTombol,
            'Tombol harus di dalam hero, sebelum kartu ringkasan personel.'
        );

        // Kutipan dan tombol harus berada pada baris bawah hero yang sama.
        $this->assertMatchesRegularExpression(
            '/<div class="mt-auto pt-5[^"]*">.*?Infrastruktur untuk Indonesia Maju.*?btn-kuning/s',
            $html,
            'Kutipan dan tombol harus berada pada baris bawah hero yang sama.'
        );
    }

    /**
     * Menu navigasi harus menandai halaman yang sedang dibuka, termasuk
     * saat Beranda dibuka lewat alias "/home".
     */
    public function test_menu_beranda_aktif_di_beranda_dan_aliasnya(): void
    {
        foreach (['/', '/home'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();

            $this->assertMatchesRegularExpression(
                '/href="'.preg_quote(route('beranda'), '/').'"\s*\n\s*class="nav-link is-active"/',
                $html,
                "Menu Beranda harus aktif di {$url}."
            );
        }
    }
}
