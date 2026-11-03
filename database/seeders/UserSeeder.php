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

       
    }
}
