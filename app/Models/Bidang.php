<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Bidang extends Model
{
    protected $table = 'bidang'; // Menentukan nama tabel secara eksplisit

    protected $fillable = ['nama_bidang', 'gambar_latar'];

    public function subMenus()
    {
        return $this->hasMany(SubMenu::class, 'bidang_id');
    }

    /**
     * Pegawai yang ditugaskan pada bidang ini.
     *
     * Admin tidak muncul di sini karena admin selalu punya akses penuh
     * tanpa perlu baris pivot.
     */
    public function pengguna(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bidang_user')->withTimestamps();
    }

    /**
     * Banner navigasi bidang.
     *
     * Path relatif di disk "public" (folder "latar-bidang"). Dikembalikan
     * sebagai URL siap pakai; null bila admin belum mengunggah banner.
     */
    public function getGambarLatarUrlAttribute(): ?string
    {
        if (empty($this->gambar_latar)) {
            return null;
        }

        return Storage::disk('public')->url($this->gambar_latar);
    }

    /**
     * Ikon lucide yang mewakili bidang ini.
     *
     * Dipilih dari nama bidang supaya tidak perlu kolom baru di tabel hanya
     * untuk kosmetik. Method (bukan atribut) karena dipakai dari dua tempat:
     * view composer navigasi dan kartu bidang di dashboard.
     */
    public function ikon(): string
    {
        $nama = strtoupper((string) $this->nama_bidang);

        return match (true) {
            str_contains($nama, 'SDA'), str_contains($nama, 'SUMBER DAYA AIR') => 'waves',
            str_contains($nama, 'CKPS'), str_contains($nama, 'CIPTA KARYA') => 'building-2',
            default => 'briefcase',
        };
    }
}