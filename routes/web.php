<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CrmcController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('crmc.index');
})->name('home');

// Autentikasi Pengguna (Login & Logout)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Halaman Detail 8 Komponen CRMC
Route::get('/crmc/{slug}', [CrmcController::class, 'show'])->name('crmc.show');

// Proses Simpan / Pembaruan Data CRMC
Route::post('/crmc/{slug}', [CrmcController::class, 'update'])->name('crmc.update');

// Penugasan Identitas Pegawai (Khusus Admin)
Route::post('/crmc/{slug}/penugasan', [CrmcController::class, 'updatePenugasan'])->name('crmc.penugasan.update');

// Upload Dokumen per Komponen (Pegawai & Admin)
Route::post('/crmc/{slug}/upload-dokumen', [CrmcController::class, 'uploadDokumen'])->name('crmc.upload.dokumen');

// Hapus Lampiran Individual (Admin)
Route::delete('/crmc/lampiran/{id}', [CrmcController::class, 'deleteLampiran'])->name('crmc.lampiran.delete');

// Admin: Update Status Residu Risiko (Komponen 7)
Route::post('/crmc/{slug}/update-residu', [CrmcController::class, 'updateResidu'])->name('crmc.update.residu');

// ========================
// ADMIN: Kelola Akun Pegawai
// ========================
Route::middleware('auth')->group(function () {
    Route::get('/admin/pegawai', [CrmcController::class, 'kelolaAkun'])->name('admin.pegawai.index');
    Route::post('/admin/pegawai', [CrmcController::class, 'simpanAkun'])->name('admin.pegawai.store');
    Route::put('/admin/pegawai/{id}', [CrmcController::class, 'updateAkun'])->name('admin.pegawai.update');
    Route::delete('/admin/pegawai/{id}', [CrmcController::class, 'hapusAkun'])->name('admin.pegawai.delete');
});