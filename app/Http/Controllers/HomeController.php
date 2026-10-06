<?php

namespace App\Http\Controllers;

use App\Models\GaleriSarana;
use App\Models\GambarStruktur;
use App\Models\LampiranCrmc;
use App\Models\SubMenu;
use App\Support\PenyimpananGambar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Halaman Beranda: penjelasan CRMC, gambar struktur organisasi, galeri
 * sarana dan prasarana, serta peta lokasi. Semua isi halaman tersebut
 * dikelola langsung oleh admin dari halaman ini juga.
 */
class HomeController extends Controller
{
    /** Koordinat kantor PUSBANGKOM, dalam derajat desimal. */
    private const LATITUDE = -6.899444;
    private const LONGITUDE = 107.662917;

    /**
     * Bentuk-degree (DMS) dari koordinat di atas, sesuai format yang
     * diminta: 6 53 58.0" S, 107 39 46.5" E.
     *
     * Simbol derajat ditulis sebagai entitas HTML supaya file sumber
     * ini tetap ASCII murni dan tidak bergantung pada encoding terminal.
     */
    private const KOORDINAT_DMS = '6&deg;53&apos;58.0&quot;S 107&deg;39&apos;46.5&quot;E';

    /**
     * Halaman beranda publik.
     */
    public function index()
    {
        $galeri = GaleriSarana::orderBy('urutan')->orderBy('id')->get();

        return view('home.index', [
            'galeri' => $galeri,
            'latitude' => self::LATITUDE,
            'longitude' => self::LONGITUDE,
            'koordinatDms' => self::KOORDINAT_DMS,
            'tautanPeta' => $this->tautanGoogleMaps(self::LATITUDE, self::LONGITUDE),
            // Bagan struktur organisasi kini berupa satu gambar yang diunggah
            // admin. Bisa null kalau belum ada yang diunggah.
            'gambarStruktur' => GambarStruktur::aktif(),
            // Dipakai angka ringkas pada banner, dihitung dari data nyata
            // supaya tidak perlu diperbarui manual.
            'jumlahSubBidang' => SubMenu::count(),
            'jumlahSop' => LampiranCrmc::where('kategori_komponen', 'sop')->count(),
        ]);
    }

    // ========================
    // ADMIN: Gambar Struktur Organisasi
    // ========================

    /**
     * Unggah (atau ganti) gambar bagan struktur organisasi.
     *
     * Hanya satu gambar yang aktif, jadi gambar sebelumnya langsung
     * dihapus dari database sekaligus dari disk.
     */
    public function simpanGambarStruktur(Request $request)
    {
        $this->wajibAdmin();

        $request->validate([
            'gambar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'keterangan' => 'nullable|string|max:300',
        ], [
            'gambar.required' => 'Pilih file gambar terlebih dahulu.',
            'gambar.image' => 'File yang dipilih bukan gambar.',
            'gambar.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
            'gambar.max' => 'Ukuran gambar maksimal 4 MB.',
            'keterangan.max' => 'Keterangan maksimal 300 karakter.',
        ]);

        $path = PenyimpananGambar::simpan($request->file('gambar'), 'struktur-organisasi');

        try {
            $sebelumnya = GambarStruktur::aktif();

            GambarStruktur::create([
                'path' => $path,
                'keterangan' => $request->input('keterangan') ?: null,
            ]);

            // Baris dan file lama dibersihkan setelah yang baru tersimpan,
            // supaya kegagalan di sini tidak meninggalkan halaman tanpa bagan.
            if ($sebelumnya) {
                $sebelumnya->delete();
                Storage::disk('public')->delete($sebelumnya->path);
            }
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }

        return back()->with('success', 'Gambar struktur organisasi berhasil diperbarui.');
    }

    public function hapusGambarStruktur()
    {
        $this->wajibAdmin();

        $gambar = GambarStruktur::aktif();

        if ($gambar) {
            $gambar->delete();
            Storage::disk('public')->delete($gambar->path);
        }

        return back()->with('success', 'Gambar struktur organisasi berhasil dihapus.');
    }

    // ========================
    // ADMIN: Galeri Sarana dan Prasarana
    // ========================

    public function simpanGaleri(Request $request)
    {
        $this->wajibAdmin();

        $data = $request->validate(
            $this->aturanGaleri(),
            $this->pesanGaleri()
        );

        // Simpan gambar dulu, baru simpan baris database. Kalau simpan
        // baris gagal, file yang sudah terlanjur tersimpan ikut dihapus
        // supaya tidak meninggalkan file sampah.
        $path = PenyimpananGambar::simpan($request->file('gambar'), 'galeri-sarana');

        try {
            GaleriSarana::create([
                'path' => $path,
                'judul' => $data['judul'],
                'keterangan' => $data['keterangan'] ?? null,
                'urutan' => (int) GaleriSarana::max('urutan') + 1,
            ]);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }

        return back()->with('success', 'Gambar sarana dan prasarana berhasil ditambahkan.');
    }

    public function ubahGaleri(Request $request, $id)
    {
        $this->wajibAdmin();

        $galeri = GaleriSarana::findOrFail($id);
        $data = $request->validate(
            $this->aturanGaleri(false),
            $this->pesanGaleri()
        );

        $data = ['judul' => $data['judul'], 'keterangan' => $data['keterangan'] ?? null];

        if ($request->hasFile('gambar')) {
            $pathBaru = PenyimpananGambar::simpan($request->file('gambar'), 'galeri-sarana');
            $pathLama = $galeri->path;

            $galeri->update([...$data, 'path' => $pathBaru]);

            // File lama dihapus setelah baris database berhasil diperbarui.
            if (!empty($pathLama) && $pathLama !== $pathBaru) {
                Storage::disk('public')->delete($pathLama);
            }
        } else {
            $galeri->update($data);
        }

        return back()->with('success', 'Gambar sarana dan prasarana berhasil diperbarui.');
    }

    public function hapusGaleri($id)
    {
        $this->wajibAdmin();

        $galeri = GaleriSarana::findOrFail($id);
        $galeri->hapusFile();
        $galeri->delete();

        return back()->with('success', 'Gambar sarana dan prasarana berhasil dihapus.');
    }

    public function urutkanGaleri(Request $request)
    {
        $this->wajibAdmin();

        $ids = $request->input('urutan', []);

        DB::transaction(function () use ($ids) {
            foreach (array_values(array_filter((array) $ids)) as $posisi => $id) {
                GaleriSarana::whereKey($id)->update(['urutan' => $posisi + 1]);
            }
        });

        return back()->with('success', 'Urutan galeri diperbarui.');
    }

    // ========================
    // Helper
    // ========================

    /**
     * Aturan validasi unggah galeri.
     *
     * Saat menambah gambar baru, `gambar` wajib ada. Saat menyunting,
     * gambar boleh dikosongkan: bila tidak ada file baru, gambar lama
     * tetap dipakai dan hanya judul serta keterangan yang berubah.
     */
    private function aturanGaleri(bool $gambarWajib = true): array
    {
        return [
            'judul' => 'required|string|max:150',
            'keterangan' => 'nullable|string|max:500',
            'gambar' => array_filter([
                $gambarWajib ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ]),
        ];
    }

    private function pesanGaleri(): array
    {
        return [
            'judul.required' => 'Judul gambar wajib diisi.',
            'judul.max' => 'Judul maksimal 150 karakter.',
            'keterangan.max' => 'Keterangan maksimal 500 karakter.',
            'gambar.required' => 'Pilih file gambar terlebih dahulu.',
            'gambar.image' => 'File yang dipilih bukan gambar.',
            'gambar.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
            'gambar.max' => 'Ukuran gambar maksimal 4 MB.',
        ];
    }

    private function bolehKelola(): bool
    {
        return Auth::check() && Auth::user()->isAdmin();
    }

    private function wajibAdmin(): void
    {
        if (!$this->bolehKelola()) {
            abort(403, 'Akses Ditolak: Hanya Administrator yang dapat mengubah isi halaman Beranda.');
        }
    }

    /** Tautan buka-lokasi di Google Maps (dipakai di tombol pada panel peta). */
    private function tautanGoogleMaps(float $lat, float $lng): string
    {
        return 'https://www.google.com/maps/search/?api=1&query=' . $lat . ',' . $lng;
    }
}