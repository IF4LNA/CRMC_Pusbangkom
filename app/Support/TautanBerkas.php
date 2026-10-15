<?php

namespace App\Support;

/**
 * Utilitas untuk tautan berkas yang disimpan sebagai URL absolut.
 *
 * Nilai `lampiran_crmc.file_path` disimpan apa adanya dari
 * Storage::url() pada saat unggah, jadi host-nya ikut terekam (mis.
 * "http://crmc_pusbangkom.test/storage/..." atau
 * "http://172.10.100.105:8000/storage/..."). Ketika aplikasi dibuka dari
 * host lain, URL itu jadi alamat lintas-asal (beda host).
 *
 * Untuk tag <iframe> dan <img> beda host tidak masalah. Tetapi berkas
 * DOCX/XLS/XLSX dibaca memakai fetch() agar bisa dirender di peramban, dan
 * fetch() lintas-asal tanpa header CORS akan diblokir peramban. Akibatnya
 * pratinjau tampak "tidak bisa dimuat" hanya karena host di URL sudah usang.
 *
 * Karena itu URL berkas di tampilan selalu diturunkan ke jalur relatifnya
 * ("/storage/..."). Permintaan otomatis menuju host yang sedang dibuka,
 * apa pun alamat yang dipakai pengguna.
 */
final class TautanBerkas
{
    /**
     * Ubah URL absolut menjadi jalur relatif terhadap host yang dipakai.
     *
     * URL yang sudah relatif dikembalikan apa adanya.
     */
    public static function relatif(?string $url): string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return '';
        }

        // Tanpa skema atau awalan "//", tautan sudah relatif.
        if (preg_match('#^(https?:)?//#i', $url) !== 1) {
            return $url;
        }

        $jalur = parse_url($url, PHP_URL_PATH);

        return is_string($jalur) && $jalur !== '' ? $jalur : $url;
    }
}
