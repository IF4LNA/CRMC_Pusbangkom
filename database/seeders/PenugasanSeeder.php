<?php

namespace Database\Seeders;

use App\Models\PenugasanCrmc;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Contoh penugasan PIC untuk sub-bidang "Manajemen Risiko".
 *
 * Seeder ini tidak lagi mengandalkan akun demo yang tidak pernah dibuat
 * oleh UserSeeder. Penugasan diambil dari akun yang benar-benar ada:
 * admin pertama sebagai Pemilik Risiko, admin berikutnya (atau pegawai
 * pertama) sebagai Pengendali Mutu, dan maksimal tiga pegawai sebagai
 * Pengendali Risiko.
 *
 * Penugasan ditulis untuk tahun berjalan dan memakai firstOrCreate,
 * sehingga aman dijalankan berulang kali tanpa menggandakan baris atau
 * menghapus penugasan tahun lain.
 */
class PenugasanSeeder extends Seeder
{
    public function run(): void
    {
        $subMenu = SubMenu::where('nama_sub_menu', 'Manajemen Risiko')->first();

        if (! $subMenu) {
            $this->command?->warn('Sub-bidang "Manajemen Risiko" belum ada; PenugasanSeeder dilewati.');

            return;
        }

        $tahun = (int) date('Y');

        $admin = User::where('role', 'admin')->orderBy('id')->get();
        $pemilikRisiko = $admin->get(0);

        if (! $pemilikRisiko) {
            $this->command?->warn('Belum ada akun admin; PenugasanSeeder dilewati.');

            return;
        }

        $pengendaliMutu = $admin->get(1) ?? User::where('role', 'pegawai')->orderBy('id')->first();
        $pengendaliRisiko = User::where('role', 'pegawai')->orderBy('id')->take(3)->get();

        $this->tugaskan($subMenu, $pemilikRisiko, 'pemilik_risiko', $tahun);

        if ($pengendaliMutu) {
            $this->tugaskan($subMenu, $pengendaliMutu, 'pengendali_mutu', $tahun);
        }

        foreach ($pengendaliRisiko as $pegawai) {
            $this->tugaskan($subMenu, $pegawai, 'pengendali_risiko', $tahun);
        }
    }

    private function tugaskan(SubMenu $subMenu, User $user, string $peran, int $tahun): void
    {
        PenugasanCrmc::firstOrCreate([
            'sub_menu_id' => $subMenu->id,
            'user_id' => $user->id,
            'peran' => $peran,
            'tahun_pelaksanaan' => $tahun,
        ]);
    }
}
