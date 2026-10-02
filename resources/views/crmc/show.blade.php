@extends('layouts.app')

@php
    // Skala status residu dipakai langsung di dalam @php block (untuk
    // menghitung warna/meter), sedangkan perulangan tingkatnya nanti
    // ditulis sebagai HTML. Karena itu alias diimpor di sini, di luar
    // @section, supaya keduanya memakai nama kelas yang sama.
    use App\Support\SkalaResiduRisiko;
@endphp

@section('title', '8 Komponen CRMC - ' . $subBidangName)

@section('content')
<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    @php
        // ==========================================================
        // BANTUAN TAMPILAN
        // ==========================================================

        // Warna + label badge berdasarkan tipe file.
        $badgeFile = function (?string $tipe) {
            return match(strtolower((string) $tipe)) {
                'pdf'      => 'bg-rose-100 text-rose-700 border-rose-200',
                'doc', 'docx' => 'bg-blue-100 text-blue-700 border-blue-200',
                'xls', 'xlsx' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                'jpg', 'jpeg', 'png', 'webp' => 'bg-purple-100 text-purple-700 border-purple-200',
                'gif' => 'bg-purple-100 text-purple-700 border-purple-200',
                default    => 'bg-slate-100 text-slate-700 border-slate-200',
            };
        };

        // Ganti tahun satu komponen tanpa kehilangan pilihan tahun komponen lain.
        $urlGantiTahun = function (string $kategori, $tahun) use ($slug, $selectedTahun, $komponen, $residuTerpilih) {
            $params = ['slug' => $slug, 'tahun' => $selectedTahun, 't' => []];
            foreach (array_keys($komponen) as $k) {
                $params['t'][$k] = ($k === $kategori) ? (int) $tahun : $komponen[$k]['tahun'];
            }
            $params['t']['residu'] = ($kategori === 'residu') ? (int) $tahun : $residuTerpilih['tahun'];

            return route('crmc.show', $params);
        };

        // ==================================================================
        // TAMPILAN PRATINJAU DOKUMEN
        // ==================================================================
        // Format yang bisa dirender browser (PDF + gambar) ditampilkan
        // langsung di halaman supaya pengguna tidak perlu menekan tombol
        // mata dulu. Format kantor (doc/xls) tidak bisa dirender browser,
        // jadi hanya dapat kotak info + tombol unduh.
        //
        // Markup kartu dokumennya ada di partial
        // crmc.partials.kartu-dokumen, dipakai Komponen 2,3,4,5,6 dan 8.
        //
        // Ketiga closure di bawah hanya menerjemahkan tipe file menjadi
        // kelas/label; logika merender atau tidak merender ada di partial.

        // Ikon lucide per tipe file, dipakai pada kotak dokumen dan pada
        // lencana tipe di kiri.
        $ikonTipeFile = function (?string $tipe): string {
            return match(strtolower((string) $tipe)) {
                'pdf' => 'file-text',
                'doc', 'docx' => 'file-type',
                'xls', 'xlsx' => 'table',
                'jpg', 'jpeg', 'png', 'webp', 'gif' => 'file-image',
                default => 'file',
            };
        };

        // Nama tampilan tipe file yang lebih enak dibaca.
        $namaTipeFile = function (?string $tipe): string {
            return match(strtolower((string) $tipe)) {
                'pdf' => 'PDF',
                'doc' => 'DOC', 'docx' => 'DOCX',
                'xls' => 'XLS', 'xlsx' => 'XLSX',
                'jpg' => 'JPG', 'jpeg' => 'JPEG',
                'png' => 'PNG', 'webp' => 'WEBP', 'gif' => 'GIF',
                default => strtoupper((string) $tipe) ?: 'FILE',
            };
        };

        // Menentukan cara menampilkan dokumen:
        //   'pdf'    -> iframe (viewer bawaan browser)
        //   'gambar' -> img
        //   null     -> browser tidak bisa merender, jadi pakai kotak info
        //
        // Penting: tipe di luar daftar harus jadi null, bukan dipaksa jadi
        // 'pdf'. Kalau .doc/.xls ikut diperlakukan sebagai PDF, iframe akan
        // menampilkan pesan "tidak didukung" dari browser, bukan petunjuk
        // untuk mengunduh berkasnya.
        $tipePratinjau = function (?string $tipe): ?string {
            return match(strtolower((string) $tipe)) {
                'pdf' => 'pdf',
                'jpg', 'jpeg', 'png', 'webp', 'gif' => 'gambar',
                default => null,
            };
        };

        // ==================================================================
        // TAMPILAN STATUS RESIDU (Komponen 7)
        // ==================================================================
        // Definisi skala 5 tingkat (Bahaya -> Terkendali) beserta warna
        // dan keterangannya terpusat di App\Support\SkalaResiduRisiko, jadi
        // controller (validasi), migration, dan view memakai satu sumber
        // yang sama.

        $residuNilai = $residuTerpilih['nilai'];
        $residu = SkalaResiduRisiko::rincian($residuNilai);
        $residuBaris = collect(SkalaResiduRisiko::TINGKAT)->map(fn ($t, $kunci) => [
            'kunci' => $kunci,
            'label' => $t['label'],
            'keterangan' => $t['keterangan'],
            'warna' => SkalaResiduRisiko::WARNA[$t['warna']],
            'aktif' => $residu['kunci'] === $kunci,
        ])->values();

        $isAdmin = auth()->check() && auth()->user()->isAdmin();

        // Berapa tahun ke belakang yang punya dokumen, untuk info header.
        $tahunAdaDokumen = $dokumenTahun->keys()->map(fn ($t) => (int) $t)->sortDesc()->values();
    @endphp

    <!-- BREADCRUMB & BACK ACTION -->
    <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
        <nav class="flex items-center space-x-2 text-slate-500">
            <a href="{{ url('/') }}" class="hover:text-blue-700 flex items-center gap-1 font-medium transition">
                <i data-lucide="home" class="w-3.5 h-3.5"></i>
                <span>Beranda CRMC</span>
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
            <span class="text-slate-600 font-semibold">{{ $parentBidang }}</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
            <span class="text-blue-700 font-semibold truncate max-w-xs">{{ $subBidangName }}</span>
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ url('/') }}" class="btn btn-outline">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                <span>Kembali ke Dashboard</span>
            </a>
            <button onclick="window.print()" class="btn btn-outline">
                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                <span class="hidden sm:inline">Cetak Laporan</span>
            </button>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
    <div class="note !border-emerald-200 !bg-emerald-50 !text-emerald-900 flex items-start gap-2">
        <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0 mt-0.5"></i>
        <div>
            <p class="font-semibold">Berhasil Disimpan</p>
            <p>{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="note !border-rose-200 !bg-rose-50 !text-rose-900">
        <p class="font-semibold flex items-center gap-1.5">
            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
            Perubahan tidak tersimpan
        </p>
        <ul class="mt-1.5 space-y-0.5 list-disc list-inside text-rose-700">
            @foreach($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(session('error'))
    <div class="note !border-rose-200 !bg-rose-50 !text-rose-900 flex items-start gap-2">
        <i data-lucide="alert-triangle" class="w-4 h-4 shrink-0 mt-0.5"></i>
        <div>
            <p class="font-semibold">Gagal Diproses</p>
            <p>{{ session('error') }}</p>
        </div>
    </div>
    @endif

    {{-- HERO: foto gedung PUSBANGKOM sebagai latar banner --}}
    <div class="relative overflow-hidden rounded-xl bg-blue-950 text-white border border-blue-900">
        <img src="{{ asset('images/gedung_pusbangkom.jpg') }}"
             alt="Gedung PUSBANGKOM"
             class="absolute inset-0 h-full w-full object-cover object-center">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-950/95 via-blue-950/85 to-blue-900/50"></div>

        <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-5 p-5 sm:p-6">
            <div class="space-y-2.5 max-w-3xl">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-900 text-blue-200 rounded-md text-[11px] font-bold border border-blue-800">
                    <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                    <span class="uppercase tracking-wider">{{ $parentBidang }}</span>
                </span>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white leading-tight">
                    {{ $subBidangName }}
                </h1>
                <p class="text-blue-200 text-xs sm:text-sm leading-relaxed">
                    Dokumen resmi Continuous Monitoring on Risk Control (CRMC) terpadu. Seluruh isi halaman berasal dari berkas yang benar-benar diunggah, tanpa data contoh.
                </p>
            </div>

            <div class="flex flex-wrap items-start gap-2 shrink-0">
                <div class="px-3 py-1.5 rounded-lg bg-blue-900/80 border border-blue-800 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full {{ $rangkuman['totalDokumen'] > 0 ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                    <span class="text-[11px] text-blue-200">Tahun: <strong class="text-white">{{ $selectedTahun }}</strong></span>
                </div>
                <div class="px-3 py-1.5 rounded-lg bg-blue-900/80 border border-blue-800 flex items-center gap-2 text-[11px] text-blue-200">
                    <i data-lucide="layers" class="w-3.5 h-3.5 text-blue-300"></i>
                    <span>Komponen: <strong class="text-white">{{ $rangkuman['komponenTerisi'] }} / {{ $rangkuman['totalKomponen'] }}</strong></span>
                </div>
                <div class="px-3 py-1.5 rounded-lg bg-blue-900/80 border border-blue-800 flex items-center gap-2 text-[11px] text-blue-200">
                    <i data-lucide="file-stack" class="w-3.5 h-3.5 text-blue-300"></i>
                    <span>Dokumen: <strong class="text-white">{{ $rangkuman['totalDokumen'] }} file</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- PENJELASAN MEKANISME TAHUN -->
    <div class="note">
        <p class="font-semibold">Cara kerja pemilihan tahun</p>
        <p class="mt-1">
            Setiap komponen punya pemilih tahun sendiri, jadi Anda bisa membandingkan dokumen antar tahun dalam satu halaman.
            Tahun <strong>{{ date('Y') }}</strong> dan <strong>{{ date('Y') + 1 }}</strong> selalu tersedia otomatis tanpa perlu apa pun,
            sehingga tahun baru langsung muncul sendiri setiap pergantian tahun.
            Admin juga bisa menambah tahun khusus di luar rentang itu lewat tombol <strong>"+ Tambah Tahun"</strong>.
        </p>
        <p class="mt-1">
            Komponen hanya menampilkan berkas yang diunggah pada tahun yang dipilih. Belum ada dokumen? Kotaknya kosong — bukan data contoh.
        </p>
    </div>

    <!-- TAHUN GLOBAL -->
    <div class="card p-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-xs">
            <i data-lucide="calendar" class="w-4 h-4 text-blue-600"></i>
            <span class="font-semibold text-slate-700">Tahun Default Halaman:</span>
            <span class="page-sub">(dipakai semua komponen yang belum memilih tahun sendiri)</span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @foreach(array_reverse($daftarTahun) as $tahun)
                <a href="{{ route('crmc.show', ['slug' => $slug, 'tahun' => $tahun]) }}"
                   class="btn btn-sm {{ $selectedTahun == $tahun ? 'btn-primary' : 'btn-outline' }}">
                    {{ $tahun }}
                    @if(in_array($tahun, $tahunAdaDokumen->all(), true))
                        <i data-lucide="database" class="w-3 h-3 opacity-70" title="Tahun ini punya dokumen"></i>
                    @endif
                </a>
            @endforeach

            @if($isAdmin)
                <button onclick="openTahunModal()" class="btn btn-sm btn-dark" title="Tambah tahun di luar rentang otomatis">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Tahun</span>
                </button>
            @endif
        </div>
    </div>

    <!-- STATS OVERVIEW -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Residu -->
        <div class="stat">
            <p class="stat-label">Residu Risiko {{ $residuTerpilih['tahun'] }}</p>
            <div class="mt-1">
                @if($residu['kunci'])
                    <span class="badge {{ $residu['warna']['chip'] }} border">{{ $residu['label'] }}</span>
                @else
                    <span class="badge">Belum Diisi</span>
                @endif
            </div>
            @if($residu['keterangan'])
                <p class="stat-note">{{ $residu['keterangan'] }}</p>
            @endif
        </div>

        <!-- Kelengkapan -->
        <div class="stat">
            <p class="stat-label">Komponen Terisi</p>
            <p class="stat-value !text-xl">
                {{ $rangkuman['komponenTerisi'] }} / {{ $rangkuman['totalKomponen'] }}
            </p>
            @php
            $persen = $rangkuman['totalKomponen'] > 0
                ? round($rangkuman['komponenTerisi'] / $rangkuman['totalKomponen'] * 100)
                : 0;
            @endphp
            <div class="mt-2 w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full bg-blue-600 transition-all duration-500" style="width: {{ $persen }}%"></div>
            </div>
        </div>

        <!-- PIC Pengendali -->
        <div class="stat">
            <p class="stat-label">PIC Pengendali</p>
            @if($pengendaliRisikoList->isNotEmpty())
                <h4 class="text-xs font-semibold text-slate-900 mt-1 truncate" title="{{ $pengendaliRisikoList->first()->name }}">{{ $pengendaliRisikoList->first()->name }}</h4>
                <p class="stat-note font-mono">
                    @if($pengendaliRisikoList->count() > 1)+{{ $pengendaliRisikoList->count() - 1 }} lainnya @else NIP. {{ $pengendaliRisikoList->first()->nip ?? '-' }} @endif
                </p>
            @else
                <h4 class="text-xs font-semibold text-slate-400 mt-1">Belum Ditugaskan</h4>
                <p class="stat-note">Admin dapat mengatur lewat Komponen 1</p>
            @endif
        </div>

        <!-- Dokumen -->
        <div class="stat">
            <p class="stat-label">Dokumen Tahun {{ $selectedTahun }}</p>
            <p class="stat-value !text-xl">{{ $rangkuman['totalDokumen'] }} <span class="text-xs font-medium text-slate-500">file</span></p>
            <p class="stat-note">Total {{ $rangkuman['jumlahTahun'] }} tahun selectable</p>
        </div>
    </div>

    <!-- SECTION TITLE -->
    <div class="pt-1">
        <span class="eyebrow">Instrumen CRMC</span>
        <h2 class="section-title">Rincian 8 Komponen</h2>
    </div>

    <!-- 8 KOMPONEN: satu komponen = satu blok penuh, disusun ke bawah.
         Semua kartu memakai lebar penuh halaman supaya pratinjau dokumen
         punya ruang yang cukup dan tidak perlu dibagi dua kolom. -->
    <div class="space-y-4">

        <!-- ============ KOMPONEN 1: IDENTITAS PEGAWAI ============ -->
        <div class="card">
            <div class="space-y-4 p-4 sm:p-5">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex items-center space-x-2">
                        <span class="badge badge-accent">Komponen 1</span>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Identitas Pegawai</span>
                    </div>

                    @if($isAdmin)
                        <button onclick="openPenugasanModal()" class="btn btn-sm btn-primary">
                            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                            <span>Atur Penugasan (Admin)</span>
                        </button>
                    @endif
                </div>

                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">1. Identitas Pegawai &amp; Penugasan PIC (3 Lapis)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Hierarki pertanggungjawaban tersusun ke bawah: lapis 1 Pemilik Risiko, lapis 2 Pengendali Mutu,
                        lapis 3 Tim Pengendali Risiko (banyak orang, digeser ke samping).
                    </p>
                </div>

                <!-- === LAPIS 1: PEMILIK RISIKO (paling atas) === -->
                <div class="rounded-xl border border-blue-900/25 bg-blue-50/50 p-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-wider text-blue-900 bg-blue-100 border border-blue-200 px-2 py-0.5 rounded">
                            <i data-lucide="shield-alert" class="w-3 h-3"></i>
                            Lapis 1 &middot; Pemilik Risiko
                        </span>
                        <span class="text-[10px] text-slate-500 font-mono">{{ $pemilikRisiko ? '1 Orang' : 'Belum Ada' }}</span>
                    </div>

                    <div class="flex items-center gap-4">
                        @if($pemilikRisiko)
                            <img src="{{ $pemilikRisiko->foto_url }}" width="112" height="144" loading="lazy" decoding="async"
                                 alt="{{ $pemilikRisiko->name }}"
                                 class="w-24 h-32 sm:w-28 sm:h-36 rounded-lg object-cover object-top border-2 border-blue-900 shrink-0">
                            <div class="min-w-0 space-y-1">
                                <h5 class="text-sm sm:text-base font-bold text-slate-900">{{ $pemilikRisiko->name }}</h5>
                                <p class="text-xs text-slate-500">NIP. {{ $pemilikRisiko->nip ?? '-' }}</p>
                                <p class="text-xs text-blue-800 font-semibold">{{ $pemilikRisiko->jabatan ?? '-' }}</p>
                                <span class="badge badge-info mt-1">Pemilik Risiko</span>
                            </div>
                        @else
                            <div class="w-24 h-32 sm:w-28 sm:h-36 rounded-lg border-2 border-dashed border-blue-900/30 bg-white/70 flex flex-col items-center justify-center text-center px-3 shrink-0">
                                <i data-lucide="user-round-x" class="w-7 h-7 text-slate-300"></i>
                                <p class="text-[10px] font-bold text-slate-500 leading-tight mt-1">Belum Ditugaskan</p>
                            </div>
                            <div class="min-w-0 space-y-1">
                                <h5 class="text-sm font-bold text-slate-400">Pemilik Risiko belum ditugaskan</h5>
                                <p class="text-xs text-slate-500">
                                    @if($isAdmin)
                                        Gunakan tombol <strong>"Atur Penugasan (Admin)"</strong> di kanan atas.
                                    @else
                                        Admin dapat mengaturnya melalui Komponen 1.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- === LAPIS 2: PENGENDALI MUTU === -->
                <div class="rounded-xl border border-sky-200 bg-sky-50/50 p-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-wider text-sky-900 bg-sky-100 border border-sky-200 px-2 py-0.5 rounded">
                            <i data-lucide="badge-check" class="w-3 h-3"></i>
                            Lapis 2 &middot; Pengendali Mutu
                        </span>
                        <span class="text-[10px] text-slate-500 font-mono">{{ $pengendaliMutu ? '1 Orang' : 'Belum Ada' }}</span>
                    </div>

                    <div class="flex items-center gap-4">
                        @if($pengendaliMutu)
                            <img src="{{ $pengendaliMutu->foto_url }}" width="112" height="144" loading="lazy" decoding="async"
                                 alt="{{ $pengendaliMutu->name }}"
                                 class="w-24 h-32 sm:w-28 sm:h-36 rounded-lg object-cover object-top border-2 border-sky-500 shrink-0">
                            <div class="min-w-0 space-y-1">
                                <h5 class="text-sm sm:text-base font-bold text-slate-900">{{ $pengendaliMutu->name }}</h5>
                                <p class="text-xs text-slate-500">NIP. {{ $pengendaliMutu->nip ?? '-' }}</p>
                                <p class="text-xs text-sky-800 font-semibold">{{ $pengendaliMutu->jabatan ?? '-' }}</p>
                                <span class="badge badge-accent mt-1">Pengendali Mutu</span>
                            </div>
                        @else
                            <div class="w-24 h-32 sm:w-28 sm:h-36 rounded-lg border-2 border-dashed border-sky-400/50 bg-white/70 flex flex-col items-center justify-center text-center px-3 shrink-0">
                                <i data-lucide="user-round-x" class="w-7 h-7 text-slate-300"></i>
                                <p class="text-[10px] font-bold text-slate-500 leading-tight mt-1">Belum Ditugaskan</p>
                            </div>
                            <div class="min-w-0 space-y-1">
                                <h5 class="text-sm font-bold text-slate-400">Pengendali Mutu belum ditugaskan</h5>
                                <p class="text-xs text-slate-500">
                                    @if($isAdmin)
                                        Gunakan tombol <strong>"Atur Penugasan (Admin)"</strong> di kanan atas.
                                    @else
                                        Admin dapat mengaturnya melalui Komponen 1.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- === LAPIS 3: TIM PENGENDALI RISIKO (banyak orang, geser ke samping) === -->
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-extrabold uppercase tracking-wider text-slate-800 bg-white border border-slate-300 px-2 py-0.5 rounded">
                            <i data-lucide="users-round" class="w-3 h-3"></i>
                            Lapis 3 &middot; Pengendali Risiko
                        </span>
                        <span class="text-[10px] text-slate-500 font-medium">
                            {{ $pengendaliRisikoList->count() }} Orang
                            @if($pengendaliRisikoList->count() > 4)
                                <span class="hidden sm:inline">&middot; geser ke samping &rarr;</span>
                            @endif
                        </span>
                    </div>

                    <div class="flex gap-3 overflow-x-auto pb-2 snap-x snap-mandatory">
                        @forelse($pengendaliRisikoList as $staf)
                            <div class="w-44 sm:w-48 shrink-0 snap-start bg-white hover:bg-blue-50/40 rounded-xl border border-slate-200 p-3 flex flex-col items-center text-center transition">
                                <img src="{{ $staf->foto_url }}" width="112" height="144" loading="lazy" decoding="async" alt="{{ $staf->name }}" class="w-24 h-32 rounded-lg object-cover object-top border border-blue-500/70">
                                <h6 class="text-xs font-bold text-slate-900 mt-2 w-full truncate" title="{{ $staf->name }}">{{ $staf->name }}</h6>
                                <p class="text-[10px] text-slate-500 w-full truncate">NIP. {{ $staf->nip ?? '-' }}</p>
                                <p class="text-[10px] text-blue-700 font-semibold w-full truncate" title="{{ $staf->jabatan }}">{{ $staf->jabatan }}</p>
                            </div>
                        @empty
                            <div class="w-full p-4 bg-white rounded-lg border border-dashed border-slate-300 text-center text-xs text-slate-400">
                                Belum ada staf pengendali risiko yang ditugaskan.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="px-4 sm:px-6 py-3 border-t border-slate-100 text-[11px] text-slate-500">
                Data penugasan di atas berasal dari akun pegawai yang ditunjuk Admin. Kosong berarti belum ada yang ditugaskan.
            </div>
        </div>

        @php
            $deskripsiKomponen = [
                'risk_register'         => 'Dokumen identifikasi peristiwa risiko, analisis dampak, dan register risiko.',
                'sop'                   => 'Standar prosedur kerja yang menjadi acuan pelaksanaan di sub-bidang ini.',
                'formulir_pengendalian' => 'Formulir pengendalian dan daftar periksa kepatuhan.',
                'jadwal_pelaksanaan'    => 'Jadwal rencana pelaksanaan kegiatan pada tahun berjalan.',
                'bukti_pelaksanaan'     => 'Bukti realisasi kegiatan berupa foto, notula, dan laporan.',
                'evaluasi'              => 'Hasil evaluasi dan rencana perbaikan berkelanjutan.',
            ];
        @endphp

        <!-- ============ KOMPONEN 2,3,4,5,6 (DENGAN DOKUMEN) ============ -->
        @foreach($komponen as $kategori => $meta)
        @php
            // Komponen 8 (evaluasi) punya kartunya sendiri di bawah, karena
            // kartunya membawa form "Ringkasan Evaluasi" selain daftar
            // dokumen. Kalau ikut di-loop, Komponen 8 tampil dua kali.
            if ($kategori === 'evaluasi') {
                continue;
            }

            $daftar = $meta['lampiran'];
            $tahunK = $meta['tahun'];
        @endphp
        <div class="card">
            <div class="space-y-4 p-4 sm:p-5">
                <!-- HEADER -->
                <div class="flex items-start justify-between gap-2 flex-wrap">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="badge badge-accent">Komponen {{ $meta['nomor'] }}</span>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">{{ $daftar->count() }} file</span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @auth
                            <button onclick="openUploadModal(@js($kategori), @js('Komponen ' . $meta['nomor'] . ' — ' . $meta['judul']), {{ $tahunK }})"
                                    class="btn btn-sm btn-quiet">
                                <i data-lucide="upload" class="w-3 h-3"></i><span>Upload</span>
                            </button>
                        @endauth
                        <i data-lucide="{{ $meta['ikon'] }}" class="w-4 h-4 text-slate-400"></i>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">{{ $meta['nomor'] }}. {{ $meta['judul'] }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $deskripsiKomponen[$kategori] }}</p>
                </div>

                <!-- PEMILIH TAHUN (per komponen) -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="label">Tahun:</span>
                    <select onchange="if(this.value) window.location.href = '{{ $urlGantiTahun($kategori, '__TAHUN__') }}'.replace('__TAHUN__', this.value)"
                            class="input !w-auto !py-1 !text-xs !px-2.5">
                        @foreach(array_reverse($meta['tahunTersedia']) as $tahun)
                            <option value="{{ $tahun }}" {{ $tahunK == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endforeach
                    </select>
                    @if(in_array($tahunK, $tahunAdaDokumen->all(), true))
                        <span class="badge badge-ok">Punya dokumen</span>
                    @else
                        <span class="badge">Belum ada dokumen</span>
                    @endif
                </div>

                <!-- DAFTAR DOKUMEN (pratinjau langsung, tanpa tombol mata) -->
                <div class="space-y-4">
                    @forelse($daftar as $idx => $lamp)
                        @include('crmc.partials.kartu-dokumen', [
                            'index' => $idx + 1,
                            'total' => $daftar->count(),
                        ])
                    @empty
                        <div class="empty">
                            <i data-lucide="file-plus-2" class="w-8 h-8 text-slate-300 mx-auto"></i>
                            <p class="text-xs font-bold text-slate-600">Belum ada dokumen tahun {{ $tahunK }}</p>
                            <p class="text-[11px] text-slate-400">Komponen ini hanya menampilkan berkas yang benar-benar diunggah.</p>
                            @auth
                                <button onclick="openUploadModal(@js($kategori), @js('Komponen ' . $meta['nomor'] . ' — ' . $meta['judul']), {{ $tahunK }})"
                                        class="btn btn-sm btn-primary mt-1">
                                    <i data-lucide="upload" class="w-3 h-3"></i><span>Upload {{ $tahunK }}</span>
                                </button>
                            @endauth
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="px-4 sm:px-6 py-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>
                    @if($daftar->isNotEmpty())
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold"><i data-lucide="check" class="w-3.5 h-3.5"></i> {{ $daftar->count() }} berkas tersimpan</span>
                    @else
                        <span class="inline-flex items-center gap-1 text-slate-400"><i data-lucide="circle-dashed" class="w-3.5 h-3.5"></i> Kosong</span>
                    @endif
                </span>
                <span class="text-slate-400">Tahun {{ $tahunK }}</span>
            </div>
        </div>
        @endforeach

        <!-- ============ KOMPONEN 7: STATUS RESIDU ============ -->
        <div class="card">
            <div class="space-y-4 p-4 sm:p-5">
                <div class="flex items-start justify-between gap-2 flex-wrap">
                    <span class="badge badge-accent">Komponen 7</span>
                    <div class="flex items-center gap-2 shrink-0">
                        @if($isAdmin)
                            <button onclick="openResiduModal({{ $residuTerpilih['tahun'] }})" class="btn btn-sm btn-quiet">
                                <i data-lucide="edit-3" class="w-3 h-3"></i><span>Ubah (Admin)</span>
                            </button>
                        @endif
                        <i data-lucide="gauge" class="w-4 h-4 text-slate-400"></i>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">7. Status Residu Risiko</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Skala 5 tingkat dari Bahaya (merah) sampai Terkendali (hijau), dibaca berurutan dari kiri ke kanan.
                        Makin ke kanan, makin lengkap dokumen pengendalian yang tersedia.
                        @if($isAdmin)<em class="text-blue-700 font-semibold">Hanya Admin yang dapat mengubah.</em>@endif
                    </p>
                </div>

                <!-- PEMILIH TAHUN -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="label">Tahun:</span>
                    <select onchange="if(this.value) window.location.href = '{{ $urlGantiTahun('residu', '__TAHUN__') }}'.replace('__TAHUN__', this.value)"
                            class="input !w-auto !py-1 !text-xs !px-2.5">
                        @foreach(array_reverse($residuTerpilih['tahunTersedia']) as $tahun)
                            <option value="{{ $tahun }}" {{ $residuTerpilih['tahun'] == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-4">
                    <!-- KARTU STATUS UTAMA -->
                    <div class="p-5 rounded-xl border-2 {{ $residu['warna']['kartu'] }}">
                        <div class="flex items-start justify-between gap-4 flex-wrap">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider {{ $residu['warna']['teks'] }}">
                                    Tingkat Residu Tahun {{ $residuTerpilih['tahun'] }}
                                </span>
                                <div class="text-2xl sm:text-3xl font-extrabold {{ $residu['warna']['teks'] }} mt-1 flex items-center gap-2">
                                    <i data-lucide="{{ $residu['kunci'] ? 'shield-check' : 'shield-question' }}" class="w-7 h-7"></i>
                                    <span>{{ $residu['label'] }}</span>
                                </div>
                                @if($residu['keterangan'])
                                    <p class="text-xs font-semibold {{ $residu['warna']['teks'] }} mt-1">{{ $residu['keterangan'] }}</p>
                                @else
                                    <p class="text-[11px] text-slate-400 italic mt-1">Status residu untuk tahun ini belum ditetapkan.</p>
                                @endif
                            </div>
                            @if($residu['kunci'])
                                <span class="badge {{ $residu['warna']['badge'] }} px-2.5 py-1 text-[11px]">{{ $residu['label'] }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- SKALA 5 TINGKAT: kartu tingkat yang sedang aktif disorot -->
                    <div class="space-y-2">
                        <span class="label">Skala Residu Risiko</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2">
                            @foreach($residuBaris as $tingkat)
                                <div class="rounded-lg border-2 p-3 flex flex-col gap-1 {{ $tingkat['aktif'] ? $tingkat['warna']['kartu'] . ' shadow-sm' : 'bg-slate-50 border-slate-200' }}">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $tingkat['warna']['bar'] }}"></span>
                                        <span class="text-xs font-bold {{ $tingkat['aktif'] ? $tingkat['warna']['teks'] : 'text-slate-600' }}">
                                            {{ $tingkat['label'] }}
                                        </span>
                                    </div>
                                    <p class="text-[10px] leading-snug {{ $tingkat['aktif'] ? $tingkat['warna']['teks'] : 'text-slate-400' }}">
                                        {{ $tingkat['keterangan'] }}
                                    </p>
                                    @if($tingkat['aktif'])
                                        <span class="badge {{ $tingkat['warna']['badge'] }} mt-auto self-start text-[9px]">
                                            <i data-lucide="check" class="w-2.5 h-2.5"></i> Status {{ $residuTerpilih['tahun'] }}
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- RINGKASAN EVALUASI TERTYANGGI -->
                    <div class="p-3.5 bg-slate-50 rounded-lg border border-slate-200/80 space-y-1.5">
                        <span class="label">Ringkasan Evaluasi (tersimpan di Komponen 8)</span>
                        @if(!empty($residuTerpilih['rangkumanEvaluasi']))
                            <p class="text-xs text-slate-700 leading-relaxed font-medium whitespace-pre-line">{{ $residuTerpilih['rangkumanEvaluasi'] }}</p>
                        @else
                            <p class="text-[11px] text-slate-400 italic">Belum ada ringkasan evaluasi untuk tahun ini.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="px-4 sm:px-6 py-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span class="text-slate-400">Nilai residu disimpan per tahun.</span>
                <span class="text-slate-400">Tahun {{ $residuTerpilih['tahun'] }}</span>
            </div>
        </div>

        <!-- ============ KOMPONEN 8: EVALUASI & RENCANA PERBAIKAN ============ -->
        @php
            $meta8 = $komponen['evaluasi'];
            $daftar8 = $meta8['lampiran'];
            $tahun8 = $meta8['tahun'];
        @endphp
        <div class="card">
            <div class="space-y-4 p-4 sm:p-5">
                <div class="flex items-start justify-between gap-2 flex-wrap">
                    <div class="flex items-center gap-2">
                        <span class="badge badge-accent">Komponen 8</span>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">{{ $daftar8->count() }} file</span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @auth
                            <button onclick="openUploadModal('evaluasi', @js('Komponen 8 — ' . $meta8['judul']), {{ $tahun8 }})"
                                    class="btn btn-sm btn-quiet">
                                <i data-lucide="upload" class="w-3 h-3"></i><span>Upload</span>
                            </button>
                        @endauth
                        <i data-lucide="trending-up" class="w-4 h-4 text-slate-400"></i>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">8. Evaluasi & Rencana Perbaikan</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $deskripsiKomponen['evaluasi'] }}</p>
                </div>

                <!-- PEMILIH TAHUN -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="label">Tahun:</span>
                    <select onchange="if(this.value) window.location.href = '{{ $urlGantiTahun('evaluasi', '__TAHUN__') }}'.replace('__TAHUN__', this.value)"
                            class="input !w-auto !py-1 !text-xs !px-2.5">
                        @foreach(array_reverse($meta8['tahunTersedia']) as $tahun)
                            <option value="{{ $tahun }}" {{ $tahun8 == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endforeach
                    </select>
                    @if(in_array($tahun8, $tahunAdaDokumen->all(), true))
                        <span class="badge badge-ok">Punya dokumen</span>
                    @else
                        <span class="badge">Belum ada dokumen</span>
                    @endif
                </div>

                <!-- FORM RINGKASAN EVALUASI (data nyata, bukan dummy) -->
                @auth
                <form action="{{ route('crmc.update', $slug) }}" method="POST" class="space-y-2 pt-1">
                    @csrf
                    <input type="hidden" name="tahun_pelaksanaan" value="{{ $tahun8 }}">
                    {{-- Kirimkan status residu apa adanya supaya menyimpan ringkasan
                         evaluasi tidak ikut mengubah status yang sudah ditetapkan
                         di Komponen 7 (status hanya boleh diubah dari sana). --}}
                    <input type="hidden" name="residu" value="{{ $residu['kunci'] }}">

                    <label class="label">Ringkasan Evaluasi Tahun {{ $tahun8 }}</label>
                    <textarea name="evaluasi" rows="4"
                              placeholder="Tulis hasil evaluasi dan rencana perbaikan tahun {{ $tahun8 }}. Kosongkan bila belum ada."
                              class="input">{{ old('evaluasi', $residuTerpilih['rangkumanEvaluasi']) }}</textarea>
                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-sm btn-dark">
                            <i data-lucide="save" class="w-3.5 h-3.5"></i><span>Simpan Ringkasan</span>
                        </button>
                    </div>
                </form>
                @else
                    <div class="pt-1">
                        <span class="label">Ringkasan Evaluasi Tahun {{ $tahun8 }}</span>
                        <p class="text-xs text-slate-700 leading-relaxed mt-1 whitespace-pre-line">{{ $residuTerpilih['rangkumanEvaluasi'] ?: 'Belum ada ringkasan evaluasi untuk tahun ini.' }}</p>
                    </div>
                @endauth

                <!-- DAFTAR DOKUMEN (pratinjau langsung, tanpa tombol mata) -->
                <div class="space-y-4">
                    @forelse($daftar8 as $idx => $lamp)
                        {{-- Komponen 8 memakai kartu dokumen yang sama dengan Komponen 2-6. --}}
                        @include('crmc.partials.kartu-dokumen', [
                            'index' => $idx + 1,
                            'total' => $daftar8->count(),
                        ])
                    @empty
                        <div class="empty">
                            <i data-lucide="file-plus-2" class="w-8 h-8 text-slate-300 mx-auto"></i>
                            <p class="text-xs font-bold text-slate-600">Belum ada dokumen tahun {{ $tahun8 }}</p>
                            <p class="text-[11px] text-slate-400">Komponen ini hanya menampilkan berkas yang benar-benar diunggah.</p>
                            @auth
                                <button onclick="openUploadModal('evaluasi', @js('Komponen 8 — ' . $meta8['judul']), {{ $tahun8 }})"
                                        class="btn btn-sm btn-primary mt-1">
                                    <i data-lucide="upload" class="w-3 h-3"></i><span>Upload {{ $tahun8 }}</span>
                                </button>
                            @endauth
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="px-4 sm:px-6 py-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>
                    @if($daftar8->isNotEmpty())
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold"><i data-lucide="check" class="w-3.5 h-3.5"></i> {{ $daftar8->count() }} berkas tersimpan</span>
                    @else
                        <span class="inline-flex items-center gap-1 text-slate-400"><i data-lucide="circle-dashed" class="w-3.5 h-3.5"></i> Kosong</span>
                    @endif
                </span>
                <span class="text-slate-400">Tahun {{ $tahun8 }}</span>
            </div>
        </div>
    </div>

    <!-- ============ ADMIN: MANAJEMEN TAHUN & HAPUS DOKUMEN PER TAHUN ============ -->
    @if($isAdmin)
    <div class="card overflow-hidden">
        <div class="card-head flex flex-wrap items-center justify-between gap-3 !bg-blue-950 !border-blue-900 text-white">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-900 text-blue-300 rounded-md flex items-center justify-center shrink-0">
                    <i data-lucide="settings-2" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Manajemen Tahun &amp; Penghapusan Dokumen</h3>
                    <p class="text-[11px] text-blue-200/70">Khusus Administrator</p>
                </div>
            </div>
            <button onclick="openTahunModal()" class="btn btn-sm btn-primary">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i><span>Tambah Tahun</span>
            </button>
        </div>

        <div class="p-4 space-y-4 text-xs">
            <!-- DAFTAR TAHUN MANUAL -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800">Tahun Ditambahkan Manual</span>
                    <span class="text-[10px] text-slate-400">{{ $tahunManual->count() }} tahun</span>
                </div>

                @if($tahunManual->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach($tahunManual as $ta)
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl">
                                <span class="font-bold text-slate-800">{{ $ta->tahun }}</span>
                                @if($ta->keterangan)
                                    <span class="text-[10px] text-slate-500 truncate max-w-[14rem]" title="{{ $ta->keterangan }}">{{ $ta->keterangan }}</span>
                                @endif
                                <form action="{{ route('crmc.tahun.destroy', $ta->id) }}" method="POST"
                                      data-konfirmasi="Hapus tahun {{ $ta->tahun }} dari daftar pilihan? Dokumen tahun {{ $ta->tahun }} yang sudah ada TIDAK ikut terhapus.">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="icon-btn icon-btn-danger !w-5 !h-5" title="Hapus dari daftar">
                                        <i data-lucide="x" class="w-3 h-3"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-[11px] text-slate-400 italic">
                        Belum ada tahun manual. Tahun {{ date('Y') }} dan {{ date('Y') + 1 }} sudah otomatis tersedia, jadi tombol ini hanya perlu dipakai untuk tahun khusus di luar rentang itu.
                    </p>
                @endif
            </div>

            <div class="border-t border-slate-100"></div>

            <!-- HAPUS DOKUMEN: SUB-BIDANG INI -->
            <form action="{{ route('crmc.hapus.tahun.subbidang', $slug) }}" method="POST" class="space-y-3">
                @csrf
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-blue-50 text-blue-700 rounded-md flex items-center justify-center shrink-0">
                        <i data-lucide="folder-minus" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-slate-800">Hapus Dokumen Sub-Bidang Ini per Tahun</h4>
                        <p class="page-sub mt-0.5">
                            Menghapus dokumen tahun tertentu pada sub-bidang <strong>{{ $subBidangName }}</strong> saja.
                            Record database dan file fisik di storage ikut terhapus. Residu tahun tersebut juga ikut hilang.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="label">Tahun</label>
                        <select name="tahun" required class="input">
                            @foreach(array_reverse($daftarTahun) as $tahun)
                                <option value="{{ $tahun }}" {{ $tahun == $selectedTahun ? 'selected' : '' }}>{{ $tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[16rem]">
                        <label class="label">Cakupan Komponen</label>
                        <select name="kategori" class="input">
                            <option value="">Semua komponen (2,3,4,5,6,8)</option>
                            @foreach($komponen as $kat => $m)
                                <option value="{{ $kat }}">Hanya Komponen {{ $m['nomor'] }} — {{ $m['judul'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" data-konfirmasi="Hapus dokumen tahun {{ $selectedTahun }} pada sub-bidang &quot;{{ $subBidangName }}&quot;? Record database dan file fisik di storage ikut terhapus. Tindakan ini tidak bisa dibatalkan."
                            class="btn btn-primary">
                        <i data-lucide="trash-2" class="w-4 h-4"></i><span>Hapus di Sub-Bidang Ini</span>
                    </button>
                </div>
            </form>

            <div class="border-t border-slate-100"></div>

            <!-- HAPUS DOKUMEN: SEMUA SUB-BIDANG -->
            <form action="{{ route('crmc.hapus.tahun.semua') }}" method="POST" class="space-y-3">
                @csrf
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-rose-50 text-rose-700 rounded-md flex items-center justify-center shrink-0">
                        <i data-lucide="triangle-alert" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-semibold text-rose-900">Hapus Seluruh Dokumen Satu Tahun (Semua Sub-Bidang)</h4>
                        <p class="text-[11px] text-rose-700 mt-0.5">
                            Menghapus semua dokumen tahun terpilih di seluruh {{ $rangkuman['totalSubMenu'] }} sub-bidang, beserta file fisiknya.
                           Dokumen tahun tersebut juga akan hilang dari halaman sub-bidang lain.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="label">Tahun</label>
                        <select name="tahun" required class="input !border-rose-300 focus:!border-rose-400 focus:!shadow-[0_0_0_3px_rgba(244,63,94,.2)]">
                            @foreach(array_reverse($daftarTahun) as $tahun)
                                <option value="{{ $tahun }}">{{ $tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            data-konfirmasi="PERINGATAN: seluruh dokumen tahun {{ $selectedTahun }} di SEMUA sub-bidang akan dihapus permanen, termasuk file fisik di storage. Tindakan ini tidak bisa dibatalkan. Lanjutkan?"
                            class="btn !bg-rose-600 !border-rose-600 !text-white hover:!bg-rose-700">
                        <i data-lucide="trash-2" class="w-4 h-4"></i><span>Hapus Semua Dokumen Tahun Tersebut</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- BOTTOM ACTION BAR -->
    <div class="card p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-md bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                <i data-lucide="info" class="w-4 h-4"></i>
            </div>
            <div>
                <h4 class="text-xs font-semibold text-slate-900">Kelengkapan Tahun {{ $selectedTahun }}</h4>
                <p class="page-sub">
                    {{ $rangkuman['komponenTerisi'] }} dari {{ $rangkuman['totalKomponen'] }} komponen dokumen terisi &middot; {{ $rangkuman['totalDokumen'] }} berkas tersimpan.
                    @if($rangkuman['tahunAdaData'])
                        Tahun dengan data: {{ implode(', ', $tahunAdaDokumen->all()) }}.
                    @else
                        Belum ada dokumen untuk tahun {{ $selectedTahun }}.
                    @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ url('/') }}" class="btn btn-quiet">
                Kembali ke Dashboard
            </a>
            @if($isAdmin)
                <a href="{{ route('admin.pegawai.index') }}" class="btn btn-dark">
                    Kelola Akun Pegawai
                </a>
            @endif
        </div>
    </div>

</main>
@endsection

@section('modals')
    <!-- ===================== MODAL UPLOAD (DENGAN KETERANGAN PER FILE) ===================== -->
    <div id="uploadDokumenModal" class="modal hidden">
        <div class="modal-card !max-w-2xl">
            <div class="modal-head">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-50 text-blue-700 rounded-md flex items-center justify-center shrink-0">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="eyebrow">Upload Dokumen</span>
                        <h3 id="uploadModalTitle" class="modal-title mt-0.5">Komponen</h3>
                    </div>
                </div>
                <button onclick="closeUploadModal()" class="icon-btn" title="Tutup">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('crmc.upload.dokumen', $slug) }}" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col min-h-0">
                <div class="modal-body space-y-3">
                    @csrf
                    <input type="hidden" name="kategori_komponen" id="uploadKategori" value="">
                    <input type="hidden" name="tahun_pelaksanaan" id="uploadTahun" value="{{ $selectedTahun }}">

                    <!-- PILIH TAHUN -->
                    <div>
                        <label class="label">Tahun Anggaran *</label>
                        <select id="uploadTahunSelect" required onchange="document.getElementById('uploadTahun').value = this.value"
                                class="input">
                            @foreach(array_reverse($daftarTahun) as $tahun)
                                <option value="{{ $tahun }}" {{ $tahun == $selectedTahun ? 'selected' : '' }}>{{ $tahun }}</option>
                            @endforeach
                        </select>
                        <p class="hint">Dokumen yang diunggah akan tersimpan pada tahun ini dan muncul di Kotak Komponen tersebut.</p>
                    </div>

                    <!-- PILIH FILE -->
                    <div>
                        <label class="label">Pilih File Dokumen (Multi-Upload) *</label>
                        <div class="border border-dashed border-slate-300 rounded-lg p-6 text-center hover:border-blue-400 transition cursor-pointer" onclick="document.getElementById('uploadFiles').click();">
                            <i data-lucide="cloud-upload" class="w-7 h-7 text-slate-400 mx-auto mb-2"></i>
                            <p class="text-slate-600 font-medium">Klik atau seret file ke sini</p>
                            <p class="text-[10px] text-slate-400 mt-1">PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, WebP (maks. 10MB per file)</p>
                        </div>
                        <input type="file" name="files[]" id="uploadFiles" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp" class="hidden" onchange="bangunDaftarBerkas(this)">
                    </div>

                    <!-- DAFTAR FILE + KETERANGAN -->
                    <div id="daftarBerkasWrap" class="hidden">
                        <label class="label">Keterangan per Berkas (opsional, tapi sangat disarankan)</label>
                        <div id="daftarBerkas" class="space-y-2"></div>
                    </div>
                </div>

                <div class="modal-foot">
                    <button type="button" onclick="closeUploadModal()" class="btn btn-quiet">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload Dokumen</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================== MODAL KETERANGAN SATU DOKUMEN ===================== -->
    <div id="keteranganModal" class="modal hidden">
        <div class="modal-card !max-w-lg">
            <div class="modal-head">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-50 text-blue-700 rounded-md flex items-center justify-center shrink-0">
                        <i data-lucide="text-quote" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="eyebrow">Keterangan Berkas</span>
                        <h3 id="keteranganModalFile" class="modal-title mt-0.5 truncate max-w-[16rem]">-</h3>
                    </div>
                </div>
                <button onclick="closeKeteranganModal()" class="icon-btn" title="Tutup">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form id="keteranganForm" method="POST" class="flex-1 flex flex-col min-h-0">
                <div class="modal-body space-y-3">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="label">Keterangan</label>
                        <textarea id="keteranganInput" name="keterangan" rows="5" maxlength="1000"
                                  placeholder="Contoh: Daftar periksa Triwulan III, ditandatangani oleh Kajur, tanggal 12 September 2026."
                                  class="input"></textarea>
                        <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                            <span>Maksimal 1000 karakter.</span>
                            <span id="keteranganCounter">0 / 1000</span>
                        </div>
                    </div>
                </div>

                <div class="modal-foot">
                    <button type="button" onclick="closeKeteranganModal()" class="btn btn-quiet">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Keterangan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===================== MODAL TAMBAH TAHUN (ADMIN) ===================== -->
    @if($isAdmin)
    <div id="tahunModal" class="modal hidden">
        <div class="modal-card !max-w-md">
            <div class="modal-head">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-50 text-blue-700 rounded-md flex items-center justify-center shrink-0">
                        <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="eyebrow">Khusus Admin</span>
                        <h3 class="modal-title mt-0.5">Tambah Tahun Anggaran</h3>
                    </div>
                </div>
                <button onclick="closeTahunModal()" class="icon-btn" title="Tutup">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('crmc.tahun.store') }}" method="POST" class="flex-1 flex flex-col min-h-0">
                <div class="modal-body space-y-3">
                    @csrf

                    <div class="note">
                        <p class="font-semibold flex items-center gap-1.5"><i data-lucide="info" class="w-4 h-4"></i> Kapan perlu tombol ini?</p>
                        <p class="mt-1">
                            Tahun <strong>{{ date('Y') }}</strong> dan <strong>{{ date('Y') + 1 }}</strong> sudah tersedia otomatis setiap tahun, jadi
                            tidak perlu ditambah manual. Tombol ini hanya untuk <strong>tahun khusus</strong> di luar rentang itu,
                            misalnya tahun anggaran khusus atau membuka tahun lama yang belum pernah ada datanya.
                        </p>
                    </div>

                    <div>
                        <label class="label">Tahun *</label>
                        <input type="number" name="tahun" required min="2000" max="2100"
                               placeholder="mis. {{ date('Y') + 2 }}"
                               class="input">
                    </div>

                    <div>
                        <label class="label">Keterangan (opsional)</label>
                        <input type="text" name="keterangan" maxlength="255"
                               placeholder="mis. Tahun Anggaran Khusus / Tahun Lampau"
                               class="input">
                    </div>
                </div>

                <div class="modal-foot">
                    <button type="button" onclick="closeTahunModal()" class="btn btn-quiet">Batal</button>
                    <button type="submit" class="btn btn-primary">Tambah Tahun</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================== MODAL RESIDU (KOMPONEN 7 - ADMIN) ===================== -->
    @if($isAdmin)
    <div id="residuModal" class="modal hidden">
        <div class="modal-card !max-w-2xl">
            <div class="modal-head">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-50 text-blue-700 rounded-md flex items-center justify-center shrink-0">
                        <i data-lucide="gauge" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="eyebrow">Khusus Admin</span>
                        <h3 class="modal-title mt-0.5">Ubah Status Residu Risiko</h3>
                    </div>
                </div>
                <button onclick="closeResiduModal()" class="icon-btn" title="Tutup">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form action="{{ route('crmc.update.residu', $slug) }}" method="POST" class="flex-1 flex flex-col min-h-0">
                <div class="modal-body space-y-3">
                    @csrf
                    <input type="hidden" name="tahun_pelaksanaan" id="residuTahunInput" value="{{ $residuTerpilih['tahun'] }}">

                    <div class="note note-warn">
                        <p class="font-semibold flex items-center gap-1.5"><i data-lucide="info" class="w-4 h-4"></i> Perhatian:</p>
                        <p class="mt-1">Status residu disimpan <strong>per tahun</strong>. Nilai ini berlaku untuk tahun yang dipilih, bukan untuk semua tahun sekaligus.</p>
                    </div>

                    <div>
                        <label class="label">Tahun *</label>
                        <select name="tahun_display" id="residuTahunSelect" onchange="document.getElementById('residuTahunInput').value = this.value"
                                class="input">
                            @foreach(array_reverse($residuTerpilih['tahunTersedia']) as $tahun)
                                <option value="{{ $tahun }}" {{ $residuTerpilih['tahun'] == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Skala 5 tingkat memakai kartu radio, bukan <select> biasa,
                         supaya warna tiap tingkat dan syarat dokumen yang melekat
                         pada opsinya ikut terbaca tanpa harus ditebak. --}}
                    <div>
                        <span class="label">Status Residu Risiko</span>
                        <div class="space-y-2 mt-1">
                            {{-- "Belum diisi" tetap jadi pilihan supaya admin bisa
                                 membatalkan status yang pernah ditetapkan, bukan
                                 hanya bisa mengganti nilainya. --}}
                            <label class="flex items-start gap-3 p-3 rounded-lg border-2 cursor-pointer transition
                                          {{ $residu['kunci'] === null ? 'bg-slate-50 border-slate-400 shadow-sm' : 'bg-white border-slate-200 hover:border-slate-300' }}">
                                <input type="radio" name="residu" value=""
                                       class="mt-0.5 w-4 h-4 shrink-0 border-slate-300 text-blue-600 focus:ring-blue-400"
                                       {{ $residu['kunci'] === null ? 'checked' : '' }}>
                                <span class="w-2.5 h-2.5 rounded-full shrink-0 mt-1 bg-slate-300"></span>
                                <span class="min-w-0">
                                    <span class="block text-xs font-bold text-slate-700">Belum Diisi</span>
                                    <span class="block text-[11px] text-slate-500 leading-snug mt-0.5">Status residu tahun ini belum ditetapkan.</span>
                                </span>
                            </label>

                            @foreach(SkalaResiduRisiko::TINGKAT as $kunci => $tingkat)
                                @php $warna = SkalaResiduRisiko::WARNA[$tingkat['warna']]; @endphp
                                <label class="flex items-start gap-3 p-3 rounded-lg border-2 cursor-pointer transition
                                          {{ $residu['kunci'] === $kunci ? $warna['kartu'] . ' shadow-sm' : 'bg-white border-slate-200 hover:border-slate-300' }}">
                                    <input type="radio" name="residu" value="{{ $kunci }}"
                                           class="mt-0.5 w-4 h-4 shrink-0 border-slate-300 text-blue-600 focus:ring-blue-400"
                                           {{ $residu['kunci'] === $kunci ? 'checked' : '' }}>
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0 mt-1 {{ $warna['bar'] }}"></span>
                                    <span class="min-w-0">
                                        <span class="block text-xs font-bold {{ $residu['kunci'] === $kunci ? $warna['teks'] : 'text-slate-800' }}">
                                            {{ $tingkat['label'] }}
                                        </span>
                                        <span class="block text-[11px] text-slate-500 leading-snug mt-0.5">{{ $tingkat['keterangan'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p class="hint">
                            Skala dibaca dari <strong>Bahaya</strong> (paling parah) ke <strong>Terkendali</strong> (tuntas).
                            Pilih sesuai kelengkapan dokumen pengendalian tahun tersebut.
                        </p>
                    </div>
                </div>

                <div class="modal-foot">
                    <button type="button" onclick="closeResiduModal()" class="btn btn-quiet">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Status</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- ===================== MODAL PENUGASAN (ADMIN) ===================== -->
    @if($isAdmin)
    <div id="penugasanModal" class="modal hidden">
        <div class="modal-card !max-w-2xl">
            <div class="modal-head">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-50 text-blue-700 rounded-md flex items-center justify-center shrink-0">
                        <i data-lucide="user-check" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="eyebrow">{{ $parentBidang }}</span>
                        <h3 class="modal-title mt-0.5">Atur Penugasan Pegawai (Komponen 1)</h3>
                        <p class="text-xs text-slate-400">{{ $subBidangName }}</p>
                    </div>
                </div>
                <button onclick="closePenugasanModal()" class="icon-btn" title="Tutup">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="px-4 py-3 border-b border-blue-100 bg-blue-50/70 text-[11px] text-blue-900 space-y-1">
                <p class="font-semibold flex items-center gap-1.5"><i data-lucide="info" class="w-4 h-4"></i> Ketentuan Penetapan PIC CRMC:</p>
                <ul class="list-disc list-inside space-y-0.5 text-blue-800">
                    <li><strong>Pemilik Risiko:</strong> Hanya ada <strong>1 orang</strong> (Kepala Pusat).</li>
                    <li><strong>Pengendali Mutu:</strong> Hanya ada <strong>1 orang</strong> di tiap bidang (Kabag / Kabid).</li>
                    <li><strong>Pengendali Risiko:</strong> Dapat ditugaskan <strong>banyak orang</strong> (Tim Pelaksana Staf).</li>
                </ul>
            </div>

            <form action="{{ route('crmc.penugasan.update', $slug) }}" method="POST" class="flex-1 flex flex-col min-h-0">
                <div class="modal-body space-y-3">
                    @csrf

                    <div>
                        <label class="label">1. Pemilik Risiko (Pilih 1 Orang) *</label>
                        <select name="pemilik_risiko_id" required class="input">
                            @foreach($allUsers as $u)
                                <option value="{{ $u->id }}" {{ (isset($pemilikRisiko) && $pemilikRisiko->id == $u->id) ? 'selected' : '' }}>
                                    {{ $u->name }} - {{ $u->jabatan }} (NIP. {{ $u->nip }})
                                </option>
                            @endforeach
                        </select>
                        <div class="hint flex items-start gap-1.5">
                            <i data-lucide="globe-2" class="w-3 h-3 shrink-0 mt-0.5"></i>
                            <span><strong>Berlaku untuk semua sub-bidang.</strong> Hanya ada 1 Pemilik Risiko untuk seluruh CRMC.</span>
                        </div>
                    </div>

                    <div>
                        <label class="label">2. Pengendali Mutu (Pilih 1 Orang per Bidang) *</label>
                        <select name="pengendali_mutu_id" required class="input">
                            @foreach($allUsers as $u)
                                <option value="{{ $u->id }}" {{ (isset($pengendaliMutu) && $pengendaliMutu->id == $u->id) ? 'selected' : '' }}>
                                    {{ $u->name }} - {{ $u->jabatan }} (NIP. {{ $u->nip }})
                                </option>
                            @endforeach
                        </select>
                        <div class="hint flex items-start gap-1.5">
                            <i data-lucide="building-2" class="w-3 h-3 shrink-0 mt-0.5"></i>
                            <span><strong>Berlaku untuk 1 bidang saja:</strong> {{ $parentBidang }}.</span>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="label !mb-0">3. Tim Pengendali Risiko (Bisa Memilih Banyak Staf) *</label>
                            <span class="badge badge-ok">Multi-Selection</span>
                        </div>

                        @php $selectedPengendaliIds = $pengendaliRisikoList->pluck('id')->toArray(); @endphp

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-56 overflow-y-auto p-1 border border-slate-200 rounded-lg bg-slate-50">
                            @foreach($allUsers as $u)
                            <label class="flex items-center space-x-3 p-2.5 rounded-lg bg-white border border-slate-200 hover:border-blue-400 cursor-pointer transition select-none">
                                <input type="checkbox" name="pengendali_risiko_ids[]" value="{{ $u->id }}"
                                       {{ in_array($u->id, $selectedPengendaliIds) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-blue-600 focus:ring-blue-400 border-slate-300 shrink-0">
                                <img src="{{ $u->foto_url }}" alt="{{ $u->name }}" class="w-8 h-8 rounded object-cover border border-slate-200 shrink-0">
                                <div class="min-w-0 flex-1">
                                    <h6 class="text-xs font-semibold text-slate-900 truncate">{{ $u->name }}</h6>
                                    <p class="text-[10px] text-slate-500 truncate">{{ $u->jabatan }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="modal-foot !justify-between">
                    <span class="text-[11px] text-slate-500">Hak Akses: Administrator CRMC</span>
                    <button type="button" onclick="closePenugasanModal()" class="btn btn-quiet">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Penugasan Pegawai</button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endsection

@push('scripts')
<script>
    // ======================================================
    // KONFIRMASI HAPUS
    // ======================================================
    // Pesan konfirmasi ditaruh di atribut data-konfirmasi, BUKAN ditulis
    // langsung ke onclick="return confirm('...')".
    //
    // Alasannya: nama file bisa mengandung tanda kutip (mis. Laporan
    // "Audit" 2026.pdf). Kalau pesan itu disisipkan ke dalam string JS,
    // satu tanda kutip saja sudah menutup string dan sisanya bisa jadi
    // potongan kode. Dengan data-konfirmasi, teksnya dibaca lewat
    // dataset sebagai data murni sehingga tidak pernah dieksekusi.
    document.addEventListener('submit', function (e) {
        const pesan = e.target.dataset && e.target.dataset.konfirmasi;
        if (pesan && !window.confirm(pesan)) {
            e.preventDefault();
        }
    }, true);

    // ======================================================
    // MODAL UPLOAD DENGAN KETERANGAN PER BERKAS
    // ======================================================
    function openUploadModal(kategori, title, tahun) {
        document.getElementById('uploadKategori').value = kategori;
        document.getElementById('uploadModalTitle').textContent = title;

        const tahunHidden = document.getElementById('uploadTahun');
        const tahunSelect = document.getElementById('uploadTahunSelect');
        if (tahun) {
            tahunHidden.value = tahun;
            tahunSelect.value = String(tahun);
        } else {
            tahunHidden.value = tahunSelect.value;
        }

        document.getElementById('uploadDokumenModal').classList.remove('hidden');
    }

    function closeUploadModal() {
        document.getElementById('uploadDokumenModal').classList.add('hidden');

        const input = document.getElementById('uploadFiles');
        const wrap = document.getElementById('daftarBerkasWrap');
        const list = document.getElementById('daftarBerkas');

        if (input) input.value = '';
        if (wrap) wrap.classList.add('hidden');
        if (list) list.innerHTML = '';
    }

    /**
     * Bangun daftar berkas dengan satu input keterangan untuk tiap file.
     *
     * Nama input dibuat `keterangan[0]`, `keterangan[1]`, dst. Indeksnya
     * sama dengan indeks file pada array `files[]`, sehingga keterangan file
     * ke-2 tidak ikut menempel pada file pertama.
     */
    function bangunDaftarBerkas(input) {
        const wrap = document.getElementById('daftarBerkasWrap');
        const list = document.getElementById('daftarBerkas');
        list.innerHTML = '';

        if (!input.files || input.files.length === 0) {
            wrap.classList.add('hidden');
            return;
        }

        wrap.classList.remove('hidden');

        Array.from(input.files).forEach((file, idx) => {
            const row = document.createElement('div');
            row.className = 'p-3 bg-slate-50 rounded-lg border border-slate-200 space-y-2';

            const head = document.createElement('div');
            head.className = 'flex items-center gap-2';
            head.innerHTML = `
                <span class="badge">#${idx + 1}</span>
                <span class="truncate font-medium text-slate-700 text-[11px]">${escapeHtml(file.name)}</span>
                <span class="text-[10px] text-slate-400 shrink-0 ml-auto">${(file.size / 1024).toFixed(1)} KB</span>
            `;

            const textarea = document.createElement('textarea');
            textarea.name = `keterangan[${idx}]`;
            textarea.rows = 2;
            textarea.maxLength = 1000;
            textarea.placeholder = 'Keterangan berkas ini (mis. tanggal, penanggung jawab, nomor surat)…';
            textarea.className = 'input !text-[11px]';

            row.appendChild(head);
            row.appendChild(textarea);
            list.appendChild(row);
        });
    }

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[c]));
    }

    // ======================================================
    // MODAL KETERANGAN SATU DOKUMEN
    // ======================================================
    function openKeteranganModal(id, namaFile, keterangan) {
        const form = document.getElementById('keteranganForm');
        const input = document.getElementById('keteranganInput');
        const judul = document.getElementById('keteranganModalFile');
        const modal = document.getElementById('keteranganModal');

        form.action = '{{ url('/crmc/lampiran') }}/' + id + '/keterangan';
        judul.textContent = namaFile || '-';
        input.value = keterangan || '';

        hitungCounterKeterangan();
        modal.classList.remove('hidden');
        setTimeout(() => input.focus(), 50);
    }

    function closeKeteranganModal() {
        document.getElementById('keteranganModal').classList.add('hidden');
    }

    function hitungCounterKeterangan() {
        const input = document.getElementById('keteranganInput');
        const counter = document.getElementById('keteranganCounter');
        if (input && counter) counter.textContent = `${input.value.length} / 1000`;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('keteranganInput');
        if (input) input.addEventListener('input', hitungCounterKeterangan);
    });

    // ======================================================
    // MODAL TAHUN (ADMIN)
    // ======================================================
    function openTahunModal() {
        document.getElementById('tahunModal').classList.remove('hidden');
    }
    function closeTahunModal() {
        document.getElementById('tahunModal').classList.add('hidden');
    }

    // ======================================================
    // MODAL RESIDU (KOMPONEN 7)
    // ======================================================
    function openResiduModal(tahun) {
        const hidden = document.getElementById('residuTahunInput');
        const select = document.getElementById('residuTahunSelect');
        if (tahun && hidden) {
            hidden.value = tahun;
            select.value = String(tahun);
        }
        document.getElementById('residuModal').classList.remove('hidden');
    }
    function closeResiduModal() {
        document.getElementById('residuModal').classList.add('hidden');
    }

    // ======================================================
    // MODAL PENUGASAN
    // ======================================================
    function openPenugasanModal() {
        document.getElementById('penugasanModal').classList.remove('hidden');
    }
    function closePenugasanModal() {
        document.getElementById('penugasanModal').classList.add('hidden');
    }

    // Escape untuk menutup modal yang terbuka.
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        ['uploadDokumenModal', 'keteranganModal', 'tahunModal', 'residuModal', 'penugasanModal']
            .forEach((id) => {
                const el = document.getElementById(id);
                if (el) el.classList.add('hidden');
            });
    });
</script>
@endpush
