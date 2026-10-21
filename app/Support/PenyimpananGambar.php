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
     * Batas megapiksel citra kerja saat kompresi berbasis ukuran. Scan
     * dokumen bisa berukuran raksasa (mis. 10.000 px per sisi); tanpa batas
     * ini memori 512 MB bisa habis hanya untuk satu berkas.
     */
    public const MEGAPIKSEL_KERJA = 30_000_000;

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
            $diperkecil = self::skalakan($sumber, self::MAKS_SISI / $sisiTerpanjang, $tipe);
            imagedestroy($sumber);
            $sumber = $diperkecil;
        }

        $berhasil = self::tulis($sumber, $pathAbsolut, $tipe);
        imagedestroy($sumber);

        return $berhasil;
    }

    /**
     * Kompres gambar di tempat yang sama sampai ukuran filenya muat di
     * $targetBytes. Berbeda dengan optimalkan() yang memangkas ke piksel
     * tampilan, fungsi ini hanya menjaga file tetap di bawah batas ukuran
     * unggahan: kualitas diturunkan dulu, lalu dimensi dikurangi bertahap
     * bila menurunkan kualitas saja tidak cukup.
     *
     * Format file tidak diubah. Bila file tidak bisa dikecilkan sampai
     * target (mis. PNG hasil scan yang sangat besar), fungsi mengembalikan
     * false dan file asli tetap utuh.
     *
     * @return bool true bila file berhasil ditulis ulang dan muat di target
     */
    public static function kompresUkuran(string $path, int $targetBytes): bool
    {
        $info = @getimagesize($path);
        if ($info === false) {
            return false;
        }

        [, , $tipe] = $info;

        // Hanya format ini yang bisa direkompres dengan kontrol ukuran.
        // GIF sengaja dilewati karena animasinya hilang kalau lewat GD.
        if (! in_array($tipe, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return false;
        }

        $sumber = self::baca($path, $tipe);
        if ($sumber === null) {
            return false;
        }

        $sumber = self::terapkanOrientasiExif($sumber, $path);

        $megapiksel = imagesx($sumber) * imagesy($sumber);
        $skalaAwal = $megapiksel > self::MEGAPIKSEL_KERJA
            ? sqrt(self::MEGAPIKSEL_KERJA / $megapiksel)
            : 1.0;

        // PNG sudah terkompresi di level piksel, jadi kualitasnya hanya
        // diatur lewat level zlib (9 = paling rapat) dan cukup sekali
        // percobaan per skala. JPEG/WebP memakai tangga kualitas.
        $kualitasList = $tipe === IMAGETYPE_PNG ? [9] : [85, 75, 65, 55, 45, 35, 25];

        $muat = false;

        for ($skala = $skalaAwal; $skala >= 0.2 && ! $muat; $skala *= 0.7) {
            $gambar = $skala >= 0.999
                ? $sumber
                : self::skalakan($sumber, $skala, $tipe);

            foreach ($kualitasList as $kualitas) {
                $tmp = tempnam(sys_get_temp_dir(), 'kompres-gambar');
                if ($tmp === false) {
                    break 2;
                }

                self::tulisKualitas($gambar, $tmp, $tipe, $kualitas);

                if (filesize($tmp) <= $targetBytes) {
                    $muat = @rename($tmp, $path);
                    if (! $muat) {
                        // Cadangan bila rename ke direktori lain ditolak
                        // sistem (jarang, tapi mungkin terjadi di Windows).
                        $muat = @copy($tmp, $path);
                        @unlink($tmp);
                    }
                    break;
                }

                @unlink($tmp);
            }

            if ($gambar !== $sumber) {
                imagedestroy($gambar);
            }
        }

        imagedestroy($sumber);

        return $muat;
    }

    /** Salinan citra yang diskalakan ulang dengan resampling. */
    private static function skalakan($sumber, float $skala, int $tipe)
    {
        $lebarBaru = max(1, (int) round(imagesx($sumber) * $skala));
        $tinggiBaru = max(1, (int) round(imagesy($sumber) * $skala));

        $tujuan = imagecreatetruecolor($lebarBaru, $tinggiBaru);
        self::pertahankanTransparansi($tujuan, $tipe);

        imagecopyresampled($tujuan, $sumber, 0, 0, 0, 0, $lebarBaru, $tinggiBaru, imagesx($sumber), imagesy($sumber));

        return $tujuan;
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
        // JPEG/WebP memakai kualitas bawaan; PNG level zlib 6 (bawaan PHP),
        // sesuai perilaku optimasi sebelumnya.
        $kualitas = $tipe === IMAGETYPE_PNG ? 6 : self::KUALITAS_JPEG;

        return self::tulisKualitas($gambar, $path, $tipe, $kualitas);
    }

    /** Seperti tulis(), tetapi kualitas/level kompresinya bisa diatur. */
    private static function tulisKualitas($gambar, string $path, int $tipe, int $kualitas): bool
    {
        return match ($tipe) {
            IMAGETYPE_JPEG => imagejpeg($gambar, $path, $kualitas),
            IMAGETYPE_PNG  => imagepng($gambar, $path, $kualitas),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($gambar, $path, $kualitas) : false,
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
