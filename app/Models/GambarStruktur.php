<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Gambar bagan struktur organisasi untuk halaman Beranda.
 *
 * Hanya baris terbaru yang dipakai. Baris lama dihapus saat admin
 * menggambar atau menghapus bagan, jadi tabel ini maksimal berisi satu baris.
 */
class GambarStruktur extends Model
{
    protected $table = 'gambar_struktur';

    protected $fillable = ['path', 'keterangan'];

    /** Baris bagan yang sedang aktif, atau null bila belum ada. */
    public static function aktif(): ?self
    {
        return static::orderByDesc('id')->first();
    }

    /** URL publik untuk ditampilkan pada <img src>. */
    public function getUrlAttribute(): string
    {
        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->path);
    }

    /**
     * Path relatif di disk "public", untuk menghapus file fisik.
     *
     * Nilai `path` disimpan relatif ("struktur/bagan.jpg"), jadi tidak perlu
     * diurai seperti pada LampiranCrmc yang memakai URL penuh.
     */
    public function getStoragePathAttribute(): ?string
    {
        return $this->path ?: null;
    }
}
