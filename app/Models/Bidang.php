<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    protected $table = 'bidang'; // Menentukan nama tabel secara eksplisit

    protected $fillable = ['nama_bidang'];

    public function subMenus()
    {
        return $this->hasMany(SubMenu::class, 'bidang_id');
    }
}