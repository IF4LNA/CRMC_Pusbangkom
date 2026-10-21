<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Kompresi otomatis berkas dokumen yang melebihi batas ukuran 20 MB.
 *
 * Aplikasi membatasi unggahan dokumen 20 MB per file. Supaya berkas yang
 * lebih besar tidak selalu ditolak mentah-mentah, berkas di atas batas itu
 * dikompres otomatis sebelum disimpan:
 *
 *   - Gambar (JPG/JPEG/PNG/WebP) → direkompres memakai GD lewat
 *     PenyimpananGambar::kompresUkuran(): kualitas diturunkan dulu, lalu
 *     dimensi dikurangi bertahap bila masih kurang kecil.
 *   - Dokumen Office (.docx/.xlsx/.pptx) → arsip zip-nya ditulis ulang
 *     dengan kompresi deflate maksimum.
 *   - PDF → dikompres lewat Imagick bila ekstensi PHP itu tersedia di
 *     server. Tanpa Imagick, PDF dibiarkan apa adanya.
 *   - Format lama (.doc/.xls) → butuh software eksternal untuk dikompres,
 *     jadi dikembalikan apa adanya.
 *
 * Kalau setelah proses ukuran berkas masih melebihi batas, kompres()
 * mengembalikan false. Controller yang memanggil bertugas menolak berkas
 * tersebut dan menyampaikan pesan yang jelas ke pengguna.
 */
class KompresiBerkas
{
    /** Batas ukuran berkas dokumen: 20 MB, sama dengan rule "max:20480". */
    public const BATAS_KB = 20480;

    /**
     * Batas penerimaan unggahan: 50 MB. Sengaja lebih longgar daripada
     * BATAS_KB supaya berkas besar masih sempat diterima lalu dikompres
     * otomatis di server sebelum ditentukan lolos atau tidak.
     */
    public const MAKS_UNGGAH_KB = 51200;

    /** Batas ukuran berkas dalam byte. */
    public static function batasBytes(): int
    {
        return self::BATAS_KB * 1024;
    }

    /**
     * Kompres berkas in-place kalau ukurannya melebihi batas 20 MB.
     *
     * @return bool true bila ukuran akhir berkas ≤ 20 MB (termasuk saat
     *              berkas memang sudah kecil dan tidak perlu dikompres)
     */
    public static function kompres(UploadedFile $file): bool
    {
        $path = $file->getRealPath();

        if ($path === '' || ! is_file($path)) {
            return false;
        }

        // Sudah sesuai batas: jangan disentuh, kualitas aslinya dipertahankan.
        if (self::ukuran($path) <= self::batasBytes()) {
            return true;
        }

        try {
            match (self::kenaliFormat($path)) {
                'gambar' => PenyimpananGambar::kompresUkuran($path, self::batasBytes()),
                'arsip'  => self::kompresArsip($path),
                default  => self::kompresPdf($path),
            };
        } catch (\Throwable $e) {
            // Kompresi tidak boleh membuat unggahan error total. Berkas yang
            // gagal dikompres akan tetap ditolak controller dengan pesan jelas.
            report($e);
        }

        return self::ukuran($path) <= self::batasBytes();
    }

    /** Ukuran file terkini; cache stat PHP dibersihkan dulu karena file bisa saja baru ditulis ulang. */
    private static function ukuran(string $path): int
    {
        clearstatcache(true, $path);

        return (int) @filesize($path);
    }

    /**
     * Ukuran terkini berkas hasil unggahan (setelah dikompres, bila perlu).
     *
     * Symfony meng-cache hasil UploadedFile::getSize() sejak validasi
     * berjalan, jadi ukuran lama bisa terbaca kalau file ditulis ulang di
     * tempat. Fungsi ini membaca ukuran langsung dari disk.
     */
    public static function ukuranAkhir(UploadedFile $file): int
    {
        $path = $file->getRealPath();

        if ($path === '' || ! is_file($path)) {
            return (int) $file->getSize();
        }

        return self::ukuran($path);
    }

    /** Kenali jenis berkas dari isinya (bukan ekstensinya). */
    private static function kenaliFormat(string $path): string
    {
        if (@getimagesize($path) !== false) {
            return 'gambar';
        }

        $zip = new \ZipArchive();
        if ($zip->open($path) === true) {
            $zip->close();

            return 'arsip';
        }

        if (str_starts_with((string) @file_get_contents($path, false, null, 0, 5), '%PDF-')) {
            return 'pdf';
        }

        return 'lain';
    }

    /**
     * Deflate ulang arsip Office (docx/xlsx/pptx) dengan kompresi maksimum.
     *
     * Office Open XML menyimpan ratusan berkas XML di dalam arsip zip.
     * Berkas hasil ekspor default sering memakai level kompresi yang lebih
     * ringan, jadi menulis ulang seluruh isinya dengan kompresi paling
     * rapat masih bisa menghemat ukuran.
     */
    private static function kompresArsip(string $path): bool
    {
        $sumber = new \ZipArchive();
        if ($sumber->open($path) !== true) {
            return false;
        }

        $tmp = tempnam(sys_get_temp_dir(), 'kompres-arsip');
        if ($tmp === false) {
            $sumber->close();

            return false;
        }

        $tujuan = new \ZipArchive();
        if ($tujuan->open($tmp, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            $sumber->close();
            @unlink($tmp);

            return false;
        }

        for ($i = 0; $i < $sumber->numFiles; $i++) {
            $info = $sumber->statIndex($i);
            if ($info === false) {
                continue;
            }

            $nama = $info['name'];

            if (str_ends_with($nama, '/')) {
                $tujuan->addEmptyDir($nama);
                continue;
            }

            $isi = $sumber->getFromIndex($i);
            if ($isi !== false) {
                $tujuan->addFromString($nama, $isi);
                $tujuan->setCompressionName($nama, \ZipArchive::CM_DEFLATE);
            }
        }

        $tujuan->close();
        $sumber->close();

        if (! is_file($tmp) || filesize($tmp) === 0) {
            @unlink($tmp);

            return false;
        }

        if (@rename($tmp, $path)) {
            return true;
        }

        // Cadangan bila rename lintas direktori ditolak sistem.
        $berhasil = @copy($tmp, $path);
        @unlink($tmp);

        return $berhasil;
    }

    /**
     * Kompres PDF lewat Imagick bila tersedia.
     *
     * Ekstensi Imagick tidak selalu dipasang (mis. di Laragon secara
     * bawaan tidak ada), jadi tanpa Imagick fungsi ini hanya memberi tanda
     * gagal dan berkas PDF dibiarkan apa adanya.
     */
    private static function kompresPdf(string $path): bool
    {
        if (! class_exists(\Imagick::class)) {
            return false;
        }

        $gambar = new \Imagick();
        $gambar->readImage($path);

        // Gambar di dalam PDF dikompresi ulang sebagai JPEG dan metadata
        // (profil warna, EXIF, dsb.) dibuang karena semuanya menambah ukuran.
        $gambar->setImageCompression(\Imagick::COMPRESSION_JPEG);
        $gambar->setImageCompressionQuality(75);
        $gambar->stripImage();
        $gambar->writeImages($path, true);
        $gambar->clear();

        return true;
    }
}
