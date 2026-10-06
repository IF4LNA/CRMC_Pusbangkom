<?php

namespace Database\Seeders;

use App\Models\Bidang;
use App\Models\SubMenu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Mengisi daftar bidang dan sub-bidang CRMC.
 *
 * Seeder ini idempoten: bidang dan sub-bidang yang sudah ada tidak akan
 * dibuat ulang atau diubah. Gunanya bukan menghapus data, melainkan melengkapi
 * daftar sub-bidang yang sebelumnya hanya ditulis manual di dalam
 * layouts/app.blade.php, sehingga kini bisa dikelola admin lewat dashboard.
 */
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
                'Pengadaan Jasa Konsultan',
                'Laporan Pengaduan Gratifikasi atau Suap',
                'Pengendalian Gratifikasi atau Suap',
                'Investigasi Laporan Pengaduan Gratifikasi atau Suap',
                'Perjalanan Dinas Dalam Negeri',
                'Pengajuan Uang Persediaan, GUP dan GUP Nihil',
                'Penyusunan Rencana Kerja Anggaran',
                'Pengelolaan Publikasi dan Informasi',
                'Pelaksanaan Pengajuan Cuti',
                'Pengelolaan BMN',
                'Penghapusan BMN',
                'Pengelolaan Pembayaran Uang Makan ASN',
                'Perhitungan Kehadiran Pegawai',
                'Pengajuan Daftar Supplier atau Kontrak',
                'Pengajuan LS Kontraktual',
                'Pengajuan LS Non Kontraktual',
                'Pengajuan LS Bendahara',
                'Survei Kepuasan Pelanggan Internal',
                'Pengelolaan Surat Masuk dan Surat Keluar',
                'Pinjaman KDO Roda 4 dan Roda 2',
                'Pengendalian Barang Persediaan',
                'Peminjaman Ruang Rapat, Gedung Dan Asrama',
                'Pengadaan Barang Jasa Secara Online',
                'Pemanfaatan BMN melalui Mekanisme Sewa dan Penatausahaan PNBP',
            ],
            'Bidang SDA' => [
                'Penyelenggaraan Bangkom SDA',
                'Kerjasama Pendidikan (MSS)',
                'Evaluasi Pasca Pelatihan SDA',
                'Penyiapan Materi E-Learning Bangkom SDA',
                'Penyusunan dan Pengembangan Kurikulum dan Modul Pembelajaran bid. SDA',
            ],
            'Bidang CKPS' => [
                // Catatan: CKPS sudah punya baris "Kerjska Pendidikan (MSS)"
                // (ejaan berbeda). Daftar ini sengaja tidak mengulanginya agar
                // tidak muncul dua sub-bidang untuk hal yang sama.
                'Penyusunan Kurikulum dan Modul',
                'Monitoring dan Evaluasi Pengembanngan Kompetensi CKPS',
                'Evaluasi Pasca Pelatihan CKPS',
                'Pembinaan Kerjasama Pelatihan',
                'Penyiapan Materi E-Learning',
                'Penyusunan Skema Sertifikasi',
            ],
        ];

        foreach ($strukturMenu as $namaBidang => $subMenus) {
            $bidang = Bidang::firstOrCreate(['nama_bidang' => $namaBidang]);

            // Nama sub-bidang dibandingkan tanpa memperhatikan huruf besar/
            // kecil dan tanda baca, supaya "Pengajuan LS Bendahara" tidak
            // tercipta dua kali hanya karena beda kapitalisasi.
            $sudahAda = $bidang->subMenus()
                ->get()
                ->map(fn (SubMenu $row) => self::kunci($row->nama_sub_menu))
                ->all();

            foreach ($subMenus as $namaSubMenu) {
                if (in_array(self::kunci($namaSubMenu), $sudahAda, true)) {
                    continue;
                }

                SubMenu::create([
                    'bidang_id' => $bidang->id,
                    'nama_sub_menu' => $namaSubMenu,
                ]);
            }
        }
    }

    /** Kunci pembanding nama sub-bidang yang tahan beda kapitalisasi & tanda baca. */
    private static function kunci(string $nama): string
    {
        return Str::lower((string) preg_replace('/[^a-z0-9]+/i', '', $nama));
    }
}