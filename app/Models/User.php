<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
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
        $foto = $this->getRawOriginal('foto_profil');

        if (!empty($foto)) {
            // Foto eksternal (URL langsung)
            if (str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://')) {
                return $foto;
            }

            // Path relatif pada disk "public" (root: storage/app/public)
            $candidates = [$foto];
            if (str_starts_with($foto, 'public/')) {
                $candidates[] = substr($foto, strlen('public/'));
            } else {
                $candidates[] = 'public/' . $foto;
            }

            foreach ($candidates as $path) {
                if (Storage::disk('public')->exists($path)) {
                    // Pakai asset() (path relatif terhadap host aktif) supaya foto tetap
                    // tampil di domain Laragon, localhost, IP, maupun port lain,
                    // tanpa bergantung pada nilai APP_URL.
                    return asset('storage/' . ltrim(str_replace('\\', '/', $path), '/'));
                }
            }
        }

        // Belum ada foto -> pakai placeholder SVG lokal (tanpa internet)
        return $this->placeholderAvatarUrl();
    }

    /**
     * Placeholder avatar berupa inline SVG (data URI).
     *
     * Diganti dari ui-avatars.com supaya halaman tetap tampil utuh di
     * jaringan yang memblokir akses ke layanan eksternal. Mengembalikan
     * data URI, jadi di view cukup menulis `{{ $user->foto_url }}`
     * tanpa perlu operator `??`.
     */
    public function placeholderAvatarUrl(int $size = 160): string
    {
        $palette = [
            ['bg' => '#0f172a', 'fg' => '#f59e0b'], // navy / amber
            ['bg' => '#1e3a8a', 'fg' => '#fcd34d'],
            ['bg' => '#134e4a', 'fg' => '#5eead4'],
            ['bg' => '#312e81', 'fg' => '#a5b4fc'],
            ['bg' => '#7c2d12', 'fg' => '#fdba74'],
            ['bg' => '#164e63', 'fg' => '#67e8f9'],
        ];

        $warna = $palette[hexdec(substr(md5((string) $this->name), 0, 2)) % count($palette)];
        $inisial = $this->initials();

        $svg = implode('', [
            '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">',
            '<rect width="100%" height="100%" fill="' . $warna['bg'] . '"/>',
            '<text x="50%" y="50%" dy="0.35em" text-anchor="middle" ',
            'font-family="Segoe UI, Roboto, Helvetica, Arial, sans-serif" ',
            'font-size="' . (int) round($size * 0.38) . '" font-weight="700" fill="' . $warna['fg'] . '">',
            htmlspecialchars($inisial, ENT_XML1, 'UTF-8'),
            '</text></svg>',
        ]);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Inisial nama untuk placeholder avatar.
     *
     * Mengabaikan gelar di depan (Dr., Dra., Ir., Prof., dll) dan gelar
     * akademik di belakang (S.T., S.Pd., M.T., dll) agar yang tampil
     * adalah inisial nama sebenarnya.
     */
    public function initials(): string
    {
        // Buang gelar akademik di belakang, contoh: "Dr. Budi Santoso, M.Sc."
        $nama = trim(explode(',', (string) $this->name)[0]);

        $kata = array_values(array_filter(preg_split('/\s+/', $nama) ?: []));
        if ($kata === []) {
            return '?';
        }

        // Buang gelar di depan: Dr., Dra., Drs., Ir., Prof., H., Hj.
        $gelarDepan = '/^(dr|dra|drs|ir|prof|h|hj)\.?(?:\s|$)/i';
        while (count($kata) > 1 && preg_match($gelarDepan, $kata[0])) {
            array_shift($kata);
        }

        $awal = mb_strtoupper(mb_substr($kata[0], 0, 1));
        $akhir = count($kata) > 1 ? mb_strtoupper(mb_substr($kata[count($kata) - 1], 0, 1)) : '';

        return $awal . $akhir;
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