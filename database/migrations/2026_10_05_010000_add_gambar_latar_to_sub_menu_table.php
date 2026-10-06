<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan gambar latar ke sub-bidang.
 *
 * Folder yang diunggah admin disimpan sebagai path relatif di disk "public"
 * (folder "latar-sub-bidang"), sama seperti foto pegawai dan galeri, supaya
 * tidak membebani repo dan bisa dilayani lewat /storage.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_menu', function (Blueprint $table) {
            $table->string('gambar_latar')->nullable()->after('nama_sub_menu');
        });
    }

    public function down(): void
    {
        Schema::table('sub_menu', function (Blueprint $table) {
            $table->dropColumn('gambar_latar');
        });
    }
};
