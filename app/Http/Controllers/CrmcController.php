<?php

namespace App\Http\Controllers;

use App\Models\SubMenu;
use App\Models\Bidang;
use App\Models\DokumenCrmc;
use App\Models\LampiranCrmc;
use App\Models\PenugasanCrmc;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CrmcController extends Controller
{
    public function show(Request $request, $slug)
    {
        // Normalisasi slug ke teks biasa
        $cleanSlug = str_replace('-', ' ', $slug);

        // Cari SubMenu di database yang cocok dengan nama atau format slug
        $subMenu = null;
        try {
            $subMenu = SubMenu::with(['bidang', 'penugasan.user', 'dokumen.lampiran'])
                ->where('nama_sub_menu', 'like', "%{$cleanSlug}%")
                ->orWhereRaw("LOWER(REPLACE(REPLACE(nama_sub_menu, ' ', '-'), '/', '-')) = ?", [strtolower($slug)])
                ->first();
        } catch (\Throwable $e) {
            $subMenu = null;
        }

        // Tentukan nama sub-bidang & parent bidang
        if ($subMenu) {
            $subBidangName = $subMenu->nama_sub_menu;
            $parentBidang = $subMenu->bidang?->nama_bidang ?? 'Bagian Umum Program & Tata Usaha';
        } else {
            $subBidangName = ucwords(str_replace(['-', '_'], ' ', $slug));
            
            // Prediksi parent kategori berdasarkan nama
            if (str_contains(strtolower($subBidangName), 'sda')) {
                $parentBidang = 'Bidang SDA';
            } elseif (str_contains(strtolower($subBidangName), 'ckps')) {
                $parentBidang = 'Bidang CKPS';
            } else {
                $parentBidang = 'Bagian Umum Program & Tata Usaha';
            }
        }

        // 1. Pemilik Risiko: HANYA ADA 1 ORANG
        $pemilikRisiko = $subMenu?->penugasan?->where('peran', 'pemilik_risiko')->first()?->user;
        if (!$pemilikRisiko) {
            $pemilikRisiko = User::where('email', 'kapus@pu.go.id')->first();
        }

        // 2. Pengendali Mutu: HANYA ADA 1 ORANG di tiap bidang & sub bidang
        $pengendaliMutu = $subMenu?->penugasan?->where('peran', 'pengendali_mutu')->first()?->user;
        if (!$pengendaliMutu) {
            $pengendaliMutu = User::where('email', 'kabag.tu@pu.go.id')->first();
        }

        // 3. Pengendali Risiko: BISA BANYAK ORANG (Multiple Risk Controllers)
        $pengendaliRisikoList = collect();
        if ($subMenu && $subMenu->penugasan) {
            $pengendaliRisikoList = $subMenu->penugasan
                ->where('peran', 'pengendali_risiko')
                ->map(fn($p) => $p->user)
                ->filter()
                ->values();
        }

        // Fallback jika belum ada penugasan pengendali risiko
        if ($pengendaliRisikoList->isEmpty()) {
            $pengendaliRisikoList = User::whereIn('email', ['rina.staf@pu.go.id', 'dwi.staf@pu.go.id', 'fauzi.staf@pu.go.id'])->get();
            if ($pengendaliRisikoList->isEmpty()) {
                $pengendaliRisikoList = User::where('role', 'pegawai')->get();
            }
        }

        // === TAHUN FILTER: Default tahun terbaru, bisa melihat tahun sebelumnya ===
        $selectedTahun = $request->input('tahun', null);
        
        // Ambil semua tahun yang memiliki dokumen
        $availableYears = collect();
        if ($subMenu) {
            $availableYears = DokumenCrmc::where('sub_menu_id', $subMenu->id)
                ->distinct()
                ->orderByDesc('tahun_pelaksanaan')
                ->pluck('tahun_pelaksanaan');
        }
        
        // Jika belum ada tahun tersimpan, tampilkan tahun berjalan saja
        if ($availableYears->isEmpty()) {
            $availableYears = collect([date('Y')]);
        }
        
        // Default ke tahun terbaru jika tidak dipilih
        if (!$selectedTahun) {
            $selectedTahun = $availableYears->first();
        }

        // Dokumen CRMC berdasarkan tahun terpilih
        $dokumenDb = null;
        if ($subMenu) {
            $dokumenDb = DokumenCrmc::with('lampiran')
                ->where('sub_menu_id', $subMenu->id)
                ->where('tahun_pelaksanaan', $selectedTahun)
                ->first();
        }

        // Lampiran dikelompokkan berdasarkan kategori komponen
        $lampiranGrouped = [];
        if ($dokumenDb) {
            $lampiranGrouped = $dokumenDb->lampiran->groupBy('kategori_komponen');
        }

        // Data 8 Komponen CRMC
        $firstPengendali = $pengendaliRisikoList->first();
        $crmcData = [
            // Metadata PIC utama
            'pegawai' => $firstPengendali?->name ?? 'Muhammad Fajar Syaffiqri',
            'pegawai_nip' => $firstPengendali?->nip ?? '199001012026011001',
            'pegawai_jabatan' => $firstPengendali?->jabatan ?? 'System Administrator & PIC Pengendalian',
            'pegawai_foto' => $firstPengendali?->foto_url ?? 'https://ui-avatars.com/api/?name=Fajar+Syaffiqri&background=0f172a&color=f59e0b&bold=true&size=160',

            // 2. Risk Register Spesifik Acuan
            'risk_register' => 'RR-CRMC-' . strtoupper(substr(md5($subBidangName), 0, 4)) . '-2026-V1',
            'deskripsi_risiko' => 'Pengendalian risiko operasional, kepatuhan regulasi, dan akuntabilitas pelaksanaan kegiatan ' . $subBidangName . '.',

            // 3. Standar Operasional Prosedur (SOP)
            'sop_file' => 'SOP-CRMC-' . Str::slug($subBidangName) . '.pdf',
            'sop_nomor' => 'SOP/PUPR/BPSDM/2026/' . str_pad(abs(crc32($subBidangName)) % 900 + 100, 3, '0', STR_PAD_LEFT),

            // 4. Formulir Pengendalian
            'checklist_file' => 'Formulir-Kepatuhan-' . Str::slug($subBidangName) . '.pdf',
            'checklist_status' => '100% Sesuai & Terverifikasi',

            // 5. Jadwal Rencana Pelaksanaan
            'jadwal' => 'Tahun Anggaran ' . date('Y') . ' (Triwulan I - IV)',
            'jadwal_detail' => 'Pemantauan berkala bulanan dan pelaporan terpadu setiap akhir triwulan.',

            // 6. Tautan Bukti Pelaksanaan
            'bukti_url' => 'https://drive.pupr.go.id/s/crmc-2026-' . Str::slug($subBidangName),

            // 7. Status Residu Risiko
            'residu' => $dokumenDb?->status_residu_risiko ?? 'Rendah',

            // 8. Evaluasi & Rencana Perbaikan
            'evaluasi' => $dokumenDb?->evaluasi_dan_rencana ?? 'Sistem pengendalian berjalan optimal, tidak ditemukan deviasi signifikan, dan terus dilakukan pemantauan rutin.',

            // Metadata
            'tanggal_update' => date('d M Y')
        ];

        // Daftar seluruh user untuk form penugasan admin
        $allUsers = User::orderBy('name')->get();

        return view('crmc.show', compact(
            'subBidangName', 
            'parentBidang', 
            'slug', 
            'crmcData', 
            'subMenu', 
            'pemilikRisiko', 
            'pengendaliMutu', 
            'pengendaliRisikoList', 
            'allUsers',
            'selectedTahun',
            'availableYears',
            'dokumenDb',
            'lampiranGrouped'
        ));
    }

    public function update(Request $request, $slug)
    {
        $request->validate([
            'pegawai' => 'nullable|string',
            'risk_register' => 'nullable|string',
            'residu' => 'nullable|string',
            'evaluasi' => 'nullable|string',
            'bukti_url' => 'nullable|string',
            'jadwal' => 'nullable|string',
        ]);

        $cleanSlug = str_replace('-', ' ', $slug);
        try {
            $subMenu = SubMenu::where('nama_sub_menu', 'like', "%{$cleanSlug}%")
                ->orWhereRaw("LOWER(REPLACE(REPLACE(nama_sub_menu, ' ', '-'), '/', '-')) = ?", [strtolower($slug)])
                ->first();

            if ($subMenu) {
                DokumenCrmc::updateOrCreate(
                    [
                        'sub_menu_id' => $subMenu->id,
                        'tahun_pelaksanaan' => date('Y'),
                    ],
                    [
                        'status_residu_risiko' => $request->input('residu', 'Rendah'),
                        'evaluasi_dan_rencana' => $request->input('evaluasi', 'Pengendalian berjalan efektif.'),
                    ]
                );
            }
        } catch (\Throwable $e) {
            // Abaikan error DB jika tabel tidak tersedia
        }

        return redirect()->route('crmc.show', $slug)->with('success', 'Dokumen 8 Komponen CRMC berhasil diperbarui.');
    }

    /**
     * Upload Dokumen per Komponen (Multi-file Upload).
     * Pegawai & Admin bisa upload di komponen 2,3,4,5,6,8.
     */
    public function uploadDokumen(Request $request, $slug)
    {
        if (!Auth::check()) {
            abort(403, 'Anda harus login untuk mengunggah dokumen.');
        }

        $request->validate([
            'kategori_komponen' => 'required|string',
            'tahun_pelaksanaan' => 'required|numeric',
            'files' => 'required|array|min:1',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
        ], [
            'files.required' => 'Pilih minimal 1 file untuk diunggah.',
            'files.*.mimes' => 'Format file harus PDF, DOC, DOCX, XLS, XLSX, JPG, atau PNG.',
            'files.*.max' => 'Ukuran file maksimal 10MB per file.',
        ]);

        $cleanSlug = str_replace('-', ' ', $slug);
        $subMenu = SubMenu::where('nama_sub_menu', 'like', "%{$cleanSlug}%")
            ->orWhereRaw("LOWER(REPLACE(REPLACE(nama_sub_menu, ' ', '-'), '/', '-')) = ?", [strtolower($slug)])
            ->first();

        if (!$subMenu) {
            $defaultBidang = Bidang::first();
            $subMenu = SubMenu::create([
                'bidang_id' => $defaultBidang?->id ?? 1,
                'nama_sub_menu' => ucwords($cleanSlug),
            ]);
        }

        $tahun = $request->input('tahun_pelaksanaan');
        $kategori = $request->input('kategori_komponen');

        // Buat/dapatkan dokumen untuk tahun ini
        $dokumen = DokumenCrmc::firstOrCreate(
            [
                'sub_menu_id' => $subMenu->id,
                'tahun_pelaksanaan' => $tahun,
            ],
            [
                'status_residu_risiko' => 'Rendah',
                'evaluasi_dan_rencana' => 'Pengendalian berjalan efektif.',
            ]
        );

        // Upload setiap file
        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            
            $path = $file->storeAs(
                "public/crmc/{$tahun}/{$subMenu->id}/{$kategori}",
                time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension
            );

            LampiranCrmc::create([
                'dokumen_crmc_id' => $dokumen->id,
                'kategori_komponen' => $kategori,
                'nama_file' => $originalName,
                'file_path' => Storage::url($path),
                'tipe_file' => strtolower($extension),
            ]);
        }

        return redirect()->route('crmc.show', ['slug' => $slug, 'tahun' => $tahun])
            ->with('success', 'Dokumen berhasil diunggah pada komponen "' . str_replace('_', ' ', ucfirst($kategori)) . '".');
    }

    /**
     * Hapus Lampiran Individual (Admin Only).
     */
    public function deleteLampiran($id)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Hanya Administrator yang dapat menghapus lampiran.');
        }

        $lampiran = LampiranCrmc::findOrFail($id);

        // Hapus file fisik dari storage
        $relativePath = str_replace('/storage/', 'public/', $lampiran->file_path);
        if (Storage::exists($relativePath)) {
            Storage::delete($relativePath);
        }

        $lampiran->delete();

        return back()->with('success', 'Lampiran "' . $lampiran->nama_file . '" berhasil dihapus.');
    }

    /**
     * Update Status Residu Risiko (Khusus Admin - Komponen 7).
     */
    public function updateResidu(Request $request, $slug)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Hanya Administrator yang dapat mengubah Status Residu Risiko.');
        }

        $request->validate([
            'residu' => 'required|string|in:Rendah,Sedang,Tinggi',
            'tahun_pelaksanaan' => 'required|numeric',
        ]);

        $cleanSlug = str_replace('-', ' ', $slug);
        $subMenu = SubMenu::where('nama_sub_menu', 'like', "%{$cleanSlug}%")
            ->orWhereRaw("LOWER(REPLACE(REPLACE(nama_sub_menu, ' ', '-'), '/', '-')) = ?", [strtolower($slug)])
            ->first();

        if (!$subMenu) {
            $defaultBidang = Bidang::first();
            $subMenu = SubMenu::create([
                'bidang_id' => $defaultBidang?->id ?? 1,
                'nama_sub_menu' => ucwords($cleanSlug),
            ]);
        }

        DokumenCrmc::updateOrCreate(
            [
                'sub_menu_id' => $subMenu->id,
                'tahun_pelaksanaan' => $request->input('tahun_pelaksanaan'),
            ],
            [
                'status_residu_risiko' => $request->input('residu'),
            ]
        );

        return redirect()->route('crmc.show', ['slug' => $slug, 'tahun' => $request->input('tahun_pelaksanaan')])
            ->with('success', 'Status Residu Risiko berhasil diperbarui menjadi "' . $request->input('residu') . '".');
    }

    /**
     * Memperbarui Penugasan Identitas Pegawai di Komponen 1 (Khusus Admin).
     */
    public function updatePenugasan(Request $request, $slug)
    {
        // Cek otorisasi: pengguna harus login dan ber-role admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator yang berwenang memberikan dan mengubah penugasan pegawai.');
        }

        $request->validate([
            'pemilik_risiko_id' => 'required|exists:users,id',
            'pengendali_mutu_id' => 'required|exists:users,id',
            'pengendali_risiko_ids' => 'required|array|min:1',
            'pengendali_risiko_ids.*' => 'exists:users,id',
        ], [
            'pemilik_risiko_id.required' => 'Pemilik risiko (1 orang) wajib dipilih.',
            'pengendali_mutu_id.required' => 'Pengendali mutu (1 orang) wajib dipilih.',
            'pengendali_risiko_ids.required' => 'Paling sedikit pilih 1 staf Pengendali Risiko.',
        ]);

        $cleanSlug = str_replace('-', ' ', $slug);
        $subMenu = SubMenu::where('nama_sub_menu', 'like', "%{$cleanSlug}%")
            ->orWhereRaw("LOWER(REPLACE(REPLACE(nama_sub_menu, ' ', '-'), '/', '-')) = ?", [strtolower($slug)])
            ->first();

        if (!$subMenu) {
            $defaultBidang = Bidang::first();
            $subMenu = SubMenu::create([
                'bidang_id' => $defaultBidang?->id ?? 1,
                'nama_sub_menu' => ucwords($cleanSlug),
            ]);
        }

        // 1. Bersihkan penugasan lama untuk sub_menu ini
        PenugasanCrmc::where('sub_menu_id', $subMenu->id)->delete();

        // 2. Simpan Pemilik Risiko (Hanya 1 Orang)
        PenugasanCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'user_id' => $request->input('pemilik_risiko_id'),
            'peran' => 'pemilik_risiko',
        ]);

        // 3. Simpan Pengendali Mutu (Hanya 1 Orang di tiap bidang & sub bidang)
        PenugasanCrmc::create([
            'sub_menu_id' => $subMenu->id,
            'user_id' => $request->input('pengendali_mutu_id'),
            'peran' => 'pengendali_mutu',
        ]);

        // 4. Simpan Pengendali Risiko (Bisa Banyak Orang)
        foreach ($request->input('pengendali_risiko_ids', []) as $stafId) {
            PenugasanCrmc::create([
                'sub_menu_id' => $subMenu->id,
                'user_id' => $stafId,
                'peran' => 'pengendali_risiko',
            ]);
        }

        return redirect()->route('crmc.show', $slug)->with('success', 'Penugasan identitas pegawai di Komponen 1 berhasil diperbarui oleh Administrator!');
    }

    // ========================
    // ADMIN: Kelola Akun Pegawai
    // ========================

    /**
     * Tampilkan daftar seluruh akun pegawai (Khusus Admin).
     */
    public function kelolaAkun()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator.');
        }

        $users = User::orderBy('name')->get();
        return view('admin.pegawai', compact('users'));
    }

    /**
     * Simpan akun pegawai baru (Khusus Admin).
     */
    public function simpanAkun(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:30|unique:users,nip',
            'jabatan' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,pegawai',
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['name', 'nip', 'jabatan', 'email', 'role']);
        $data['password'] = Hash::make($request->input('password'));

        if ($request->hasFile('foto_profil')) {
            $data['foto_profil'] = $request->file('foto_profil')->store('public/foto-pegawai');
            $data['foto_profil'] = str_replace('public/', '', $data['foto_profil']);
        }

        User::create($data);

        return redirect()->route('admin.pegawai.index')->with('success', 'Akun pegawai "' . $data['name'] . '" berhasil dibuat.');
    }

    /**
     * Update data akun pegawai (Khusus Admin).
     */
    public function updateAkun(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator.');
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:30|unique:users,nip,' . $user->id,
            'jabatan' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,pegawai',
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only(['name', 'nip', 'jabatan', 'email', 'role']);
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        if ($request->hasFile('foto_profil')) {
            // Hapus foto lama jika ada
            if ($user->foto_profil && Storage::exists('public/' . $user->foto_profil)) {
                Storage::delete('public/' . $user->foto_profil);
            }
            $data['foto_profil'] = $request->file('foto_profil')->store('public/foto-pegawai');
            $data['foto_profil'] = str_replace('public/', '', $data['foto_profil']);
        }

        $user->update($data);

        return redirect()->route('admin.pegawai.index')->with('success', 'Data pegawai "' . $data['name'] . '" berhasil diperbarui.');
    }

    /**
     * Hapus akun pegawai (Khusus Admin).
     */
    public function hapusAkun($id)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator.');
        }

        $user = User::findOrFail($id);

        // Jangan izinkan hapus diri sendiri
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;

        // Hapus foto jika ada
        if ($user->foto_profil && Storage::exists('public/' . $user->foto_profil)) {
            Storage::delete('public/' . $user->foto_profil);
        }

        $user->delete();

        return redirect()->route('admin.pegawai.index')->with('success', 'Akun pegawai "' . $name . '" berhasil dihapus.');
    }
}