<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Cache data navigasi utama (daftar bidang, daftar sub-bidang, dan jumlah
 * SOP) yang disusun view composer `layouts.app`.
 *
 * Data ini dibaca di SETIAP halaman, padahal isinya jarang berubah. Sebuah
 * nomor versi dipakai sebagai bagian kunci cache supaya seluruh varian
 * (admin, tamu, dan tiap kombinasi bidang milik pegawai) bisa dibuang
 * sekaligus saat data sumbernya berubah. Cara ini dipilih daripada tag cache
 * karena tag tidak didukung semua driver (mis. driver "database").
 */
final class CacheNavigasi
{
    /** Kunci penyimpan nomor versi cache navigasi. */
    private const KUNCI_VERSI = 'navigasi:versi';

    /** Lama data navigasi disimpan, dalam menit. */
    private const MENIT_SIMPAN = 30;

    /** Umur simpan data navigasi. */
    public static function kedaluwarsa(): \DateTimeInterface
    {
        return now()->addMinutes(self::MENIT_SIMPAN);
    }

    /**
     * Kunci cache untuk satu cakupan bidang.
     *
     * null  = admin (semua bidang)
     * []    = tamu / pegawai tanpa bidang
     * array = id bidang yang boleh dibuka pegawai
     *
     * @param  array<int, int>|null  $batasBidang
     */
    public static function kunci(?array $batasBidang): string
    {
        $cakupan = $batasBidang === null ? 'semua' : implode('-', $batasBidang);

        return 'navigasi:'.self::versi().':'.$cakupan;
    }

    /**
     * Buang seluruh varian cache navigasi dengan menaikkan nomor versinya.
     *
     * Dipanggil otomatis saat Bidang, SubMenu, DokumenCrmc, atau LampiranCrmc
     * berubah (lihat AppServiceProvider) supaya angka pada navigasi tidak
     * tertinggal dari data yang sebenarnya.
     */
    public static function lupakan(): void
    {
        Cache::forever(self::KUNCI_VERSI, self::versi() + 1);
    }

    private static function versi(): int
    {
        return (int) Cache::get(self::KUNCI_VERSI, 1);
    }
}
