<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            // Jika menggunakan Sanctum, buat token API
            $token = $user->createToken('CRMC_Token')->plainTextToken;

            return response()->json([
                'message' => 'Login berhasil',
                'user' => $user,
                'token' => $token
            ]);
        }

        return response()->json([
            'message' => 'Email atau password salah.'
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.'
        ]);
    }
}
