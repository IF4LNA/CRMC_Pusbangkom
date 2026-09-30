<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CrmcController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;

Route::get('/', function () {
    return view('crmc.index');
})->name('home');

// Halaman Beranda: penjelasan CRMC, struktur organisasi, galeri, dan peta
Route::get('/home', [HomeController::class, 'index'])->name('beranda');

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

// ========================
// ADMIN: Kelola Isi Halaman Beranda
// ========================
// Rute tetap memakai middleware 'auth' supaya pengguna tak dikenal tetap
// diarahkan ke form login. Otorisasi admin ditegur ulang di dalam
// HomeController::wajibAdmin() pada setiap aksi ubah/hapus.
Route::middleware('auth')->prefix('admin/beranda')->name('admin.beranda.')->group(function () {
    // Struktur Organisasi
    Route::post('/struktur', [HomeController::class, 'simpanStruktur'])->name('struktur.simpan');
    Route::put('/struktur/{id}', [HomeController::class, 'ubahStruktur'])->name('struktur.ubah');
    Route::delete('/struktur/{id}', [HomeController::class, 'hapusStruktur'])->name('struktur.hapus');
    Route::post('/struktur/urutan', [HomeController::class, 'urutkanStruktur'])->name('struktur.urutan');

    // Galeri Sarana dan Prasarana
    Route::post('/galeri', [HomeController::class, 'simpanGaleri'])->name('galeri.simpan');
    Route::put('/galeri/{id}', [HomeController::class, 'ubahGaleri'])->name('galeri.ubah');
    Route::delete('/galeri/{id}', [HomeController::class, 'hapusGaleri'])->name('galeri.hapus');
    Route::post('/galeri/urutan', [HomeController::class, 'urutkanGaleri'])->name('galeri.urutan');
});