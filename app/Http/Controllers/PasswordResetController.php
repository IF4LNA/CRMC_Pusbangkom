<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class PasswordResetController extends Controller
{
    /**
     * Tampilkan formulir permintaan tautan atur ulang kata sandi.
     */
    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim tautan atur ulang kata sandi ke email pengguna.
     *
     * Respons sengaja dibuat umum untuk semua status supaya halaman ini
     * tidak bisa dipakai menebak email mana yang terdaftar di sistem.
     */
    public function sendResetLinkEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Alamat email dinas wajib diisi.',
            'email.email' => 'Format email tidak valid.',
        ]);

        try {
            Password::sendResetLink($request->only('email'));
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Tautan atur ulang gagal dikirim. Silakan coba lagi atau hubungi administrator.',
                ]);
        }

        // Pesan yang sama untuk semua status (terkirim, dibatasi, atau
        // email tidak terdaftar) supaya halaman ini tidak bisa dipakai
        // menebak email mana yang terdaftar di sistem.
        return back()->with(
            'status',
            'Jika alamat email tersebut terdaftar, tautan atur ulang kata sandi akan dikirim ke email dinas Anda.'
        );
    }

    /**
     * Tampilkan formulir atur ulang kata sandi dari tautan email.
     */
    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    /**
     * Simpan kata sandi baru setelah token diverifikasi.
     */
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/'],
        ], [
            'token.required' => 'Token atur ulang kata sandi tidak ditemukan.',
            'email.required' => 'Alamat email dinas wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.max' => 'Kata sandi maksimal 64 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.regex' => 'Kata sandi harus mengandung angka dan simbol.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with(
                'status',
                'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru Anda.'
            );
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => $this->pesanGagalReset($status),
            ]);
    }

    /**
     * Terjemahkan status broker menjadi pesan yang ditampilkan ke pengguna.
     */
    private function pesanGagalReset(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN, Password::INVALID_USER => 'Tautan atur ulang kata sandi tidak valid atau sudah kedaluwarsa. Silakan minta tautan baru.',
            Password::RESET_THROTTLED => 'Permintaan tautan baru saja dibuat. Silakan tunggu satu menit sebelum mencoba lagi.',
            default => 'Kata sandi gagal diubah. Silakan coba lagi.',
        };
    }
}
