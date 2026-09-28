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
        Schema::create('penugasan_crmc', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_menu_id')->constrained('sub_menu')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('peran', ['pemilik_risiko', 'pengendali_mutu', 'pengendali_risiko']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penugasan_crmc');
    }
};
