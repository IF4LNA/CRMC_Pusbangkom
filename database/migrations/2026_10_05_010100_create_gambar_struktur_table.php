<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyimpan gambar bagan struktur organisasi.
 *
 * Section "Struktur Organisasi" pada halaman Beranda kini menampilkan satu
 * gambar yang diunggah admin, bukan lagi kartu orang hasil input manual.
 * Tabel ini hanya berisi satu baris aktif: gambar terakhir yang diunggah.
 * Baris sebelumnya dihapus (beserta file fisiknya) oleh HomeController saat
 * menggantinya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gambar_struktur', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gambar_struktur');
    }
};
