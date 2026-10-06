<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\DokumenCrmc;
use App\Models\LampiranCrmc;
use App\Models\SubMenu;
use App\Support\PenyimpananGambar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Dashboard CRMC beserta pengelolaan bidang dan sub-bidangnya.
 *
 * Daftar bidang dan sub-bidang dibaca dari tabel `bidang` dan `sub_menu`,
 * bukan dari daftar yang ditulis manual di dalam view. Admin karena itu
 * bisa menambah sub-bidang baru dan memasang gambar latar pada kartunya
 * tanpa menyentuh kode.
 */
class BidangController extends Controller
{
    /** Halaman dashboard utama CRMC (route "/"). */
    public function dashboard()
    {
        $daftarBidang = $this->daftarBidang();

        return view('crmc.index', [
            'daftarBidang' => $daftarBidang,
            'isAdmin' => $this->bolehKelola(),
        ]);
    }

    /**
     * Semua bidang beserta sub-bidangnya, siap dipakai di view.
     *
     * `withCount` dipakai supaya angka sub-bidang dan jumlah dokumen per
     * bidang bisa ditampilkan tanpa query tambahan di dalam loop Blade.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Bidang>
     */
    private function daftarBidang()
    {
        // Sub-bidang diurutkan sesuai urutan di database (id), bukan
        // alfabetis. Kalau diurutkan nama, urutan kartu akan berbeda dari
        // urutan yang terlihat di Manage/Kelola, sehingga admin bingung
        // mencocokkan kartu dengan barisnya.
        return Bidang::query()
            ->with([
                'subMenus' => fn ($q) => $q->orderBy('id'),
            ])
            ->withCount([
                'subMenus',
                'subMenus as jumlah_dokumen' => fn ($q) => $q->has('dokumen'),
            ])
            // Urutan bidang mengikuti urutan pembuatan (id), bukan alfabetis.
            // Jika diurutkan nama, "Bidang CKPS" akan muncul sebelum
            // "Bidang SDA" dan urutan tab berubah dari yang biasa dipakai.
            ->orderBy('id')
            ->get();
    }

    // ========================
    // ADMIN: Kelola Sub-Bidang
    // ========================

    public function simpanSubBidang(Request $request)
    {
        $this->wajibAdmin();

        $data = $request->validate($this->aturanSubBidang(), $this->pesanSubBidang());

        $data['nama_sub_menu'] = $this->rapikanNama($data['nama_sub_menu']);

        // Gambar latar opsional. Kalau kosong, kartu tetap tampil polos.
        if ($request->hasFile('gambar_latar')) {
            $data['gambar_latar'] = PenyimpananGambar::simpan(
                $request->file('gambar_latar'),
                'latar-sub-bidang'
            );
        }

        SubMenu::create($data);

        return back()->with('success', 'Sub-bidang "' . $data['nama_sub_menu'] . '" berhasil ditambahkan.');
    }

    public function ubahSubBidang(Request $request, $id)
    {
        $this->wajibAdmin();

        $subMenu = SubMenu::findOrFail($id);

        // Saat menyunting, gambar boleh dikosongkan: berarti gambar lama
        // dipertahankan, bukan dihapus.
        $data = $request->validate($this->aturanSubBidang(false), $this->pesanSubBidang());

        $data['nama_sub_menu'] = $this->rapikanNama($data['nama_sub_menu']);

        $pathLama = $subMenu->gambar_latar;

        if ($request->hasFile('gambar_latar')) {
            $pathBaru = PenyimpananGambar::simpan($request->file('gambar_latar'), 'latar-sub-bidang');

            $subMenu->update([...$data, 'gambar_latar' => $pathBaru]);

            // File lama dihapus setelah baris database berhasil diperbarui.
            if ($pathLama && $pathLama !== $pathBaru) {
                Storage::disk('public')->delete($pathLama);
            }

            $pesan = 'Sub-bidang "' . $data['nama_sub_menu'] . '" berhasil diperbarui.';
            if ($pathLama) {
                $pesan .= ' Gambar latarnya diganti.';
            }

            return back()->with('success', $pesan);
        }

        // Opsi "hapus gambar latar": dilepas dari database dan file
        // fisiknya ikut dihapus, bukan sekadar dikosongkan kolomnya.
        if ($request->boolean('hapus_gambar_latar')) {
            $subMenu->update([...$data, 'gambar_latar' => null]);

            if ($pathLama) {
                Storage::disk('public')->delete($pathLama);
            }

            return back()->with(
                'success',
                'Gambar latar sub-bidang "' . $data['nama_sub_menu'] . '" telah dihapus.'
            );
        }

        // Tanpa berkas baru dan tanpa permintaan hapus: gambar lama
        // dipertahankan.
        $subMenu->update($data);

        return back()->with('success', 'Sub-bidang "' . $data['nama_sub_menu'] . '" berhasil diperbarui.');
    }

    public function hapusSubBidang($id)
    {
        $this->wajibAdmin();

        $subMenu = SubMenu::findOrFail($id);
        $nama = $subMenu->nama_sub_menu;

        // Hapus file fisik lampiran DAN dokumen induk secara manual dulu.
        // Foreign key cascade di database tidak menghapus file di disk,
        // tanpa langkah ini akan ada file sampah yang tidak pernah dibersihkan.
        foreach (LampiranCrmc::whereIn(
            'dokumen_crmc_id',
            DokumenCrmc::where('sub_menu_id', $subMenu->id)->select('id')
        )->get() as $lampiran) {
            $path = $lampiran->storage_path;
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        if ($subMenu->gambar_latar) {
            Storage::disk('public')->delete($subMenu->gambar_latar);
        }

        $subMenu->delete();

        return back()->with(
            'success',
            'Sub-bidang "' . $nama . '" beserta seluruh dokumen dan lampirannya berhasil dihapus.'
        );
    }

    // ========================
    // Helper
    // ========================

    private function aturanSubBidang(bool $gambarWajib = false): array
    {
        return [
            'bidang_id' => 'required|exists:bidang,id',
            'nama_sub_menu' => 'required|string|max:150',
            'gambar_latar' => array_filter([
                $gambarWajib ? 'required' : 'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ]),
        ];
    }

    private function pesanSubBidang(): array
    {
        return [
            'bidang_id.required' => 'Bidang wajib dipilih.',
            'bidang_id.exists' => 'Bidang yang dipilih tidak ditemukan.',
            'nama_sub_menu.required' => 'Nama sub-bidang wajib diisi.',
            'nama_sub_menu.max' => 'Nama sub-bidang maksimal 150 karakter.',
            'gambar_latar.image' => 'Berkas yang dipilih bukan gambar.',
            'gambar_latar.mimes' => 'Format gambar harus JPG, PNG, atau WebP.',
            'gambar_latar.max' => 'Ukuran gambar maksimal 4 MB.',
        ];
    }

    /**
     * Rapatkan spasi ganda di nama sub-bidang.
     *
     * Nama dipakai untuk membentuk slug URL, jadi spasi berlebih akan
     * membuat tautan yang berbeda untuk sub-bidang yang sebenarnya sama.
     */
    private function rapikanNama(string $nama): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $nama));
    }

    private function bolehKelola(): bool
    {
        return Auth::check() && Auth::user()->isAdmin();
    }

    private function wajibAdmin(): void
    {
        if (!$this->bolehKelola()) {
            abort(403, 'Akses Ditolak: Hanya Administrator yang dapat mengelola bidang dan sub-bidang.');
        }
    }
}
