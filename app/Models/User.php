<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // 1. Import trait HasApiTokens di sini


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // 2. Masukkan HasApiTokens ke dalam trait User

    protected $fillable = [
        'name',
        'nip',
        'jabatan',
        'email',
        'password',
        'foto_profil',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getFotoUrlAttribute(): string
    {
        if (!empty($this->foto_profil)) {
            if (str_starts_with($this->foto_profil, 'http://') || str_starts_with($this->foto_profil, 'https://')) {
                return $this->foto_profil;
            }
            if (file_exists(public_path('storage/' . $this->foto_profil))) {
                return asset('storage/' . $this->foto_profil);
            }
            if (file_exists(public_path($this->foto_profil))) {
                return asset($this->foto_profil);
            }
        }

        // Fallback modern avatar dengan inisial dan palet PUPR (Navy & Amber)
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0f172a&color=f59e0b&bold=true&size=160';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPegawai(): bool
    {
        return $this->role === 'pegawai';
    }

    public function penugasan()
    {
        return $this->hasMany(PenugasanCrmc::class, 'user_id');
    }
}