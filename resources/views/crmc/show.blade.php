@extends('layouts.app')

@section('title', '8 Komponen CRMC - ' . $subBidangName)

@section('content')
<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- BREADCRUMB & BACK ACTION -->
    <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
        <nav class="flex items-center space-x-2 text-slate-500">
            <a href="{{ url('/') }}" class="hover:text-blue-900 flex items-center gap-1 font-medium transition">
                <i data-lucide="home" class="w-3.5 h-3.5"></i>
                <span>Beranda CRMC</span>
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
            <span class="text-slate-600 font-semibold">{{ $parentBidang }}</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
            <span class="text-amber-600 font-bold truncate max-w-xs">{{ $subBidangName }}</span>
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ url('/') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold transition shadow-sm">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Kembali ke Dashboard</span>
            </a>
            <button onclick="window.print()" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold transition shadow-sm">
                <i data-lucide="printer" class="w-3.5 h-3.5 text-slate-500"></i>
                <span class="hidden sm:inline">Cetak Laporan</span>
            </button>
        </div>
    </div>

    <!-- FLASH SUCCESS MESSAGE -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center space-x-3 text-xs text-emerald-900 shadow-sm animate-fade-in">
        <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
            <i data-lucide="check" class="w-4 h-4"></i>
        </div>
        <div class="flex-1">
            <p class="font-bold">Pembaruan Berhasil Disimpan!</p>
            <p class="text-emerald-700">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    <!-- VALIDATION FAILED: tanpa blok ini, redirect gagal validasi tampil
         seperti tidak terjadi sama sekali karena view hanya memeriksa
         session('success'). -->
    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-start space-x-3 text-xs text-rose-900 shadow-sm animate-fade-in">
        <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0">
            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
        </div>
        <div class="flex-1">
            <p class="font-bold">Perubahan tidak tersimpan!</p>
            <ul class="mt-1 space-y-0.5 list-disc list-inside text-rose-700">
                @foreach($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center space-x-3 text-xs text-rose-900 shadow-sm animate-fade-in">
        <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0">
            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
        </div>
        <div class="flex-1">
            <p class="font-bold">Gagal Diproses!</p>
            <p class="text-rose-700">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <!-- SUB-BIDANG HERO BANNER -->
    <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white border border-slate-800 shadow-xl relative overflow-hidden">
        <!-- Background Decorative Glow -->
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="space-y-3 max-w-3xl">
                <div class="inline-flex items-center space-x-2 px-3 py-1 bg-amber-500/20 text-amber-400 rounded-full text-xs font-bold border border-amber-500/30">
                    <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                    <span class="uppercase tracking-wider">{{ $parentBidang }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight text-white leading-tight">
                    {{ $subBidangName }}
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed">
                    Dokumen resmi Continuous Monitoring on Risk Control (CRMC) terpadu. Memuat 8 komponen instrumen kepatuhan, mitigasi risiko, SOP, dan pembuktian realisasi kegiatan.
                </p>
            </div>

            <!-- Header Quick Badges -->
            <div class="flex flex-wrap lg:flex-col items-start lg:items-end gap-2.5 shrink-0">
                <div class="px-4 py-2 rounded-2xl bg-slate-800/80 border border-slate-700 backdrop-blur-sm flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-xs text-slate-300">Tahun Anggaran: <strong class="text-white">{{ $selectedTahun }}</strong></span>
                </div>
                <div class="px-4 py-2 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 flex items-center space-x-2 text-xs font-semibold">
                    <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                    <span>Status: 8/8 Komponen Terpenuhi</span>
                </div>
                <span class="text-[11px] text-slate-400">Pembaruan: {{ $crmcData['tanggal_update'] }}</span>
            </div>
        </div>
    </div>

    <!-- YEAR FILTER BAR -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center space-x-2 text-xs">
            <i data-lucide="calendar" class="w-4 h-4 text-amber-500"></i>
            <span class="font-bold text-slate-700">Pilih Tahun Dokumen:</span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @foreach($availableYears as $tahun)
                <a href="{{ route('crmc.show', ['slug' => $slug, 'tahun' => $tahun]) }}" 
                   class="px-4 py-1.5 rounded-xl text-xs font-bold transition shadow-sm border {{ $selectedTahun == $tahun ? 'bg-amber-500 text-slate-950 border-amber-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:border-amber-300' }}">
                    {{ $tahun }}
                </a>
            @endforeach
            @if(!$availableYears->contains(date('Y')))
                <a href="{{ route('crmc.show', ['slug' => $slug, 'tahun' => date('Y')]) }}" 
                   class="px-4 py-1.5 rounded-xl text-xs font-bold transition shadow-sm border {{ $selectedTahun == date('Y') ? 'bg-amber-500 text-slate-950 border-amber-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:border-amber-300' }}">
                    {{ date('Y') }} <span class="text-[10px] opacity-70">(Baru)</span>
                </a>
            @endif
        </div>
    </div>

    <!-- STATS OVERVIEW CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Tingkat Residu -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Residu Risiko</p>
                <div class="flex items-center gap-2 mt-1">
                    @php
                        $residuColor = match(strtolower($crmcData['residu'])) {
                            'rendah' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'sedang' => 'bg-amber-100 text-amber-800 border-amber-200',
                            'tinggi' => 'bg-rose-100 text-rose-800 border-rose-200',
                            default => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        };
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-extrabold {{ $residuColor }} border">
                        {{ strtoupper($crmcData['residu']) }} RISK
                    </span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- 2. Kelengkapan Berkas -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Kelengkapan 8 Komponen</p>
                <p class="text-xl font-black text-slate-900 mt-1">8 / 8 <span class="text-xs font-semibold text-emerald-600">(100%)</span></p>
                <div class="w-28 bg-slate-100 h-1.5 rounded-full mt-2 overflow-hidden">
                    <div class="bg-emerald-500 h-full rounded-full w-full"></div>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center">
                <i data-lucide="layers" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- 3. PIC Pengendali -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center justify-between">
            <div class="truncate mr-2">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">PIC Pengendali</p>
                <h4 class="text-xs font-bold text-slate-900 mt-1 truncate" title="{{ $crmcData['pegawai'] }}">{{ $crmcData['pegawai'] }}</h4>
                <p class="text-[10px] text-slate-500 font-mono mt-0.5">NIP. {{ $crmcData['pegawai_nip'] }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="user-check" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- 4. Dokumen Terunggah -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Dokumen Terunggah</p>
                @php $totalLampiran = collect($lampiranGrouped)->flatten()->count(); @endphp
                <h4 class="text-xl font-black text-slate-900 mt-1">{{ $totalLampiran }} <span class="text-xs font-semibold text-slate-500">file</span></h4>
                <p class="text-[10px] text-slate-500 mt-0.5">Tahun {{ $selectedTahun }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <i data-lucide="file-stack" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- 8 KOMPONEN SECTION TITLE -->
    <div class="flex items-center justify-between pt-2">
        <div>
            <span class="text-xs font-extrabold uppercase tracking-widest text-amber-600">Instrumen Lengkap CRMC</span>
            <h2 class="text-xl font-black text-slate-900">Rincian 8 Komponen Wajib</h2>
        </div>
        <span class="text-xs text-slate-400 hidden sm:inline">Pusbangkom Kementerian Pekerjaan Umum</span>
    </div>

    <!-- 8 KOMPONEN FULL BENTO GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <!-- ==================== KOMPONEN 1: Identitas Pegawai ==================== -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between group md:col-span-2">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wide bg-blue-100 text-blue-900 rounded-lg">
                            Komponen 1
                        </span>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Identitas Pegawai</span>
                    </div>

                    @auth
                        @if(Auth::user()->isAdmin())
                        <button onclick="openPenugasanModal()" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl text-xs transition shadow-sm">
                            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                            <span>Atur Penugasan (Admin)</span>
                        </button>
                        @endif
                    @endauth
                </div>

                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">1. Identitas Pegawai & Penugasan PIC (3 Lapis)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Hierarki pertanggungjawaban terbagi atas 1 Pemilik Risiko, 1 Pengendali Mutu, dan Tim Pengendali Risiko.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 pt-1">
                    <!-- Lapis 1: Pemilik Risiko (1 Orang) - Foto Besar -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/90 flex flex-col items-center text-center space-y-3">
                        <div class="flex items-center justify-between w-full">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-blue-900 bg-blue-100 px-2 py-0.5 rounded">1. Pemilik Risiko</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $pemilikRisiko ? '1 Orang' : 'Belum Ada' }}</span>
                        </div>
                        @if($pemilikRisiko)
                            <img src="{{ $pemilikRisiko->foto_url }}" width="224" height="288" loading="lazy" decoding="async"
                                 alt="{{ $pemilikRisiko->name }}"
                                 class="w-56 h-72 rounded-2xl object-cover object-top border-2 border-blue-900 shadow-md">
                            <div class="space-y-0.5">
                                <h5 class="text-xs sm:text-sm font-bold text-slate-900">{{ $pemilikRisiko->name }}</h5>
                                <p class="text-[11px] text-slate-500">NIP. {{ $pemilikRisiko->nip ?? '-' }}</p>
                                <p class="text-[10px] text-blue-800 font-semibold">{{ $pemilikRisiko->jabatan ?? '-' }}</p>
                            </div>
                        @else
                            <div class="w-56 h-72 rounded-2xl border-2 border-dashed border-blue-900/30 bg-white/60 flex flex-col items-center justify-center text-center px-4 space-y-1.5">
                                <i data-lucide="user-round-x" class="w-8 h-8 text-slate-300"></i>
                                <p class="text-[11px] font-bold text-slate-500 leading-tight">Pemilik Risiko<br>Belum Ditugaskan</p>
                                <p class="text-[10px] text-slate-400">Atur lewat tombol "Atur Penugasan"</p>
                            </div>
                        @endif
                    </div>

                    <!-- Lapis 2: Pengendali Mutu (1 Orang per Bidang/Sub-Bidang) - Foto Besar -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/90 flex flex-col items-center text-center space-y-3">
                        <div class="flex items-center justify-between w-full">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-900 bg-amber-100 px-2 py-0.5 rounded">2. Pengendali Mutu</span>
                            <span class="text-[10px] text-slate-400 font-mono">{{ $pengendaliMutu ? '1 Orang' : 'Belum Ada' }}</span>
                        </div>
                        @if($pengendaliMutu)
                            <img src="{{ $pengendaliMutu->foto_url }}" width="224" height="288" loading="lazy" decoding="async"
                                 alt="{{ $pengendaliMutu->name }}"
                                 class="w-56 h-72 rounded-2xl object-cover object-top border-2 border-amber-500 shadow-md">
                            <div class="space-y-0.5">
                                <h5 class="text-xs sm:text-sm font-bold text-slate-900">{{ $pengendaliMutu->name }}</h5>
                                <p class="text-[11px] text-slate-500">NIP. {{ $pengendaliMutu->nip ?? '-' }}</p>
                                <p class="text-[10px] text-amber-800 font-semibold">{{ $pengendaliMutu->jabatan ?? '-' }}</p>
                            </div>
                        @else
                            <div class="w-56 h-72 rounded-2xl border-2 border-dashed border-amber-500/40 bg-white/60 flex flex-col items-center justify-center text-center px-4 space-y-1.5">
                                <i data-lucide="user-round-x" class="w-8 h-8 text-slate-300"></i>
                                <p class="text-[11px] font-bold text-slate-500 leading-tight">Pengendali Mutu<br>Belum Ditugaskan</p>
                                <p class="text-[10px] text-slate-400">Atur lewat tombol "Atur Penugasan"</p>
                            </div>
                        @endif
                    </div>

                    <!-- Lapis 3: Tim Pengendali Risiko (Bisa Banyak Orang) -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/90 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded">
                                3. Pengendali Risiko
                            </span>
                            <span class="text-[10px] text-slate-400 font-medium">{{ $pengendaliRisikoList->count() }} Orang</span>
                        </div>

                        <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1">
                            @forelse($pengendaliRisikoList as $staf)
                            <div class="p-3 bg-white hover:bg-slate-100/80 rounded-xl border border-slate-200/80 flex items-center space-x-3 transition">
                                <img src="{{ $staf->foto_url }}" width="96" height="112" loading="lazy" decoding="async" alt="{{ $staf->name }}" class="w-24 h-28 rounded-xl object-cover object-top border border-emerald-500/80 shrink-0 shadow-xs">
                                <div class="flex-1 min-w-0">
                                    <h6 class="text-xs font-bold text-slate-900 truncate">{{ $staf->name }}</h6>
                                    <p class="text-[10px] text-slate-500 truncate">NIP. {{ $staf->nip ?? '-' }}</p>
                                    <p class="text-[10px] text-emerald-700 font-semibold">{{ $staf->jabatan }}</p>
                                </div>
                                <span class="text-[9px] font-semibold bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full shrink-0">Aktif</span>
                            </div>
                            @empty
                            <div class="p-3 bg-white rounded-xl border border-dashed border-slate-300 text-center text-xs text-slate-400">
                                Belum ada staf pengendali risiko yang ditugaskan.
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold"><i data-lucide="check" class="w-3.5 h-3.5"></i> Terverifikasi BPSDM</span>
                <span class="font-mono text-slate-400">ID: PIC-{{ substr(md5($subBidangName), 0, 6) }}</span>
            </div>
        </div>

        @php
            // Komponen yang mendukung upload dokumen: 2,3,4,5,6,8
            $uploadableKomponens = [
                2 => 'risk_register',
                3 => 'sop',
                4 => 'formulir_pengendalian',
                5 => 'jadwal_pelaksanaan',
                6 => 'bukti_pelaksanaan',
                8 => 'evaluasi',
            ];

            $komponenMeta = [
                2 => ['title' => 'Risk Register Spesifik Acuan', 'desc' => 'Peta identifikasi peristiwa risiko, analisis dampak, dan nomor acuan register.', 'icon' => 'file-spreadsheet'],
                3 => ['title' => 'Standar Operasional Prosedur (SOP)', 'desc' => 'Dasar acuan baku teknis pelaksanaan kerja yang telah disahkan pimpinan.', 'icon' => 'book-open'],
                4 => ['title' => 'Formulir Pengendalian / Daftar Periksa', 'desc' => 'Checklist kepatuhan berkas dan instrumen kendali mutu di lapangan.', 'icon' => 'check-square'],
                5 => ['title' => 'Jadwal Rencana Pelaksanaan', 'desc' => 'Timeline tahapan kegiatan pengawasan risiko sepanjang tahun berjalan.', 'icon' => 'calendar'],
                6 => ['title' => 'Tautan Bukti Pelaksanaan', 'desc' => 'Repositori penyimpanan dokumen bukti fisik, foto, notula, dan laporan riil.', 'icon' => 'link-2'],
            ];
        @endphp

        <!-- ==================== KOMPONEN 2 - 6 ==================== -->
        @foreach($komponenMeta as $kompNum => $meta)
        @php $kategoriKey = $uploadableKomponens[$kompNum]; @endphp
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wide bg-blue-100 text-blue-900 rounded-lg">
                        Komponen {{ $kompNum }}
                    </span>
                    <div class="flex items-center gap-2">
                        @auth
                        <button onclick="openUploadModal('{{ $kategoriKey }}', '{{ $kompNum }}. {{ $meta['title'] }}')" class="inline-flex items-center space-x-1 px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-900 rounded-lg text-[10px] font-bold transition">
                            <i data-lucide="upload" class="w-3 h-3"></i>
                            <span>Upload</span>
                        </button>
                        @if(Auth::user()->isAdmin())
                        <button onclick="openResiduKomponenModal('{{ $kategoriKey }}')" class="inline-flex items-center space-x-1 px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-lg text-[10px] font-bold transition border border-amber-200">
                            <i data-lucide="edit-3" class="w-3 h-3"></i>
                            <span>Residu</span>
                        </button>
                        @endif
                        @endauth
                        <i data-lucide="{{ $meta['icon'] }}" class="w-5 h-5 text-blue-900"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">{{ $kompNum }}. {{ $meta['title'] }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $meta['desc'] }}</p>
                </div>

                @if($kompNum === 2)
                <!-- Risk Register Detail -->
                <div class="space-y-3 pt-1">
                    <div class="bg-slate-900 text-white p-4 rounded-2xl border border-slate-800 relative overflow-hidden">
                        <div class="text-[10px] font-bold text-amber-400 uppercase tracking-widest mb-1">Kode Register Resmi</div>
                        <div class="text-base sm:text-lg font-mono font-bold text-white flex items-center justify-between">
                            <span>{{ $crmcData['risk_register'] }}</span>
                            <span class="text-[10px] bg-slate-800 text-slate-300 px-2 py-0.5 rounded font-sans border border-slate-700">Tersertifikasi</span>
                        </div>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Uraian Risiko Teridentifikasi</span>
                        <p class="text-xs text-slate-700 leading-relaxed font-medium">{{ $crmcData['deskripsi_risiko'] }}</p>
                    </div>
                </div>
                @elseif($kompNum === 3)
                <!-- SOP Detail -->
                <div class="space-y-3 pt-1">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between gap-3">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-xs shrink-0">PDF</div>
                            <div class="truncate">
                                <h4 class="text-xs font-bold text-slate-900 truncate">{{ $crmcData['sop_file'] }}</h4>
                                <p class="text-[10px] text-slate-500 mt-0.5 font-mono">{{ $crmcData['sop_nomor'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @elseif($kompNum === 4)
                <!-- Checklist Detail -->
                <div class="space-y-3 pt-1">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between gap-3">
                        <div class="flex items-center space-x-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0">
                                <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                            </div>
                            <div class="truncate">
                                <h4 class="text-xs font-bold text-slate-900 truncate">{{ $crmcData['checklist_file'] }}</h4>
                                <span class="inline-block mt-0.5 text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">{{ $crmcData['checklist_status'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                @elseif($kompNum === 5)
                <!-- Jadwal Detail -->
                <div class="space-y-3 pt-1">
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Periode Aktif</span>
                            <h4 class="text-xs font-bold text-slate-900">{{ $crmcData['jadwal'] }}</h4>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 bg-blue-100 text-blue-900 rounded-lg">Aktif Terjadwal</span>
                    </div>
                    <div class="grid grid-cols-4 gap-2 text-center text-xs">
                        <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200">
                            <span class="text-[10px] font-bold text-emerald-800 block">TW I</span>
                            <span class="text-[10px] text-emerald-600 font-medium">Selesai</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200">
                            <span class="text-[10px] font-bold text-emerald-800 block">TW II</span>
                            <span class="text-[10px] text-emerald-600 font-medium">Selesai</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-blue-50 border border-blue-200">
                            <span class="text-[10px] font-bold text-blue-800 block">TW III</span>
                            <span class="text-[10px] text-blue-600 font-bold">Berjalan</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200">
                            <span class="text-[10px] font-bold text-slate-600 block">TW IV</span>
                            <span class="text-[10px] text-slate-400">Menunggu</span>
                        </div>
                    </div>
                </div>
                @elseif($kompNum === 6)
                <!-- Bukti Pelaksanaan Detail -->
                <div class="space-y-3 pt-1">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Tautan Cloud Drive Resmi</span>
                        <div class="flex items-center gap-2">
                            <input type="text" readonly value="{{ $crmcData['bukti_url'] }}" class="w-full text-xs font-mono text-blue-900 bg-white p-2 rounded-xl border border-slate-200 truncate focus:outline-none">
                            <button onclick="navigator.clipboard.writeText('{{ $crmcData['bukti_url'] }}'); showToast('Tautan berhasil disalin!');" class="p-2 bg-slate-200 hover:bg-slate-300 rounded-xl transition shrink-0" title="Salin Tautan">
                                <i data-lucide="copy" class="w-4 h-4 text-slate-700"></i>
                            </button>
                        </div>
                    </div>
                </div>
                @endif

                <!-- PREVIEW DOKUMEN / LAMPIRAN (Carousel jika > 1) -->
                @php $lampiranList = $lampiranGrouped[$kategoriKey] ?? collect(); @endphp
                @if($lampiranList->count() > 0)
                <div class="pt-2 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                            <i data-lucide="paperclip" class="w-3.5 h-3.5 text-amber-500"></i>
                            Dokumen Terunggah ({{ $lampiranList->count() }} file)
                        </span>
                    </div>

                    @if($lampiranList->count() === 1)
                        @php $lamp = $lampiranList->first(); @endphp
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between gap-3">
                            <div class="flex items-center space-x-3 min-w-0">
                                @php
                                    $iconColor = match($lamp->tipe_file) {
                                        'pdf' => 'bg-rose-100 text-rose-600',
                                        'doc', 'docx' => 'bg-blue-100 text-blue-600',
                                        'xls', 'xlsx' => 'bg-emerald-100 text-emerald-600',
                                        'jpg', 'jpeg', 'png' => 'bg-purple-100 text-purple-600',
                                        default => 'bg-slate-100 text-slate-600',
                                    };
                                @endphp
                                <div class="w-9 h-9 rounded-lg {{ $iconColor }} flex items-center justify-center text-[10px] font-bold shrink-0 uppercase">{{ $lamp->tipe_file }}</div>
                                <div class="truncate">
                                    <h5 class="text-xs font-bold text-slate-900 truncate">{{ $lamp->nama_file }}</h5>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="{{ $lamp->file_path }}" target="_blank" class="p-1.5 bg-blue-900 hover:bg-blue-950 text-white rounded-lg transition" title="Preview">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                </a>
                                <a href="{{ $lamp->file_path }}" download class="p-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg transition" title="Unduh">
                                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                </a>
                                @auth
                                    @if(Auth::user()->isAdmin())
                                    <form action="{{ route('crmc.lampiran.delete', $lamp->id) }}" method="POST" onsubmit="return confirm('Hapus lampiran ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Hapus">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    @else
                        <!-- CAROUSEL untuk Multiple Dokumen -->
                        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-50" x-data="{ activeSlide: 0, total: {{ $lampiranList->count() }} }">
                            <div class="flex transition-transform duration-300" id="carousel-{{ $kategoriKey }}" style="transform: translateX(0%)">
                                @foreach($lampiranList as $idx => $lamp)
                                <div class="w-full flex-shrink-0 p-3 flex items-center justify-between gap-3" data-slide="{{ $idx }}">
                                    <div class="flex items-center space-x-3 min-w-0">
                                        @php
                                            $iconColor = match($lamp->tipe_file) {
                                                'pdf' => 'bg-rose-100 text-rose-600',
                                                'doc', 'docx' => 'bg-blue-100 text-blue-600',
                                                'xls', 'xlsx' => 'bg-emerald-100 text-emerald-600',
                                                'jpg', 'jpeg', 'png' => 'bg-purple-100 text-purple-600',
                                                default => 'bg-slate-100 text-slate-600',
                                            };
                                        @endphp
                                        <div class="w-9 h-9 rounded-lg {{ $iconColor }} flex items-center justify-center text-[10px] font-bold shrink-0 uppercase">{{ $lamp->tipe_file }}</div>
                                        <div class="truncate">
                                            <h5 class="text-xs font-bold text-slate-900 truncate">{{ $lamp->nama_file }}</h5>
                                            <span class="text-[10px] text-slate-400">File {{ $idx + 1 }} / {{ $lampiranList->count() }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <a href="{{ $lamp->file_path }}" target="_blank" class="p-1.5 bg-blue-900 hover:bg-blue-950 text-white rounded-lg transition" title="Preview">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        </a>
                                        <a href="{{ $lamp->file_path }}" download class="p-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg transition" title="Unduh">
                                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                        </a>
                                        @auth
                                            @if(Auth::user()->isAdmin())
                                            <form action="{{ route('crmc.lampiran.delete', $lamp->id) }}" method="POST" onsubmit="return confirm('Hapus lampiran ini?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Hapus">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                            @endif
                                        @endauth
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <!-- Carousel Controls -->
                            <div class="flex items-center justify-between px-3 pb-2.5 pt-0.5">
                                <button onclick="carouselPrev('{{ $kategoriKey }}', {{ $lampiranList->count() }})" class="p-1 bg-white hover:bg-slate-100 rounded-lg border border-slate-200 transition">
                                    <i data-lucide="chevron-left" class="w-3.5 h-3.5 text-slate-600"></i>
                                </button>
                                <div class="flex items-center gap-1" id="dots-{{ $kategoriKey }}">
                                    @foreach($lampiranList as $idx => $l)
                                    <span class="w-1.5 h-1.5 rounded-full {{ $idx === 0 ? 'bg-amber-500' : 'bg-slate-300' }} transition" data-dot="{{ $idx }}"></span>
                                    @endforeach
                                </div>
                                <button onclick="carouselNext('{{ $kategoriKey }}', {{ $lampiranList->count() }})" class="p-1 bg-white hover:bg-slate-100 rounded-lg border border-slate-200 transition">
                                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-600"></i>
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
                @endif
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold"><i data-lucide="check" class="w-3.5 h-3.5"></i> Terverifikasi</span>
                <span class="text-slate-400">{{ $selectedTahun }}</span>
            </div>
        </div>
        @endforeach

        <!-- ==================== KOMPONEN 7: Status Residu Risiko (Admin Only) ==================== -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wide bg-blue-100 text-blue-900 rounded-lg">
                        Komponen 7
                    </span>
                    <div class="flex items-center gap-2">
                        @auth
                            @if(Auth::user()->isAdmin())
                            <button onclick="openResiduModal()" class="inline-flex items-center space-x-1 px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-lg text-[10px] font-bold transition border border-amber-200">
                                <i data-lucide="edit-3" class="w-3 h-3"></i>
                                <span>Ubah (Admin)</span>
                            </button>
                            @endif
                        @endauth
                        <i data-lucide="gauge" class="w-5 h-5 text-blue-900"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">7. Status Residu Risiko</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Tingkat sisa risiko pasca penerapan seluruh sistem kendali internal. <em class="text-amber-600 font-semibold">Hanya Admin yang dapat mengubah.</em></p>
                </div>

                <div class="space-y-3 pt-1">
                    @php
                        $residuBg = match(strtolower($crmcData['residu'])) {
                            'rendah' => 'bg-emerald-50 border-emerald-200',
                            'sedang' => 'bg-amber-50 border-amber-200',
                            'tinggi' => 'bg-rose-50 border-rose-200',
                            default => 'bg-emerald-50 border-emerald-200',
                        };
                        $residuTextColor = match(strtolower($crmcData['residu'])) {
                            'rendah' => 'text-emerald-900',
                            'sedang' => 'text-amber-900',
                            'tinggi' => 'text-rose-900',
                            default => 'text-emerald-900',
                        };
                        $residuLabel = match(strtolower($crmcData['residu'])) {
                            'rendah' => 'Low Risk',
                            'sedang' => 'Medium Risk',
                            'tinggi' => 'High Risk',
                            default => 'Low Risk',
                        };
                    @endphp
                    <div class="p-4 {{ $residuBg }} rounded-2xl border flex items-center justify-between">
                        <div>
                            <span class="text-[10px] font-bold {{ $residuTextColor }} uppercase tracking-wider">Tingkat Residu Saat Ini</span>
                            <div class="text-xl font-extrabold {{ $residuTextColor }} mt-0.5 flex items-center gap-1.5">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                                <span>{{ strtoupper($crmcData['residu']) }} ({{ $residuLabel }})</span>
                            </div>
                        </div>
                    </div>

                    <!-- Risk Meter Visualizer -->
                    <div class="space-y-1.5">
                        <div class="flex justify-between text-[11px] text-slate-500 font-medium">
                            <span class="text-emerald-700 font-bold">Rendah (Low)</span>
                            <span class="text-amber-700">Sedang (Medium)</span>
                            <span class="text-rose-700">Tinggi (High)</span>
                        </div>
                        <div class="h-2.5 w-full bg-slate-100 rounded-full flex overflow-hidden p-0.5">
                            @php
                                $meterWidth = match(strtolower($crmcData['residu'])) {
                                    'rendah' => 'w-1/3',
                                    'sedang' => 'w-2/3',
                                    'tinggi' => 'w-full',
                                    default => 'w-1/3',
                                };
                                $meterColor = match(strtolower($crmcData['residu'])) {
                                    'rendah' => 'bg-emerald-500',
                                    'sedang' => 'bg-amber-400',
                                    'tinggi' => 'bg-rose-500',
                                    default => 'bg-emerald-500',
                                };
                            @endphp
                            <div class="{{ $meterColor }} {{ $meterWidth }} rounded-full transition-all duration-500"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold"><i data-lucide="check" class="w-3.5 h-3.5"></i> Dalam Batas Toleransi</span>
                <span class="text-slate-400">Selera Risiko: Low</span>
            </div>
        </div>

        <!-- ==================== KOMPONEN 8: Evaluasi & Rencana Perbaikan ==================== -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wide bg-blue-100 text-blue-900 rounded-lg">
                        Komponen 8
                    </span>
                    <div class="flex items-center gap-2">
                        @auth
                        <button onclick="openUploadModal('evaluasi', '8. Evaluasi & Rencana Perbaikan')" class="inline-flex items-center space-x-1 px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-900 rounded-lg text-[10px] font-bold transition">
                            <i data-lucide="upload" class="w-3 h-3"></i>
                            <span>Upload</span>
                        </button>
                        @if(Auth::user()->isAdmin())
                        <button onclick="openResiduKomponenModal('evaluasi')" class="inline-flex items-center space-x-1 px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-lg text-[10px] font-bold transition border border-amber-200">
                            <i data-lucide="edit-3" class="w-3 h-3"></i>
                            <span>Residu</span>
                        </button>
                        @endif
                        @endauth
                        <i data-lucide="trending-up" class="w-5 h-5 text-blue-900"></i>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">8. Evaluasi & Rencana Perbaikan</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Tindak lanjut penyempurnaan proses kendali dan rencana aksi berkelanjutan.</p>
                </div>

                <!-- Year Selector untuk Komponen 8 -->
                <div class="flex items-center gap-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Tahun:</span>
                    <select onchange="if(this.value) window.location.href = '{{ route('crmc.show', ['slug' => $slug]) }}?tahun=' + this.value" class="px-3 py-1.5 rounded-xl text-xs font-bold border border-slate-200 bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        @foreach($availableYears as $tahun)
                            <option value="{{ $tahun }}" {{ $selectedTahun == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-3 pt-1">
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Hasil Evaluasi Berkala</span>
                        <p class="text-xs text-slate-700 leading-relaxed font-medium">{{ $crmcData['evaluasi'] }}</p>
                    </div>

                    <div class="p-3 bg-amber-50/70 border border-amber-200/80 rounded-2xl space-y-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-900">Rencana Aksi Berkelanjutan:</span>
                        <ul class="text-[11px] text-amber-900 space-y-1 list-disc list-inside">
                            <li>Mempertahankan kepatuhan pengunggahan berkas bukti tepat waktu.</li>
                            <li>Melakukan review SOP setiap semester bersama tim penjamin mutu.</li>
                        </ul>
                    </div>
                </div>

                <!-- Preview Dokumen Komponen 8 -->
                @php $lampiranEvaluasi = $lampiranGrouped['evaluasi'] ?? collect(); @endphp
                @if($lampiranEvaluasi->count() > 0)
                <div class="pt-2 space-y-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1.5">
                        <i data-lucide="paperclip" class="w-3.5 h-3.5 text-amber-500"></i>
                        Dokumen Terunggah ({{ $lampiranEvaluasi->count() }} file)
                    </span>

                    @if($lampiranEvaluasi->count() === 1)
                        @php $lamp = $lampiranEvaluasi->first(); @endphp
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between gap-3">
                            <div class="flex items-center space-x-3 min-w-0">
                                @php
                                    $iconColor = match($lamp->tipe_file) {
                                        'pdf' => 'bg-rose-100 text-rose-600',
                                        'doc', 'docx' => 'bg-blue-100 text-blue-600',
                                        default => 'bg-slate-100 text-slate-600',
                                    };
                                @endphp
                                <div class="w-9 h-9 rounded-lg {{ $iconColor }} flex items-center justify-center text-[10px] font-bold shrink-0 uppercase">{{ $lamp->tipe_file }}</div>
                                <div class="truncate">
                                    <h5 class="text-xs font-bold text-slate-900 truncate">{{ $lamp->nama_file }}</h5>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="{{ $lamp->file_path }}" target="_blank" class="p-1.5 bg-blue-900 hover:bg-blue-950 text-white rounded-lg transition" title="Preview"><i data-lucide="eye" class="w-3.5 h-3.5"></i></a>
                                <a href="{{ $lamp->file_path }}" download class="p-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg transition" title="Unduh"><i data-lucide="download" class="w-3.5 h-3.5"></i></a>
                                @auth @if(Auth::user()->isAdmin())
                                <form action="{{ route('crmc.lampiran.delete', $lamp->id) }}" method="POST" onsubmit="return confirm('Hapus lampiran ini?');">@csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Hapus"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                                </form>
                                @endif @endauth
                            </div>
                        </div>
                    @else
                        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-50">
                            <div class="flex transition-transform duration-300" id="carousel-evaluasi" style="transform: translateX(0%)">
                                @foreach($lampiranEvaluasi as $idx => $lamp)
                                <div class="w-full flex-shrink-0 p-3 flex items-center justify-between gap-3" data-slide="{{ $idx }}">
                                    <div class="flex items-center space-x-3 min-w-0">
                                        @php
                                            $iconColor = match($lamp->tipe_file) {
                                                'pdf' => 'bg-rose-100 text-rose-600',
                                                'doc', 'docx' => 'bg-blue-100 text-blue-600',
                                                default => 'bg-slate-100 text-slate-600',
                                            };
                                        @endphp
                                        <div class="w-9 h-9 rounded-lg {{ $iconColor }} flex items-center justify-center text-[10px] font-bold shrink-0 uppercase">{{ $lamp->tipe_file }}</div>
                                        <div class="truncate">
                                            <h5 class="text-xs font-bold text-slate-900 truncate">{{ $lamp->nama_file }}</h5>
                                            <span class="text-[10px] text-slate-400">File {{ $idx + 1 }} / {{ $lampiranEvaluasi->count() }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <a href="{{ $lamp->file_path }}" target="_blank" class="p-1.5 bg-blue-900 hover:bg-blue-950 text-white rounded-lg transition"><i data-lucide="eye" class="w-3.5 h-3.5"></i></a>
                                        <a href="{{ $lamp->file_path }}" download class="p-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg transition"><i data-lucide="download" class="w-3.5 h-3.5"></i></a>
                                        @auth @if(Auth::user()->isAdmin())
                                        <form action="{{ route('crmc.lampiran.delete', $lamp->id) }}" method="POST" onsubmit="return confirm('Hapus?');">@csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                                        </form>
                                        @endif @endauth
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <div class="flex items-center justify-between px-3 pb-2.5 pt-0.5">
                                <button onclick="carouselPrev('evaluasi', {{ $lampiranEvaluasi->count() }})" class="p-1 bg-white hover:bg-slate-100 rounded-lg border border-slate-200 transition"><i data-lucide="chevron-left" class="w-3.5 h-3.5 text-slate-600"></i></button>
                                <div class="flex items-center gap-1" id="dots-evaluasi">
                                    @foreach($lampiranEvaluasi as $idx => $l)
                                    <span class="w-1.5 h-1.5 rounded-full {{ $idx === 0 ? 'bg-amber-500' : 'bg-slate-300' }} transition" data-dot="{{ $idx }}"></span>
                                    @endforeach
                                </div>
                                <button onclick="carouselNext('evaluasi', {{ $lampiranEvaluasi->count() }})" class="p-1 bg-white hover:bg-slate-100 rounded-lg border border-slate-200 transition"><i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-600"></i></button>
                            </div>
                        </div>
                    @endif
                </div>
                @endif
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold"><i data-lucide="check" class="w-3.5 h-3.5"></i> Kaizen / Perbaikan Terus Menerus</span>
                <span class="text-slate-400">Review Terjadwal</span>
            </div>
        </div>

    </div>

    <!-- BOTTOM ACTION BAR -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-bold shrink-0">
                <i data-lucide="check-check" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-900">Dokumen CRMC Siap untuk Pengawasan Berkala</h4>
                <p class="text-[11px] text-slate-500">Seluruh 8 komponen terverifikasi dan memenuhi standar kepatuhan BPSDM Kementerian PU.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ url('/') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                Kembali ke Dashboard
            </a>
            @auth
                @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.pegawai.index') }}" class="px-4 py-2 bg-blue-900 hover:bg-blue-950 text-white font-bold rounded-xl text-xs transition shadow-sm">
                    Kelola Akun Pegawai
                </a>
                @endif
            @endauth
        </div>
    </div>

</main>
@endsection

@section('modals')
    <!-- MODAL UPLOAD DOKUMEN PER KOMPONEN -->
    <div id="uploadDokumenModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
        <div class="bg-white w-full max-w-lg max-h-[92vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-slate-100">
            <div class="bg-slate-900 text-white p-5 sm:p-6 flex items-start justify-between border-b border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold shrink-0">
                        <i data-lucide="upload" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest">Upload Dokumen</span>
                        <h3 id="uploadModalTitle" class="text-lg font-extrabold text-white">Komponen</h3>
                    </div>
                </div>
                <button onclick="closeUploadModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-5 sm:p-6">
                <form action="{{ route('crmc.upload.dokumen', $slug) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                    @csrf
                    <input type="hidden" name="kategori_komponen" id="uploadKategori" value="">
                    <input type="hidden" name="tahun_pelaksanaan" value="{{ $selectedTahun }}">

                    <div>
                        <label class="block font-bold text-slate-800 mb-2">Pilih File Dokumen (Multi-Upload) *</label>
                        <div class="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:border-amber-400 transition cursor-pointer" onclick="document.getElementById('uploadFiles').click();">
                            <i data-lucide="cloud-upload" class="w-8 h-8 text-slate-400 mx-auto mb-2"></i>
                            <p class="text-slate-600 font-medium">Klik atau seret file ke sini</p>
                            <p class="text-[10px] text-slate-400 mt-1">Format: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Maks. 10MB/file)</p>
                        </div>
                        <input type="file" name="files[]" id="uploadFiles" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" class="hidden" onchange="showFileNames(this)">
                        <div id="fileNamesList" class="mt-2 space-y-1 hidden"></div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex justify-end space-x-2">
                        <button type="button" onclick="closeUploadModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold shadow-sm transition">Upload Dokumen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL STATUS RESIDU (ADMIN ONLY) -->
    @auth
    @if(Auth::user()->isAdmin())
    <div id="residuModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
        <div class="bg-white w-full max-w-md max-h-[92vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-slate-100">
            <div class="bg-slate-900 text-white p-5 sm:p-6 flex items-start justify-between border-b border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center text-slate-950 font-bold shrink-0">
                        <i data-lucide="gauge" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest">Khusus Admin</span>
                        <h3 class="text-lg font-extrabold text-white">Ubah Status Residu Risiko</h3>
                    </div>
                </div>
                <button onclick="closeResiduModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-5 sm:p-6">
                <form action="{{ route('crmc.update.residu', $slug) }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <input type="hidden" name="tahun_pelaksanaan" value="{{ $selectedTahun }}">

                    <div class="bg-amber-50 p-3 rounded-2xl border border-amber-200 text-xs text-amber-900">
                        <p class="font-bold flex items-center gap-1.5"><i data-lucide="info" class="w-4 h-4 text-amber-600"></i> Perhatian:</p>
                        <p class="mt-1">Perubahan status residu risiko hanya dapat dilakukan oleh Administrator dan akan berlaku untuk tahun <strong>{{ $selectedTahun }}</strong>.</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Status Residu Risiko *</label>
                        <select name="residu" required class="w-full p-2.5 rounded-xl border border-slate-300 font-semibold bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <option value="Rendah" {{ strtolower($crmcData['residu']) == 'rendah' ? 'selected' : '' }}>Rendah (Low Risk)</option>
                            <option value="Sedang" {{ strtolower($crmcData['residu']) == 'sedang' ? 'selected' : '' }}>Sedang (Medium Risk)</option>
                            <option value="Tinggi" {{ strtolower($crmcData['residu']) == 'tinggi' ? 'selected' : '' }}>Tinggi (High Risk)</option>
                        </select>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex justify-end space-x-2">
                        <button type="button" onclick="closeResiduModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold shadow-sm transition">Simpan Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endauth

    <!-- MODAL RESIDU UNTUK SETIAP KOMPONEN (ADMIN ONLY) -->
    @auth
    @if(Auth::user()->isAdmin())
    <div id="residuKomponenModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
        <div class="bg-white w-full max-w-md max-h-[92vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-slate-100">
            <div class="bg-slate-900 text-white p-5 sm:p-6 flex items-start justify-between border-b border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center text-slate-950 font-bold shrink-0">
                        <i data-lucide="gauge" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest">Khusus Admin</span>
                        <h3 id="residuKomponenModalTitle" class="text-lg font-extrabold text-white">Ubah Status Residu</h3>
                    </div>
                </div>
                <button onclick="closeResiduKomponenModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-5 sm:p-6">
                <form id="residuKomponenForm" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <input type="hidden" name="tahun_pelaksanaan" value="{{ $selectedTahun }}">

                    <div class="bg-amber-50 p-3 rounded-2xl border border-amber-200 text-xs text-amber-900">
                        <p class="font-bold flex items-center gap-1.5"><i data-lucide="info" class="w-4 h-4 text-amber-600"></i> Perhatian:</p>
                        <p class="mt-1">Perubahan status residu risiko hanya dapat dilakukan oleh Administrator dan akan berlaku untuk tahun <strong>{{ $selectedTahun }}</strong>.</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Status Residu Risiko *</label>
                        <select name="residu" required class="w-full p-2.5 rounded-xl border border-slate-300 font-semibold bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <option value="Rendah" {{ strtolower($crmcData['residu']) == 'rendah' ? 'selected' : '' }}>Rendah (Low Risk)</option>
                            <option value="Sedang" {{ strtolower($crmcData['residu']) == 'sedang' ? 'selected' : '' }}>Sedang (Medium Risk)</option>
                            <option value="Tinggi" {{ strtolower($crmcData['residu']) == 'tinggi' ? 'selected' : '' }}>Tinggi (High Risk)</option>
                        </select>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex justify-end space-x-2">
                        <button type="button" onclick="closeResiduKomponenModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold shadow-sm transition">Simpan Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endauth

    <!-- MODAL PENUGASAN IDENTITAS PEGAWAI (KHUSUS ADMIN) -->
    @auth
    @if(Auth::user()->isAdmin())
    <div id="penugasanModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
        <div class="bg-white w-full max-w-2xl max-h-[92vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-slate-100">
            
            <div class="bg-slate-900 text-white p-5 sm:p-6 flex items-start justify-between border-b border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center text-slate-950 font-bold shrink-0">
                        <i data-lucide="user-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest">{{ $parentBidang }}</span>
                        <h3 class="text-lg font-extrabold text-white leading-snug">Atur Penugasan Pegawai (Komponen 1)</h3>
                        <p class="text-xs text-slate-400">{{ $subBidangName }}</p>
                    </div>
                </div>
                <button onclick="closePenugasanModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- Petunjuk Hierarki Penugasan Sesuai Kebijakan -->
            <div class="bg-amber-50 px-6 py-3 border-b border-amber-200 text-xs text-amber-900 space-y-1">
                <p class="font-bold flex items-center gap-1.5"><i data-lucide="info" class="w-4 h-4 text-amber-600"></i> Ketentuan Penetapan PIC CRMC:</p>
                <ul class="text-[11px] list-disc list-inside space-y-0.5 text-amber-800">
                    <li><strong>Pemilik Risiko:</strong> Hanya ada <strong>1 orang</strong> (Kepala Pusat).</li>
                    <li><strong>Pengendali Mutu:</strong> Hanya ada <strong>1 orang</strong> di tiap bidang & sub-bidang (Kabag / Kabid).</li>
                    <li><strong>Pengendali Risiko:</strong> Dapat ditugaskan <strong>banyak orang</strong> (Tim Pelaksana Staf).</li>
                </ul>
            </div>

            <div class="flex-1 overflow-y-auto p-5 sm:p-6">
                <form action="{{ route('crmc.penugasan.update', $slug) }}" method="POST" class="space-y-4 text-xs">
                    @csrf

                    <!-- 1. Pemilik Risiko (1 Orang - berlaku untuk semua sub-bidang) -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">
                            1. Pemilik Risiko (Pilih 1 Orang) *
                        </label>
                        <select name="pemilik_risiko_id" required class="w-full p-2.5 rounded-xl border border-slate-300 font-semibold bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            @foreach($allUsers as $u)
                                <option value="{{ $u->id }}" {{ (isset($pemilikRisiko) && $pemilikRisiko->id == $u->id) ? 'selected' : '' }}>
                                    {{ $u->name }} - {{ $u->jabatan }} (NIP. {{ $u->nip }})
                                </option>
                            @endforeach
                        </select>
                        <div class="mt-1 flex items-start gap-1.5 text-[10px] text-blue-800 bg-blue-50 border border-blue-200 rounded-lg px-2 py-1.5">
                            <i data-lucide="globe-2" class="w-3 h-3 shrink-0 mt-0.5"></i>
                            <span><strong>Berlaku untuk semua sub-bidang.</strong> Hanya ada 1 Pemilik Risiko untuk seluruh CRMC, apa pun sub-bidang yang Anda buka. Mengganti di sini langsung berlaku di {{ \App\Models\SubMenu::count() }} sub-bidang sekaligus.</span>
                        </div>
                    </div>

                    <!-- 2. Pengendali Mutu (1 Orang per Bidang) -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">
                            2. Pengendali Mutu (Pilih 1 Orang per Bidang) *
                        </label>
                        <select name="pengendali_mutu_id" required class="w-full p-2.5 rounded-xl border border-slate-300 font-semibold bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            @foreach($allUsers as $u)
                                <option value="{{ $u->id }}" {{ (isset($pengendaliMutu) && $pengendaliMutu->id == $u->id) ? 'selected' : '' }}>
                                    {{ $u->name }} - {{ $u->jabatan }} (NIP. {{ $u->nip }})
                                </option>
                            @endforeach
                        </select>
                        <div class="mt-1 flex items-start gap-1.5 text-[10px] text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-2 py-1.5">
                            <i data-lucide="building-2" class="w-3 h-3 shrink-0 mt-0.5"></i>
                            <span><strong>Berlaku untuk 1 bidang saja:</strong> {{ $parentBidang }}. Otomatis tampil di semua sub-bidang di bawah bidang ini, tetapi <em>tidak</em> memengaruhi bidang lain.</span>
                        </div>
                    </div>

                    <!-- 3. Pengendali Risiko (Bisa Banyak Orang) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block font-bold text-slate-800">
                                3. Tim Pengendali Risiko (Bisa Memilih Banyak Staf) *
                            </label>
                            <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-bold">Multi-Selection</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mb-2">Centang semua staf teknis yang menjadi pengendali operasional untuk sub-bidang ini:</p>

                        @php
                            $selectedPengendaliIds = $pengendaliRisikoList->pluck('id')->toArray();
                        @endphp

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-56 overflow-y-auto p-1 border border-slate-200 rounded-2xl bg-slate-50/50">
                            @foreach($allUsers as $u)
                            <label class="flex items-center space-x-3 p-2.5 rounded-xl bg-white border border-slate-200 hover:border-amber-400 cursor-pointer transition select-none">
                                <input type="checkbox" name="pengendali_risiko_ids[]" value="{{ $u->id }}" 
                                       {{ in_array($u->id, $selectedPengendaliIds) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-amber-500 focus:ring-amber-400 border-slate-300 shrink-0">
                                <img src="{{ $u->foto_url }}" alt="{{ $u->name }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 shrink-0">
                                <div class="min-w-0 flex-1">
                                    <h6 class="text-xs font-bold text-slate-900 truncate">{{ $u->name }}</h6>
                                    <p class="text-[10px] text-slate-500 truncate">{{ $u->jabatan }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-200 flex justify-end space-x-2">
                        <button type="button" onclick="closePenugasanModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold shadow-sm transition">
                            Simpan Penugasan Pegawai
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-slate-50 px-6 py-3 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
                <span>Hak Akses: Administrator CRMC</span>
                <button onclick="closePenugasanModal()" class="px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg font-medium">Tutup</button>
            </div>

        </div>
    </div>
    @endif
    @endauth
@endsection

@push('scripts')
<script>
    // Carousel State
    const carouselState = {};

    function carouselNext(id, total) {
        if (!carouselState[id]) carouselState[id] = 0;
        carouselState[id] = (carouselState[id] + 1) % total;
        updateCarousel(id, total);
    }

    function carouselPrev(id, total) {
        if (!carouselState[id]) carouselState[id] = 0;
        carouselState[id] = (carouselState[id] - 1 + total) % total;
        updateCarousel(id, total);
    }

    function updateCarousel(id, total) {
        const carousel = document.getElementById('carousel-' + id);
        const dotsContainer = document.getElementById('dots-' + id);
        if (carousel) {
            carousel.style.transform = `translateX(-${carouselState[id] * 100}%)`;
        }
        if (dotsContainer) {
            dotsContainer.querySelectorAll('[data-dot]').forEach((dot, idx) => {
                dot.className = `w-1.5 h-1.5 rounded-full transition ${idx === carouselState[id] ? 'bg-amber-500' : 'bg-slate-300'}`;
            });
        }
    }

    // Upload Modal
    function openUploadModal(kategori, title) {
        document.getElementById('uploadKategori').value = kategori;
        document.getElementById('uploadModalTitle').textContent = title;
        document.getElementById('uploadDokumenModal').classList.remove('hidden');
    }
    function closeUploadModal() {
        document.getElementById('uploadDokumenModal').classList.add('hidden');
        document.getElementById('fileNamesList').classList.add('hidden');
        document.getElementById('fileNamesList').innerHTML = '';
    }

    function showFileNames(input) {
        const list = document.getElementById('fileNamesList');
        list.innerHTML = '';
        if (input.files.length > 0) {
            list.classList.remove('hidden');
            Array.from(input.files).forEach((file, idx) => {
                const div = document.createElement('div');
                div.className = 'flex items-center gap-2 p-2 bg-emerald-50 rounded-xl border border-emerald-200 text-xs text-emerald-800';
                div.innerHTML = `<i data-lucide="file" class="w-3.5 h-3.5 text-emerald-600"></i> <span class="truncate font-medium">${file.name}</span> <span class="text-[10px] text-emerald-500 shrink-0">(${(file.size / 1024).toFixed(1)} KB)</span>`;
                list.appendChild(div);
            });
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }

    // Residu Modal (Komponen 7 - General)
    function openResiduModal() {
        const modal = document.getElementById('residuModal');
        if (modal) modal.classList.remove('hidden');
    }
    function closeResiduModal() {
        const modal = document.getElementById('residuModal');
        if (modal) modal.classList.add('hidden');
    }

    // Residu Modal per Komponen (2,3,4,5,6,8)
    function openResiduKomponenModal(kategori) {
        const modal = document.getElementById('residuKomponenModal');
        const form = document.getElementById('residuKomponenForm');
        const title = document.getElementById('residuKomponenModalTitle');
        if (modal && form) {
            // Update form action to include kategori
            form.action = '{{ route('crmc.update.residu', $slug) }}';
            // Update title based on kategori
            const kategoriNames = {
                'risk_register': 'Risk Register',
                'sop': 'SOP',
                'formulir_pengendalian': 'Formulir Pengendalian',
                'jadwal_pelaksanaan': 'Jadwal Pelaksanaan',
                'bukti_pelaksanaan': 'Bukti Pelaksanaan',
                'evaluasi': 'Evaluasi & Rencana Perbaikan'
            };
            if (title) {
                title.textContent = 'Ubah Status Residu - ' + (kategoriNames[kategori] || kategori);
            }
            modal.classList.remove('hidden');
        }
    }
    function closeResiduKomponenModal() {
        const modal = document.getElementById('residuKomponenModal');
        if (modal) modal.classList.add('hidden');
    }

    // Penugasan Modal
    function openPenugasanModal() {
        const modal = document.getElementById('penugasanModal');
        if (modal) modal.classList.remove('hidden');
    }
    function closePenugasanModal() {
        const modal = document.getElementById('penugasanModal');
        if (modal) modal.classList.add('hidden');
    }
</script>
@endpush
