<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Tampilkan halaman formulir login.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('crmc.dashboard')->with('info', 'Anda sudah masuk sebagai ' . Auth::user()->name);
        }

        return view('auth.login');
    }

    /**
     * Proses autentikasi login pengguna (Web & API).
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Alamat email dinas wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if ($request->expectsJson() || $request->is('api/*')) {
                $token = $user->createToken('CRMC_Token')->plainTextToken;
                return response()->json([
                    'message' => 'Login berhasil',
                    'user' => $user,
                    'token' => $token
                ]);
            }

            $request->session()->regenerate();

            $roleText = $user->isAdmin() ? 'Administrator' : 'Pegawai';
            // Tuju dashboard CRMC: setelah masuk, pegawainya langsung
            // bekerja di halaman instrumen, bukan membaca halaman depan.
            return redirect()->intended(route('crmc.dashboard'))->with('success', "Selamat datang, {$user->name}! Anda berhasil masuk dalam Mode {$roleText}.");
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Email atau kata sandi tidak cocok.'
            ], 401);
        }

        return back()->withErrors([
            'email' => 'Kombinasi email dan kata sandi yang Anda masukkan tidak sesuai.',
        ])->onlyInput('email');
    }

    /**
     * Proses keluar (logout) pengguna.
     */
    public function logout(Request $request)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            if ($request->user() && method_exists($request->user(), 'currentAccessToken') && $request->user()->currentAccessToken()) {
                $request->user()->currentAccessToken()->delete();
            }
            return response()->json([
                'message' => 'Logout berhasil.'
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('beranda')->with('success', 'Anda telah berhasil keluar dari sistem CRMC.');
    }
}
