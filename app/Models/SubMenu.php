<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubMenu extends Model
{
    protected $table = 'sub_menu';

    protected $fillable = ['bidang_id', 'nama_sub_menu', 'gambar_latar'];

    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    // Relasi ke Penugasan PIC (3 Lapis Penanggung Jawab)
    public function penugasan()
    {
        return $this->hasMany(PenugasanCrmc::class, 'sub_menu_id')->with('user');
    }

    public function dokumen()
    {
        return $this->hasMany(DokumenCrmc::class, 'sub_menu_id');
    }

    /**
     * Ubah teks apa pun menjadi slug URL yang sama persis dengan hasil
     * fungsi generateSlug() di layout, supaya tautan yang dibuat di view
     * selalu ketemu dengan sub-bidang yang dimaksud.
     *
     * Aturannya sengaja memangkas SEMUA karakter selain huruf, angka, spasi,
     * dan tanda hubung. Jadi "Kerjasama Pendidikan (MSS)" menjadi
     * "kerjasama-pendidikan-mss", bukan "kerjasama-pendidikan-(mss)" yang
     * tidak akan cocok saat dicari balik.
     */
    public static function normalisasiSlug(string $teks): string
    {
        $teks = Str::lower($teks);
        $teks = str_replace('&', 'dan', $teks);
        $teks = (string) preg_replace('/[^a-z0-9\s-]+/', '', $teks);

        return trim((string) preg_replace('/[\s-]+/', '-', $teks), '-');
    }

    /** Slug siap pakai untuk route `crmc.show`. */
    public function getSlugAttribute(): string
    {
        return self::normalisasiSlug((string) $this->nama_sub_menu);
    }

    /**
     * Gambar latar kartu sub-bidang.
     *
     * Path relatif di disk "public" (folder "latar-sub-bidang"). Dikembalikan
     * sebagai URL siap pakai; null bila admin belum mengunggah gambar.
     */
    public function getGambarLatarUrlAttribute(): ?string
    {
        if (empty($this->gambar_latar)) {
            return null;
        }

        return Storage::disk('public')->url($this->gambar_latar);
    }
}
