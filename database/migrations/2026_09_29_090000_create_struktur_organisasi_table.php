<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Struktur organisasi CRMC yang ditampilkan pada halaman Beranda.
     *
     * Sengaja dipisahkan dari tabel `penugasan_crmc`. Penugasan pada tabel
     * itu bersifat per sub-bidang, sedangkan struktur organisasi bersifat
     * ringkas dan global: 1 Pemilik Risiko untuk seluruh CRMC, lalu 1
     * Pengendali Mutu per bidang. Kalau keduanya dicampur dalam satu tabel,
     * pembacaan keduanya jadi ambigu dan sulit dipelihara.
     */
    public function up(): void
    {
        Schema::create('struktur_organisasi', function (Blueprint $table) {
            $table->id();

            // Nullable: kursi boleh sengaja dikosongkan (mis. belum ada
            // Pengendali Mutu untuk suatu bidang) dan tetap tampil sebagai
            // "Belum Ditunjuk" di org chart, bukan ikut hilang.
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('peran', ['pemilik_risiko', 'pengendali_mutu', 'pengendali_risiko']);

            // Label yang tampil di org chart, mis. "Pengendali Mutu Bidang SDA".
            $table->string('nama_jabatan');

            // Hanya relevan untuk pengendali_mutu & pengendali_risiko.
            // Null berarti berlaku untuk seluruh CRMC (baris paling atas).
            $table->foreignId('bidang_id')
                ->nullable()
                ->constrained('bidang')
                ->cascadeOnDelete();

            $table->unsignedSmallInteger('urutan')->default(0);
            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->index(['peran', 'bidang_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('struktur_organisasi');
    }
};