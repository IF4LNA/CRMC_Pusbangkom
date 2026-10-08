<?php

namespace App\Support;

/**
 * Tautan Google Drive per Komponen (Komponen 1,2,3,4,5,6,8).
 *
 * Aplikasi tidak menyimpan berkas di Google Drive, hanya tautannya. Karena
 * itu isinya bukan file, melainkan satu alamat yang dibuka lewat tombol.
 *
 * Aturan penentuannya sengaja dipisah dari controller supaya validasi,
 * peny_normaalannya, dan pesan errornya berada di satu tempat. Tanpa itu,
 * satu tautan bisa lolos dari form tapi ditolak di tempat lain.
 */
final class TautanGoogleDrive
{
    /**
     * Domain Google yang boleh dipakai pada tautan.
     *
     * Daftar dibuat longgar (semua subdomain google.com) karena tautan
     * Drive bisa berbentuk folder, berkas, Google Sheet, Google Docs, atau
     * form, dan semuanya berada di domain yang berbeda-beda:
     * drive.google.com, docs.google.com, sheets.google.com, dan lain-lain.
     *
     * @var array<int, string>
     */
    public const HOST_DIIZINKAN = [
        'google.com',
        'googleusercontent.com',
    ];

    /**
     * Bersihkan tautan yang diketik pengguna menjadi URL siap pakai.
     *
     * Yang ditangani di sini:
     *   - spasi di awal/akhir ikut dibuang (sering terjadi saat menyalin),
     *   - tautan tanpa skema (mis. "drive.google.com/...") diberi "https://",
     *   - tautan dari luar Google mengembalikan null supaya controller bisa
     *     menolak dengan pesan yang jelas.
     */
    public static function normalisasi(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        // people often paste the link without the scheme
        if (! preg_match('~^https?://~i', $url)) {
            $url = 'https://'.ltrim($url, '/');
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return null;
        }

        $host = strtolower($host);

        foreach (self::HOST_DIIZINKAN as $domain) {
            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                return $url;
            }
        }

        return null;
    }

    /**
     * Pesan validasi berbahasa Indonesia untuk tautan Google Drive.
     *
     * Tanpa pesan ini, penolakan hanya muncul sebagai "validasi gagal" tanpa
     * penjelasan, padahal masalahnya biasanya hanya host yang keliru tulis.
     *
     * @return array<string, string>
     */
    public static function pesanValidasi(): array
    {
        return [
            'required' => 'Tautan Google Drive wajib diisi.',
            'url' => 'Tautan harus berupa alamat web yang valid.',
            'max' => 'Tautan maksimal 500 karakter.',
            'google' => 'Tautan harus menunjuk ke Google Drive (drive.google.com atau docs.google.com), bukan situs lain.',
            'label.max' => 'Teks tombol maksimal 120 karakter.',
        ];
    }
}
