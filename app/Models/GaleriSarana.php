<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Satu gambar pada carousel "Sarana dan Prasarana" di halaman Beranda.
 */
class GaleriSarana extends Model
{
    protected $table = 'galeri_sarana';

    protected $fillable = [
        'path',
        'judul',
        'keterangan',
        'urutan',
    ];

    /**
     * URL gambar yang siap dipakai di view.
     *
     * Memakai asset() supaya mengikuti host aktif (domain Laragon, localhost,
     * atau IP) tanpa bergantung pada nilai APP_URL.
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . ltrim(str_replace('\\', '/', (string) $this->path), '/'));
    }

    /** Hapus file gambarnya dari disk. Aman dipanggil walau file sudah hilang. */
    public function hapusFile(): void
    {
        if (!empty($this->path)) {
            Storage::disk('public')->delete($this->path);
        }
    }
}