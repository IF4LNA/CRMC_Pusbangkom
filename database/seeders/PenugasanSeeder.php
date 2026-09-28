<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SubMenu;
use App\Models\PenugasanCrmc;
use Illuminate\Database\Seeder;

class PenugasanSeeder extends Seeder
{
    public function run()
    {
        // Ambil data user berdasarkan email (dari UserSeeder)
        $kapus = User::where('email', 'kapus@pu.go.id')->first();
        $kabagTU = User::where('email', 'kabag.tu@pu.go.id')->first();
        $stafRina = User::where('email', 'rina.staf@pu.go.id')->first();
        $stafDwi = User::where('email', 'dwi.staf@pu.go.id')->first();
        $stafFauzi = User::where('email', 'fauzi.staf@pu.go.id')->first();

        // Ambil sub-menu Manajemen Risiko
        $subMenuManajemenRisiko = SubMenu::where('nama_sub_menu', 'Manajemen Risiko')->first();

        if ($subMenuManajemenRisiko) {
            // Bersihkan data lama jika ada
            PenugasanCrmc::where('sub_menu_id', $subMenuManajemenRisiko->id)->delete();

            // 1. Assign Pemilik Risiko (1 Orang - Kepala Pusat)
            if ($kapus) {
                PenugasanCrmc::create([
                    'sub_menu_id' => $subMenuManajemenRisiko->id,
                    'user_id' => $kapus->id,
                    'peran' => 'pemilik_risiko',
                ]);
            }

            // 2. Assign Pengendali Mutu (1 Orang - Kepala Bagian TU)
            if ($kabagTU) {
                PenugasanCrmc::create([
                    'sub_menu_id' => $subMenuManajemenRisiko->id,
                    'user_id' => $kabagTU->id,
                    'peran' => 'pengendali_mutu',
                ]);
            }

            // 3. Assign Pengendali Risiko (Bisa Banyak Orang - Tim Pelaksana Staf)
            $stafPengendali = array_filter([$stafRina, $stafDwi, $stafFauzi]);
            foreach ($stafPengendali as $staf) {
                PenugasanCrmc::create([
                    'sub_menu_id' => $subMenuManajemenRisiko->id,
                    'user_id' => $staf->id,
                    'peran' => 'pengendali_risiko',
                ]);
            }
        }
    }
}
