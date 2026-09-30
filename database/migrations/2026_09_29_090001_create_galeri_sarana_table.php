<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Galeri carousel "Sarana dan Prasarana" pada halaman Beranda.
     *
     * Admin mengunggah gambarnya sendiri lewat panel di halaman tersebut,
     * sehingga tidak perlu menyentuh kode maupun placing file manual di
     * dalam folder public.
     */
    public function up(): void
    {
        Schema::create('galeri_sarana', function (Blueprint $table) {
            $table->id();

            // Path relatif pada disk "public", mis. "galeri-sarana/abc.jpg".
            $table->string('path');

            $table->string('judul');
            $table->text('keterangan')->nullable();

            // Menentukan urutan tampil di carousel (kecil ke besar).
            $table->unsignedSmallInteger('urutan')->default(0);

            $table->timestamps();

            $table->index('urutan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeri_sarana');
    }
};