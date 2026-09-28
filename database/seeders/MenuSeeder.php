<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\SubMenu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run()
    {
        $strukturMenu = [
            'Bagian Umum Program & Tata Usaha' => [
                'Manajemen Risiko',
                'Pengadaan Barang Jasa Konstruksi dan Non Konstruksi',
                'Implementasi SAKIP',
                'Pengelolaan Arsip',
                'Laporan Keuangan',
                // Anda dapat menambahkan 24 item lainnya di sini...
            ],
            'Bidang SDA' => [
                'Penyelenggaraan Bangkom SDA',
                'Kerjasama Pendidikan (MSS)',
                'Evaluasi Pasca Pelatihan SDA',
                'Penyiapan Materi E-Learning Bangkom SDA',
                'Penyusunan dan Pengembangan Kurikulum dan Modul Pembelajaran bid. SDA',
            ],
            'Bidang CKPS' => [
                'Kerjasama Pendidikan (MSS)',
                'Penyusunan Kurikulum dan Modul',
                'Monitoring dan Evaluasi Pengembanngan Kompetensi CKPS',
                'Evaluasi Pasca Pelatihan CKPS',
                'Pembinaan Kerjasama Pelatihan',
                'Penyiapan Materi E-Learning',
                'Penyusunan Skema Sertifikasi',
            ],
        ];

        foreach ($strukturMenu as $namaBidang => $subMenus) {
            // Buat record Bidang
            $bidang = Bidang::create(['nama_bidang' => $namaBidang]);

            // Looping dan buat record Sub Menu yang berelasi dengan Bidang
            foreach ($subMenus as $namaSubMenu) {
                SubMenu::create([
                    'bidang_id' => $bidang->id,
                    'nama_sub_menu' => $namaSubMenu,
                ]);
            }
        }
    }
}
