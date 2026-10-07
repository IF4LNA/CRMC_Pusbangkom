<?php

namespace App\Http\Controllers;

use App\Models\DokumenDasarHukum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Berkas dokumen dasar hukum pada tab "Dasar Hukum" dashboard.
 *
 * Daftar regulasinya sendiri dibuat lewat seeder dan tidak bisa ditambah
 * lewat antarmuka. Controller ini hanya menangani berkas scan-nya: unggah
 * (atau ganti), dan hapus.
 *
 * Form berkas sengaja memakai POST biasa, bukan XHR. Setelah mengunggah,
 * halaman dimuat ulang supaya pratinjau langsung terlihat.
 */
class DokumenDasarHukumController extends Controller
{
    /** Format berkas yang masih berguna untuk pratinjau dan unduh. */
    private const MIME_TIPE = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];

    /** Batas ukuran berkas: 10 MB, sama dengan Komponen dokumen CRMC. */
    private const MAKS_UKURAN_KB = 10240;

    /**
     * Unggah berkas untuk satu regulasi, mengganti berkas sebelumnya.
     */
    public function unggahBerkas(Request $request, $id)
    {
        $this->wajibAdmin();

        $dokumen = DokumenDasarHukum::findOrFail($id);

        $request->validate([
            'berkas' => ['required', 'file', 'mimes:'.implode(',', self::MIME_TIPE), 'max:'.self::MAKS_UKURAN_KB],
        ], [
            'berkas.required' => 'Pilih berkas terlebih dahulu.',
            'berkas.file' => 'Berkas yang dipilih tidak valid.',
            'berkas.mimes' => 'Format berkas harus PDF, DOC, DOCX, XLS, XLSX, JPG, atau PNG.',
            'berkas.max' => 'Ukuran berkas maksimal 10 MB.',
        ]);

        $berkas = $request->file('berkas');
        $pathLama = $dokumen->path;

        $pathBaru = $berkas->store('dasar-hukum', 'public');

        // Simpan file dulu, baru perbarui baris database. Kalau baris gagal,
        // file yang terlanjur tersimpan ikut dihapus supaya tidak tertinggal.
        try {
            $dokumen->update([
                'path' => $pathBaru,
                'nama_file' => $berkas->getClientOriginalName(),
                'tipe_file' => strtolower($berkas->getClientOriginalExtension()),
                'ukuran' => $berkas->getSize(),
            ]);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($pathBaru);
            throw $e;
        }

        // Berkas lama dihapus setelah baris berhasil diperbarui, supaya
        // Failed update tidak membuat regulations kehilangan dokumen.
        if ($pathLama && $pathLama !== $pathBaru) {
            Storage::disk('public')->delete($pathLama);
        }

        return back()->with(
            'success',
            'Dokumen "'.$dokumen->nomor.'" berhasil diunggah dan langsung tampil di pratinjau.'
        );
    }

    /**
     * Hapus berkas satu regulasi. Baris regulasinya tetap ada supaya
     // admin bisa mengunggah ulang tanpa kehilangan identitas naskah.
     */
    public function hapusBerkas($id)
    {
        $this->wajibAdmin();

        $dokumen = DokumenDasarHukum::findOrFail($id);

        $dokumen->hapusFile();
        $dokumen->update([
            'path' => null,
            'nama_file' => null,
            'tipe_file' => null,
            'ukuran' => null,
        ]);

        return back()->with('success', 'Dokumen "'.$dokumen->nomor.'" berhasil dihapus.');
    }

    private function wajibAdmin(): void
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator yang dapat mengelola dokumen dasar hukum.');
        }
    }
}
