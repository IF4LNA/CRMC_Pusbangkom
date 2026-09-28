<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lampiran_crmc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dokumen_crmc_id')->constrained('dokumen_crmc')->onDelete('cascade');
            $table->enum('kategori_komponen', [
                'identitas_pegawai', // Opsional jika butuh lampiran identitas tambahan
                'risk_register',
                'sop',
                'formulir_pengendalian',
                'jadwal_pelaksanaan',
                'bukti_pelaksanaan'
            ]);
            $table->string('nama_file');
            $table->string('file_path');
            $table->string('tipe_file');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lampiran_crmc');
    }
};
