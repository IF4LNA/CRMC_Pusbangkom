<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CrmcController extends Controller
{
    public function show($slug)
    {
        // Contoh data dummy/mock berdasarkan slug (di aplikasi nyata diambil dari Database MySQL)
        $subBidangName = ucwords(str_replace('-', ' ', $slug));
        
        // Data 8 komponen yang tersimpan (jika ada)
        $crmcData = [
            'pegawai' => 'Muhammad Fajar Syaffiqri',
            'risk_register' => 'RR-CRMC-2026-V1',
            'sop_file' => 'SOP-CRMC-PUPR.pdf',
            'checklist_file' => 'Formulir-Kepatuhan.pdf',
            'jadwal' => 'Triwulan I - IV Tahun Anggaran 2026',
            'bukti_url' => 'https://drive.pupr.go.id/s/crmc-2026',
            'residu' => 'Rendah',
            'evaluasi' => 'Pengendalian berjalan efektif dan terpantau secara kontinu.'
        ];

        return view('crmc.show', compact('subBidangName', 'slug', 'crmcData'));
    }

    public function update(Request $request, $slug)
    {
        // Validasi dan simpan ke database
        $request->validate([
            'pegawai' => 'required|string',
            'risk_register' => 'required|string',
            'residu' => 'required|string',
        ]);

        // Simpan logika ke database di sini...

        return redirect()->route('crmc.show', $slug)->with('success', 'Dokumen 8 Komponen CRMC berhasil diperbarui.');
    }
}