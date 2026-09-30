<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Daftar tahun anggaran yang ditambahkan manual oleh Administrator.
     *
     * Tahun yang muncul otomatis (tahun berjalan + 1 tahun ke depan + tahun
     * yang sudah punya dokumen) tidak perlu disimpan di sini. Tabel ini hanya
     * untuk tahun khusus di luar rentang otomatis, misalnya tahun anggaran
     * khusus atau tahun lampau yang belum pernah ada datanya.
     */
    public function up(): void
    {
        Schema::create('tahun_anggaran', function (Blueprint $table) {
            $table->id();
            $table->year('tahun')->unique();
            $table->string('keterangan', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahun_anggaran');
    }
};
