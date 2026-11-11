<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use RuntimeException;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_lupa_kata_sandi_terbuka(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('Lupa Kata Sandi?');
    }

    public function test_halaman_atur_ulang_kata_sandi_terbuka_dari_tautan_email(): void
    {
        $this->get('/reset-password/token-uji?email=pegawai@pu.go.id')
            ->assertOk()
            ->assertSee('Atur Ulang Kata Sandi')
            ->assertSee('token-uji', false)
            ->assertSee('pegawai@pu.go.id');
    }

    public function test_tautan_atur_ulang_dikirim_untuk_email_terdaftar(): void
    {
        $user = $this->pegawai();
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'pegawai@pu.go.id'])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_email_tidak_terdaftar_tetap_mendapat_pesan_umum_tanpa_mengirim_notifikasi(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'tidak.ada@pu.go.id'])
            ->assertSessionHas('status');

        Notification::assertNothingSent();
    }

    /**
     * Pesan untuk email terdaftar dan tidak terdaftar harus identik.
     * Perbedaan sekecil apa pun bisa dipakai menebak akun yang ada.
     */
    public function test_email_terdaftar_dan_tidak_terdaftar_mendapat_pesan_yang_sama(): void
    {
        $this->pegawai();
        Notification::fake();

        $pesan = 'Jika alamat email tersebut terdaftar, tautan atur ulang kata sandi akan dikirim ke email dinas Anda.';

        $this->post('/forgot-password', ['email' => 'pegawai@pu.go.id'])
            ->assertSessionHas('status', $pesan);

        $this->post('/forgot-password', ['email' => 'tidak.ada@pu.go.id'])
            ->assertSessionHas('status', $pesan);
    }

    public function test_kegagalan_kirim_email_menampilkan_pesan_yang_ramah(): void
    {
        Exceptions::fake();
        Password::shouldReceive('sendResetLink')->andThrow(new RuntimeException('SMTP tidak tersedia'));

        $this->post('/forgot-password', ['email' => 'pegawai@pu.go.id'])
            ->assertSessionHasErrors('email');

        Exceptions::assertReported(RuntimeException::class);
    }

    public function test_kata_sandi_berhasil_diubah_dengan_token_valid(): void
    {
        $user = $this->pegawai();
        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'pegawai@pu.go.id',
            'password' => 'SandiBaru123!',
            'password_confirmation' => 'SandiBaru123!',
        ])
            ->assertRedirect('/login')
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('SandiBaru123!', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'pegawai@pu.go.id']);
    }

    public function test_tautan_tidak_valid_tidak_mengubah_kata_sandi(): void
    {
        $user = $this->pegawai();

        $this->post('/reset-password', [
            'token' => 'token-palsu',
            'email' => 'pegawai@pu.go.id',
            'password' => 'SandiBaru123!',
            'password_confirmation' => 'SandiBaru123!',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
        $this->assertGuest();
    }

    public function test_kata_sandi_baru_harus_memenuhi_policy(): void
    {
        $user = $this->pegawai();
        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'pegawai@pu.go.id',
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_konfirmasi_kata_sandi_harus_cocok(): void
    {
        $user = $this->pegawai();
        $token = Password::broker()->createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => 'pegawai@pu.go.id',
            'password' => 'SandiBaru123!',
            'password_confirmation' => 'BedaSandi123!',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_email_atur_ulang_memakai_bahasa_indonesia_dan_tautan_bertoken(): void
    {
        $user = User::factory()->make(['name' => 'Rina Melati', 'email' => 'rina@pu.go.id']);

        $mail = (new ResetPasswordNotification('token-uji'))->toMail($user);

        $this->assertSame('Atur Ulang Kata Sandi CRMC PUSBANGKOM', $mail->subject);
        $this->assertStringContainsString('token-uji', $mail->actionUrl);
        $this->assertStringContainsString('60 menit', implode(' ', $mail->outroLines));
    }

    public function test_halaman_login_tidak_lagi_menampilkan_kredensial_default(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Lupa kata sandi?')
            ->assertDontSee('password123')
            ->assertDontSee('admin@pu.go.id');
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
