<?php

namespace App\Http\Controllers;

use App\Models\SubMenu;
use App\Models\DokumenCrmc;
use App\Models\LampiranCrmc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DokumenCrmcController extends Controller
{
    // Menampilkan dokumen, PIC, dan lampiran berdasarkan Sub Menu & Tahun
    public function show(Request $request, $subMenuId)
    {
        $tahun = $request->input('tahun', date('Y'));

        $subMenu = SubMenu::with(['penugasan.user'])->findOrFail($subMenuId);

        $dokumen = DokumenCrmc::with('lampiran')
            ->where('sub_menu_id', $subMenuId)
            ->where('tahun_pelaksanaan', $tahun)
            ->first();

        $lampiranGrouped = $dokumen ? $dokumen->lampiran->groupBy('kategori_komponen') : [];

        // Memisahkan 3 lapis PIC
        $pic = [
            'pemilik_risiko'   => $subMenu->penugasan->where('peran', 'pemilik_risiko')->first()?->user,
            'pengendali_mutu'  => $subMenu->penugasan->where('peran', 'pengendali_mutu')->first()?->user,
            'pengendali_risiko' => $subMenu->penugasan->where('peran', 'pengendali_risiko')->first()?->user,
        ];

        return response()->json([
            'sub_menu' => $subMenu,
            'tahun' => $tahun,
            'pic' => $pic,
            'dokumen' => $dokumen,
            'lampiran' => $lampiranGrouped,
        ]);
    }

    // Menyimpan / Memperbarui dokumen dan multi-upload file
    public function storeOrUpdate(Request $request, $subMenuId)
    {
        $request->validate([
            'tahun_pelaksanaan' => 'required|numeric',
            'status_residu_risiko' => 'nullable|string',
            'evaluasi_dan_rencana' => 'nullable|string',
            'files.*.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,png|max:10240',
        ]);

        $tahun = $request->input('tahun_pelaksanaan');

        $dokumen = DokumenCrmc::updateOrCreate(
            [
                'sub_menu_id' => $subMenuId,
                'tahun_pelaksanaan' => $tahun,
            ],
            [
                'status_residu_risiko' => $request->input('status_residu_risiko'),
                'evaluasi_dan_rencana' => $request->input('evaluasi_dan_rencana'),
            ]
        );

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $kategori => $fileArray) {
                foreach ($fileArray as $file) {
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    
                    $path = $file->storeAs(
                        "crmc/{$tahun}/{$subMenuId}/{$kategori}",
                        time() . '_' . $originalName,
                        'public'
                    );

                    LampiranCrmc::create([
                        'dokumen_crmc_id' => $dokumen->id,
                        'kategori_komponen' => $kategori,
                        'nama_file' => $originalName,
                        'file_path' => Storage::disk('public')->url($path),
                        'tipe_file' => strtolower($extension),
                    ]);
                }
            }
        }

        return response()->json([
            'message' => 'Dokumen CRMC berhasil disimpan.',
            'data' => $dokumen->load('lampiran')
        ], 200);
    }

    // Menghapus file lampiran individual
    public function destroyAttachment($attachmentId)
    {
        $lampiran = LampiranCrmc::findOrFail($attachmentId);

        $relativePath = $lampiran->storage_path;
        if ($relativePath && Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }

        $lampiran->delete();

        return response()->json(['message' => 'File lampiran berhasil dihapus.']);
    }
}