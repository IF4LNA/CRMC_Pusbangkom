<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Membuat akun administrator awal.
 *
 * Kredensial tidak lagi ditulis di dalam kode. Email diambil dari
 * ADMIN_EMAIL dan kata sandi dari ADMIN_PASSWORD. Bila kata sandi tidak
 * diisi, seeder membuat kata sandi acak dan menampilkannya sekali di
 * terminal.
 *
 * Seeder memakai firstOrCreate: menjalankannya ulang tidak akan mengubah
 * kata sandi akun yang sudah ada. Dengan begitu `db:seed` di produksi
 * tidak pernah menimpa kredensial admin yang sedang dipakai.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim((string) config('app.admin.email')) ?: 'admin@pu.go.id';
        $kataSandi = (string) config('app.admin.password', '');
        $kataSandiDariEnv = $kataSandi !== '';

        $sudahAda = User::where('email', $email)->exists();

        if (! $sudahAda && ! $kataSandiDariEnv) {
            $kataSandi = Str::password(20);
        }

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => trim((string) config('app.admin.name')) ?: 'Administrator CRMC',
                'nip' => trim((string) config('app.admin.nip')) ?: '199001012026011001',
                'jabatan' => 'System Administrator & PIC Pengendalian',
                'password' => Hash::make($kataSandi),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        if (! $admin->wasRecentlyCreated) {
            $this->command?->line("Akun admin {$email} sudah ada; kata sandi tidak diubah.");

            return;
        }

        $this->command?->info("Akun admin dibuat: {$email}");

        if (! $kataSandiDariEnv) {
            $this->command?->warn("Kata sandi acak: {$kataSandi}");
            $this->command?->warn('Catat kata sandi ini sekarang; nilainya tidak ditampilkan lagi.');
        }
    }
}
