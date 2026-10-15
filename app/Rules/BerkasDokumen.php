<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

/**
 * Validasi berkas dokumen kantor (PDF, Word, Excel, gambar).
 *
 * Rule bawaan "mimes" menentukan jenis berkas dari tipe MIME hasil deteksi
 * fileinfo. Untuk berkas Office Open XML (.docx/.xlsx) fileinfo kadang
 * melaporkannya sebagai "application/zip" atau "application/octet-stream"
 * — terutama pada Windows/Laragon atau berkas yang dibuat generator
 * tertentu. Akibatnya berkas .xlsx yang sah ikut ditolak, padahal
 * ekstensinya jelas .xlsx.
 *
 * Rule ini menerima berkas bila salah satu terpenuhi:
 *   1. ekstensi hasil deteksi fileinfo termasuk daftar yang diizinkan, atau
 *   2. isi berkas tidak bisa dikenali (zip/octet-stream) TETAPI ekstensi
 *      asli dari klien termasuk daftar yang diizinkan.
 *
 * Skrip PHP dan ekstensi berbahaya lain tetap ditolak lewat jalur mana pun,
 * karena penyamaran ekstensi tidak boleh membuka celah eksekusi.
 *
 * Daftar ekstensi bisa diatur lewat konstruktor supaya tiap halaman tetap
 * memakai format yang sama seperti sebelumnya:
 *     new BerkasDokumen()                       // daftar dokumen CRMC
 *     new BerkasDokumen(['pdf', 'xlsx', ...])   // daftar khusus halaman lain
 */
class BerkasDokumen implements ValidationRule
{
    /** Ekstensi bawaan untuk dokumen 8 Komponen CRMC. */
    public const EKSTENSI_DOKUMEN = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'webp',
    ];

    /**
     * Ekstensi skrip berbahaya: tidak pernah boleh masuk, walau isinya
     * tidak terdeteksi sebagai skrip.
     */
    private const EKSTENSI_TERLARANG = [
        'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
    ];

    /**
     * Hasil deteksi fileinfo yang tidak bisa dipakai untuk memastikan jenis
     * berkas. Untuk jenis generik ini, ekstensi asli dari klien yang
     * menentukan, karena .docx/.xlsx memang berupa arsip zip.
     */
    private const EKSTENSI_GENERIK = ['', 'zip', 'zipx', 'bin', 'octet-stream'];

    /** @var array<int, string> */
    private array $diizinkan;

    /**
     * @param  array<int, string>|null  $ekstensiDiizinkan  null = daftar dokumen CRMC
     */
    public function __construct(?array $ekstensiDiizinkan = null)
    {
        $this->diizinkan = array_values(array_map(
            static fn ($ekstensi) => strtolower((string) $ekstensi),
            $ekstensiDiizinkan ?? self::EKSTENSI_DOKUMEN
        ));
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail('Berkas yang dipilih tidak valid.');

            return;
        }

        $ekstensiKlien = strtolower((string) $value->getClientOriginalExtension());
        $ekstensiDeteksi = strtolower((string) $value->guessExtension());

        // Jangan pernah menerima skrip, apa pun nama berkasnya.
        if (in_array($ekstensiKlien, self::EKSTENSI_TERLARANG, true)
            || in_array($ekstensiDeteksi, self::EKSTENSI_TERLARANG, true)) {
            $fail('Format file tidak diizinkan.');

            return;
        }

        $dikenaliDariIsi = in_array($ekstensiDeteksi, $this->diizinkan, true);

        // Berkas kantor yang benar-benar .xlsx/.docx tapi terdeteksi sebagai
        // arsip zip tetap diterima lewat ekstensi aslinya.
        $generikTapiEkstensiCocok = in_array($ekstensiDeteksi, self::EKSTENSI_GENERIK, true)
            && in_array($ekstensiKlien, $this->diizinkan, true);

        if (! $dikenaliDariIsi && ! $generikTapiEkstensiCocok) {
            $fail('Format file harus '.strtoupper(implode(', ', $this->diizinkan)).'.');
        }
    }
}
