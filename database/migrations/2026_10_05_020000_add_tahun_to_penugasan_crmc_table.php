<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan tahun pada penugasan pegawai (Komponen 1).
 *
 * Identitas pegawai beserta PIC-nya berubah dari tahun ke tahun, jadi
 * penugasan tidak lagi Tunggal: satu orang bisa menjadi pengendali mutu
 * tahun 2026 lalu berganti persona tahun 2027. Tanpa kolom tahun, penugasan
 * tahun lalu akan menimpa penugasan tahun baru.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('penugasan_crmc', 'tahun_pelaksanaan')) {
            Schema::table('penugasan_crmc', function (Blueprint $table) {
                $table->year('tahun_pelaksanaan')->nullable()->after('peran');
            });
        }

        // Penugasan lama belum punya tahun. Diisi dengan tahun berjalan supaya
        // tetap tampil di halaman, bukan hilang karena kolomnya NULL.
        DB::table('penugasan_crmc')
            ->whereNull('tahun_pelaksanaan')
            ->update(['tahun_pelaksanaan' => (int) date('Y')]);

        // Satu orang satu peran satu tahun. Tanpa batasan ini, admin yang
        // menekan "Simpan" dua kali untuk tahun yang sama akan menghasilkan
        // baris dobel dan kartu Komponen 1 menampilkan orang yang sama
        // beberapa kali.
        $sudahAda = collect(Schema::getIndexes('penugasan_crmc'))->contains(
            fn ($idx) => ($idx['unique'] ?? false)
                && is_array($idx['columns'])
                && $idx['columns'] === ['sub_menu_id', 'user_id', 'peran', 'tahun_pelaksanaan']
        );

        if (!$sudahAda) {
            Schema::table('penugasan_crmc', function (Blueprint $table) {
                $table->unique(
                    ['sub_menu_id', 'user_id', 'peran', 'tahun_pelaksanaan'],
                    'penugasan_crmc_sub_menu_user_peran_tahun_unik'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::table('penugasan_crmc', function (Blueprint $table) {
            $table->dropUnique('penugasan_crmc_sub_menu_user_peran_tahun_unik');
            $table->dropColumn('tahun_pelaksanaan');
        });
    }
};