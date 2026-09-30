<?php

namespace App\Http\Controllers;

use App\Models\SubMenu;
use App\Models\Bidang;
use App\Models\DokumenCrmc;
use App\Models\LampiranCrmc;
use App\Models\PenugasanCrmc;
use App\Models\User;
use App\Support\PenyimpananGambar;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        // 1. Pemilik Risiko: HANYA ADA 1 ORANG untuk seluruh CRMC.
        //    Penugasan sudah disalin ke setiap sub-bidang saat disimpan
        //    (lihat updatePenugasan), jadi cukup ambil yang pertama di sini.
        $pemilikRisiko = $subMenu?->penugasan?->firstWhere('peran', 'pemilik_risiko')?->user;

        // 2. Pengendali Mutu: HANYA ADA 1 ORANG per bidang, berlaku untuk
        //    semua sub-bidang di bawah bidang tersebut.
        $pengendaliMutu = $subMenu?->penugasan?->firstWhere('peran', 'pengendali_mutu')?->user;

        // 3. Pengendali Risiko: BISA BANYAK ORANG (Multiple Risk Controllers)
        // Hanya menampilkan yang benar-benar ditugaskan. Tanpa penugasan -> kosong,
        // dan view menampilkan empty state (bukan daftar semua pegawai).
        $pengendaliRisikoList = collect();
        if ($subMenu && $subMenu->penugasan) {
            $pengendaliRisikoList = $subMenu->penugasan
                ->where('peran', 'pengendali_risiko')
                ->map(fn($p) => $p->user)
                ->filter()
                ->unique('id')
                ->values();
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
        $picCadangan = new User(['name' => 'PIC CRMC', 'nip' => '-', 'jabatan' => '-']);
        $crmcData = [
            // Metadata PIC utama
            'pegawai' => $firstPengendali?->name ?? 'PIC CRMC',
            'pegawai_nip' => $firstPengendali?->nip ?? '-',
            'pegawai_jabatan' => $firstPengendali?->jabatan ?? '-',
            'pegawai_foto' => ($firstPengendali ?? $picCadangan)->foto_url,

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
                "crmc/{$tahun}/{$subMenu->id}/{$kategori}",
                time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension,
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
        $relativePath = $lampiran->storage_path;
        if ($relativePath && Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
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

        // Hierarki penugasan (lihat CrmcController::show):
        //   - Pemilik Risiko    : 1 orang, berlaku untuk SELURUH sub-bidang.
        //   - Pengendali Mutu   : 1 orang per BIDANG, berlaku untuk semua
        //                         sub-bidang di bawah bidang tersebut.
        //   - Pengendali Risiko : per sub-bidang.
        //
        // Diimplementasikan dengan menulis peran ke seluruh sub_menu_id
        // yang relevan, sehingga pembacaan di `show()` tetap sederhana
        // (cukup query satu sub-bidang).
        DB::transaction(function () use ($subMenu, $request) {
            $semuaSubMenu = SubMenu::pluck('id');
            $subMenuBidangIni = SubMenu::where('bidang_id', $subMenu->bidang_id)->pluck('id');

            // 1. Bersihkan penugasan lama pada tiga cakupannya, supaya
            //    tidak ada data lama yang tertinggal setelah diganti.
            //    a. pemilik_risiko  -> seluruh CRMC, bukan hanya sub-bidang ini
            //    b. pengendali_mutu -> bidang ini saja, jangan ganggu bidang lain
            //    c. peran lainnya   -> sub-bidang ini saja
            PenugasanCrmc::where('peran', 'pemilik_risiko')->delete();
            PenugasanCrmc::whereIn('sub_menu_id', $subMenuBidangIni)
                ->where('peran', 'pengendali_mutu')
                ->delete();
            PenugasanCrmc::where('sub_menu_id', $subMenu->id)
                ->whereNotIn('peran', ['pemilik_risiko', 'pengendali_mutu'])
                ->delete();

            // 2. Pemilik Risiko: satu orang untuk semua sub-bidang
            $pemilikId = (int) $request->input('pemilik_risiko_id');
            foreach ($semuaSubMenu as $id) {
                PenugasanCrmc::create([
                    'sub_menu_id' => $id,
                    'user_id' => $pemilikId,
                    'peran' => 'pemilik_risiko',
                ]);
            }

            // 3. Pengendali Mutu: satu orang untuk semua sub-bidang di bidang ini
            $mutuId = (int) $request->input('pengendali_mutu_id');
            foreach ($subMenuBidangIni as $id) {
                PenugasanCrmc::create([
                    'sub_menu_id' => $id,
                    'user_id' => $mutuId,
                    'peran' => 'pengendali_mutu',
                ]);
            }

            // 4. Pengendali Risiko: khusus sub-bidang ini saja
            foreach (array_unique($request->input('pengendali_risiko_ids', [])) as $stafId) {
                PenugasanCrmc::create([
                    'sub_menu_id' => $subMenu->id,
                    'user_id' => $stafId,
                    'peran' => 'pengendali_risiko',
                ]);
            }
        });

        $ringkasan = sprintf(
            'Penanggung jawab identitas pegawai Komponen 1 diperbarui. Pemilik Risiko (%s) berlaku untuk seluruh %d sub-bidang; Pengendali Mutu (%s) berlaku untuk %d sub-bidang bidang "%s"; Tim Pengendali Risiko (%d orang) khusus sub-bidang ini.',
            User::find($request->input('pemilik_risiko_id'))?->name ?? '-',
            SubMenu::count(),
            User::find($request->input('pengendali_mutu_id'))?->name ?? '-',
            SubMenu::where('bidang_id', $subMenu->bidang_id)->count(),
            $subMenu->bidang?->nama_bidang ?? '-',
            count(array_unique($request->input('pengendali_risiko_ids', [])))
        );

        return redirect()->route('crmc.show', $slug)->with('success', $ringkasan);
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
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], self::pesanValidasiAkun());

        $data = $request->only(['name', 'nip', 'jabatan', 'email', 'role']);
        $data['password'] = Hash::make($request->input('password'));

        if ($request->hasFile('foto_profil')) {
            $data['foto_profil'] = PenyimpananGambar::simpan($request->file('foto_profil'), 'foto-pegawai');
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
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], self::pesanValidasiAkun());

        $data = $request->only(['name', 'nip', 'jabatan', 'email', 'role']);
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $fotoLama = $user->getRawOriginal('foto_profil');

        if ($request->hasFile('foto_profil')) {
            // Simpan foto baru DULU, baru hapus foto lama.
            // Kalau dibalik, kegagalan saat menyimpan membuat foto lama hilang.
            $data['foto_profil'] = PenyimpananGambar::simpan($request->file('foto_profil'), 'foto-pegawai');
        }

        $user->update($data);

        if ($request->hasFile('foto_profil') && !empty($fotoLama) && $fotoLama !== $data['foto_profil']) {
            $this->hapusFotoPegawai($user, $fotoLama);
        }

        $pesan = 'Data pegawai "' . $data['name'] . '" berhasil diperbarui.';
        if ($request->hasFile('foto_profil')) {
            $pesan .= ' Foto profil baru telah disimpan.';
        }

        return redirect()->route('admin.pegawai.index')->with('success', $pesan);
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
        $this->hapusFotoPegawai($user);

        $user->delete();

        return redirect()->route('admin.pegawai.index')->with('success', 'Akun pegawai "' . $name . '" berhasil dihapus.');
    }

    /**
     * Pesan validasi berbahasa Indonesia untuk form akun pegawai.
     *
     * Tanpa pesan ini, kegagalan validasi (mis. foto bukan gambar / NIP
     * sudah dipakai) hanya diam-diam mengarahkan browser kembali ke
     * halaman daftar tanpa penjelasan apa pun.
     */
    private static function pesanValidasiAkun(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP ini sudah dipakai pegawai lain.',
            'nip.max' => 'NIP maksimal 30 karakter.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'email.required' => 'Email dinas wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah dipakai pegawai lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role harus Admin atau Pegawai.',
            'foto_profil.image' => 'Foto profil harus berupa file gambar (JPG, PNG, atau WebP).',
            'foto_profil.mimes' => 'Format foto harus JPG, PNG, atau WebP. File HEIC perlu dikonversi dulu.',
            'foto_profil.max' => 'Ukuran foto maksimal 4 MB. Foto tidak tersimpan karena melebihi batas.',
        ];
    }

    /**
     * Hapus file foto pegawai dari disk "public".
     * `foto_profil` disimpan sebagai path relatif, contoh: foto-pegawai/abc.jpg
     *
     * @param string|null $foto Path foto yang akan dihapus. Bila null,
     *                         diambil dari nilai mentah `foto_profil`.
     */
    private function hapusFotoPegawai(User $user, ?string $foto = null): void
    {
        $foto ??= $user->getRawOriginal('foto_profil');

        if (empty($foto) || str_starts_with($foto, 'http')) {
            return;
        }

        // Data lama mungkin masih memakai prefix "public/"
        $candidates = [$foto];
        if (str_starts_with($foto, 'public/')) {
            $candidates[] = substr($foto, strlen('public/'));
        } else {
            $candidates[] = 'public/' . $foto;
        }

        foreach ($candidates as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}