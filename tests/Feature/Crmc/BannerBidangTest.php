<?php

namespace Tests\Feature\Crmc;

use App\Models\Bidang;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Uji banner navigasi bidang: gambar bawaan, unggah/ganti oleh admin,
 * penghapusan banner kustom, dan pembatasan hak akses.
 */
class BannerBidangTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $pegawai;

    private Bidang $bidang;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'name' => 'Admin Banner',
            'nip' => 'NIP0000010001',
            'jabatan' => 'Administrator',
        ]);

        $this->pegawai = User::factory()->create([
            'role' => 'pegawai',
            'name' => 'Pegawai Banner',
            'nip' => 'NIP0000010002',
            'jabatan' => 'Staf',
        ]);

        $this->bidang = Bidang::create(['nama_bidang' => 'Bidang SDA']);
        SubMenu::create([
            'bidang_id' => $this->bidang->id,
            'nama_sub_menu' => 'Bangkom SDA',
        ]);
    }

    public function test_banner_bidang_tampil_dengan_gambar_bawaan(): void
    {
        $this->actingAs($this->admin)->get('/dashboard')
            ->assertOk()
            ->assertSee('Navigasi Bidang')
            ->assertSee('images/crmc_hero.jpg')
            ->assertSee('Ganti Banner')
            ->assertSee('Tambah Sub-Bidang');
    }

    public function test_admin_bisa_mengunggah_banner_bidang(): void
    {
        $this->actingAs($this->admin)
            ->from('/dashboard')
            ->post('/bidang/' . $this->bidang->id . '/banner', [
                'gambar' => UploadedFile::fake()->image('banner.jpg', 1600, 600),
            ])
            ->assertRedirect('/dashboard')
            ->assertSessionHasNoErrors();

        $this->bidang->refresh();
        $this->assertNotNull($this->bidang->gambar_latar);
        Storage::disk('public')->assertExists($this->bidang->gambar_latar);

        $this->actingAs($this->admin)->get('/dashboard')
            ->assertOk()
            ->assertSee($this->bidang->gambar_latar_url);
    }

    public function test_mengganti_banner_menghapus_file_lama(): void
    {
        $this->actingAs($this->admin)->post('/bidang/' . $this->bidang->id . '/banner', [
            'gambar' => UploadedFile::fake()->image('lama.jpg', 1200, 500),
        ]);

        $pathLama = $this->bidang->fresh()->gambar_latar;
        $this->assertNotNull($pathLama);

        $this->actingAs($this->admin)->post('/bidang/' . $this->bidang->id . '/banner', [
            'gambar' => UploadedFile::fake()->image('baru.jpg', 1200, 500),
        ])->assertRedirect();

        Storage::disk('public')->assertMissing($pathLama);
        $pathBaru = $this->bidang->fresh()->gambar_latar;
        $this->assertNotSame($pathLama, $pathBaru);
        Storage::disk('public')->assertExists($pathBaru);
    }

    public function test_admin_bisa_menghapus_banner_kustom(): void
    {
        $this->actingAs($this->admin)->post('/bidang/' . $this->bidang->id . '/banner', [
            'gambar' => UploadedFile::fake()->image('banner.jpg', 1200, 500),
        ]);

        $path = $this->bidang->fresh()->gambar_latar;
        $this->assertNotNull($path);

        $this->actingAs($this->admin)
            ->from('/dashboard')
            ->post('/bidang/' . $this->bidang->id . '/banner', ['hapus_gambar' => '1'])
            ->assertRedirect('/dashboard')
            ->assertSessionHasNoErrors();

        $this->bidang->refresh();
        $this->assertNull($this->bidang->gambar_latar);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_berkas_bukan_gambar_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->post('/bidang/' . $this->bidang->id . '/banner', [
                'gambar' => UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'),
            ])
            ->assertSessionHasErrors('gambar', null, 'bannerBidang');

        $this->assertNull($this->bidang->fresh()->gambar_latar);
    }

    public function test_tanpa_gambar_dan_tanpa_hapus_ditolak(): void
    {
        $this->actingAs($this->admin)
            ->post('/bidang/' . $this->bidang->id . '/banner', [])
            ->assertSessionHasErrors(
                ['gambar' => 'Pilih gambar banner terlebih dahulu, atau centang opsi hapus banner.'],
                null,
                'bannerBidang'
            );
    }

    public function test_pegawai_tidak_boleh_mengganti_banner(): void
    {
        $this->actingAs($this->pegawai)
            ->post('/bidang/' . $this->bidang->id . '/banner', [
                'gambar' => UploadedFile::fake()->image('banner.jpg', 800, 400),
            ])
            ->assertForbidden();

        $this->assertNull($this->bidang->fresh()->gambar_latar);
    }

    public function test_pegawai_tidak_melihat_tombol_ganti_banner(): void
    {
        $this->actingAs($this->pegawai)->get('/dashboard')
            ->assertOk()
            ->assertDontSee('Ganti Banner')
            ->assertDontSee('modalBannerBidang');
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->post('/bidang/' . $this->bidang->id . '/banner', [])
            ->assertRedirect('/login');
    }

    public function test_bidang_tak_dikenal_menghasilkan_404(): void
    {
        $this->actingAs($this->admin)
            ->post('/bidang/9999/banner', [
                'gambar' => UploadedFile::fake()->image('banner.jpg', 800, 400),
            ])
            ->assertNotFound();
    }
}
