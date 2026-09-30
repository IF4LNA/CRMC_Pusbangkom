<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Utilitas penyimpanan gambar yang diunggah pengguna.
 *
 * Foto dari kamera HP berukuran 2000-4000 px (2-5 MB). Tapi di halaman
 * web ukurannya jauh lebih kecil (mis. 224x288 px untuk foto identitas),
 * jadi dimensi aslinya tidak menambah ketajaman, hanya membebani
 * bandwidth. Kelas ini menskalakan ulang ke ukuran yang masih tajam untuk
 * layar 2x, lalu mengompres ulang.
 *
 * Dipakai untuk foto pegawai (folder "foto-pegawai") maupun galeri
 * sarana dan prasarana (folder "galeri-sarana").
 */
class PenyimpananGambar
{
    /** Sisi terpanjang hasil optimasi (px). 1200 px tajam sampai ~2.7x ukuran tampil. */
    public const MAKS_SISI = 1200;

    /** Kualitas JPEG hasil rekompresi. */
    public const KUALITAS_JPEG = 85;

    /**
     * Simpan file ke disk, lalu optimalkan di tempat (in-place).
     *
     * Format dan ekstensi file tidak diubah, supaya Content-Type yang
     * dikirimi web server tetap konsisten dengan isi file.
     *
     * @return string Path relatif hasil penyimpanan, mis. "foto-pegawai/abc.jpg"
     */
    public static function simpan(UploadedFile $file, string $folder, string $disk = 'public'): string
    {
        $path = $file->store($folder, $disk);

        try {
            self::optimalkan(Storage::disk($disk)->path($path));
        } catch (\Throwable $e) {
            // Optimasi bersifat opsional: bila gagal, file asli tetap
            // tersimpan utuh dan foto tetap bisa tampil.
            report($e);
        }

        return $path;
    }

    /**
     * Skalakan ulang + rekompres file gambar di lokasi yang sama.
     *
     * @return bool true bila file berhasil ditulis ulang
     */
    public static function optimalkan(string $pathAbsolut): bool
    {
        if (!is_file($pathAbsolut)) {
            return false;
        }

        $info = @getimagesize($pathAbsolut);
        if ($info === false) {
            return false;
        }

        [$lebar, $tinggi, $tipe] = $info;

        $sumber = self::baca($pathAbsolut, $tipe);
        if ($sumber === null) {
            return false;
        }

        $sumber = self::terapkanOrientasiExif($sumber, $pathAbsolut);

        // Hitung skala hanya kalau memang melebihi batas.
        $sisiTerpanjang = max($lebar, $tinggi);
        if ($sisiTerpanjang > self::MAKS_SISI) {
            $skala = self::MAKS_SISI / $sisiTerpanjang;
            $lebarBaru = max(1, (int) round($lebar * $skala));
            $tinggiBaru = max(1, (int) round($tinggi * $skala));

            $tujuan = imagecreatetruecolor($lebarBaru, $tinggiBaru);
            self::pertahankanTransparansi($tujuan, $tipe);

            imagecopyresampled($tujuan, $sumber, 0, 0, 0, 0, $lebarBaru, $tinggiBaru, imagesx($sumber), imagesy($sumber));
            imagedestroy($sumber);
            $sumber = $tujuan;
        }

        $berhasil = self::tulis($sumber, $pathAbsolut, $tipe);
        imagedestroy($sumber);

        return $berhasil;
    }

    /** Baca file gambar menjadi resource GD sesuai tipenya. */
    private static function baca(string $path, int $tipe)
    {
        return match ($tipe) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            IMAGETYPE_GIF  => @imagecreatefromgif($path),
            default        => null,
        };
    }

    /** Tulis resource GD kembali ke file, mempertahankan format aslinya. */
    private static function tulis($gambar, string $path, int $tipe): bool
    {
        return match ($tipe) {
            IMAGETYPE_JPEG => imagejpeg($gambar, $path, self::KUALITAS_JPEG),
            IMAGETYPE_PNG  => imagepng($gambar, $path, 6),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($gambar, $path, self::KUALITAS_JPEG) : false,
            IMAGETYPE_GIF  => imagegif($gambar, $path),
            default        => false,
        };
    }

    /**
     * Foto dari kamera HP sering menyimpan orientasi di metadata EXIF
     * (bukan di pikselnya). Tanpa koreksi orientasi, hasil resize bisa
     * terpotong 90 derajat.
     */
    private static function terapkanOrientasiExif($gambar, string $path)
    {
        if (!function_exists('exif_read_data')) {
            return $gambar;
        }

        $exif = @exif_read_data($path);
        $orientasi = (int) ($exif['Orientation'] ?? 1);

        $putar = match ($orientasi) {
            3       => IMG_ROTATE_180,
            6       => IMG_ROTATE_90,
            8       => IMG_ROTATE_270,
            default => false,
        };

        if ($putar !== false) {
            $dibalik = imagerotate($gambar, $putar);
            if ($dibalik !== false) {
                imagedestroy($gambar);
                return $dibalik;
            }
        }

        return $gambar;
    }

    /** PNG/WebP: siapkan alpha channel supaya latar transparan tidak jadi hitam. */
    private static function pertahankanTransparansi($gambar, int $tipe): void
    {
        if (!in_array($tipe, [IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return;
        }

        imagealphablending($gambar, false);
        imagesavealpha($gambar, true);
        $transparan = imagecolorallocatealpha($gambar, 0, 0, 0, 127);
        imagefilledrectangle($gambar, 0, 0, imagesx($gambar) - 1, imagesy($gambar) - 1, $transparan);
        imagealphablending($gambar, true);
    }
}
