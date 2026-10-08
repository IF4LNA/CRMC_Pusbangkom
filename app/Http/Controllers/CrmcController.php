<?php

namespace App\Http\Controllers;

use App\Models\SubMenu;
use App\Models\Bidang;
use App\Models\DokumenCrmc;
use App\Models\LampiranCrmc;
use App\Models\PenugasanCrmc;
use App\Models\TahunAnggaran;
use App\Models\TautanDriveCrmc;
use App\Models\User;
use App\Support\PenyimpananGambar;
use App\Support\SkalaResiduRisiko;
use App\Support\TautanGoogleDrive;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CrmcController extends Controller
{
    /**
     * Komponen yang bisa diisi dokumen + keterangan oleh Admin/Pegawai.
     * Dipakai bersama oleh show(), uploadDokumen(), dan view.
     */
    private const KOMPONEN_UPLOAD = [
        'risk_register'         => ['nomor' => 2, 'judul' => 'Risk Register Spesifik Acuan', 'ikon' => 'file-spreadsheet'],
        'sop'                   => ['nomor' => 3, 'judul' => 'Standar Operasional Prosedur (SOP)', 'ikon' => 'book-open'],
        'formulir_pengendalian' => ['nomor' => 4, 'judul' => 'Formulir Pengendalian / Daftar Periksa', 'ikon' => 'clipboard-check'],
        'jadwal_pelaksanaan'    => ['nomor' => 5, 'judul' => 'Jadwal Rencana Pelaksanaan', 'ikon' => 'calendar'],
        'bukti_pelaksanaan'     => ['nomor' => 6, 'judul' => 'Bukti Pelaksanaan', 'ikon' => 'link-2'],
        'evaluasi'              => ['nomor' => 8, 'judul' => 'Evaluasi & Rencana Perbaikan', 'ikon' => 'trending-up'],
    ];

    /**
     * Komponen 1 (identitas pegawai) sebagai komponen dokumen.
     *
     * Identitas Pegawai biasanya bukan sekadar daftar nama: kartu itu sering
     * dilampiri SK/KP, surat keputusan, atau dokumen delegasi. Karena itu
     * Komponen 1 memakai tombol Upload dan tautan Google Drive yang sama
     * dengan Komponen 2-6 dan 8.
     *
     * Dipisahkan dari KOMPONEN_UPLOAD supaya urutan tampilan di halaman tetap
     * 1 -> 2 -> ... -> 8, sementara daftar kategori upload dan tautan Google
     * Drive tetap bisa memanggil semuanya lewat daftarKategoriDokumen().
     */
    private const KOMPONEN_IDENTITAS = [
        'identitas_pegawai' => ['nomor' => 1, 'judul' => 'Identitas Pegawai', 'ikon' => 'user-round'],
    ];

    /**
     * Semua komponen yang bisa menyimpan berkas: Komponen 1 dan Komponen
     * 2,3,4,5,6,8.
     *
     * Komponen 7 tidak termasuk karena isinya status residu (satu nilai
     * status per tahun), bukan kumpulan dokumen.
     *
     * @return array<string, array{nomor: int, judul: string, ikon: string}>
     */
    private function daftarKategoriDokumen(): array
    {
        return self::KOMPONEN_IDENTITAS + self::KOMPONEN_UPLOAD;
    }

    /**
     * Daftar tahun yang bisa dipilih pada halaman 8 Komponen.
     *
     * Gabungan empat sumber:
     *   1. Otomatis  : tahun berjalan + 1 tahun ke depan (untuk perencanaan),
     *                  sehingga tahun baru muncul sendiri saat pergantian tahun
     *                  tanpa perlu ada action dari admin.
     *   2. Data      : tahun yang sudah punya dokumen di sub-bidang ini.
     *   3. Manual    : tahun yang ditambahkan Admin lewat tombol "Tambah Tahun",
     *                  untuk tahun khusus di luar rentang otomatis.
     *   4. Penugasan & tautan Drive: tahun yang sudah dipakai Komponen 1
     *                  atau Komponen 2-6/8 walau belum ada berkasnya.
     *
     * @return array<int> daftar tahun urut menaik
     */
    private function daftarTahun(?SubMenu $subMenu = null): array
    {
        $tahunSekarang = (int) date('Y');

        $dariDokumen = DokumenCrmc::query()
            ->when($subMenu, fn ($q) => $q->where('sub_menu_id', $subMenu->id))
            ->distinct()
            ->pluck('tahun_pelaksanaan')
            ->all();

        $dariAdmin = TahunAnggaran::pluck('tahun')->all();

        // Komponen 1 (identitas pegawai) juga bisa diisi lebih awal, sebelum
        // ada dokumen maupun tahun yang sengaja ditambahkan admin. Kalau
        // tahun penugasan tidak ikut ke daftar, admin tidak akan pernah
        // bisa membuka tahun itu lewat halaman.
        $dariPenugasan = $subMenu
            ? PenugasanCrmc::where('sub_menu_id', $subMenu->id)->distinct()->pluck('tahun_pelaksanaan')->all()
            : PenugasanCrmc::distinct()->pluck('tahun_pelaksanaan')->all();

        // Sama seperti penugasan: tautan Google Drive boleh diisi lebih dulu,
        // sebelum ada berkas apa pun, jadi tahun yang dipakainya harus tetap
        // bisa dibuka lewat halaman.
        $dariTautan = $subMenu
            ? TautanDriveCrmc::where('sub_menu_id', $subMenu->id)->distinct()->pluck('tahun_pelaksanaan')->all()
            : TautanDriveCrmc::distinct()->pluck('tahun_pelaksanaan')->all();

        return collect([$tahunSekarang, $tahunSekarang + 1])
            ->merge($dariDokumen)
            ->merge($dariAdmin)
            ->merge($dariPenugasan)
            ->merge($dariTautan)
            ->map(fn ($t) => (int) $t)
            // Batas atas longgar sampai 2100, sama dengan batas validasi
            // tambahTahun(). Kalau lebih kecil, tahun yang berhasil
            // ditambahkan admin tetap tidak muncul di dropdown.
            ->filter(fn ($t) => $t >= 2000 && $t <= 2100)
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    public function show(Request $request, $slug)
    {
        // Cari SubMenu di database yang cocok dengan nama atau format slug.
        // Sengaja memakai cariSubMenuTersedia() (tidak membuat baris baru),
        // sehingga sub-bidang yang sudah admin hapus tidak muncul lagi
        // hanya karena tautan lamanya dibuka.
        $subMenu = null;
        try {
            $subMenu = $this->cariSubMenuTersedia($slug);
        } catch (\Throwable $e) {
            $subMenu = null;
        }

        // Hanya nama bidang yang dimuat di sini. Penugasan sengaja dimuat
        // terpisah setelah tahun ditentukan (lihat bawah), supaya yang
        // terbaca benar-benar penugasan tahun yang sedang dibuka.
        $subMenu?->load('bidang');

        // Tentukan nama sub-bidang & parent bidang
        if ($subMenu) {
            $subBidangName = $subMenu->nama_sub_menu;
            $parentBidang = $subMenu->bidang?->nama_bidang ?? 'Bagian Umum Program & Tata Usaha';
        } else {
            $subBidangName = Str::headline($slug);

            // Prediksi parent kategori berdasarkan nama
            if (str_contains(strtolower($subBidangName), 'sda')) {
                $parentBidang = 'Bidang SDA';
            } elseif (str_contains(strtolower($subBidangName), 'ckps')) {
                $parentBidang = 'Bidang CKPS';
            } else {
                $parentBidang = 'Bagian Umum Program & Tata Usaha';
            }
        }

        // === TAHUN ===
        // Ditentukan lebih dulu karena Komponen 1 (identitas pegawai) punya
        // tahun sendiri: penugasan PIC berbeda tiap tahun, jadi kartu ini
        // harus menampilkan orang yang bertugas pada tahun yang sedang dibuka,
        // bukan penugasan terakhir yang tersimpan.
        $daftarTahun = $this->daftarTahun($subMenu);

        // Tahun default = tahun BERJALAN, bukan tahun terbaru di daftar.
        //
        // `daftarTahun()` sengaja memuat tahun berjalan + 1 tahun ke depan
        // supaya admin bisa merencanakan tahun berikutnya. Kalau default
        // memakai entri terakhir, halaman selalu terbuka di tahun depan,
        // padahal belum ada dokumen maupun penugasan untuk tahun itu.
        $tahunSekarang = (int) date('Y');
        $defaultTahun = in_array($tahunSekarang, $daftarTahun, true)
            ? $tahunSekarang
            : ($daftarTahun[count($daftarTahun) - 1] ?? $tahunSekarang);

        $selectedTahun = (int) $request->input('tahun', $defaultTahun);
        if (!in_array($selectedTahun, $daftarTahun, true)) {
            $selectedTahun = $defaultTahun;
        }

        // === IDENTITAS PEGAWAI TAHUN INI (KOMPONEN 1) ===
        // Penugasan dibatasi pada $selectedTahun. Penugasan tetap ditulis ke
        // setiap sub-bidang yang relevan (lihat updatePenugasan), jadi cukup
        // satu baris per peran yang terambil di sini.
        $penugasanTahunIni = $subMenu
            ? $subMenu->penugasan()->with('user')->tahun($selectedTahun)->get()
            : collect();

        // 1. Pemilik Risiko: HANYA ADA 1 ORANG untuk seluruh CRMC.
        $pemilikRisiko = $penugasanTahunIni->firstWhere('peran', 'pemilik_risiko')?->user;

        // 2. Pengendali Mutu: HANYA ADA 1 ORANG per bidang, berlaku untuk
        //    semua sub-bidang di bawah bidang tersebut.
        $pengendaliMutu = $penugasanTahunIni->firstWhere('peran', 'pengendali_mutu')?->user;

        // 3. Pengendali Risiko: BISA BANYAK ORANG (Multiple Risk Controllers)
        // Hanya menampilkan yang benar-benar ditugaskan. Tanpa penugasan ->
        // kosong, dan view menampilkan empty state (bukan daftar semua pegawai).
        $pengendaliRisikoList = $penugasanTahunIni
            ->where('peran', 'pengendali_risiko')
            ->map(fn ($p) => $p->user)
            ->filter()
            ->unique('id')
            ->values();

        // Setiap komponen punya pilihan tahun sendiri (?t[kategori]=YYYY),
        // sehingga admin/pegawai bisa membandingkan dokumen antar tahun
        // pada satu halaman. Kalau tidak diisi, memakai tahun default di atas.
        $tahunPerKomponen = [];
        foreach (array_keys(self::KOMPONEN_UPLOAD) as $kategori) {
            $tahun = (int) $request->input("t.{$kategori}", $selectedTahun);
            $tahunPerKomponen[$kategori] = in_array($tahun, $daftarTahun, true) ? $tahun : $selectedTahun;
        }

        // Komponen 7 (residu) juga mengikuti tahun, sama seperti komponen dokumen.
        $tahunResidu = (int) $request->input('t.residu', $selectedTahun);
        if (!in_array($tahunResidu, $daftarTahun, true)) {
            $tahunResidu = $selectedTahun;
        }

        // === DOKUMEN ===
        // Muat dokumen sub-bidang ini beserta seluruh lampirannya sekali saja,
        // lalu kelompokkan per (tahun, kategori) supaya tiap komponen bisa
        // menampilkan dokumen sesuai tahun yang dipilihnya.
        $dokumenTahun = collect();
        $lampiranPerTahun = [];

        if ($subMenu) {
            $dokumenTahun = DokumenCrmc::with('lampiran')
                ->where('sub_menu_id', $subMenu->id)
                ->orderByDesc('tahun_pelaksanaan')
                ->get()
                ->keyBy('tahun_pelaksanaan');

            foreach ($dokumenTahun as $tahun => $dokumen) {
                foreach ($dokumen->lampiran->groupBy('kategori_komponen') as $kategori => $list) {
                    $lampiranPerTahun[(int) $tahun][$kategori] = $list->values();
                }
            }
        }

        // === TAUTAN GOOGLE DRIVE PER KOMPONEN ===
        // Satu tautan untuk satu (tahun, komponen). Dikelompokkan dengan
        // bentuk yang sama seperti $lampiranPerTahun supaya view cukup
        // menulis $tautanDrive[$tahun][$kategori].
        $tautanDrive = [];
        if ($subMenu) {
            foreach (TautanDriveCrmc::where('sub_menu_id', $subMenu->id)->get() as $tautan) {
                $tautanDrive[(int) $tautan->tahun_pelaksanaan][$tautan->kategori_komponen] = $tautan;
            }
        }

        // Rakit data tiap komponen: tahun terpilih + dokumen tahun itu
        $komponen = [];
        foreach (self::KOMPONEN_UPLOAD as $kategori => $meta) {
            $tahun = $tahunPerKomponen[$kategori];
            $komponen[$kategori] = [
                'nomor' => $meta['nomor'],
                'judul' => $meta['judul'],
                'ikon' => $meta['ikon'],
                'tahun' => $tahun,
                'tahunTersedia' => $daftarTahun,
                'lampiran' => $lampiranPerTahun[$tahun][$kategori] ?? collect(),
            ];
        }

        // Komponen 1 punya bentuk data yang sama dengan komponen dokumen lain,
        // sehingga view bisa memakai partial daftar dokumen yang sama. Tahun
        // lampirannya ikut tahun identitas di atas, jadi berkas yang tampil
        // selalu milik tahun penugasan yang sedang dibaca.
        $kategoriIdentitas = array_key_first(self::KOMPONEN_IDENTITAS);
        $metaIdentitas = self::KOMPONEN_IDENTITAS[$kategoriIdentitas];
        $komponenIdentitas = [
            'kategori' => $kategoriIdentitas,
            'nomor' => $metaIdentitas['nomor'],
            'judul' => $metaIdentitas['judul'],
            'ikon' => $metaIdentitas['ikon'],
            'tahun' => $selectedTahun,
            'tahunTersedia' => $daftarTahun,
            'lampiran' => $lampiranPerTahun[$selectedTahun][$kategoriIdentitas] ?? collect(),
        ];

        // Status residu untuk tahun yang dipilih pada Komponen 7
        $residuTerpilih = [
            'tahun' => $tahunResidu,
            'tahunTersedia' => $daftarTahun,
            'nilai' => $dokumenTahun->get($tahunResidu)?->status_residu_risiko,
            'rangkumanEvaluasi' => $dokumenTahun->get($tahunResidu)?->evaluasi_dan_rencana,
        ];

        // Ringkasan nyata (dihitung dari dokumen, bukan angka dummy)
        $dokumenTahunTerpilih = $dokumenTahun->get($selectedTahun);

        // PENTING: rangkuman selalu dihitung untuk $selectedTahun (tahun
        // default halaman), bukan tahun per-komponen. Kalau dihitung dari
        // tahun per-komponen, angkanya tidak akan cocok dengan label
        // "Kelengkapan Tahun X" padahal tiap komponen bisa menampilkan
        // tahun yang berbeda.
        //
        // Komponen 1 ikut dihitung karena sejak sekarang kartu identitas juga
        // bisa menyimpan berkas. Komponen 7 tidak masuk, karena isinya status
        // residu dan bukan dokumen, sehingga totalnya 7 (bukan 8).
        $semuaKategoriDokumen = $this->daftarKategoriDokumen();

        $komponenTerisi = collect($semuaKategoriDokumen)
            ->filter(fn ($meta, $kategori) => ($lampiranPerTahun[$selectedTahun][$kategori] ?? collect())->isNotEmpty())
            ->keys();

        $rangkuman = [
            'tahun' => $selectedTahun,
            'totalDokumen' => (int) $dokumenTahunTerpilih?->lampiran?->count(),
            'komponenTerisi' => $komponenTerisi->count(),
            'totalKomponen' => count($semuaKategoriDokumen),
            'jumlahTahun' => count($daftarTahun),
            'totalSubMenu' => SubMenu::count(),
            'tahunAdaData' => $dokumenTahun->keys()->map(fn ($t) => (int) $t)->all(),
        ];

        // Daftar user & tahun manual hanya dipakai di panel admin. Ambil
        // dengan when() supaya pegawai & pengunjung tidak memuat 75 baris
        // user dan tabel tahun yang tidak akan ditampilkan.
        $isAdmin = Auth::check() && Auth::user()->isAdmin();

        $allUsers = $isAdmin
            ? User::orderBy('name')->get()
            : collect();

        $tahunManual = $isAdmin
            ? TahunAnggaran::orderByDesc('tahun')->get()
            : collect();

        // Tahun yang sedang dibuka pada Komponen 1 diberi nama terpisah dari
        // $selectedTahun, supaya form penugasan dan badge tahun jelas merujuk
        // tahun identitas, bukan tahun dokumen.
        $tahunPenugasan = $selectedTahun;

        return view('crmc.show', compact(
            'subBidangName',
            'parentBidang',
            'slug',
            'subMenu',
            'pemilikRisiko',
            'pengendaliMutu',
            'pengendaliRisikoList',
            'allUsers',
            'selectedTahun',
            'daftarTahun',
            'tahunPenugasan',
            'dokumenTahun',
            'dokumenTahunTerpilih',
            'komponen',
            'komponenIdentitas',
            'tautanDrive',
            'residuTerpilih',
            'rangkuman',
            'tahunManual',
            'isAdmin',
        ));
    }

    /**
     * Simpan data ringkasan CRMC untuk satu tahun (rangkuman evaluasi / residu).
     *
     * Nilai kosong tidak lagi diisi default dummy. Null berarti "belum diisi"
     * dan view menampilkannya sebagai empty state, bukan data karangan.
     */
    public function update(Request $request, $slug)
    {
        if (!Auth::check()) {
            abort(403, 'Anda harus login untuk memperbarui data CRMC.');
        }

        $request->validate([
            'residu' => ['nullable', 'string', 'in:' . implode(',', SkalaResiduRisiko::nilaiValid())],
            'evaluasi' => 'nullable|string|max:5000',
            'tahun_pelaksanaan' => 'required|numeric|min:2000|max:2100',
        ]);

        try {
            // Sama seperti show(): sub-bidang dicari lewat normalisasi slug,
            // bukan LIKE, supaya nama bertanda baca (mis. "Kerjasama
            // Pendidikan (MSS)") tetap ketemu.
            $subMenu = $this->cariSubMenuTersedia($slug);
        } catch (\Throwable $e) {
            // Abaikan error DB jika tabel tidak tersedia
            $subMenu = null;
        }

        // Slug yang tidak dikenal harus ditolak, bukan diam-diam diabaikan:
        // kalau tidak, admin mengira ringkasan tersimpan padahal tidak ada.
        abort_if($subMenu === null, 404, 'Sub-bidang tidak ditemukan.');

        try {
            DokumenCrmc::updateOrCreate(
                [
                    'sub_menu_id' => $subMenu->id,
                    'tahun_pelaksanaan' => $request->input('tahun_pelaksanaan'),
                ],
                [
                    'status_residu_risiko' => $request->input('residu') ?: null,
                    'evaluasi_dan_rencana' => $request->input('evaluasi') ?: null,
                ]
            );
        } catch (\Throwable $e) {
            // Abaikan error DB jika tabel tidak tersedia
        }

        return redirect()
            ->route('crmc.show', ['slug' => $slug, 'tahun' => $request->input('tahun_pelaksanaan')])
            ->with('success', 'Data CRMC berhasil diperbarui.');
    }

    /**
     * Upload Dokumen per Komponen (Multi-file Upload).
     * Pegawai & Admin bisa upload di Komponen 1,2,3,4,5,6,8.
     *
     * Setiap file boleh punya "keterangan" sendiri. Input keterangan dikirim
     * sebagai array bernomor yang indeksnya sama dengan indeks file, jadi
     * keterangan file ke-2 menempel pada file ke-2, bukan file pertama.
     */
    public function uploadDokumen(Request $request, $slug)
    {
        if (!Auth::check()) {
            abort(403, 'Anda harus login untuk mengunggah dokumen.');
        }

        $request->validate([
            'kategori_komponen' => 'required|string|max:100',
            'tahun_pelaksanaan' => 'required|numeric|min:2000|max:2100',
            'files' => 'required|array|min:1',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,webp|max:10240',
            'keterangan' => 'nullable|array',
            'keterangan.*' => 'nullable|string|max:1000',
        ], [
            'files.required' => 'Pilih minimal 1 file untuk diunggah.',
            'files.*.mimes' => 'Format file harus PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, atau WebP.',
            'files.*.max' => 'Ukuran file maksimal 10MB per file.',
            'keterangan.*.max' => 'Keterangan maksimal 1000 karakter.',
        ]);

        $kategori = $request->input('kategori_komponen');

        // Batasi kategori ke komponen yang memang punya slot upload, supaya
        // request buatan tangan tidak bisa menulis kategori bebas ke tabel.
        $semuaKategoriDokumen = $this->daftarKategoriDokumen();

        if (!array_key_exists($kategori, $semuaKategoriDokumen)) {
            abort(422, 'Komponen tidak dikenal.');
        }

        $subMenu = $this->cariSubMenu($slug);

        $tahun = (int) $request->input('tahun_pelaksanaan');
        $keterangan = $request->input('keterangan', []);

        // Buat/dapatkan dokumen untuk tahun ini. Residu & evaluasi dibiarkan
        // kosong supaya tidak ada nilai default yang menyesatkan di Komponen 7.
        $dokumen = DokumenCrmc::firstOrCreate(
            [
                'sub_menu_id' => $subMenu->id,
                'tahun_pelaksanaan' => $tahun,
            ],
            []
        );

        $folder = "crmc/{$tahun}/{$subMenu->id}/{$kategori}";
        $jumlahBerhasil = 0;
        $keteranganTersimpan = 0;

        // Upload setiap file
        foreach ($request->file('files') as $index => $file) {
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();

            $path = $file->storeAs(
                $folder,
                time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension,
                'public'
            );

            // Keterangan diambil berdasarkan indeks file yang sama.
            $ket = trim((string) ($keterangan[$index] ?? ''));
            if ($ket !== '') {
                $keteranganTersimpan++;
            }

            LampiranCrmc::create([
                'dokumen_crmc_id' => $dokumen->id,
                'kategori_komponen' => $kategori,
                'nama_file' => $originalName,
                'keterangan' => $ket !== '' ? $ket : null,
                'file_path' => Storage::disk('public')->url($path),
                'tipe_file' => strtolower($extension),
            ]);

            $jumlahBerhasil++;
        }

        $judul = $semuaKategoriDokumen[$kategori]['judul'];

        $pesan = "{$jumlahBerhasil} dokumen berhasil diunggah pada Komponen {$this->nomorKomponen($kategori)} — {$judul} — tahun {$tahun}.";
        if ($keteranganTersimpan > 0) {
            $pesan .= " {$keteranganTersimpan} berkas disertai keterangan.";
        }

        return redirect()
            ->route('crmc.show', $this->urlTujuanKomponen($slug, $kategori, $tahun))
            ->with('success', $pesan);
    }

    /**
     * Simpan tautan Google Drive untuk satu Komponen (Pegawai & Admin).
     *
     * Satu komponen hanya boleh punya satu tautan per tahun, jadi penyimpanannya
     * memakai updateOrCreate pada kunci (sub-bidang, tahun, komponen). Kalau
     * tautan sebelumnya ada, isinya diperbarui, bukan digandakan.
     *
     * Komponen 7 tidak punya tautan: isinya status residu, bukan berkas.
     */
    public function simpanTautanDrive(Request $request, $slug)
    {
        if (!Auth::check()) {
            abort(403, 'Anda harus login untuk menyimpan tautan Google Drive.');
        }

        // Jangan pakai cariSubMenu(): fungsi itu membuat sub-bidang baru saat
        // slug tidak dikenal, sehingga satu ketikalan URL menyisakan baris
        // sampah di database.
        $subMenu = $this->cariSubMenuTersedia($slug);
        abort_if($subMenu === null, 404, 'Sub-bidang tidak ditemukan.');

        $request->validate([
            'kategori_komponen' => [
                'required',
                'string',
                'max:50',
                Rule::in(array_keys($this->daftarKategoriDokumen())),
            ],
            'tahun_pelaksanaan' => 'required|numeric|min:2000|max:2100',
            'label' => 'nullable|string|max:120',
            'url' => [
                'required',
                'string',
                'max:500',
                function ($attribute, $value, $fail) {
                    if (TautanGoogleDrive::normalisasi($value) === null) {
                        $fail(TautanGoogleDrive::pesanValidasi()['google']);
                    }
                },
            ],
        ], array_merge(TautanGoogleDrive::pesanValidasi(), [
            'kategori_komponen.required' => 'Komponen tautan Google Drive belum dipilih.',
            'kategori_komponen.in' => 'Komponen tidak dikenal.',
            'tahun_pelaksanaan.required' => 'Tahun tautan wajib dipilih.',
        ]));

        $kategori = (string) $request->input('kategori_komponen');
        $tahun = (int) $request->input('tahun_pelaksanaan');
        $url = TautanGoogleDrive::normalisasi($request->input('url'));
        $label = trim((string) $request->input('label'));

        TautanDriveCrmc::updateOrCreate(
            [
                'sub_menu_id' => $subMenu->id,
                'tahun_pelaksanaan' => $tahun,
                'kategori_komponen' => $kategori,
            ],
            [
                'url' => $url,
                'label' => $label !== '' ? $label : null,
                'user_id' => Auth::id(),
            ]
        );

        return redirect()
            ->route('crmc.show', $this->urlTujuanKomponen($slug, $kategori, $tahun))
            ->with('success', "Tautan Google Drive Komponen {$this->nomorKomponen($kategori)} tahun {$tahun} berhasil disimpan.");
    }

    /**
     * Hapus tautan Google Drive satu Komponen.
     *
     * Admin boleh menghapus tautan mana pun. Pegawai hanya boleh menghapus
     * tautan yang ia isi sendiri, supaya tautan yang disusun unit lain tidak
     * hilang tanpa sebab.
     */
    public function hapusTautanDrive($id)
    {
        if (!Auth::check()) {
            abort(403, 'Anda harus login untuk menghapus tautan Google Drive.');
        }

        $tautan = TautanDriveCrmc::with('subMenu')->findOrFail($id);

        if (!Auth::user()->isAdmin() && $tautan->user_id !== Auth::id()) {
            abort(403, 'Hanya Admin atau pembuat tautan yang dapat menghapus tautan ini.');
        }

        $kategori = $tautan->kategori_komponen;
        $tahun = (int) $tautan->tahun_pelaksanaan;
        $nomor = $this->nomorKomponen($kategori);
        $slug = $tautan->subMenu?->slug;

        $tautan->delete();

        // Tautan bisa tetap ada setelah sub-bidangnya dihapus. Kalau begitu
        // tidak ada halaman tujuan yang bisa dibuka lagi, jadi kembalikan
        // pengguna ke dashboard.
        if ($slug === null) {
            return redirect()
                ->route('crmc.dashboard')
                ->with('success', "Tautan Google Drive Komponen {$nomor} tahun {$tahun} berhasil dihapus.");
        }

        return redirect()
            ->route('crmc.show', $this->urlTujuanKomponen($slug, $kategori, $tahun))
            ->with('success', "Tautan Google Drive Komponen {$nomor} tahun {$tahun} berhasil dihapus.");
    }

    /**
     * Susun parameter URL halaman 8 Komponen untuk satu komponen tertentu.
     *
     * Komponen 1 memakai parameter "tahun" (dipakai juga untuk penugasan
     * pegawai), sedangkan komponen lain memakai "t[kategori]" supaya pilihan
     * tahun tiap komponen tetap terpisah.
     *
     * @return array<string, mixed>
     */
    private function urlTujuanKomponen(string $slug, string $kategori, int $tahun): array
    {
        $kategoriIdentitas = array_key_first(self::KOMPONEN_IDENTITAS);

        if ($kategori === $kategoriIdentitas) {
            return ['slug' => $slug, 'tahun' => $tahun];
        }

        return ['slug' => $slug, 'tahun' => $tahun, 't' => [$kategori => $tahun]];
    }

    /**
     * Perbarui keterangan satu lampiran (Admin & Pegawai).
     */
    public function updateKeterangan(Request $request, $id)
    {
        if (!Auth::check()) {
            abort(403, 'Anda harus login untuk mengubah keterangan.');
        }

        $request->validate([
            'keterangan' => 'nullable|string|max:1000',
        ], [
            'keterangan.max' => 'Keterangan maksimal 1000 karakter.',
        ]);

        $lampiran = LampiranCrmc::findOrFail($id);

        $keterangan = trim((string) $request->input('keterangan'));
        $lampiran->update(['keterangan' => $keterangan !== '' ? $keterangan : null]);

        $tahun = $lampiran->dokumenCrmc?->tahun_pelaksanaan;
        $kategori = $lampiran->kategori_komponen;
        $slug = $lampiran->dokumenCrmc?->subMenu
            ? Str::slug($lampiran->dokumenCrmc->subMenu->nama_sub_menu)
            : null;

        return redirect()
            ->route('crmc.show', array_filter([
                'slug' => $slug,
                'tahun' => $tahun,
                't' => $kategori ? [$kategori => $tahun] : null,
            ]))
            ->with('success', 'Keterangan "' . $lampiran->nama_file . '" berhasil diperbarui.');
    }

    // ========================
    // ADMIN: Management Tahun
    // ========================

    /**
     * Tambah tahun anggaran baru di luar rentang otomatis (Khusus Admin).
     */
    public function tambahTahun(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator yang dapat menambah tahun.');
        }

        $request->validate([
            'tahun' => 'required|numeric|min:2000|max:2100|unique:tahun_anggaran,tahun',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'tahun.required' => 'Tahun wajib diisi.',
            'tahun.numeric' => 'Tahun harus berupa angka.',
            'tahun.min' => 'Tahun minimal 2000.',
            'tahun.max' => 'Tahun maksimal 2100.',
            'tahun.unique' => 'Tahun ini sudah ada di daftar tahun tambahan.',
        ]);

        $tahun = (int) $request->input('tahun');
        $keterangan = trim((string) $request->input('keterangan'));

        if (TahunAnggaran::where('tahun', $tahun)->exists()) {
            return back()->with('error', "Tahun {$tahun} sudah ada di daftar tahun tambahan.");
        }

        TahunAnggaran::create([
            'tahun' => $tahun,
            'keterangan' => $keterangan !== '' ? $keterangan : null,
        ]);

        return back()->with('success', "Tahun {$tahun} berhasil ditambahkan ke daftar pilihan tahun.");
    }

    /**
     * Hapus tahun tambahan yang ditambahkan Admin (Khusus Admin).
     *
     * Hanya menghapus entri daftar tahun. Dokumen tahun tersebut TIDAK ikut
     * terhapus -- penghapusan dokumen tetap dilakukan lewat aksi hapus dokumen
     * per tahun supaya tidak ada kehilangan data yang tidak disengaja.
     */
    public function hapusTahun($id)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator yang dapat menghapus tahun.');
        }

        $tahunAnggaran = TahunAnggaran::findOrFail($id);
        $tahun = $tahunAnggaran->tahun;

        $jumlahDokumen = DokumenCrmc::where('tahun_pelaksanaan', $tahun)->count();

        $tahunAnggaran->delete();

        $pesan = "Tahun {$tahun} dihapus dari daftar tahun tambahan.";
        if ($jumlahDokumen > 0) {
            $pesan .= " Catatan: {$jumlahDokumen} dokumen tahun {$tahun} tidak ikut terhapus.";
        }

        return back()->with('success', $pesan);
    }

    // ========================
    // ADMIN: Hapus Dokumen per Tahun
    // ========================

    /**
     * Hapus seluruh dokumen satu sub-bidang pada satu tahun (Khusus Admin).
     *
     * Menghapus record lampiran, record dokumen, dan file fisik di disk.
     */
    public function hapusDokumenTahunSubBidang(Request $request, $slug)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator yang dapat menghapus dokumen per tahun.');
        }

        $request->validate([
            'tahun' => 'required|numeric|min:2000|max:2100',
            'kategori' => 'nullable|string|max:100',
        ], [
            'tahun.required' => 'Tahun wajib dipilih.',
        ]);

        $kategori = $request->input('kategori');
        if ($kategori !== null && $kategori !== '' && !array_key_exists($kategori, $this->daftarKategoriDokumen())) {
            abort(422, 'Komponen tidak dikenal.');
        }

        // Jangan pakai cariSubMenu() di sini: fungsi itu membuat sub-bidang
        // baru kalau slug tidak dikenal, sehingga satu ketikalan URL akan
        // menyisakan baris sampah di database.
        $subMenu = $this->cariSubMenuTersedia($slug);
        abort_if($subMenu === null, 404, 'Sub-bidang tidak ditemukan.');

        $tahun = (int) $request->input('tahun');

        $hasil = $this->hapusDokumen([
            'sub_menu_id' => $subMenu->id,
            'tahun_pelaksanaan' => $tahun,
        ], $kategori ?: null);

        if ($hasil['file'] === 0) {
            return back()->with('error', "Tidak ada dokumen tahun {$tahun} yang bisa dihapus pada sub-bidang ini.");
        }

        $rujukan = $kategori
            ? 'Komponen ' . $this->nomorKomponen($kategori) . ' (' . $this->judulKomponen($kategori) . ')'
            : 'seluruh komponen';

        return back()->with(
            'success',
            "{$hasil['file']} berkas tahun {$tahun} pada sub-bidang \"{$subMenu->nama_sub_menu}\" — {$rujukan} — berhasil dihapus."
        );
    }

    /**
     * Hapus seluruh dokumen tahun tertentu di SEMUA sub-bidang (Khusus Admin).
     */
    public function hapusDokumenTahunSemua(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator yang dapat menghapus dokumen per tahun.');
        }

        $request->validate([
            'tahun' => 'required|numeric|min:2000|max:2100',
        ], [
            'tahun.required' => 'Tahun wajib dipilih.',
        ]);

        $tahun = (int) $request->input('tahun');
        $hasil = $this->hapusDokumen(['tahun_pelaksanaan' => $tahun]);

        if ($hasil['file'] === 0) {
            return back()->with('error', "Tidak ada dokumen tahun {$tahun} di seluruh sub-bidang, jadi tidak ada yang dihapus.");
        }

        return back()->with(
            'success',
            "{$hasil['file']} berkas tahun {$tahun} dari {$hasil['sub_bidang']} sub-bidang berhasil dihapus (termasuk file fisik di storage)."
        );
    }

    /**
     * Kerjakan penghapusan dokumen berdasarkan filter, sekalian file fisiknya.
     *
     * $kategori hanya ada di tabel lampiran_crmc, bukan dokumen_crmc. Kalau
     * kategori diberikan, dokumen induknya tetap dipertahankan supaya status
     * residu dan ringkasan evaluasinya tidak ikut hilang.
     *
     * @param  array<string, mixed>  $filter  kolom dokumen_crmc: sub_menu_id, tahun_pelaksanaan
     * @param  string|null  $kategori  batasi ke satu kategori komponen
     * @return array{file:int, dokumen:int, sub_bidang:int, lampiran:int}
     */
    private function hapusDokumen(array $filter, ?string $kategori = null): array
    {
        $dokumenQuery = DokumenCrmc::query()->where($filter);
        $dokumenIds = (clone $dokumenQuery)->pluck('id');

        // Kumpulkan lampiran yang akan dihapus (seluruh dokumen, atau satu kategori).
        $lampiranQuery = LampiranCrmc::query()
            ->whereIn('dokumen_crmc_id', $dokumenIds)
            ->when($kategori, fn ($q) => $q->where('kategori_komponen', $kategori));

        $paths = (clone $lampiranQuery)->pluck('file_path')
            ->map(fn ($p) => $this->pathDariUrl($p))
            ->filter()
            ->unique()
            ->all();

        $jumlahLampiran = (clone $lampiranQuery)->count();
        $jumlahSubMenu = (clone $dokumenQuery)->distinct()->count('sub_menu_id');
        $jumlahDokumen = $dokumenIds->count();

        // Hapus file fisik terlebih dahulu. Kalau gagal di tengah, record DB
        // masih utuh sehingga admin bisa mencoba ulang tanpa kehilangan data.
        foreach ($paths as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        // Baris lampiran dihapus eksplisit supaya aman pada instalasi lama
        // yang belum punya cascade di skema.
        (clone $lampiranQuery)->delete();

        // Dokumen induk hanya dihapus kalau tidak ada lampiran tersisa, supaya
        // status residu tetap utuh saat hanya satu komponen yang dihapus.
        foreach ($dokumenIds as $id) {
            if (!LampiranCrmc::where('dokumen_crmc_id', $id)->exists()) {
                DokumenCrmc::where('id', $id)->delete();
            }
        }

        return [
            'file' => $jumlahLampiran,
            'dokumen' => $jumlahDokumen,
            'sub_bidang' => $jumlahSubMenu,
            'lampiran' => $jumlahLampiran,
        ];
    }

    /**
     * Ubah URL absolut hasil Storage::url() menjadi path relatif di disk.
     */
    private function pathDariUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // Path relatif yang tersimpan rapi sejak awal.
        if (!Str::startsWith($url, ['http://', 'https://', '/'])) {
            return ltrim($url, '/');
        }

        $prefix = parse_url(Storage::disk('public')->url(''), PHP_URL_PATH) ?: '/storage';
        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        if (Str::startsWith($path, rtrim($prefix, '/'))) {
            return ltrim(Str::after($path, rtrim($prefix, '/')), '/');
        }

        // Fallback: ambil semua yang setelah "/storage/".
        if (Str::contains($path, '/storage/')) {
            return Str::after($path, '/storage/');
        }

        return ltrim($path, '/');
    }

    /**
     * Cari SubMenu berdasarkan slug, buat otomatis bila belum ada.
     *
     * HATI-HATI: fungsi ini menulis ke database. Jangan dipakai di jalur yang
     * hanya membaca/mengubah, karena satu ketikalan URL akan membuat
     * sub-bidang baru. Untuk jalur hapus & ubah residu, pakai
     * cariSubMenuTersedia() yang menolak dengan 404.
     */
    private function cariSubMenu(string $slug): SubMenu
    {
        $subMenu = $this->cariSubMenuTersedia($slug);

        if (!$subMenu) {
            $defaultBidang = Bidang::first();
            $subMenu = SubMenu::create([
                'bidang_id' => $defaultBidang?->id ?? 1,
                'nama_sub_menu' => ucwords(str_replace('-', ' ', $slug)),
            ]);
        }

        return $subMenu;
    }

    /**
     * Cari SubMenu berdasarkan slug tanpa membuatnya. Null kalau tidak ada.
     *
     * Perbandingan dilakukan di sisi PHP lewat SubMenu::normalisasiSlug(),
     * bukan dengan LIKE/REPLACE di SQL. Alasannya, tanda baca pada nama
     * sub-bidang (mis. "Kerjasama Pendidikan (MSS)") tidak boleh membuat
     * tautan yang dibuat di dashboard tidak pernah ketemu.
     *
     * Jumlah sub-bidang kecil (puluhan baris), jadi memuat semuanya sekali
     * jalan tidak membebani.
     */
    private function cariSubMenuTersedia(string $slug): ?SubMenu
    {
        $target = SubMenu::normalisasiSlug($slug);

        if ($target === '') {
            return null;
        }

        return SubMenu::query()
            ->orderBy('id')
            ->get()
            ->first(fn (SubMenu $row) => $row->slug === $target);
    }

    /**
     * Nomor komponen dari kategori lampiran/tautan.
     *
     * Mencakup Komponen 1 dan Komponen 2-6,8. Mengembalikan 0 kalau
     * kategorinya tidak dikenal supaya tidak ada exception saat pesan
     * penyimpanannya disusun.
     */
    private function nomorKomponen(string $kategori): int
    {
        return $this->daftarKategoriDokumen()[$kategori]['nomor'] ?? 0;
    }

    /**
     * Judul komponen dari kategori lampiran/tautan.
     *
     * Dipakai untuk menyusun pesan, jadi kategori yang tidak dikenal
     * tetap punya teks pengganti.
     */
    private function judulKomponen(string $kategori): string
    {
        return $this->daftarKategoriDokumen()[$kategori]['judul'] ?? 'Tidak Diketahui';
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

        $namaFile = $lampiran->nama_file;
        $tahun = $lampiran->dokumenCrmc?->tahun_pelaksanaan;
        $kategori = $lampiran->kategori_komponen;

        $lampiran->delete();

        return back()->with('success', 'Dokumen "' . $namaFile . '" tahun ' . $tahun . ' berhasil dihapus.');
    }

    /**
     * Update Status Residu Risiko (Khusus Admin - Komponen 7).
     *
     * Nilai yang disimpan adalah KUNCI skala (mis. "waspada_ii"), bukan
     * label tampilan, supaya label masih bisa diubah tanpa menulis ulang
     * data lama.
     */
    public function updateResidu(Request $request, $slug)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Hanya Administrator yang dapat mengubah Status Residu Risiko.');
        }

        // "residu" boleh kosong: kartu radio di modal menyediakan opsi
        // "Belum Diisi" supaya admin bisa membatalkan status yang pernah
        // ditetapkan, bukan hanya menggantinya.
        $request->validate([
            'residu' => ['nullable', 'string', 'in:' . implode(',', SkalaResiduRisiko::nilaiValid())],
            'tahun_pelaksanaan' => 'required|numeric|min:2000|max:2100',
        ]);

        // Sama seperti hapus: jangan sampai slug ngawur membuat sub-bidang baru.
        $subMenu = $this->cariSubMenuTersedia($slug);
        abort_if($subMenu === null, 404, 'Sub-bidang tidak ditemukan.');

        $kunci = SkalaResiduRisiko::normalisasi($request->input('residu'));

        DokumenCrmc::updateOrCreate(
            [
                'sub_menu_id' => $subMenu->id,
                'tahun_pelaksanaan' => $request->input('tahun_pelaksanaan'),
            ],
            ['status_residu_risiko' => $kunci]
        );

        $pesan = $kunci === null
            ? 'Status Residu Risiko tahun ' . $request->input('tahun_pelaksanaan') . ' dikosongkan kembali.'
            : 'Status Residu Risiko berhasil diperbarui menjadi "'
                . SkalaResiduRisiko::TINGKAT[$kunci]['label'] . '".';

        return redirect()->route('crmc.show', ['slug' => $slug, 'tahun' => $request->input('tahun_pelaksanaan')])
            ->with('success', $pesan);
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
            'tahun_pelaksanaan' => 'required|numeric|min:2000|max:2100',
            'pemilik_risiko_id' => 'required|exists:users,id',
            'pengendali_mutu_id' => 'required|exists:users,id',
            'pengendali_risiko_ids' => 'required|array|min:1',
            'pengendali_risiko_ids.*' => 'exists:users,id',
        ], [
            'tahun_pelaksanaan.required' => 'Tahun pelaksanaan wajib diisi.',
            'pemilik_risiko_id.required' => 'Pemilik risiko (1 orang) wajib dipilih.',
            'pengendali_mutu_id.required' => 'Pengendali mutu (1 orang) wajib dipilih.',
            'pengendali_risiko_ids.required' => 'Paling sedikit pilih 1 staf Pengendali Risiko.',
        ]);

        $tahun = (int) $request->input('tahun_pelaksanaan');

        $cleanSlug = str_replace('-', ' ', $slug);
        $subMenu = $this->cariSubMenuTersedia($slug);

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
        //
        // Semua penulisan dibatasi pada tahun yang dipilih: mengisi tahun
        // 2027 tidak boleh menghapus penugasan 2026.
        DB::transaction(function () use ($subMenu, $request, $tahun) {
            $semuaSubMenu = SubMenu::pluck('id');
            $subMenuBidangIni = SubMenu::where('bidang_id', $subMenu->bidang_id)->pluck('id');

            // 1. Bersihkan penugasan lama pada tahun ini saja, supaya
            //    tidak ada data lama yang tertinggal setelah diganti dan
            //    penugasan tahun lain tetap utuh.
            //    a. pemilik_risiko  -> seluruh CRMC, bukan hanya sub-bidang ini
            //    b. pengendali_mutu -> bidang ini saja, jangan ganggu bidang lain
            //    c. peran lainnya   -> sub-bidang ini saja
            PenugasanCrmc::tahun($tahun)->where('peran', 'pemilik_risiko')->delete();
            PenugasanCrmc::tahun($tahun)
                ->whereIn('sub_menu_id', $subMenuBidangIni)
                ->where('peran', 'pengendali_mutu')
                ->delete();
            PenugasanCrmc::tahun($tahun)
                ->where('sub_menu_id', $subMenu->id)
                ->whereNotIn('peran', ['pemilik_risiko', 'pengendali_mutu'])
                ->delete();

            // 2. Pemilik Risiko: satu orang untuk semua sub-bidang
            $pemilikId = (int) $request->input('pemilik_risiko_id');
            foreach ($semuaSubMenu as $id) {
                PenugasanCrmc::create([
                    'sub_menu_id' => $id,
                    'user_id' => $pemilikId,
                    'peran' => 'pemilik_risiko',
                    'tahun_pelaksanaan' => $tahun,
                ]);
            }

            // 3. Pengendali Mutu: satu orang untuk semua sub-bidang di bidang ini
            $mutuId = (int) $request->input('pengendali_mutu_id');
            foreach ($subMenuBidangIni as $id) {
                PenugasanCrmc::create([
                    'sub_menu_id' => $id,
                    'user_id' => $mutuId,
                    'peran' => 'pengendali_mutu',
                    'tahun_pelaksanaan' => $tahun,
                ]);
            }

            // 4. Pengendali Risiko: khusus sub-bidang ini saja
            foreach (array_unique($request->input('pengendali_risiko_ids', [])) as $stafId) {
                PenugasanCrmc::create([
                    'sub_menu_id' => $subMenu->id,
                    'user_id' => $stafId,
                    'peran' => 'pengendali_risiko',
                    'tahun_pelaksanaan' => $tahun,
                ]);
            }
        });

        $ringkasan = sprintf(
            'Penanggung jawab identitas pegawai Komponen 1 tahun %d diperbarui. Pemilik Risiko (%s) berlaku untuk seluruh %d sub-bidang; Pengendali Mutu (%s) berlaku untuk %d sub-bidang bidang "%s"; Tim Pengendali Risiko (%d orang) khusus sub-bidang ini.',
            $tahun,
            User::find($request->input('pemilik_risiko_id'))?->name ?? '-',
            SubMenu::count(),
            User::find($request->input('pengendali_mutu_id'))?->name ?? '-',
            SubMenu::where('bidang_id', $subMenu->bidang_id)->count(),
            $subMenu->bidang?->nama_bidang ?? '-',
            count(array_unique($request->input('pengendali_risiko_ids', [])))
        );

        // Kembali ke tahun yang sama supaya admin langsung melihat hasil
        // simpanannya, bukan lompat ke tahun default.
        return redirect()
            ->route('crmc.show', ['slug' => $slug, 'tahun' => $tahun])
            ->with('success', $ringkasan);
    }

    // ========================
    // ADMIN: Kelola Akun Pegawai
    // ========================

    /**
     * Tampilkan daftar seluruh akun pegawai (Khusus Admin).
     *
     * Pencarian dilakukan di server (?q=) supaya tetap ringan pada daftar yang
     * panjang dan tidak perlu memuat seluruh baris ke browser. Yang dicari
     * adalah nama, NIP, jabatan, dan email, karena itulah kolom yang
     * sering diingat admin saat mencari pegawai tertentu.
     */
    public function kelolaAkun(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator.');
        }

        $kataKunci = trim((string) $request->input('q'));
        $peran = (string) $request->input('peran');

        $users = User::query()
            // Escape wildcard LIKE supaya "%" atau "_" yang diketik user
            // dicari apa adanya, bukan jadi pola bebas.
            ->when($kataKunci !== '', function ($q) use ($kataKunci) {
                $saya = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $kataKunci) . '%';
                $q->where(function ($dalam) use ($saya) {
                    $dalam->where('name', 'like', $saya)
                        ->orWhere('nip', 'like', $saya)
                        ->orWhere('jabatan', 'like', $saya)
                        ->orWhere('email', 'like', $saya);
                });
            })
            ->when(in_array($peran, ['admin', 'pegawai'], true), fn ($q) => $q->where('role', $peran))
            ->orderBy('name')
            ->get();

        // Angka pada kartu statistik selalu menggambarkan seluruh pegawai,
        // bukan hasil pencarian. Kalau ikut terfilter, nilainya berubah
        // setiap kali mengetik dan jadi tidak berguna sebagai pembanding.
        return view('admin.pegawai', [
            'users' => $users,
            'kataKunci' => $kataKunci,
            'peran' => $peran,
            'totalPegawai' => User::count(),
            'totalAdmin' => User::where('role', 'admin')->count(),
            'totalPegawaiRole' => User::where('role', 'pegawai')->count(),
        ]);
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
