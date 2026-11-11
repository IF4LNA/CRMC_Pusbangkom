<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Uji verifikasi Cloudflare Turnstile pada halaman login.
 *
 * Verifikasi captcha dipalsukan lewat Http::fake() sehingga tes tidak
 * menghubungi server Cloudflare sungguhan, tetapi jalur kode yang
 * ditempuh tetap sama dengan produksi.
 */
class LoginCaptchaTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_login_menampilkan_widget_turnstile(): void
    {
        config(['services.turnstile.site_key' => 'site-key-uji']);

        $this->get('/login')
            ->assertOk()
            ->assertSee('cf-turnstile', false)
            ->assertSee('site-key-uji', false)
            ->assertSee('challenges.cloudflare.com/turnstile/v0/api.js', false);
    }

    /**
     * CSP harus mengizinkan challenges.cloudflare.com pada script-src,
     * frame-src, style-src, dan connect-src. Tanpa itu widget Turnstile
     * diblokir browser diam-diam dan pengguna tidak bisa login sama sekali.
     */
    public function test_csp_mengizinkan_domain_turnstile(): void
    {
        $csp = $this->get('/login')->assertOk()->headers->get('Content-Security-Policy');

        $this->assertNotNull($csp, 'Halaman login harus mengirim Content-Security-Policy.');
        $this->assertMatchesRegularExpression('/script-src[^;]*https:\/\/challenges\.cloudflare\.com/', $csp);
        $this->assertMatchesRegularExpression('/frame-src[^;]*https:\/\/challenges\.cloudflare\.com/', $csp);
        $this->assertMatchesRegularExpression('/style-src[^;]*https:\/\/challenges\.cloudflare\.com/', $csp);
        $this->assertMatchesRegularExpression('/connect-src[^;]*https:\/\/challenges\.cloudflare\.com/', $csp);
    }

    public function test_halaman_login_tanpa_site_key_tidak_menampilkan_widget(): void
    {
        config(['services.turnstile.site_key' => null]);

        $this->get('/login')
            ->assertOk()
            ->assertDontSee('cf-turnstile', false)
            ->assertDontSee('challenges.cloudflare.com', false);
    }

    public function test_login_ditolak_tanpa_token_turnstile(): void
    {
        $this->pegawai();
        config(['services.turnstile.secret' => 'secret-uji']);

        // Pengguna nyata membuka halaman login dulu sebelum mengirim form.
        $this->get('/login');

        // Validasi "required" harus menolak sebelum ada panggilan HTTP
        // ke Cloudflare, jadi permintaan liar (nyata) harus gagal tes.
        Http::preventStrayRequests();

        $this->post('/login', [
            'email' => 'pegawai@pu.go.id',
            'password' => 'password',
        ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertGuest();
    }

    public function test_login_ditolak_bila_cloudflare_menolak_token(): void
    {
        $this->pegawai();
        config(['services.turnstile.secret' => 'secret-uji']);

        Http::fake([
            'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => false]),
        ]);

        $this->get('/login');

        $this->post('/login', [
            'email' => 'pegawai@pu.go.id',
            'password' => 'password',
            'cf-turnstile-response' => 'token-palsu',
        ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertGuest();

        Http::assertSent(fn (Request $request) => $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
            && ($request->data()['response'] ?? null) === 'token-palsu');
    }

    public function test_login_berhasil_bila_turnstile_terverifikasi(): void
    {
        $user = $this->pegawai();
        config(['services.turnstile.secret' => 'secret-uji']);

        Http::fake([
            'challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response(['success' => true]),
        ]);

        $this->post('/login', [
            'email' => 'pegawai@pu.go.id',
            'password' => 'password',
            'cf-turnstile-response' => 'token-uji',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);

        // Secret key dan token dari widget harus dikirim ke endpoint
        // resmi Cloudflare, bukan dioper ke tempat lain.
        Http::assertSent(function (Request $request) {
            $data = $request->data();

            return $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
                && ($data['secret'] ?? null) === 'secret-uji'
                && ($data['response'] ?? null) === 'token-uji'
                && ($data['remoteip'] ?? null) === '127.0.0.1';
        });
    }

    public function test_login_ditolak_bila_koneksi_ke_cloudflare_gagal(): void
    {
        $this->pegawai();
        config(['services.turnstile.secret' => 'secret-uji']);

        Exceptions::fake();
        Http::fake([
            'challenges.cloudflare.com/*' => Http::failedConnection(),
        ]);

        $this->get('/login');

        $this->post('/login', [
            'email' => 'pegawai@pu.go.id',
            'password' => 'password',
            'cf-turnstile-response' => 'token-uji',
        ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('cf-turnstile-response');

        $this->assertGuest();

        // Kegagalan koneksi harus dilaporkan supaya admin tahu verifikasi
        // sedang tidak berjalan normal.
        Exceptions::assertReported(ConnectionException::class);
    }

    public function test_tanpa_secret_key_login_tidak_wajib_captcha(): void
    {
        $user = $this->pegawai();
        config([
            'services.turnstile.site_key' => null,
            'services.turnstile.secret' => null,
        ]);

        Http::preventStrayRequests();

        $this->post('/login', [
            'email' => 'pegawai@pu.go.id',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    private function pegawai(): User
    {
        return User::factory()->create([
            'email' => 'pegawai@pu.go.id',
            'role' => 'pegawai',
            'nip' => fake()->unique()->numerify('##################'),
            'jabatan' => 'Staf',
        ]);
    }
}
