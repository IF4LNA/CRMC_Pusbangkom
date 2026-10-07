<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Satu dokumen dasar hukum (landasan regulasi) pada tab "Dasar Hukum".
 *
 * Daftar regulasinya sudah pasti, jadi barisnya dibuat sekali lewat seeder
 * dan tidak bisa ditambah lewat antarmuka. Yang dikelola admin hanya
 * berkas scan-nya: unggah, ganti, atau hapus.
 */
class DokumenDasarHukum extends Model
{
    protected $table = 'dokumen_dasar_hukum';

    protected $fillable = [
        'kode',
        'jenis',
        'penerbit',
        'nomor',
        'tentang',
        'urutan',
        'path',
        'nama_file',
        'tipe_file',
        'ukuran',
    ];

    protected $casts = [
        'ukuran' => 'integer',
    ];

    /** true bila admin sudah mengunggah berkas untuk regulasi ini. */
    public function sudahAdaBerkas(): bool
    {
        return ! empty($this->path);
    }

    /**
     * URL berkas untuk ditampilkan pada pratinjau.
     *
     * Memakai asset() supaya mengikuti host aktif (domain Laragon, localhost,
     * atau IP) tanpa bergantung pada nilai APP_URL.
     */
    public function getUrlAttribute(): ?string
    {
        if (! $this->sudahAdaBerkas()) {
            return null;
        }

        return asset('storage/'.ltrim(str_replace('\\', '/', (string) $this->path), '/'));
    }

    /** Path relatif di disk "public", untuk menghapus file fisik. */
    public function getStoragePathAttribute(): ?string
    {
        return $this->path ?: null;
    }

    /** Hapus file berkasnya dari disk. Aman dipanggil walau file sudah hilang. */
    public function hapusFile(): void
    {
        if ($this->sudahAdaBerkas()) {
            Storage::disk('public')->delete($this->path);
        }
    }

    /**
     * Ukuran berkas dalam bahasa manusia, mis. "1,8 MB".
     *
     * Nilai kosong berarti berkas belum diunggah, jadi jangan tampilkan
     * "0 B" yang menyesatkan.
     */
    public function ukuranTerbaca(): ?string
    {
        if (! $this->sudahAdaBerkas() || ! $this->ukuran) {
            return null;
        }

        $bita = $this->ukuran;

        foreach (['B', 'KB', 'MB', 'GB'] as $satuan) {
            if ($bita < 1024) {
                return round($bita, $satuan === 'B' ? 0 : 1).' '.$satuan;
            }

            $bita /= 1024;
        }

        return round($bita, 1).' TB';
    }

    /** Ikon lucide sesuai jenis naskah: surat edaran atau keputusan. */
    public function ikon(): string
    {
        return $this->jenis === 'Keputusan' ? 'file-check-2' : 'mail';
    }
}
