<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LampiranCrmc extends Model
{
    use HasFactory;

    protected $table = 'lampiran_crmc';

    protected $fillable = [
        'dokumen_crmc_id',
        'kategori_komponen',
        'nama_file',
        'keterangan',
        'file_path',
        'tipe_file',
    ];

    /**
     * Path relatif terhadap disk "public" (root: storage/app/public).
     *
     * Sejak perbaikan penyimpanan, `file_path` disimpan sebagai jalur
     * relatif, mis. "crmc/2026/1/sop/file.pdf". Data lama masih berupa
     * URL penuh ("http://localhost:8000/storage/crmc/2026/1/sop/file.pdf"),
     * jadi accessor ini tetap memakai parse_url + pemotongan prefix disk
     * supaya keduanya menghasilkan jalur yang sama. Lihat juga perintah
     * `php artisan dokumen:migrasi-path` untuk menormalkan data lama.
     */
    public function getStoragePathAttribute(): ?string
    {
        if (empty($this->file_path)) {
            return null;
        }

        // Ambil bagian path saja dari URL (buang scheme + host)
        $path = parse_url($this->file_path, PHP_URL_PATH) ?: $this->file_path;

        // Buang prefix path milik disk public, mis. "/storage"
        $diskUrlPath = parse_url(Storage::disk('public')->url(''), PHP_URL_PATH) ?: '/storage';
        if ($diskUrlPath !== '/' && str_starts_with($path, $diskUrlPath)) {
            $path = substr($path, strlen($diskUrlPath));
        }

        $path = ltrim($path, '/');

        // Dukungan data lama: path lama memakai prefix "public/"
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        return $path !== '' ? $path : null;
    }

    /**
     * URL berkas untuk tampilan, dibentuk dinamis dari konfigurasi disk
     * "public" yang sedang aktif sehingga mengikuti host/APP_URL sekarang,
     * bukan host lama yang terekam pada data lama.
     *
     * Data lama berupa URL penuh dipulihkan dulu ke jalur relatif lewat
     * accessor storage_path. Bila storage_path kosong, nilai mentah
     * dikembalikan agar tidak ada informasi yang hilang dari tampilan.
     */
    public function getUrlAttribute(): ?string
    {
        $path = $this->storage_path;

        if ($path === null) {
            return $this->file_path ?: null;
        }

        return Storage::disk('public')->url($path);
    }

    public function dokumenCrmc()
    {
        return $this->belongsTo(DokumenCrmc::class, 'dokumen_crmc_id');
    }
}