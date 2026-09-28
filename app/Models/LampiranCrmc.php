<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LampiranCrmc extends Model
{
    use HasFactory;

    protected $table = 'lampiran_crmc';

    protected $fillable = [
        'dokumen_crmc_id',
        'kategori_komponen',
        'nama_file',
        'file_path',
        'tipe_file',
    ];

    public function dokumenCrmc()
    {
        return $this->belongsTo(DokumenCrmc::class, 'dokumen_crmc_id');
    }
}