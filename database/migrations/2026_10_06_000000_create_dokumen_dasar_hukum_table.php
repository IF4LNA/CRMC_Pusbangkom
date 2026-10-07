<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dokumen dasar hukum (landasan regulasi) CRMC.
 *
 * Daftar regulasinya sendiri sudah pasti dan tidak berubah tiap tahun,
 * jadi disimpan sebagai baris tetap. Yang boleh berubah tiap saat hanya
 * berkas yang diunggah admin: semua kolom berkas nullable supaya
 * dokumen tetap tampil walau admin belum sempat mengunggah scan-nya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_dasar_hukum', function (Blueprint $table) {
            $table->id();

            // Pengenal stabil untuk tiap regulasi, mis. "se-12-2024".
            // Dipakai sebagai kunci pada seeder dan form, bukan id auto
            // yang bisa berbeda antar lingkungan.
            $table->string('kode')->unique();

            // "Surat Edaran" atau "Keputusan"; menentukan lencana pada kartu.
            $table->string('jenis');

            // Instansi penerbit, mis. "Kementerian Pekerjaan Umum dan
            // Perumahan Rakyat" atau "Kepala Pusat".
            $table->string('penerbit');

            $table->string('nomor');

            // Bagian "TENTANG ..." pada naskah asli.
            $table->text('tentang');

            $table->unsignedSmallInteger('urutan')->default(0);

            // Berkas hasil unggahan admin. Nullable karena regulasinya
            // tampil lebih dulu, dokumen menyusul.
            $table->string('path')->nullable();
            $table->string('nama_file')->nullable();
            $table->string('tipe_file')->nullable();
            $table->unsignedBigInteger('ukuran')->nullable();

            $table->timestamps();

            $table->index('urutan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_dasar_hukum');
    }
};
