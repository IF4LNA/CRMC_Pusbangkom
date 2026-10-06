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

    /**
     * Ikon lucide yang mewakili bidang ini.
     *
     * Dipilih dari nama bidang supaya tidak perlu kolom baru di tabel hanya
     * untuk kosmetik. Method (bukan atribut) karena dipakai dari dua tempat:
     * view composer navigasi dan kartu bidang di dashboard.
     */
    public function ikon(): string
    {
        $nama = strtoupper((string) $this->nama_bidang);

        return match (true) {
            str_contains($nama, 'SDA'), str_contains($nama, 'SUMBER DAYA AIR') => 'waves',
            str_contains($nama, 'CKPS'), str_contains($nama, 'CIPTA KARYA') => 'building-2',
            default => 'briefcase',
        };
    }
}