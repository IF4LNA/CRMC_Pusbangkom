<?php

namespace App\Console\Commands;

use App\Models\LampiranCrmc;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Pindahkan berkas lampiran CRMC dari disk publik ke disk privat.
 *
 * Unggahan baru sudah disimpan di disk "local" (storage/app/private) dan
 * hanya bisa dibaca lewat route ber-otorisasi `crmc.lampiran.berkas`.
 * Berkas lama masih berada di disk "public", sehingga URL "/storage/..."
 * dapat dibuka langsung tanpa pemeriksaan bidang. Perintah ini menutup celah
 * tersebut tanpa mengubah isi database: jalur relatif yang sama dipakai di
 * kedua disk, jadi `LampiranCrmc::diskBerkas()` otomatis menemukan berkas di
 * disk privat setelah dipindahkan.
 *
 * Secara bawaan perintah berjalan sebagai laporan (dry-run). Tambahkan
 * --apply untuk benar-benar memindahkan berkas.
 */
class PindahkanDokumenKePrivat extends Command
{
    protected $signature = 'dokumen:pindahkan-ke-privat
        {--apply : Pindahkan berkas (tanpa opsi ini hanya laporan/dry-run)}
        {--force : Lewati konfirmasi saat --apply dijalankan}';

    protected $description = 'Pindahkan lampiran CRMC dari disk "public" ke disk privat "local" (default: dry-run)';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $this->newLine();
        $this->line('  <fg=yellow>PENTING:</> jalankan backup database dan folder storage sebelum memakai <fg=green>--apply</>.');
        $this->line('  Setelah dipindahkan, berkas hanya dapat dibuka lewat route ber-otorisasi.');
        $this->newLine();

        if ($apply && ! $this->option('force') && ! $this->confirm('Lanjutkan memindahkan berkas ke disk privat?', false)) {
            $this->warn('Dibatalkan. Tidak ada berkas yang dipindahkan.');

            return self::SUCCESS;
        }

        $dipindah = 0;
        $sudahPrivat = 0;
        $hilang = 0;
        $gagal = 0;

        LampiranCrmc::query()
            ->select('id', 'nama_file', 'file_path')
            ->orderBy('id')
            ->chunkById(100, function ($daftar) use ($apply, &$dipindah, &$sudahPrivat, &$hilang, &$gagal) {
                foreach ($daftar as $lampiran) {
                    $path = $lampiran->storage_path;

                    if ($path === null) {
                        continue;
                    }

                    $publik = Storage::disk('public');
                    $privat = Storage::disk('local');

                    if ($privat->exists($path)) {
                        $sudahPrivat++;

                        continue;
                    }

                    if (! $publik->exists($path)) {
                        $hilang++;
                        $this->warn("  [hilang] lampiran#{$lampiran->id} ({$lampiran->nama_file}): {$path} tidak ada di disk publik.");

                        continue;
                    }

                    if (! $apply) {
                        $this->line("  lampiran#{$lampiran->id}: {$path}");
                        $dipindah++;

                        continue;
                    }

                    try {
                        $aliran = $publik->readStream($path);

                        if ($aliran === null || ! $privat->writeStream($path, $aliran)) {
                            throw new \RuntimeException('Gagal menulis ke disk privat.');
                        }

                        if (is_resource($aliran)) {
                            fclose($aliran);
                        }

                        // Berkas lama baru dihapus setelah salinan privat
                        // benar-benar tertulis, supaya kegagalan di tengah
                        // tidak menghilangkan satu-satunya salinan.
                        $publik->delete($path);

                        $dipindah++;
                        $this->line("  dipindah lampiran#{$lampiran->id}: {$path}");
                    } catch (\Throwable $e) {
                        $gagal++;
                        $this->error("  [gagal] lampiran#{$lampiran->id}: {$e->getMessage()}");
                    }
                }
            });

        $this->newLine();
        $this->line("Total: {$dipindah} berkas ".($apply ? 'DIPINDAH' : 'PERLU DIPINDAH (dry-run)')
            .", {$sudahPrivat} sudah privat, {$hilang} hilang, {$gagal} gagal.");

        if (! $apply && $dipindah > 0) {
            $this->comment('Jalankan ulang dengan --apply untuk memindahkan berkas.');
        }

        if ($hilang > 0 || $gagal > 0) {
            $this->warn('Periksa baris yang ditandai [hilang]/[gagal] secara manual.');
        }

        return self::SUCCESS;
    }
}
