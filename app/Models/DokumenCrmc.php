<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenCrmc extends Model
{
    use HasFactory;

    protected $table = 'dokumen_crmc';

    protected $fillable = [
        'sub_menu_id',
        'tahun_pelaksanaan',
        'status_residu_risiko',
        'evaluasi_dan_rencana',
    ];

    // Relasi ke Sub Menu
    public function subMenu()
    {
        return $this->belongsTo(SubMenu::class, 'sub_menu_id');
    }

    // Relasi ke Lampiran File (One-to-Many)
    public function lampiran()
    {
        return $this->hasMany(LampiranCrmc::class, 'dokumen_crmc_id');
    }
}
