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
     * URL berkas untuk tampilan.
     *
     * Sejak berkas CRMC disimpan di disk privat, URL publik "/storage/..."
     * tidak lagi dipakai. Semua tautan melewati route `crmc.lampiran.berkas`
     * yang memeriksa akses bidang sebelum mengirim isi berkas. Route dibuat
     * relatif supaya tetap mengikuti host yang sedang dibuka.
     */
    public function getUrlAttribute(): ?string
    {
        if (! $this->exists) {
            return $this->file_path ?: null;
        }

        return route('crmc.lampiran.berkas', ['id' => $this->id], false);
    }

    /** URL unduhan paksa (Content-Disposition: attachment). */
    public function getUrlUnduhAttribute(): ?string
    {
        $url = $this->url;

        return $url === null ? null : $url . '?unduh=1';
    }

    /**
     * Disk tempat berkas fisik berada.
     *
     * Berkas baru ada di disk "local" (storage/app/private). Berkas lama
     * masih ada di disk "public" sampai perintah
     * `dokumen:pindahkan-ke-privat` dijalankan. Disk "local" dicek lebih
     * dulu karena unggahan baru selalu menulis ke sana.
     */
    public function diskBerkas(): ?string
    {
        $path = $this->storage_path;

        if ($path === null) {
            return null;
        }

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return $disk;
            }
        }

        return null;
    }

    /**
     * Hapus berkas fisik dari disk mana pun tempat ia berada.
     *
     * Dipakai oleh aksi hapus lampiran, hapus dokumen per tahun, dan hapus
     * sub-bidang. Tanpa pencarian di dua disk, berkas privat akan tertinggal
     * sebagai sampah setelah baris database dihapus.
     */
    public function hapusBerkas(): void
    {
        $path = $this->storage_path;

        if ($path === null) {
            return;
        }

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
        }
    }

    public function dokumenCrmc()
    {
        return $this->belongsTo(DokumenCrmc::class, 'dokumen_crmc_id');
    }
}