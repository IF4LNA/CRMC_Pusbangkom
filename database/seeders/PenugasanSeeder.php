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

        // Ambil salah satu sub-menu sebagai contoh (Manajemen Risiko)
        $subMenuManajemenRisiko = SubMenu::where('nama_sub_menu', 'Manajemen Risiko')->first();

        if ($subMenuManajemenRisiko) {
            // 1. Assign Pemilik Risiko (Kepala Pusat)
            PenugasanCrmc::create([
                'sub_menu_id' => $subMenuManajemenRisiko->id,
                'user_id' => $kapus->id,
                'peran' => 'pemilik_risiko',
            ]);

            // 2. Assign Pengendali Mutu (Kepala Bagian TU)
            PenugasanCrmc::create([
                'sub_menu_id' => $subMenuManajemenRisiko->id,
                'user_id' => $kabagTU->id,
                'peran' => 'pengendali_mutu',
            ]);

            // 3. Assign Pengendali Risiko (Staf Teknis)
            PenugasanCrmc::create([
                'sub_menu_id' => $subMenuManajemenRisiko->id,
                'user_id' => $stafRina->id,
                'peran' => 'pengendali_risiko',
            ]);
        }
    }
}
