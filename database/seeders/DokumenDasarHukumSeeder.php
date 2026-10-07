<?php

namespace Database\Seeders;

use App\Models\DokumenDasarHukum;
use Illuminate\Database\Seeder;

/**
 * Daftar landasan regulasi CRMC.
 *
 * Barisnya dibuat tanpa berkas: regulation itself sudah pasti, sedangkan
 * scan dokumen diunggah admin lewat tab "Dasar Hukum". updateOrCreate
 * dipakai supaya seeder aman dijalankan berulang kali, dan supaya
 * berkas yang sudah diunggah admin tidak ikut terhapus saat seeding
 * diulang.
 */
class DokumenDasarHukumSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->daftarRegulasi() as $urutan => $regulasi) {
            DokumenDasarHukum::updateOrCreate(
                ['kode' => $regulasi['kode']],
                [
                    'jenis' => $regulasi['jenis'],
                    'penerbit' => $regulasi['penerbit'],
                    'nomor' => $regulasi['nomor'],
                    'tentang' => $regulasi['tentang'],
                    'urutan' => $urutan + 1,
                ]
            );
        }
    }

    /**
     * Naskah yang berlaku di lingkungan PUSBANGKOM.
     *
     * `@return array<int, array{kode:string, jenis:string, penerbit:string, nomor:string, tentang:string}>
     */
    private function daftarRegulasi(): array
    {
        return [
            [
                'kode' => 'se-12-2024',
                'jenis' => 'Surat Edaran',
                'penerbit' => 'Menteri Pekerjaan Umum dan Perumahan Rakyat',
                'nomor' => '12/SE/M/2024',
                'tentang' => 'Pedoman Penerapan Manajemen Risiko di Kementerian Pekerjaan Umum dan Perumahan Rakyat',
            ],
            [
                'kode' => 'se-03-2025',
                'jenis' => 'Surat Edaran',
                'penerbit' => 'Kepala Badan Pengembangan Sumber Daya Manusia',
                'nomor' => '03/SE/Km/2025',
                'tentang' => 'Pedoman Pelaksanaan Pengendalian Risiko melalui Continuous Monitoring on Risk Control (CRMC) di Badan Pengembangan Sumber Daya Manusia',
            ],
            [
                'kode' => 'kep-07-2026',
                'jenis' => 'Keputusan',
                'penerbit' => 'Kepala Pusat Pengembangan Kompetensi Sumber Daya Air, Cipta Karya dan Prasarana Strategis',
                'nomor' => '07/KPTS/Ma/2026',
                'tentang' => 'Penetapan Tim Manajemen Risiko (MR) dan Pengendalian Internal atas Pelaporan Keuangan (PIPK) di Lingkungan Pusat Pengembangan Kompetensi Sumber Daya Air, Cipta Karya dan Prasarana Strategis TA 2026',
            ],
            [
                'kode' => 'kep-08-2026',
                'jenis' => 'Keputusan',
                'penerbit' => 'Kepala Pusat Pengembangan Kompetensi Sumber Daya Air, Cipta Karya dan Prasarana Strategis',
                'nomor' => '08/KPTS/Ma/2026',
                'tentang' => 'Penetapan Tim Satuan Tugas Pengendalian Gratifikasi di Lingkungan Pusat Pengembangan Kompetensi Sumber Daya Air, Cipta Karya dan Prasarana Strategis',
            ],
        ];
    }
}
