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
        Schema::create('sub_menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidang_id')->constrained('bidang')->onDelete('cascade');
            $table->string('nama_sub_menu');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_menu');
    }
};
