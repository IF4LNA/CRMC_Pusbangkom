<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Bidang;
use App\Models\SubMenu;
use App\Models\PenugasanCrmc;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed bidang and submenu
        $bidang = Bidang::create(['nama_bidang' => 'Bagian Umum Program & Tata Usaha']);
        SubMenu::create([
            'bidang_id' => $bidang->id,
            'nama_sub_menu' => 'Manajemen Risiko'
        ]);
    }

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_login_page_renders_properly(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Masuk Sistem CRMC');
        // Latar foto gedung memakai aset yang sama dengan banner Beranda.
        $response->assertSee('images/gedung_pusbangkom.jpg', false);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::create([
            'name' => 'Test Admin',
            'nip' => '199001012026011999',
            'jabatan' => 'Administrator',
            'email' => 'admin@pu.go.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@pu.go.id',
            'password' => 'password123',
        ]);

        // Setelah masuk, pengguna langsung diarahkan ke dashboard CRMC.
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_crmc_show_page_returns_successful_response(): void
    {
        $response = $this->get('/crmc/manajemen-risiko');

        $response->assertStatus(200);
        $response->assertSee('Rincian 8 Komponen');
        $response->assertSee('Manajemen Risiko');
    }

    public function test_crmc_update_redirects_successfully(): void
    {
        $pegawai = User::create([
            'name' => 'Fajar Fikri',
            'nip' => '199001012026011234',
            'jabatan' => 'Staf',
            'email' => 'fajar@pu.go.id',
            'password' => Hash::make('password123'),
            'role' => 'pegawai',
        ]);

        // Penyimpanan ringkasan hanya boleh dilakukan pengguna yang login,
        // jadi test harus menyamar sebagai pegawai.
        $response = $this->actingAs($pegawai)->post('/crmc/manajemen-risiko', [
            'pegawai' => 'Fajar Fikri',
            'risk_register' => 'RR-TEST-001',
            'residu' => 'waspada_i',
            'evaluasi' => 'Pengujian berhasil.',
            'tahun_pelaksanaan' => 2026,
        ]);

        $response->assertRedirect('/crmc/manajemen-risiko?tahun=2026');
        $response->assertSessionHas('success');
    }

    public function test_admin_can_update_penugasan_for_sub_bidang(): void
    {
        $admin = User::create([
            'name' => 'Fajar Admin',
            'nip' => '199001012026011001',
            'jabatan' => 'Admin CRMC',
            'email' => 'admin@pu.go.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        $kapus = User::create([
            'name' => 'Dr. Budi Santoso',
            'nip' => '197502022000031002',
            'jabatan' => 'Kepala Pusat',
            'email' => 'kapus@pu.go.id',
            'password' => Hash::make('password123'),
            'role' => 'pegawai',
        ]);

        $kabag = User::create([
            'name' => 'Andi Wirawan',
            'nip' => '198003032005011003',
            'jabatan' => 'Kabag TU',
            'email' => 'kabag.tu@pu.go.id',
            'password' => Hash::make('password123'),
            'role' => 'pegawai',
        ]);

        $staf1 = User::create([
            'name' => 'Rina Melati',
            'nip' => '199505052020122005',
            'jabatan' => 'Staf Evaluasi',
            'email' => 'rina.staf@pu.go.id',
            'password' => Hash::make('password123'),
            'role' => 'pegawai',
        ]);

        $staf2 = User::create([
            'name' => 'Dwi Prasetyo',
            'nip' => '199308152019031006',
            'jabatan' => 'Staf Teknis',
            'email' => 'dwi.staf@pu.go.id',
            'password' => Hash::make('password123'),
            'role' => 'pegawai',
        ]);

        // Penugasan Komponen 1 disimpan per tahun, jadi formnya menyertakan
        // tahun pelaksanaan.
        $response = $this->actingAs($admin)->post('/crmc/manajemen-risiko/penugasan', [
            'pemilik_risiko_id' => $kapus->id,
            'pengendali_mutu_id' => $kabag->id,
            'pengendali_risiko_ids' => [$staf1->id, $staf2->id],
            'tahun_pelaksanaan' => 2026,
        ]);

        $response->assertRedirect('/crmc/manajemen-risiko?tahun=2026');
        $response->assertSessionHas('success');

        // Pastikan penugasan tersimpan di database
        $this->assertDatabaseHas('penugasan_crmc', [
            'user_id' => $kapus->id,
            'peran' => 'pemilik_risiko',
            'tahun_pelaksanaan' => 2026,
        ]);
        $this->assertDatabaseHas('penugasan_crmc', [
            'user_id' => $kabag->id,
            'peran' => 'pengendali_mutu',
        ]);
        $this->assertDatabaseHas('penugasan_crmc', [
            'user_id' => $staf1->id,
            'peran' => 'pengendali_risiko',
        ]);
        $this->assertDatabaseHas('penugasan_crmc', [
            'user_id' => $staf2->id,
            'peran' => 'pengendali_risiko',
        ]);
    }

    public function test_non_admin_cannot_update_penugasan(): void
    {
        $pegawai = User::create([
            'name' => 'Staf Biasa',
            'nip' => '199505052020122999',
            'jabatan' => 'Staf',
            'email' => 'biasa@pu.go.id',
            'password' => Hash::make('password123'),
            'role' => 'pegawai',
        ]);

        $response = $this->actingAs($pegawai)->post('/crmc/manajemen-risiko/penugasan', [
            'pemilik_risiko_id' => 1,
            'pengendali_mutu_id' => 2,
            'pengendali_risiko_ids' => [3],
        ]);

        $response->assertStatus(403);
    }
}
