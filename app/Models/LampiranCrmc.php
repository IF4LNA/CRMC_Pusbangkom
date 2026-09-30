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
     * Nilai `file_path` disimpan sebagai URL penuh dari Storage::url(),
     * misalnya "http://localhost:8000/storage/crmc/2026/1/sop/file.pdf".
     * Path ini diperlukan untuk menghapus file fisik dari storage.
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

    public function dokumenCrmc()
    {
        return $this->belongsTo(DokumenCrmc::class, 'dokumen_crmc_id');
    }
}