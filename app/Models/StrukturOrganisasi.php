<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Satu baris pada struktur organisasi yang tampil di halaman Beranda.
 *
 * Berbeda dengan PenugasanCrmc yang bersifat per sub-bidang, model ini
 * menggambarkan susunan organisasi yang diringkas: satu Pemilik Risiko di
 * puncak, lalu pengendali per bidang di bawahnya.
 */
class StrukturOrganisasi extends Model
{
    protected $table = 'struktur_organisasi';

    protected $fillable = [
        'user_id',
        'peran',
        'nama_jabatan',
        'bidang_id',
        'urutan',
        'keterangan',
    ];

    /**
     * Nilai enum `peran` yang sah, beserta label, warna, dan tingkat untuk
     * ditampilkan pada org chart.
     */
    public const PERAN = [
        'pemilik_risiko' => [
            'label' => 'Pemilik Risiko',
            'warna' => 'blue',
            'tingkat' => 1,
        ],
        'pengendali_mutu' => [
            'label' => 'Pengendali Mutu',
            'warna' => 'amber',
            'tingkat' => 2,
        ],
        'pengendali_risiko' => [
            'label' => 'Pengendali Risiko',
            'warna' => 'emerald',
            'tingkat' => 3,
        ],
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class);
    }

    /** Label peran, atau teks aslinya bila nilai tidak dikenal. */
    public function labelPeran(): string
    {
        return self::PERAN[$this->peran]['label'] ?? Str::headline((string) $this->peran);
    }
}