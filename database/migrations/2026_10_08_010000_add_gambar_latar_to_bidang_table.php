<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan gambar latar (banner) ke bidang.
 *
 * Gambar ini dipakai sebagai latar panel "Navigasi Bidang" pada tab tiap
 * bidang di dashboard. Sama seperti gambar latar sub-bidang, path-nya
 * disimpan relatif di disk "public" (folder "latar-bidang") supaya bisa
 * diganti admin tanpa menyentuh kode.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bidang', function (Blueprint $table) {
            $table->string('gambar_latar')->nullable()->after('nama_bidang');
        });
    }

    public function down(): void
    {
        Schema::table('bidang', function (Blueprint $table) {
            $table->dropColumn('gambar_latar');
        });
    }
};
