<?php

namespace App\Http\Controllers;

use App\Services\PemulihanDokumenService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class PemulihanDokumenController extends Controller
{
    public function __construct(private PemulihanDokumenService $layanan) {}

    private function wajibAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator.');
        }
    }

    public function index(Request $request)
    {
        $this->wajibAdmin();

        $hasilPindai = $this->layanan->pindai();
        $items = collect($hasilPindai['items']);

        $kataKunci = trim((string) $request->input('q'));
        $tahun = $request->input('tahun');
        $kategori = (string) $request->input('kategori');

        if ($kataKunci !== '') {
            $items = $items->filter(function ($item) use ($kataKunci) {
                return stripos($item['nama_file'], $kataKunci) !== false;
            });
        }

        if ($tahun !== null && $tahun !== '' && ctype_digit((string) $tahun)) {
            $tahunInt = (int) $tahun;
            $items = $items->filter(fn ($item) => $item['tahun'] === $tahunInt);
        }

        if ($kategori !== '') {
            $items = $items->filter(fn ($item) => $item['kategori'] === $kategori);
        }

        // Daftar halaman ini fokus pada file yang belum terdaftar.
        $items = $items->reject(fn ($item) => $item['status'] === 'already_registered')->values();

        $perPage = 20;
        $page = max(1, (int) $request->input('page', 1));
        $paginator = new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $daftarTahun = $items->pluck('tahun')->filter()->unique()->sort()->values();
        // Tahun dari seluruh hasil pindai untuk filter (bukan hanya halaman).
        $semuaTahun = collect($hasilPindai['items'])->pluck('tahun')->filter()->unique()->sort()->values();

        return view('admin.pemulihan-dokumen', [
            'items' => $paginator,
            'stats' => $hasilPindai['stats'],
            'kataKunci' => $kataKunci,
            'tahun' => $tahun,
            'kategori' => $kategori,
            'daftarTahun' => $semuaTahun,
            'kategoriDokumen' => PemulihanDokumenService::kategoriDokumen(),
        ]);
    }

    public function pulihkan(Request $request)
    {
        $this->wajibAdmin();

        $request->validate([
            'paths' => 'required|array|min:1',
            'paths.*' => 'required|string',
        ]);

        $paths = $request->input('paths', []);

        $berhasil = 0;
        $gagal = 0;
        $pesanGagal = [];

        foreach ($paths as $path) {
            $hasil = $this->layanan->pulihkanSatu((string) $path);
            if ($hasil['success']) {
                $berhasil++;
            } else {
                $gagal++;
                $pesanGagal[] = basename((string) $path).': '.$hasil['message'];
            }
        }

        $redirect = redirect()->route('admin.pemulihan.index');

        if ($berhasil > 0) {
            $redirect->with('success', $berhasil.' file berhasil dipulihkan.');
        }

        if ($gagal > 0) {
            $redirect->with('error', $gagal.' file gagal dipulihkan. '.implode(' | ', array_slice($pesanGagal, 0, 5)));
        }

        return $redirect;
    }
}
