<?php

namespace App\Http\Controllers;

use App\Models\DokumenDasarHukum;
use App\Rules\BerkasDokumen;
use App\Support\KompresiBerkas;
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

    /** Batas ukuran akhir berkas: 20 MB, sama dengan Komponen dokumen CRMC. */
    private const MAKS_UKURAN_KB = KompresiBerkas::BATAS_KB;

    /**
     * Batas penerimaan unggahan: 50 MB. Lebih longgar daripada batas akhir
     * supaya berkas besar masih sempat dikompres otomatis sebelum ditolak.
     */
    private const MAKS_UNGGAH_KB = KompresiBerkas::MAKS_UNGGAH_KB;

    /**
     * Unggah berkas untuk satu regulasi, mengganti berkas sebelumnya.
     */
    public function unggahBerkas(Request $request, $id)
    {
        $this->wajibAdmin();

        $dokumen = DokumenDasarHukum::findOrFail($id);

        $request->validate([
            // BerkasDokumen dipakai, bukan "mimes", supaya .xlsx yang oleh
            // fileinfo terdeteksi sebagai "application/zip" tetap diterima.
            'berkas' => [
                'required',
                'file',
                new BerkasDokumen(self::MIME_TIPE),
                'max:'.self::MAKS_UNGGAH_KB,
            ],
        ], [
            'berkas.required' => 'Pilih berkas terlebih dahulu.',
            'berkas.file' => 'Berkas yang dipilih tidak valid.',
            'berkas.max' => 'Ukuran berkas maksimal 50MB. Berkas di atas 20MB akan dikompres otomatis; bila kompresi gagal, berkas ditolak.',
        ]);

        $berkas = $request->file('berkas');

        // Berkas di atas batas 20MB dikompres otomatis. Kalau ukurannya masih
        // melebihi batas setelah dikompres, unggahan dibatalkan dan berkas
        // lama tetap utuh. Lihat App\Support\KompresiBerkas.
        if (! KompresiBerkas::kompres($berkas)) {
            $batasMb = self::MAKS_UKURAN_KB / 1024;

            return back()->withErrors([
                'berkas' => 'Ukuran berkas "'.$berkas->getClientOriginalName().'" tetap melebihi '.$batasMb.'MB setelah dikompres otomatis. Silakan kompres berkas secara manual lalu unggah ulang.',
            ]);
        }

        $pathLama = $dokumen->path;

        $pathBaru = $berkas->store('dasar-hukum', 'public');

        // Simpan file dulu, baru perbarui baris database. Kalau baris gagal,
        // file yang terlanjur tersimpan ikut dihapus supaya tidak tertinggal.
        try {
            $dokumen->update([
                'path' => $pathBaru,
                'nama_file' => $berkas->getClientOriginalName(),
                'tipe_file' => strtolower($berkas->getClientOriginalExtension()),
                // Dibaca langsung dari disk karena getSize() bisa masih
                // menyimpan ukuran sebelum kompresi (di-cache sejak validasi).
                'ukuran' => KompresiBerkas::ukuranAkhir($berkas),
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
