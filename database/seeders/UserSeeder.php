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
        User::updateOrCreate(
            ['email' => 'admin@pu.go.id'],
            [
                'name' => 'Muhammad Fajar Syaffiqri',
                'nip' => '199001012026011001',
                'jabatan' => 'System Administrator & PIC Pengendalian',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // 2. Akun Kepala Pusat (Pemilik Risiko Universal)
        User::updateOrCreate(
            ['email' => 'kapus@pu.go.id'],
            [
                'name' => 'Dr. Ir. Budi Santoso, M.Sc.',
                'nip' => '197502022000031002',
                'jabatan' => 'Kepala Pusat (Pemilik Risiko)',
                'password' => Hash::make('password123'),
                'role' => 'pegawai',
            ]
        );

        // 3. Akun Kepala Bidang (Pengendali Mutu)
        User::updateOrCreate(
            ['email' => 'kabag.tu@pu.go.id'],
            [
                'name' => 'Andi Wirawan, S.T., M.T.',
                'nip' => '198003032005011003',
                'jabatan' => 'Kepala Bagian Umum Program & Tata Usaha',
                'password' => Hash::make('password123'),
                'role' => 'pegawai',
            ]
        );

        User::updateOrCreate(
            ['email' => 'kabid.sda@pu.go.id'],
            [
                'name' => 'Siti Aminah, S.T., M.Eng.',
                'nip' => '198204042008022004',
                'jabatan' => 'Kepala Bidang SDA',
                'password' => Hash::make('password123'),
                'role' => 'pegawai',
            ]
        );

        // 4. Akun Petugas Teknis / Staf (Pengendali Risiko - Tim Pelaksana Banyak Orang)
        User::updateOrCreate(
            ['email' => 'rina.staf@pu.go.id'],
            [
                'name' => 'Rina Melati, S.Kom.',
                'nip' => '199505052020122005',
                'jabatan' => 'Staf Evaluasi & Pelaporan',
                'password' => Hash::make('password123'),
                'role' => 'pegawai',
            ]
        );

        User::updateOrCreate(
            ['email' => 'dwi.staf@pu.go.id'],
            [
                'name' => 'Dwi Prasetyo, S.T.',
                'nip' => '199308152019031006',
                'jabatan' => 'Staf Pengendali Risiko Teknis',
                'password' => Hash::make('password123'),
                'role' => 'pegawai',
            ]
        );

        User::updateOrCreate(
            ['email' => 'fauzi.staf@pu.go.id'],
            [
                'name' => 'Ahmad Fauzi, S.Sos.',
                'nip' => '199411202020121007',
                'jabatan' => 'Staf Tata Usaha & Kepatuhan',
                'password' => Hash::make('password123'),
                'role' => 'pegawai',
            ]
        );

        User::updateOrCreate(
            ['email' => 'dewi.staf@pu.go.id'],
            [
                'name' => 'Dewi Lestari, S.E.',
                'nip' => '199602142022032008',
                'jabatan' => 'Staf Keuangan & BMN',
                'password' => Hash::make('password123'),
                'role' => 'pegawai',
            ]
        );
    }
}
