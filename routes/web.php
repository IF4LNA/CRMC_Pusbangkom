<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BidangController;
use App\Http\Controllers\CrmcController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SopController;

// Dashboard utama CRMC. Daftar bidang & sub-bidang dibaca dari database
// sehingga admin bisa menambah sub-bidang tanpa mengubah kode.
Route::get('/', [BidangController::class, 'dashboard'])->name('home');

// Halaman Beranda: penjelasan CRMC, gambar struktur, galeri, dan peta
Route::get('/home', [HomeController::class, 'index'])->name('beranda');

// Kumpulan seluruh dokumen SOP (Komponen 3) dari semua sub-bidang
Route::get('/sop', [SopController::class, 'index'])->name('sop.index');

// Autentikasi Pengguna (Login & Logout)
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ============================================================
// PENTING: route dengan path LITERAL harus didaftarkan SEBELUM route
// berparameter {slug}. Laravel mencocokkan route sesuai urutan
// pendaftaran, jadi "/crmc/tahun" yang diletakkan setelah
// "/crmc/{slug}" akan tertangkap sebagai slug="tahun" dan
// memanggil crmc.update(), bukan crmc.tahun.store().
// ============================================================

// ========================
// ADMIN: Kelola Sub-Bidang (kartu pada tab tiap bidang)
// ========================
Route::middleware('auth')->prefix('bidang/sub-bidang')->name('bidang.sub-bidang.')->group(function () {
    Route::post('/', [BidangController::class, 'simpanSubBidang'])->name('simpan');
    Route::put('/{id}', [BidangController::class, 'ubahSubBidang'])->name('ubah');
    Route::delete('/{id}', [BidangController::class, 'hapusSubBidang'])->name('hapus');
});

// ========================
// ADMIN: Management Tahun Anggaran
// Catatan urutan: lihat catatan di atas soal path literal vs {slug}.
Route::post('/crmc/tahun', [CrmcController::class, 'tambahTahun'])->name('crmc.tahun.store');
Route::delete('/crmc/tahun/{id}', [CrmcController::class, 'hapusTahun'])->name('crmc.tahun.destroy');

// ========================
// ADMIN: Hapus Dokumen per Tahun
// ========================

// Hapus dokumen tahun tertentu di seluruh sub-bidang
Route::post('/crmc/hapus-dokumen-tahun', [CrmcController::class, 'hapusDokumenTahunSemua'])->name('crmc.hapus.tahun.semua');

// Lampiran individual (path literal "lampiran" agar tidak jadi {slug})
Route::delete('/crmc/lampiran/{id}', [CrmcController::class, 'deleteLampiran'])->name('crmc.lampiran.delete');
Route::patch('/crmc/lampiran/{id}/keterangan', [CrmcController::class, 'updateKeterangan'])->name('crmc.lampiran.keterangan');

// Halaman Detail 8 Komponen CRMC
Route::get('/crmc/{slug}', [CrmcController::class, 'show'])->name('crmc.show');

// Proses Simpan / Pembaruan Data CRMC
Route::post('/crmc/{slug}', [CrmcController::class, 'update'])->name('crmc.update');

// Penugasan Identitas Pegawai (Khusus Admin)
Route::post('/crmc/{slug}/penugasan', [CrmcController::class, 'updatePenugasan'])->name('crmc.penugasan.update');

// Upload Dokumen per Komponen (Pegawai & Admin)
Route::post('/crmc/{slug}/upload-dokumen', [CrmcController::class, 'uploadDokumen'])->name('crmc.upload.dokumen');

// Admin: Update Status Residu Risiko (Komponen 7)
Route::post('/crmc/{slug}/update-residu', [CrmcController::class, 'updateResidu'])->name('crmc.update.residu');

// Hapus dokumen satu sub-bidang pada satu tahun (opsional per kategori komponen)
Route::post('/crmc/{slug}/hapus-dokumen-tahun', [CrmcController::class, 'hapusDokumenTahunSubBidang'])->name('crmc.hapus.tahun.subbidang');

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
    // Gambar bagan Struktur Organisasi (menggantikan input manual peran)
    Route::post('/struktur/gambar', [HomeController::class, 'simpanGambarStruktur'])->name('struktur.gambar.simpan');
    Route::delete('/struktur/gambar', [HomeController::class, 'hapusGambarStruktur'])->name('struktur.gambar.hapus');

    // Galeri Sarana dan Prasarana
    Route::post('/galeri', [HomeController::class, 'simpanGaleri'])->name('galeri.simpan');
    Route::put('/galeri/{id}', [HomeController::class, 'ubahGaleri'])->name('galeri.ubah');
    Route::delete('/galeri/{id}', [HomeController::class, 'hapusGaleri'])->name('galeri.hapus');
    Route::post('/galeri/urutan', [HomeController::class, 'urutkanGaleri'])->name('galeri.urutan');
});