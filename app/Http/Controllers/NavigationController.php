<?php

namespace App\Http\Controllers;

use App\Models\Bidang;

class NavigationController extends Controller
{
    public function index()
    {
        // Mengambil semua bidang beserta daftar sub-menu di dalamnya
        $bidang = Bidang::with('subMenus')->get();

        return response()->json([
            'status' => 'success',
            'data' => $bidang
        ]);
    }
}