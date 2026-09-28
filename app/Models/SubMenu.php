<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubMenu extends Model
{
    protected $table = 'sub_menu';
    protected $fillable = ['bidang_id', 'nama_sub_menu'];

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
}
