<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 1. Akun Super Admin Utama
        User::create([
            'name' => 'Muhammad Fajar Syaffiqri',
            'nip' => '199001012026011001',
            'jabatan' => 'System Administrator',
            'email' => 'admin@pu.go.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // 2. Akun Kepala Pusat (Pemilik Risiko Universal)
        User::create([
            'name' => 'Dr. Ir. Budi Santoso, M.Sc.',
            'nip' => '197502022000031002',
            'jabatan' => 'Kepala Pusat',
            'email' => 'kapus@pu.go.id',
            'password' => Hash::make('password123'),
            'role' => 'pegawai',
        ]);

        // 3. Akun Kepala Bidang (Pengendali Mutu)
        User::create([
            'name' => 'Andi Wirawan, S.T., M.T.',
            'nip' => '198003032005011003',
            'jabatan' => 'Kepala Bagian Umum Program & Tata Usaha',
            'email' => 'kabag.tu@pu.go.id',
            'password' => Hash::make('password123'),
            'role' => 'pegawai',
        ]);

        User::create([
            'name' => 'Siti Aminah, S.T., M.Eng.',
            'nip' => '198204042008022004',
            'jabatan' => 'Kepala Bidang SDA',
            'email' => 'kabid.sda@pu.go.id',
            'password' => Hash::make('password123'),
            'role' => 'pegawai',
        ]);

        // 4. Akun Petugas Teknis / Staf (Pengendali Risiko)
        User::create([
            'name' => 'Rina Melati, S.Kom.',
            'nip' => '199505052020122005',
            'jabatan' => 'Staf Evaluasi & Pelaporan',
            'email' => 'rina.staf@pu.go.id',
            'password' => Hash::make('password123'),
            'role' => 'pegawai',
        ]);
    }
}
