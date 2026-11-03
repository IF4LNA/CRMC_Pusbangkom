<?php

namespace App\Services;

use App\Models\DokumenCrmc;
use App\Models\LampiranCrmc;
use App\Models\SubMenu;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Pemindaian dan pemulihan record lampiran_crmc dari file yang masih
 * tersedia di private storage (disk "local").
 *
 * Tidak mengubah atau menghapus file fisik.
 */
class PemulihanDokumenService
{
    public const DISK = 'local';

    public const BASE = 'crmc';

    /**
     * Daftar kategori komponen yang dikenali aplikasi.
     *
     * @return array<string, string>
     */
    public static function kategoriDokumen(): array
    {
        return [
            'identitas_pegawai' => 'Identitas Pegawai',
            'risk_register' => 'Risk Register Spesifik Acuan',
            'sop' => 'Standar Operasional Prosedur (SOP)',
            'formulir_pengendalian' => 'Formulir Pengendalian / Daftar Periksa',
            'jadwal_pelaksanaan' => 'Jadwal Rencana Pelaksanaan',
            'bukti_pelaksanaan' => 'Bukti Pelaksanaan',
            'evaluasi' => 'Evaluasi & Rencana Perbaikan',
        ];
    }

    /**
     * Pindai seluruh file pada folder CRMC di private storage.
     *
     * @return array{items: array<int, array<string, mixed>>, stats: array<string, int>}
     */
    public function pindai(): array
    {
        $terdaftar = $this->jalurTerdaftar();

        $berkas = Storage::disk(self::DISK)->allFiles(self::BASE);

        $items = [];
        foreach ($berkas as $jalur) {
            $items[] = $this->bangunItem($jalur, $terdaftar);
        }

        $stats = [
            'total' => count($items),
            'terdaftar' => 0,
            'belum_terdaftar' => 0,
            'siap' => 0,
            'perlu_pemeriksaan' => 0,
            'tidak_dapat' => 0,
        ];

        foreach ($items as $item) {
            if ($item['status'] === 'already_registered') {
                $stats['terdaftar']++;
            } else {
                $stats['belum_terdaftar']++;
            }

            if ($item['status'] === 'ready') {
                $stats['siap']++;
            } elseif ($item['status'] === 'needs_review') {
                $stats['perlu_pemeriksaan']++;
            } elseif ($item['status'] === 'unrecoverable') {
                $stats['tidak_dapat']++;
            }
        }

        return ['items' => $items, 'stats' => $stats];
    }

    /**
     * Pulihkan satu file berdasarkan jalur relatifnya.
     *
     * @return array{success: bool, path: string, message: string}
     */
    public function pulihkanSatu(string $jalur): array
    {
        $jalur = trim($jalur);

        if (! $this->jalurValid($jalur)) {
            return ['success' => false, 'path' => $jalur, 'message' => 'Jalur file tidak valid.'];
        }

        if (! Storage::disk(self::DISK)->exists($jalur)) {
            return ['success' => false, 'path' => $jalur, 'message' => 'File fisik tidak ditemukan.'];
        }

        $terdaftar = $this->jalurTerdaftar();
        $item = $this->bangunItem($jalur, $terdaftar);

        if ($item['status'] === 'already_registered') {
            return ['success' => false, 'path' => $jalur, 'message' => 'File sudah terdaftar di database.'];
        }

        if ($item['status'] !== 'ready') {
            return ['success' => false, 'path' => $jalur, 'message' => $item['reason'] ?? 'File tidak dapat dipulihkan.'];
        }

        $dokumen = DokumenCrmc::find($item['dokumen_crmc_id']);
        if ($dokumen === null) {
            return ['success' => false, 'path' => $jalur, 'message' => 'Dokumen induk tidak ditemukan.'];
        }

        // Cegah duplikasi berdasarkan file_path.
        if (LampiranCrmc::where('file_path', $jalur)->exists()) {
            return ['success' => false, 'path' => $jalur, 'message' => 'File sudah terdaftar di database.'];
        }

        $ekstensi = strtolower(pathinfo($item['nama_file'], PATHINFO_EXTENSION));

        try {
            DB::transaction(function () use ($item, $jalur, $dokumen, $ekstensi) {
                LampiranCrmc::create([
                    'dokumen_crmc_id' => $dokumen->id,
                    'kategori_komponen' => $item['kategori'],
                    'nama_file' => $item['nama_file'],
                    'keterangan' => null,
                    'file_path' => $jalur,
                    'tipe_file' => $ekstensi !== '' ? $ekstensi : null,
                ]);
            });

            Log::info('Pemulihan dokumen CRMC', [
                'path' => $jalur,
                'dokumen_crmc_id' => $dokumen->id,
                'admin' => auth()->id(),
                'waktu' => now()->toDateTimeString(),
            ]);

            return ['success' => true, 'path' => $jalur, 'message' => 'Berhasil dipulihkan.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'path' => $jalur, 'message' => 'Gagal memulihkan: '.$e->getMessage()];
        }
    }

    /**
     * Validasi jalur relatif agar tetap di dalam folder CRMC.
     */
    public function jalurValid(string $jalur): bool
    {
        if ($jalur === '' || str_contains($jalur, '..') || str_starts_with($jalur, '/') || str_starts_with($jalur, '\\')) {
            return false;
        }

        $parts = explode('/', $jalur);
        if ($parts[0] !== self::BASE) {
            return false;
        }

        // Pastikan tidak ada segmen kosong.
        foreach ($parts as $bagian) {
            if ($bagian === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, bool>  $terdaftar
     * @return array<string, mixed>
     */
    private function bangunItem(string $jalur, array $terdaftar): array
    {
        $parts = explode('/', $jalur);
        $kategoriDokumen = self::kategoriDokumen();

        $tahun = $parts[1] ?? null;
        $subMenuIdRaw = $parts[2] ?? null;
        $kategori = $parts[3] ?? null;
        $namaFile = $parts[4] ?? null;

        // Jika ada lebih dari 5 segmen, struktur tidak dikenali.
        $strukturValid = count($parts) === 5 && $parts[0] === self::BASE;

        $subMenuId = (is_string($subMenuIdRaw) && ctype_digit($subMenuIdRaw)) ? (int) $subMenuIdRaw : null;
        $subMenu = $subMenuId !== null ? SubMenu::find($subMenuId) : null;

        $dokumen = null;
        if ($subMenu !== null && $tahun !== null && ctype_digit($tahun)) {
            $dokumen = DokumenCrmc::where('sub_menu_id', $subMenu->id)
                ->where('tahun_pelaksanaan', (int) $tahun)
                ->first();
        }

        $kategoriDikenal = is_string($kategori) && array_key_exists($kategori, $kategoriDokumen);
        $ekstensi = is_string($namaFile) ? strtolower(pathinfo($namaFile, PATHINFO_EXTENSION)) : '';

        $sudahTerdaftar = isset($terdaftar[$jalur]);

        $status = 'unrecoverable';
        $alasan = null;

        if ($sudahTerdaftar) {
            $status = 'already_registered';
        } elseif (! $strukturValid) {
            $status = 'unrecoverable';
            $alasan = 'Struktur folder tidak dikenali.';
        } elseif (! ctype_digit((string) $tahun) || strlen((string) $tahun) !== 4) {
            $status = 'unrecoverable';
            $alasan = 'Tahun tidak valid.';
        } elseif ($subMenu === null) {
            $status = 'needs_review';
            $alasan = 'Sub-bidang tidak ditemukan.';
        } elseif (! $kategoriDikenal) {
            $status = 'needs_review';
            $alasan = 'Kategori komponen tidak dikenali.';
        } elseif ($dokumen === null) {
            $status = 'needs_review';
            $alasan = 'Dokumen induk tidak ditemukan.';
        } elseif ($ekstensi === '') {
            $status = 'needs_review';
            $alasan = 'Ekstensi file tidak dikenali.';
        } else {
            $status = 'ready';
        }

        return [
            'path' => $jalur,
            'tahun' => ctype_digit((string) $tahun) ? (int) $tahun : null,
            'sub_menu_id' => $subMenuId,
            'sub_menu' => $subMenu,
            'bidang' => $subMenu?->bidang?->nama_bidang,
            'kategori' => $kategori,
            'kategori_judul' => $kategoriDikenal ? $kategoriDokumen[$kategori] : 'Tidak dikenali',
            'nama_file' => $namaFile ?? basename($jalur),
            'ekstensi' => $ekstensi,
            'status' => $status,
            'reason' => $alasan,
            'dokumen_crmc_id' => $dokumen?->id,
            'sudah_terdaftar' => $sudahTerdaftar,
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function jalurTerdaftar(): array
    {
        $hasil = [];

        foreach (LampiranCrmc::all() as $lampiran) {
            $path = $lampiran->storage_path;
            if (is_string($path) && $path !== '') {
                $hasil[$path] = true;
            }
            if (is_string($lampiran->file_path) && $lampiran->file_path !== '') {
                $hasil[$lampiran->file_path] = true;
            }
        }

        return $hasil;
    }
}
