<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenugasanCrmc extends Model
{
    use HasFactory;

    protected $table = 'penugasan_crmc'; // Tambahkan baris ini

    protected $fillable = [
        'sub_menu_id',
        'user_id',
        'peran',
        'tahun_pelaksanaan',
    ];

    public function subMenu()
    {
        return $this->belongsTo(SubMenu::class, 'sub_menu_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Batasi penugasan pada satu tahun pelaksanaan.
     *
     * Identitas pegawai beserta PIC-nya berbeda tiap tahun, jadi pembacaan
     * di halaman 8 Komponen selalu harus dibatasi tahun. Tanpa filter ini,
     * penugasan tahun lalu ikut terbaca dan kartu menampilkan orang yang
     * sudah tidak lagi bertugas.
     */
    public function scopeTahun($query, int $tahun)
    {
        return $query->where('tahun_pelaksanaan', $tahun);
    }
}
