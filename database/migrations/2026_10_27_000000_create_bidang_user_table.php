<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Akses pegawai per bidang (many-to-many).
 *
 * Sebelum tabel ini ada, semua pegawai dapat membuka seluruh bidang. Supaya
 * perubahan ini tidak langsung mengunci pegawai lama dari pekerjaannya,
 * migrasi menyalin kondisi lama: setiap pegawai yang sudah ada diberikan
 * akses ke semua bidang yang ada saat migrasi dijalankan. Admin tetap dapat
 * mencabut akses lewat halaman Kelola Akun Pegawai.
 *
 * Pegawai yang dibuat SETELAH migrasi ini tidak mendapat akses otomatis;
 * admin harus memilih bidangnya secara eksplisit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bidang_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('bidang_id')->constrained('bidang')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['user_id', 'bidang_id']);
        });

        // Backfill non-destruktif: pertahankan akses penuh pegawai lama.
        $pegawaiIds = DB::table('users')->where('role', 'pegawai')->pluck('id');
        $bidangIds = DB::table('bidang')->pluck('id');

        if ($pegawaiIds->isEmpty() || $bidangIds->isEmpty()) {
            return;
        }

        $sekarang = now();
        $baris = [];

        foreach ($pegawaiIds as $userId) {
            foreach ($bidangIds as $bidangId) {
                $baris[] = [
                    'user_id' => $userId,
                    'bidang_id' => $bidangId,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ];
            }
        }

        foreach (array_chunk($baris, 500) as $potongan) {
            DB::table('bidang_user')->insertOrIgnore($potongan);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bidang_user');
    }
};
