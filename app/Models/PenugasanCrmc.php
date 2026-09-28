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
    ];

    public function subMenu()
    {
        return $this->belongsTo(SubMenu::class, 'sub_menu_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
