<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\GaleriSarana;
use App\Models\StrukturOrganisasi;
use App\Models\User;
use App\Support\PenyimpananGambar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Halaman Beranda: penjelasan CRMC, struktur organisasi, galeri sarana dan
 * prasarana, serta peta lokasi. Semua isi halaman tersebut dikelola langsung
 * oleh admin dari halaman ini juga.
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
        $struktur = StrukturOrganisasi::with(['user', 'bidang'])
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();

        // Dipisahkan per peran supaya view tidak perlu memfilter ulang.
        $pemilikRisiko = $struktur->where('peran', 'pemilik_risiko')->values();
        $pengendaliMutu = $struktur->where('peran', 'pengendali_mutu')
            ->sortBy([['bidang_id', 'asc'], ['urutan', 'asc']])
            ->values();
        $pengendaliRisiko = $struktur->where('peran', 'pengendali_risiko')
            ->sortBy([['bidang_id', 'asc'], ['urutan', 'asc']])
            ->values();

        $galeri = GaleriSarana::orderBy('urutan')->orderBy('id')->get();

        $data = [
            'pemilikRisiko' => $pemilikRisiko,
            'pengendaliMutu' => $pengendaliMutu,
            'pengendaliRisiko' => $pengendaliRisiko,
            // Pengendali risiko dikelompokkan per bidang supaya bisa
            // digantung tepat di bawah kartu pengendali mutu yang sama.
            'risikoPerBidang' => $pengendaliRisiko->groupBy('bidang_id'),
            'galeri' => $galeri,
            'latitude' => self::LATITUDE,
            'longitude' => self::LONGITUDE,
            'koordinatDms' => self::KOORDINAT_DMS,
            'tautanPeta' => $this->tautanGoogleMaps(self::LATITUDE, self::LONGITUDE),
        ];

        // Daftar bidang juga dipakai sebagai pilihan pada form admin,
        // sehingga selalu diambil di sini.
        $data['daftarBidang'] = Bidang::orderBy('nama_bidang')->get();

        if ($this->bolehKelola()) {
            $data['daftarUser'] = User::orderBy('name')->get(['id', 'name', 'nip', 'jabatan', 'foto_profil']);
        }

        return view('home.index', $data);
    }

    // ========================
    // ADMIN: Struktur Organisasi
    // ========================

    public function simpanStruktur(Request $request)
    {
        $this->wajibAdmin();

        $data = $this->validasiStruktur($request);
        $data['urutan'] ??= (int) StrukturOrganisasi::max('urutan') + 1;

        StrukturOrganisasi::create($data);

        return back()->with('success', 'Struktur organisasi berhasil ditambahkan.');
    }

    public function ubahStruktur(Request $request, $id)
    {
        $this->wajibAdmin();

        $struktur = StrukturOrganisasi::findOrFail($id);
        $struktur->update($this->validasiStruktur($request));

        return back()->with('success', 'Struktur organisasi berhasil diperbarui.');
    }

    public function hapusStruktur($id)
    {
        $this->wajibAdmin();

        StrukturOrganisasi::findOrFail($id)->delete();

        return back()->with('success', 'Struktur organisasi berhasil dihapus.');
    }

    /**
     * Atur ulang urutan tampil struktur organisasi sekaligus menukar posisi
     * (dipakai oleh tombol panah atas dan bawah).
     */
    public function urutkanStruktur(Request $request)
    {
        $this->wajibAdmin();

        $ids = $request->input('urutan', []);

        DB::transaction(function () use ($ids) {
            foreach (array_values(array_filter((array) $ids)) as $posisi => $id) {
                StrukturOrganisasi::whereKey($id)->update(['urutan' => $posisi + 1]);
            }
        });

        return back()->with('success', 'Urutan struktur organisasi diperbarui.');
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

    /**
     * Validasi dan bersihkan input struktur organisasi.
     *
     * `nama_jabatan` wajib diisi karena label pada org chart boleh berbeda
     * dari peran default-nya, mis. "Pengendali Mutu Bidang SDA".
     */
    private function validasiStruktur(Request $request): array
    {
        $request->validate([
            'peran' => ['required', Rule::in(array_keys(StrukturOrganisasi::PERAN))],
            'nama_jabatan' => 'required|string|max:150',
            'user_id' => 'nullable|exists:users,id',
            'bidang_id' => 'nullable|exists:bidang,id',
            'urutan' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|string|max:500',
        ], [
            'peran.required' => 'Peran wajib dipilih.',
            'peran.in' => 'Peran tidak dikenal.',
            'nama_jabatan.required' => 'Nama jabatan wajib diisi.',
            'user_id.exists' => 'Pegawai yang dipilih tidak ditemukan.',
            'bidang_id.exists' => 'Bidang yang dipilih tidak ditemukan.',
        ]);

        $peran = $request->input('peran');
        $bidangId = $request->input('bidang_id');

        return [
            'peran' => $peran,
            'nama_jabatan' => $request->input('nama_jabatan'),
            'user_id' => $request->input('user_id') ?: null,
            // Baris teratas (Pemilik Risiko) tidak punya bidang.
            'bidang_id' => $peran === 'pemilik_risiko' ? null : ($bidangId ?: null),
            'urutan' => (int) ($request->input('urutan') ?? 0),
            'keterangan' => $request->input('keterangan') ?: null,
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