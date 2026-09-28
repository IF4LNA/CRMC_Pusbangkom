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
        Schema::create('dokumen_crmc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_menu_id')->constrained('sub_menu')->onDelete('cascade');
            $table->year('tahun_pelaksanaan');
            $table->text('status_residu_risiko')->nullable();
            $table->text('evaluasi_dan_rencana')->nullable();
            $table->timestamps();

            // Mencegah duplikasi data untuk sub-menu di tahun yang sama
            $table->unique(['sub_menu_id', 'tahun_pelaksanaan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_crmc');
    }
};
