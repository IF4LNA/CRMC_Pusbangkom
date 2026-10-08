<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tautan Google Drive milik satu Komponen CRMC pada satu tahun.
 *
 * Berkasnya tetap berada di Google Drive; tabel ini hanya menyimpan
 * alamatnya. Satu baris mewakili satu (sub-bidang, tahun, komponen).
 */
class TautanDriveCrmc extends Model
{
    protected $table = 'tautan_drive_crmc';

    protected $fillable = [
        'sub_menu_id',
        'tahun_pelaksanaan',
        'kategori_komponen',
        'label',
        'url',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'tahun_pelaksanaan' => 'integer',
        ];
    }

    public function subMenu()
    {
        return $this->belongsTo(SubMenu::class, 'sub_menu_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Batasi tautan pada satu tahun pelaksanaan.
     *
     * Tautan disimpan per tahun, sama seperti dokumen dan penugasan PIC, jadi
     * pembacaan di halaman 8 Komponen selalu harus dibatasi tahun.
     */
    public function scopeTahun($query, int $tahun)
    {
        return $query->where('tahun_pelaksanaan', $tahun);
    }

    /**
     * Teks yang dipakai pada tombol pembuka tautan.
     *
     * Kolom label boleh kosong karena yang paling sering diisi hanya alamat,
     * bukan teks tombol. Teks bawaan dipakai supaya tombol tetap
     * berbahasa manusia.
     */
    public function getLabelTampilAttribute(): string
    {
        $label = trim((string) $this->label);

        return $label !== '' ? $label : 'Buka Google Drive';
    }
}
