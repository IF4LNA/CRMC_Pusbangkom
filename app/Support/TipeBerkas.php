<?php

namespace App\Support;

/**
 * Pengelompokan tipe berkas menurut cara menampilkannya di halaman.
 *
 * Satu sumber keputusan supaya halaman "8 Komponen" (partial kartu-dokumen)
 * dan tab "Dasar Hukum" (partial tab-dasar-hukum) tidak pernah berbeda
 * pendapat soal berkas mana yang bisa dipratinjau.
 *
 * Kelompok yang dikembalikan `kelompok()`:
 *   - 'pdf'    : iframe viewer bawaan peramban.
 *   - 'gambar' : tag <img>.
 *   - 'kantor' : tidak bisa dibuka peramban sendiri, tetapi isinya dibaca
 *                di sisi klien memakai library JS (docx-preview untuk DOCX,
 *                SheetJS untuk XLS/XLSX) lalu ditulis ke halaman. Markup dan
 *                skripnya ada di crmc/partials/pratinjau-berkas.
 *   - null     : benar-benar tidak bisa dipratinjau (mis. .doc lama), jadi
 *                hanya ditawarkan tombol unduh.
 *
 * .doc sengaja TIDAK masuk kelompok 'kantor': belum ada library peramban
 * yang bisa membaca format biner Word 97-2003, jadi memasukkannya ke situ
 * hanya akan menghasilkan kotak kosong.
 */
final class TipeBerkas
{
    public const PDF = 'pdf';

    public const GAMBAR = 'gambar';

    public const KANTOR = 'kantor';

    /**
     * Ekstensi gambar yang dirender tag <img>.
     *
     * @var list<string>
     */
    public const EKSTENSI_GAMBAR = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    /**
     * Ekstensi kantor yang isinya bisa dirender peramban oleh library JS.
     *
     * @var list<string>
     */
    public const EKSTENSI_KANTOR = ['docx', 'xls', 'xlsx'];

    /**
     * Kelompok pratinjau sebuah tipe berkas (biasanya ekstensi).
     *
     * Mengembalikan 'pdf', 'gambar', 'kantor', atau null bila tidak bisa
     * dipratinjau sama sekali.
     */
    public static function kelompok(?string $tipe): ?string
    {
        $tipe = strtolower(trim((string) $tipe));

        return match (true) {
            $tipe === self::PDF => self::PDF,
            in_array($tipe, self::EKSTENSI_GAMBAR, true) => self::GAMBAR,
            in_array($tipe, self::EKSTENSI_KANTOR, true) => self::KANTOR,
            default => null,
        };
    }

    /** true bila berkas bisa ditampilkan langsung di halaman. */
    public static function bisaDilihat(?string $tipe): bool
    {
        return self::kelompok($tipe) !== null;
    }

    /** Label tipe berkas yang enak dibaca, mis. "docx" -> "DOCX". */
    public static function label(?string $tipe): string
    {
        $tipe = strtolower(trim((string) $tipe));

        return $tipe !== '' ? strtoupper($tipe) : 'BERKAS';
    }
}
