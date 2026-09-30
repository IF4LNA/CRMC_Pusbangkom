<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Tahun anggaran yang ditambahkan manual oleh Administrator.
 *
 * Melengkapi daftar tahun otomatis (tahun berjalan, tahun depan, dan tahun
 * yang sudah punya dokumen) sehingga admin tetap bisa membuka tahun khusus
 * di luar rentang tersebut.
 */
class TahunAnggaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_anggaran';

    protected $fillable = [
        'tahun',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
        ];
    }
}
