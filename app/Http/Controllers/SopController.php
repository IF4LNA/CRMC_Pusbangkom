<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\LampiranCrmc;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Halaman kumpulan SOP (route "/sop").
 *
 * SOP di CRMC sudah tersimpan sebagai Komponen 3 pada setiap sub-bidang,
 * jadi halaman ini tidak menyimpan data baru: ia hanya menghimpun seluruh
 * lampiran berkategori "sop" dari semua sub-bidang dan menyediakan filter
 * bidang, tahun, serta pencarian nama berkas.
 */
class SopController extends Controller
{
    /** Jumlah SOP per halaman. */
    private const PER_HALAMAN = 24;

    public function index(Request $request)
    {
        $daftarBidang = Bidang::orderBy('nama_bidang')->get();

        // Tahun yang benar-benar punya SOP, supaya dropdown tidak
        // menawarkan tahun kosong.
        $daftarTahun = LampiranCrmc::query()
            ->where('kategori_komponen', 'sop')
            ->join('dokumen_crmc', 'dokumen_crmc.id', '=', 'lampiran_crmc.dokumen_crmc_id')
            ->distinct()
            ->orderByDesc('dokumen_crmc.tahun_pelaksanaan')
            ->pluck('dokumen_crmc.tahun_pelaksanaan')
            ->all();

        $filter = [
            'bidang_id' => $request->integer('bidang') ?: null,
            'tahun' => $request->integer('tahun') ?: null,
            'q' => trim((string) $request->input('q', '')),
        ];

        $query = $this->querySop($filter);

        $daftarSop = $query->with(['dokumenCrmc.subMenu.bidang'])
            ->orderByDesc('dokumen_crmc.tahun_pelaksanaan')
            ->orderBy('nama_file')
            ->paginate(self::PER_HALAMAN)
            ->withQueryString();

        return view('sop.index', [
            'daftarSop' => $daftarSop,
            'daftarBidang' => $daftarBidang,
            'daftarTahun' => $daftarTahun,
            'filter' => $filter,
            'totalSop' => LampiranCrmc::where('kategori_komponen', 'sop')->count(),
        ]);
    }

    /**
     * Bangun query lampiran SOP berdasarkan filter yang aktif.
     *
     * Pencarian mencakup nama berkas, keterangan, dan nama sub-bidang
     * karena pengguna sering mengingat "bidang SDA" atau nama sub-bidang
     * alih-alih nama berkasnya.
     */
    private function querySop(array $filter): Builder
    {
        $namaBerkas = '%' . str_replace(['%', '_'], ['\%', '\_'], $filter['q']) . '%';

        return LampiranCrmc::query()
            ->where('kategori_komponen', 'sop')
            ->join('dokumen_crmc', 'dokumen_crmc.id', '=', 'lampiran_crmc.dokumen_crmc_id')
            ->join('sub_menu', 'sub_menu.id', '=', 'dokumen_crmc.sub_menu_id')
            ->when($filter['bidang_id'], fn (Builder $q, $bidang) => $q->where('sub_menu.bidang_id', $bidang))
            ->when($filter['tahun'], fn (Builder $q, $tahun) => $q->where('dokumen_crmc.tahun_pelaksanaan', $tahun))
            ->when($filter['q'] !== '', fn (Builder $q) => $q->where(function (Builder $dalam) use ($namaBerkas) {
                $dalam->where('lampiran_crmc.nama_file', 'like', $namaBerkas)
                    ->orWhere('lampiran_crmc.keterangan', 'like', $namaBerkas)
                    ->orWhere('sub_menu.nama_sub_menu', 'like', $namaBerkas);
            }))
            ->select('lampiran_crmc.*');
    }
}
