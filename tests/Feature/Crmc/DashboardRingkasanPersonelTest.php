<?php

namespace Tests\Feature\Crmc;

use App\Models\Bidang;
use App\Models\PenugasanCrmc;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Uji kartu "Ringkasan Personel" pada dashboard.
 *
 * Kartu ini menggantikan angka dokumen karangan yang sebelumnya ditulis
 * manual di view. Isinya hanya jumlah angka per peran PIC, dihitung dari
 * tabel `users` dan `penugasan_crmc` untuk tahun berjalan.
 */
class DashboardRingkasanPersonelTest extends TestCase
{
    use RefreshDatabase;

    private int $tahun;

    private User $kapus;

    private User $kabag;

    private User $staf;

    private SubMenu $subMenuA;

    private SubMenu $subMenuB;

    protected function setUp(): void
    {
        parent::setUp();

        // Kartu dashboard selalu memakai tahun berjalan, jadi penugasan
        // pada pengujian juga harus dibuat untuk tahun yang sama.
        $this->tahun = (int) date('Y');

        $this->buatUser('admin', 'Siti Aminah', 'Administrator');
        $this->kapus = $this->buatUser('pegawai', 'Budi Santoso', 'Kepala Pusat');
        $this->kabag = $this->buatUser('pegawai', 'Andi Wirawan', 'Kepala Bagian TU');
        $this->staf = $this->buatUser('pegawai', 'Rina Melati', 'Staf Pelaksana');

        $bidang = Bidang::create(['nama_bidang' => 'Bagian Umum Program & Tata Usaha']);

        $this->subMenuA = SubMenu::create([
            'bidang_id' => $bidang->id,
            'nama_sub_menu' => 'Manajemen Risiko',
        ]);

        $this->subMenuB = SubMenu::create([
            'bidang_id' => $bidang->id,
            'nama_sub_menu' => 'Rencana Anggaran',
        ]);
    }

    private function buatUser(string $role, string $nama, string $jabatan): User
    {
        static $urut = 0;
        $urut++;

        return User::factory()->create([
            'role' => $role,
            'name' => $nama,
            'nip' => 'NIP'.str_pad((string) $urut, 8, '0', STR_PAD_LEFT),
            'jabatan' => $jabatan,
        ]);
    }

    public function test_kartu_menampilkan_empat_angka_ringkasan(): void
    {
        $this->tugaskan($this->subMenuA);

        $res = $this->get('/dashboard');

        $res->assertOk();
        $res->assertSee('Ringkasan Personel');
        $res->assertSee('Tahun '.$this->tahun);

        $this->assertAngkaKartu('Pemilik Risiko', 1);
        $this->assertAngkaKartu('Pengendali Mutu', 1);
        $this->assertAngkaKartu('Pengendali Risiko', 1);
        $this->assertAngkaKartu('Jumlah Pegawai', 4);
    }

    /**
     * Pemilik Risiko ditulis ke seluruh sub-bidang, jadi satu orang ada di
     * beberapa baris penugasan. Kalau yang dihitung jumlah baris, angkanya
     * ikut naik dan jadi salah.
     */
    public function test_satu_orang_ditulis_di_semua_sub_bidang_tetap_dihitung_satu(): void
    {
        $this->tugaskan($this->subMenuA);
        $this->tugaskan($this->subMenuB);

        $html = $this->get('/dashboard')->assertOk()->getContent();

        $this->assertAngkaKartu('Pemilik Risiko', 1, $html);
        $this->assertAngkaKartu('Pengendali Mutu', 1, $html);
        $this->assertAngkaKartu('Pengendali Risiko', 1, $html);
    }

    public function test_banyak_pengendali_risiko_dihitung_per_orang(): void
    {
        $stafLain = $this->buatUser('pegawai', 'Dwi Prasetyo', 'Staf Teknis');

        $this->tugaskan($this->subMenuA);
        PenugasanCrmc::create([
            'sub_menu_id' => $this->subMenuA->id,
            'user_id' => $stafLain->id,
            'peran' => 'pengendali_risiko',
            'tahun_pelaksanaan' => $this->tahun,
        ]);

        $this->assertAngkaKartu('Pengendali Risiko', 2);
    }

    public function test_angka_hanya_mengikuti_penugasan_tahun_berjalan(): void
    {
        $this->tugaskan($this->subMenuA);

        // Penugasan tahun lalu sengaja dibuat untuk orang yang berbeda,
        // supaya penyaringan tahun di dashboard ikut teruji.
        $lama = $this->buatUser('pegawai', 'Orang Tahun Lalu', 'Staf Lama');
        PenugasanCrmc::create([
            'sub_menu_id' => $this->subMenuA->id,
            'user_id' => $lama->id,
            'peran' => 'pengendali_risiko',
            'tahun_pelaksanaan' => $this->tahun - 1,
        ]);

        $this->assertAngkaKartu('Pengendali Risiko', 1);
    }

    public function test_jumlah_pegawai_mengikuti_tabel_users(): void
    {
        $res = $this->get('/dashboard');

        $res->assertOk();
        $this->assertAngkaKartu('Jumlah Pegawai', 4);
        $res->assertSee('3 pegawai');
        $res->assertSee('1 admin');
    }

    public function test_tanpa_penugasan_seluruh_angka_peran_nol(): void
    {
        $this->assertAngkaKartu('Pemilik Risiko', 0);
        $this->assertAngkaKartu('Pengendali Mutu', 0);
        $this->assertAngkaKartu('Pengendali Risiko', 0);
    }

    /**
     * Nama PIC sengaja tidak boleh muncul di dashboard. Daftar lengkapnya
     * sudah tersedia di Komponen 1 halaman 8 Komponen.
     */
    public function test_kartu_tidak_menampilkan_nama_pic(): void
    {
        $this->tugaskan($this->subMenuA);

        $html = $this->get('/dashboard')->assertOk()->getContent();

        $this->assertStringNotContainsString('Budi Santoso', $html);
        $this->assertStringNotContainsString('Andi Wirawan', $html);
        $this->assertStringNotContainsString('Rina Melati', $html);
    }

    /**
     * Mencocokkan angka pada kartu tertentu, bukan angka pertama di kartu.
     *
     * @param  string|null  $html  HTML yang sudah diambil; diambil ulang bila null.
     */
    private function assertAngkaKartu(string $label, int $harapan, ?string $html = null): void
    {
        $html ??= $this->get('/dashboard')->assertOk()->getContent();

        $pola = '/'.preg_quote($label, '/').'\s*<\/p>\s*<p class="stat-value[^"]*[^>]*>\s*'
            .$harapan.'\s*</';

        $this->assertMatchesRegularExpression(
            $pola,
            $html,
            "Kartu \"{$label}\" harus berisi angka {$harapan}."
        );
    }

    /**
     * Meniru apa yang dilakukan CrmcController::updatePenugasan: pemilik
     * risiko untuk semua sub-bidang, pengendali mutu untuk bidangnya,
     * pengendali risiko untuk sub-bidang tersebut saja.
     */
    private function tugaskan(SubMenu $sub): void
    {
        PenugasanCrmc::create([
            'sub_menu_id' => $sub->id,
            'user_id' => $this->kapus->id,
            'peran' => 'pemilik_risiko',
            'tahun_pelaksanaan' => $this->tahun,
        ]);

        PenugasanCrmc::create([
            'sub_menu_id' => $sub->id,
            'user_id' => $this->kabag->id,
            'peran' => 'pengendali_mutu',
            'tahun_pelaksanaan' => $this->tahun,
        ]);

        PenugasanCrmc::create([
            'sub_menu_id' => $sub->id,
            'user_id' => $this->staf->id,
            'peran' => 'pengendali_risiko',
            'tahun_pelaksanaan' => $this->tahun,
        ]);
    }
}
