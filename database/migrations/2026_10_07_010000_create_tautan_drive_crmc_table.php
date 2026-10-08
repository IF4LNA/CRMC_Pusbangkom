<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tautan Google Drive per Komponen CRMC.
 *
 * Satu baris = satu tautan untuk satu (sub-bidang, tahun, komponen).
 * Komponen 7 tidak punya tabel ini karena isinya status residu, bukan berkas.
 *
 * Benderanya unik per (sub_menu_id, tahun_pelaksanaan, kategori_komponen)
 * supaya satu komponen hanya bisa punya satu tautan Google Drive: mengikuti
 * aturan "satu link per komponen per tahun".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tautan_drive_crmc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_menu_id')->constrained('sub_menu')->onDelete('cascade');
            $table->year('tahun_pelaksanaan');
            $table->string('kategori_komponen', 50);

            // Teks tombol. Nullable karena saat kosong view memakai teks
            // bawaan "Buka Google Drive".
            $table->string('label', 120)->nullable();

            $table->string('url', 500);

            // Siapa yang mengisi tautan terakhir. Dipakai supaya pegawai
            // hanya bisa menghapus tautan miliknya sendiri, sedangkan admin
            // bisa menghapus semuanya.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(
                ['sub_menu_id', 'tahun_pelaksanaan', 'kategori_komponen'],
                'tautan_drive_crmc_komponen_tahun_unik'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tautan_drive_crmc');
    }
};
