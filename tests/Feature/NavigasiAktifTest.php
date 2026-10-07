<?php

namespace Tests\Feature;

use App\Models\Bidang;
use App\Models\SubMenu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Uji status aktif pada navigasi utama.
 *
 * Dua hal yang diuji:
 *   1. Warna status aktif memakai kuning, sama seperti hover dan seperti
 *      warna logo Kementerian Pekerjaan Umum.
 *   2. Saat tab berpindah, penanda aktif pindah ikut. "Dashboard" dan
 *      "Beranda" adalah <a>, bukan <button>, jadi pembersihan status aktif
 *      tidak boleh hanya menyasar .nav-btn.
 */
class NavigasiAktifTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $bidang = Bidang::create(['nama_bidang' => 'Bagian Umum Program & Tata Usaha']);
        SubMenu::create(['bidang_id' => $bidang->id, 'nama_sub_menu' => 'Manajemen Risiko']);
    }

    // ====================
    // Warna status aktif
    // ====================

    public function test_status_aktif_memakai_kuning(): void
    {
        $css = $this->cssNavigasi();

        $this->assertMatchesRegularExpression(
            '/\.nav-link\.is-active\s*\{[^}]*background:\s*#fbbf24/',
            $css,
            'Status aktif navigasi harus memakai kuning.'
        );
    }

    /**
     * Varian header gelap menulis ulang warnanya, jadi aturan kuning
     * harus ada di sana juga. Kalau tidak, .nav-dark .nav-link di atasnya
     * akan menimpa warna status aktif.
     */
    public function test_status_aktif_pada_header_gelap_juga_kuning(): void
    {
        $css = $this->cssNavigasi();

        $this->assertMatchesRegularExpression(
            '/\.nav-dark \.nav-link\.is-active\s*\{[^}]*background:\s*#fbbf24/',
            $css,
            'Status aktif pada header gelap harus tetap kuning.'
        );
    }

    /**
     * Kursor yang lewat ke tab aktif tidak boleh membuat tab itu terlihat
     * seperti berubah status. Karena warna sama,atur hover dan aktif
     * harus sama persis.
     */
    public function test_status_aktif_tetap_kuning_saat_disorot(): void
    {
        $css = $this->cssNavigasi();

        $this->assertMatchesRegularExpression(
            '/\.nav-dark \.nav-link\.is-active:hover[^{]*\{[^}]*background:\s*#fbbf24/',
            $css,
            'Hover di atas tab aktif harus tetap kuning.'
        );
    }

    public function test_warna_aktif_bukan_lagi_putih(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/\.nav-dark \.nav-link\.is-active\s*\{[^}]*background:\s*#fff/',
            $this->cssNavigasi(),
            'Status aktif tidak boleh lagi putih.'
        );
    }

    // ====================
    // Perpindahan tab
    // ====================

    /**
     * switchTab() harus melepas status aktif dari seluruh tautan navigasi.
     *
     * Sebelumnya hanya .nav-btn yang dibersihkan, sehingga tautan
     * <a> seperti "Dashboard" tetap menyala setelah pindah tab.
     */
    public function test_perpindahan_tab_melepas_status_aktif_dari_seluruh_tautan(): void
    {
        $html = $this->get('/dashboard')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            "/querySelectorAll\('#navUtama \\.nav-link'\)/",
            $html,
            'switchTab() harus membersihkan seluruh .nav-link di dalam navigasi.'
        );

        $this->assertDoesNotMatchRegularExpression(
            "/querySelectorAll\('\.nav-btn'\)/",
            $html,
            'Membersihkan hanya .nav-btn akan meninggalkan tautan <a> tetap aktif.'
        );
    }

    /**
     * Tautan dashboard harus punya id agar bisa dinyalakan kembali saat
     * tab dashboard dibuka lewat ?tab=dashboard.
     */
    public function test_tautan_dashboard_punya_id_untuk_switchtab(): void
    {
        $res = $this->get('/dashboard');

        $res->assertOk();
        $res->assertSee('id="nav-dashboard"', false);
    }

    public function test_hanya_satu_tautan_aktif_per_waktu(): void
    {
        // Tanpa ?tab, tab dashboard yang aktif.
        $html = $this->get('/dashboard')->assertOk()->getContent();

        $this->assertSame(
            1,
            substr_count($this->navUtama($html), 'is-active'),
            'Hanya satu tautan navigasi yang boleh aktif.'
        );

        // Dengan ?tab=dasar-hukum, yang aktif pindah ke Dasar Hukum.
        $html = $this->get('/dashboard?tab=dasar-hukum')->assertOk()->getContent();

        $nav = $this->navUtama($html);
        $this->assertSame(1, substr_count($nav, 'is-active'));
        $this->assertStringContainsString('id="nav-dasar-hukum"', $nav);
    }

    /**
     * Di halaman Beranda, hanya "Beranda" yang aktif. Menu tab dashboard
     * tidak boleh ikut menyala karena halaman itu memang tidak punya tab.
     */
    public function test_halaman_beranda_hanya_menyalakan_beranda(): void
    {
        $nav = $this->navUtama($this->get('/')->assertOk()->getContent());

        $this->assertSame(1, substr_count($nav, 'is-active'));

        // Id tab tetap ada di markup, yang dicek adalah status aktifnya:
        // di Beranda tidak ada tab yang sedang dibuka.
        $this->assertDoesNotMatchRegularExpression(
            '/id="nav-dasar-hukum"[^>]*nav-link[^"]*is-active/',
            $nav,
            'Menu tab tidak boleh aktif di halaman Beranda.'
        );
    }

    /**
     * Ambil isi elemen <nav id="navUtama"> supaya penanda aktif di luar
     * navigasi tidak ikut terhitung.
     */
    private function navUtama(string $html): string
    {
        $this->assertSame(1, preg_match('/<nav id="navUtama".*?<\/nav>/s', $html, $blok), 'Navigasi utama tidak ditemukan.');

        return $blok[0];
    }

    /** Ambil blok CSS navigasi dari halaman yang memakainya. */
    private function cssNavigasi(): string
    {
        return $this->get('/dashboard')->assertOk()->getContent();
    }
}
