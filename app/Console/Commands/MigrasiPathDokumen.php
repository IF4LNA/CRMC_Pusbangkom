<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Menormalkan nilai path dokumen lama yang tersimpan sebagai URL absolut
 * (mis. "http://172.10.10.168:8000/storage/crmc/2026/1/sop/file.pdf")
 * menjadi jalur relatif terhadap disk "public"
 * (mis. "crmc/2026/1/sop/file.pdf").
 *
 * Sebelumnya CrmcController menyimpan hasil Storage::disk('public')->url()
 * ke kolom, sehingga host (IP lokal / domain) ikut terekam. Setelah
 * aplikasi dipindah ke server lain, URL itu menunjuk alamat yang tidak
 * tersedia. Jalur relatif tidak menyimpan host dan selalu dibentuk ulang
 * dari konfigurasi aktif (lihat App\Support\TautanBerkas).
 *
 * Perintah ini HANYA mengubah kolom di database. File fisik tidak
 * dipindah, diganti nama, atau dihapus. Secara bawaan perintah berjalan
 * sebagai laporan (dry-run); tambahkan --apply untuk menulis perubahan.
 */
class MigrasiPathDokumen extends Command
{
    /**
     * Daftar kolom yang dinormalkan: [tabel, kolom, label].
     */
    private const TARGET = [
        ['lampiran_crmc', 'file_path', 'Lampiran CRMC'],
        ['dokumen_dasar_hukum', 'path', 'Dasar Hukum'],
    ];

    protected $signature = 'dokumen:migrasi-path
        {--apply : Tulis perubahan ke database (tanpa opsi ini hanya laporan/dry-run)}
        {--force : Lewati konfirmasi saat --apply dijalankan}';

    protected $description = 'Ubah path dokumen lama yang berupa URL absolut menjadi jalur relatif disk "public" (default: dry-run)';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $this->newLine();
        $this->line('  <fg=yellow>PENTING:</> jalankan backup database sebelum memakai <fg=green>--apply</>.');
        $this->line('  Perintah ini hanya mengubah kolom di database; file fisik tidak disentuh.');
        $this->newLine();

        if ($apply && ! $this->option('force') && ! $this->confirm('Lanjutkan menulis perubahan ke database?', false)) {
            $this->warn('Dibatalkan. Tidak ada perubahan yang ditulis.');

            return self::SUCCESS;
        }

        $totalUbah = 0;
        $totalAnomali = 0;
        $totalHilang = 0;

        foreach (self::TARGET as [$tabel, $kolom, $label]) {
            if (! DB::getSchemaBuilder()->hasTable($tabel) || ! DB::getSchemaBuilder()->hasColumn($tabel, $kolom)) {
                $this->warn("[$label] Tabel/kolom {$tabel}.{$kolom} tidak ditemukan, dilewati.");

                continue;
            }

            $this->info("== {$label} ({$tabel}.{$kolom}) ==");

            $barisDiubah = 0;
            $barisDilewati = 0;

            DB::table($tabel)
                ->select('id', $kolom)
                ->orderBy('id')
                ->chunkById(200, function ($baris) use ($tabel, $kolom, $apply, &$barisDiubah, &$barisDilewati, &$totalAnomali, &$totalHilang) {
                    foreach ($baris as $row) {
                        $nilai = $row->{$kolom};

                        // Nilai kosong: bukan urusan perintah ini.
                        if ($nilai === null || trim((string) $nilai) === '') {
                            $barisDilewati++;

                            continue;
                        }

                        $relatif = $this->kePathRelatif((string) $nilai);

                        // Sudah berupa jalur relatif yang benar (mis.
                        // "/storage/crmc/...") -> tidak perlu diubah.
                        if ($relatif === null) {
                            $barisDilewati++;

                            continue;
                        }

                        // URL absolut tetapi tidak punya segmen /storage/
                        // (mis. tautan Drive atau URL situs lain) -> jangan disentuh.
                        if ($relatif === false) {
                            $totalAnomali++;
                            $this->warn("  [anomali] {$tabel}#{$row->id}: \"{$nilai}\" tidak mengandung /storage/ — dibiarkan, periksa manual.");

                            continue;
                        }

                        $adaBerkas = Storage::disk('public')->exists($relatif);

                        if (! $adaBerkas) {
                            $totalHilang++;
                            $this->warn("  [berkas hilang] {$tabel}#{$row->id}: {$relatif} tidak ada di storage/app/public — path tetap dinormalkan, periksa manual.");
                        }

                        $this->line("  {$tabel}#{$row->id}: {$nilai}");
                        $this->line("      -> {$relatif}");

                        if ($apply) {
                            DB::table($tabel)->where('id', $row->id)->update([$kolom => $relatif]);
                        }

                        $barisDiubah++;
                    }
                });

            $totalUbah += $barisDiubah;

            $this->line("  diringkas: {$barisDiubah} perlu dinormalkan, {$barisDilewati} sudah relatif/kosong.");
            $this->newLine();
        }

        $this->line("Total: {$totalUbah} baris ".($apply ? 'DIUBAH' : 'PERLU DIUBAH (dry-run)').", {$totalAnomali} anomali, {$totalHilang} berkas hilang.");

        if (! $apply && $totalUbah > 0) {
            $this->newLine();
            $this->comment('Jalankan ulang dengan --apply untuk menulis perubahan.');
        }

        return self::SUCCESS;
    }

    /**
     * Kembalikan jalur relatif (string) bila nilai berupa URL absolut yang
     * perlu dinormalkan, null bila nilainya sudah relatif, atau false bila
     * URL absolut tersebut tidak punya segmen /storage/ sehingga dianggap
     * anomali dan tidak boleh disentuh.
     */
    private function kePathRelatif(string $nilai): string|false|null
    {
        $nilai = trim($nilai);

        // Bukan URL absolut (tidak ada skema dan tidak diawali "//")
        // -> anggap sudah jalur relatif.
        if (preg_match('#^(https?:)?//#i', $nilai) !== 1) {
            // Jalur yang sudah berawalan "/storage/..." (format simpanan
            // sekarang) tidak perlu diubah sama sekali.
            $prefixDiskUntukJalur = parse_url(Storage::disk('public')->url(''), PHP_URL_PATH) ?: '/storage';

            if ($prefixDiskUntukJalur !== '/' && str_starts_with($nilai, $prefixDiskUntukJalur.'/')) {
                return null;
            }

            // Selain itu, rapikan sisa awalan "public/" pada data lama.
            if (str_starts_with($nilai, 'public/')) {
                return substr($nilai, strlen('public/'));
            }

            return null;
        }

        $jalur = parse_url($nilai, PHP_URL_PATH);

        if (! is_string($jalur) || $jalur === '') {
            return false;
        }

        // Buang prefix path milik disk public, mis. "/storage".
        $prefixDisk = parse_url(Storage::disk('public')->url(''), PHP_URL_PATH) ?: '/storage';

        if ($prefixDisk !== '/' && str_starts_with($jalur, $prefixDisk.'/')) {
            $jalur = substr($jalur, strlen($prefixDisk));
        } elseif (! str_contains($jalur, '/storage/')) {
            return false;
        } else {
            $jalur = substr($jalur, strpos($jalur, '/storage/') + strlen('/storage'));
        }

        $jalur = ltrim($jalur, '/');

        // Dukungan data lama berprefiks "public/".
        if (str_starts_with($jalur, 'public/')) {
            $jalur = substr($jalur, strlen('public/'));
        }

        return $jalur !== '' ? $jalur : false;
    }
}
