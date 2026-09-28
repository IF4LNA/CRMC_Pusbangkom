<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('crmc.index');
});

// Halaman Detail 8 Komponen CRMC
Route::get('/crmc/{slug}', [CrmcController::class, 'show'])->name('crmc.show');

// Proses Simpan / Pembaruan Data CRMC
Route::post('/crmc/{slug}', [CrmcController::class, 'update'])->name('crmc.update');