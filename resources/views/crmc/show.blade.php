@extends('layouts.app')

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

        // Warna status residu (null = belum ditentukan).
        $warnaResidu = function (?string $nilai) {
            return match(strtolower((string) $nilai)) {
                'rendah' => ['bg-emerald-50 border-emerald-200', 'text-emerald-900', 'Low Risk', 'bg-emerald-500', 'w-1/3'],
                'sedang' => ['bg-amber-50 border-amber-200', 'text-amber-900', 'Medium Risk', 'bg-amber-400', 'w-2/3'],
                'tinggi' => ['bg-rose-50 border-rose-200', 'text-rose-900', 'High Risk', 'bg-rose-500', 'w-full'],
                default  => ['bg-slate-50 border-slate-200', 'text-slate-500', 'Belum ditentukan', 'bg-slate-300', 'w-0'],
            };
        };

        $residuNilai = $residuTerpilih['nilai'];
        [$residuBg, $residuTeks, $residuLabel, $residuBar, $residuLebar] = $warnaResidu($residuNilai);

        $isAdmin = auth()->check() && auth()->user()->isAdmin();

        // Berapa tahun ke belakang yang punya dokumen, untuk info header.
        $tahunAdaDokumen = $dokumenTahun->keys()->map(fn ($t) => (int) $t)->sortDesc()->values();
    @endphp

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

    <!-- FLASH MESSAGES -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center space-x-3 text-xs text-emerald-900 shadow-sm animate-fade-in">
        <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
            <i data-lucide="check" class="w-4 h-4"></i>
        </div>
        <div class="flex-1">
            <p class="font-bold">Berhasil Disimpan</p>
            <p class="text-emerald-700">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-start space-x-3 text-xs text-rose-900 shadow-sm animate-fade-in">
        <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0">
            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
        </div>
        <div class="flex-1">
            <p class="font-bold">Perubahan tidak tersimpan</p>
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
            <p class="font-bold">Gagal Diproses</p>
            <p class="text-rose-700">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    <!-- HERO BANNER -->
    <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white border border-slate-800 shadow-xl relative overflow-hidden">
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
                    Dokumen resmi Continuous Monitoring on Risk Control (CRMC) terpadu. Seluruh isi halaman berasal dari berkas yang benar-benar diunggah, tanpa data contoh.
                </p>
            </div>

            <div class="flex flex-wrap lg:flex-col items-start lg:items-end gap-2.5 shrink-0">
                <div class="px-4 py-2 rounded-2xl bg-slate-800/80 border border-slate-700 backdrop-blur-sm flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $rangkuman['totalDokumen'] > 0 ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
                    <span class="text-xs text-slate-300">Tahun Anggaran: <strong class="text-white">{{ $selectedTahun }}</strong></span>
                </div>
                <div class="px-4 py-2 rounded-2xl bg-slate-800/80 border border-slate-700 flex items-center space-x-2 text-xs text-slate-300">
                    <i data-lucide="layers" class="w-4 h-4 text-blue-300"></i>
                    <span>Komponen Terisi: <strong class="text-white">{{ $rangkuman['komponenTerisi'] }} / {{ $rangkuman['totalKomponen'] }}</strong></span>
                </div>
                <div class="px-4 py-2 rounded-2xl bg-slate-800/80 border border-slate-700 flex items-center space-x-2 text-xs text-slate-300">
                    <i data-lucide="file-stack" class="w-4 h-4 text-amber-300"></i>
                    <span>Dokumen: <strong class="text-white">{{ $rangkuman['totalDokumen'] }} file</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- PENJELASAN MEKANISME TAHUN -->
    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 flex items-start space-x-3 text-xs text-blue-900">
        <i data-lucide="info" class="w-4 h-4 text-blue-600 shrink-0 mt-0.5"></i>
        <div class="space-y-1">
            <p class="font-bold">Cara kerja pemilihan tahun</p>
            <p>
                Setiap komponen punya pemilih tahun sendiri, jadi Anda bisa membandingkan dokumen antar tahun dalam satu halaman.
                Tahun <strong>{{ date('Y') }}</strong> dan <strong>{{ date('Y') + 1 }}</strong> selalu tersedia otomatis tanpa perlu apa pun,
                sehingga tahun baru langsung muncul sendiri setiap pergantian tahun.
                Admin juga bisa menambah tahun khusus di luar rentang itu lewat tombol <strong>"+ Tambah Tahun"</strong>.
            </p>
            <p class="text-blue-700">
                Komponen hanya menampilkan berkas yang diunggah pada tahun yang dipilih. Belum ada dokumen? Kotaknya kosong — bukan data contoh.
            </p>
        </div>
    </div>

    <!-- TAHUN GLOBAL -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 text-xs">
            <i data-lucide="calendar" class="w-4 h-4 text-amber-500"></i>
            <span class="font-bold text-slate-700">Tahun Default Halaman:</span>
            <span class="text-[10px] text-slate-400">(dipakai semua komponen yang belum memilih tahun sendiri)</span>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @foreach(array_reverse($daftarTahun) as $tahun)
                <a href="{{ route('crmc.show', ['slug' => $slug, 'tahun' => $tahun]) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition shadow-sm border inline-flex items-center gap-1.5 {{ $selectedTahun == $tahun ? 'bg-amber-500 text-slate-950 border-amber-600' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50 hover:border-amber-300' }}">
                    {{ $tahun }}
                    @if(in_array($tahun, $tahunAdaDokumen->all(), true))
                        <i data-lucide="database" class="w-3 h-3 opacity-70" title="Tahun ini punya dokumen"></i>
                    @endif
                </a>
            @endforeach

            @if($isAdmin)
                <button onclick="openTahunModal()" class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition shadow-sm" title="Tambah tahun di luar rentang otomatis">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Tambah Tahun</span>
                </button>
            @endif
        </div>
    </div>

    <!-- STATS OVERVIEW -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Residu -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Residu Risiko {{ $residuTerpilih['tahun'] }}</p>
                <div class="flex items-center gap-2 mt-1">
                    @if($residuNilai)
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold {{ $residuBg }} border {{ $residuTeks }}">{{ strtoupper($residuNilai) }}</span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-slate-100 text-slate-500 border border-slate-200">Belum Diisi</span>
                    @endif
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Kelengkapan -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Komponen Terisi</p>
                <p class="text-xl font-black text-slate-900 mt-1">
                    {{ $rangkuman['komponenTerisi'] }} / {{ $rangkuman['totalKomponen'] }}
                </p>
                <div class="w-28 bg-slate-100 h-1.5 rounded-full mt-2 overflow-hidden">
                    @php
                    $persen = $rangkuman['totalKomponen'] > 0
                        ? round($rangkuman['komponenTerisi'] / $rangkuman['totalKomponen'] * 100)
                        : 0;
                    $barWarna = $persen === 100 ? 'bg-emerald-500' : 'bg-amber-500';
                @endphp
                    <div class="h-full rounded-full {{ $barWarna }} transition-all duration-500" style="width: {{ $persen }}%"></div>
                </div>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center">
                <i data-lucide="layers" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- PIC Pengendali -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center justify-between">
            <div class="truncate mr-2">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">PIC Pengendali</p>
                @if($pengendaliRisikoList->isNotEmpty())
                    <h4 class="text-xs font-bold text-slate-900 mt-1 truncate" title="{{ $pengendaliRisikoList->first()->name }}">{{ $pengendaliRisikoList->first()->name }}</h4>
                    <p class="text-[10px] text-slate-500 font-mono mt-0.5">
                        @if($pengendaliRisikoList->count() > 1)+{{ $pengendaliRisikoList->count() - 1 }} lainnya @else NIP. {{ $pengendaliRisikoList->first()->nip ?? '-' }} @endif
                    </p>
                @else
                    <h4 class="text-xs font-bold text-slate-400 mt-1">Belum Ditugaskan</h4>
                    <p class="text-[10px] text-slate-400 mt-0.5">Admin dapat mengatur lewat Komponen 1</p>
                @endif
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i data-lucide="user-check" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Dokumen -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Dokumen Tahun {{ $selectedTahun }}</p>
                <h4 class="text-xl font-black text-slate-900 mt-1">{{ $rangkuman['totalDokumen'] }} <span class="text-xs font-semibold text-slate-500">file</span></h4>
                <p class="text-[10px] text-slate-500 mt-0.5">Total {{ $rangkuman['jumlahTahun'] }} tahun selectable</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                <i data-lucide="file-stack" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- SECTION TITLE -->
    <div class="flex items-center justify-between pt-2">
        <div>
            <span class="text-xs font-extrabold uppercase tracking-widest text-amber-600">Instrumen CRMC</span>
            <h2 class="text-xl font-black text-slate-900">Rincian 8 Komponen</h2>
        </div>
        <span class="text-xs text-slate-400 hidden sm:inline">Pusbangkom Kementerian Pekerjaan Umum</span>
    </div>

    <!-- 8 KOMPONEN GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <!-- ============ KOMPONEN 1: IDENTITAS PEGAWAI ============ -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between group md:col-span-2">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wide bg-blue-100 text-blue-900 rounded-lg">Komponen 1</span>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Identitas Pegawai</span>
                    </div>

                    @if($isAdmin)
                        <button onclick="openPenugasanModal()" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl text-xs transition shadow-sm">
                            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                            <span>Atur Penugasan (Admin)</span>
                        </button>
                    @endif
                </div>

                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">1. Identitas Pegawai & Penugasan PIC (3 Lapis)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Hierarki pertanggungjawaban terbagi atas 1 Pemilik Risiko, 1 Pengendali Mutu, dan Tim Pengendali Risiko.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 pt-1">
                    <!-- Lapis 1: Pemilik Risiko -->
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
                                @if($isAdmin)<p class="text-[10px] text-slate-400">Atur lewat tombol "Atur Penugasan"</p>@endif
                            </div>
                        @endif
                    </div>

                    <!-- Lapis 2: Pengendali Mutu -->
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
                                @if($isAdmin)<p class="text-[10px] text-slate-400">Atur lewat tombol "Atur Penugasan"</p>@endif
                            </div>
                        @endif
                    </div>

                    <!-- Lapis 3: Tim Pengendali Risiko -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/90 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-800 bg-emerald-100 px-2.5 py-0.5 rounded">3. Pengendali Risiko</span>
                            <span class="text-[10px] text-slate-400 font-medium">{{ $pengendaliRisikoList->count() }} Orang</span>
                        </div>

                        <div class="space-y-2.5 max-h-96 overflow-y-auto pr-1">
                            @forelse($pengendaliRisikoList as $staf)
                            <div class="p-3 bg-white hover:bg-slate-100/80 rounded-xl border border-slate-200/80 flex items-center space-x-3 transition">
                                <img src="{{ $staf->foto_url }}" width="96" height="112" loading="lazy" decoding="async" alt="{{ $staf->name }}" class="w-24 h-28 rounded-xl object-cover object-top border border-emerald-500/80 shrink-0">
                                <div class="flex-1 min-w-0">
                                    <h6 class="text-xs font-bold text-slate-900 truncate">{{ $staf->name }}</h6>
                                    <p class="text-[10px] text-slate-500 truncate">NIP. {{ $staf->nip ?? '-' }}</p>
                                    <p class="text-[10px] text-emerald-700 font-semibold">{{ $staf->jabatan }}</p>
                                </div>
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
            <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-500">
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
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
            <div class="space-y-4">
                <!-- HEADER -->
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wide bg-blue-100 text-blue-900 rounded-lg">Komponen {{ $meta['nomor'] }}</span>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">{{ $daftar->count() }} file</span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @auth
                            <button onclick="openUploadModal(@js($kategori), @js('Komponen ' . $meta['nomor'] . ' — ' . $meta['judul']), {{ $tahunK }})"
                                    class="inline-flex items-center space-x-1 px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-900 rounded-lg text-[10px] font-bold transition">
                                <i data-lucide="upload" class="w-3 h-3"></i><span>Upload</span>
                            </button>
                        @endauth
                        <i data-lucide="{{ $meta['ikon'] }}" class="w-5 h-5 text-blue-900"></i>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">{{ $meta['nomor'] }}. {{ $meta['judul'] }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $deskripsiKomponen[$kategori] }}</p>
                </div>

                <!-- PEMILIH TAHUN (per komponen) -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Tahun:</span>
                    <select onchange="if(this.value) window.location.href = '{{ $urlGantiTahun($kategori, '__TAHUN__') }}'.replace('__TAHUN__', this.value)"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border border-slate-200 bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        @foreach(array_reverse($meta['tahunTersedia']) as $tahun)
                            <option value="{{ $tahun }}" {{ $tahunK == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endforeach
                    </select>
                    @if(in_array($tahunK, $tahunAdaDokumen->all(), true))
                        <span class="text-[10px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-lg font-semibold">Punya dokumen</span>
                    @else
                        <span class="text-[10px] text-slate-400 bg-slate-50 border border-slate-200 px-2 py-0.5 rounded-lg">Belum ada dokumen</span>
                    @endif
                </div>

                <!-- DAFTAR DOKUMEN -->
                @forelse($daftar as $idx => $lamp)
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center space-x-3 min-w-0">
                                <div class="w-9 h-9 rounded-lg {{ $badgeFile($lamp->tipe_file) }} border flex items-center justify-center text-[9px] font-bold shrink-0 uppercase">{{ $lamp->tipe_file }}</div>
                                <div class="truncate min-w-0">
                                    <h5 class="text-xs font-bold text-slate-900 truncate" title="{{ $lamp->nama_file }}">{{ $lamp->nama_file }}</h5>
                                    <span class="text-[10px] text-slate-400">
                                        {{ $idx + 1 }} / {{ $daftar->count() }}
                                        @if($lamp->dokumenCrmc) &middot; {{ $lamp->dokumenCrmc->tahun_pelaksanaan }} @endif
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="{{ $lamp->file_path }}" target="_blank" rel="noopener" class="p-1.5 bg-blue-900 hover:bg-blue-950 text-white rounded-lg transition" title="Buka / preview"><i data-lucide="eye" class="w-3.5 h-3.5"></i></a>
                                <a href="{{ $lamp->file_path }}" download class="p-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg transition" title="Unduh"><i data-lucide="download" class="w-3.5 h-3.5"></i></a>
                                <button onclick="openKeteranganModal({{ $lamp->id }}, @js($lamp->nama_file), @js($lamp->keterangan ?? ''))" class="p-1.5 bg-slate-200 hover:bg-amber-100 text-slate-600 rounded-lg transition" title="Keterangan"><i data-lucide="text-quote" class="w-3.5 h-3.5"></i></button>
                                @if($isAdmin)
                                    <form action="{{ route('crmc.lampiran.delete', $lamp->id) }}" method="POST"
                                          data-konfirmasi="Hapus dokumen &quot;{{ $lamp->nama_file }}&quot;? File fisik di storage ikut terhapus dan tidak bisa dibatalkan.">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Hapus"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        @if(!empty($lamp->keterangan))
                            <div class="flex items-start space-x-2 pt-2 border-t border-slate-200/80">
                                <i data-lucide="info" class="w-3.5 h-3.5 text-amber-500 shrink-0 mt-0.5"></i>
                                <p class="text-[11px] text-slate-600 leading-relaxed">{{ $lamp->keterangan }}</p>
                            </div>
                        @else
                            <div class="pt-2 border-t border-slate-200/80">
                                <span class="text-[10px] text-slate-400 italic">Tanpa keterangan</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="py-10 px-4 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/60 text-center space-y-2">
                        <i data-lucide="file-plus-2" class="w-8 h-8 text-slate-300 mx-auto"></i>
                        <p class="text-xs font-bold text-slate-600">Belum ada dokumen tahun {{ $tahunK }}</p>
                        <p class="text-[11px] text-slate-400">Komponen ini hanya menampilkan berkas yang benar-benar diunggah.</p>
                        @auth
                            <button onclick="openUploadModal(@js($kategori), @js('Komponen ' . $meta['nomor'] . ' — ' . $meta['judul']), {{ $tahunK }})"
                                    class="mt-1 inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold rounded-xl transition">
                                <i data-lucide="upload" class="w-3.5 h-3.5"></i><span>Upload {{ $tahunK }}</span>
                            </button>
                        @endauth
                    </div>
                @endforelse
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
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

        <!-- ============ KOMPONEN 7: STATUS RESIDU (ADMIN) ============ -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
            <div class="space-y-4">
                <div class="flex items-start justify-between gap-2">
                    <span class="px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wide bg-blue-100 text-blue-900 rounded-lg">Komponen 7</span>
                    <div class="flex items-center gap-2 shrink-0">
                        @if($isAdmin)
                            <button onclick="openResiduModal({{ $residuTerpilih['tahun'] }})" class="inline-flex items-center space-x-1 px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-lg text-[10px] font-bold transition border border-amber-200">
                                <i data-lucide="edit-3" class="w-3 h-3"></i><span>Ubah (Admin)</span>
                            </button>
                        @endif
                        <i data-lucide="gauge" class="w-5 h-5 text-blue-900"></i>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">7. Status Residu Risiko</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Tingkat sisa risiko pasca penerapan sistem kendali internal. @if($isAdmin)<em class="text-amber-600 font-semibold">Hanya Admin yang dapat mengubah.</em>@endif</p>
                </div>

                <!-- PEMILIH TAHUN -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Tahun:</span>
                    <select onchange="if(this.value) window.location.href = '{{ $urlGantiTahun('residu', '__TAHUN__') }}'.replace('__TAHUN__', this.value)"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border border-slate-200 bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        @foreach(array_reverse($residuTerpilih['tahunTersedia']) as $tahun)
                            <option value="{{ $tahun }}" {{ $residuTerpilih['tahun'] == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-3 pt-1">
                    <div class="p-4 {{ $residuBg }} rounded-2xl border">
                        <span class="text-[10px] font-bold {{ $residuTeks }} uppercase tracking-wider">Tingkat Residu Tahun {{ $residuTerpilih['tahun'] }}</span>
                        <div class="text-xl font-extrabold {{ $residuTeks }} mt-0.5 flex items-center gap-1.5">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                            <span>{{ $residuNilai ? strtoupper($residuNilai) . " (" . $residuLabel . ")" : "Belum Diisi" }}</span>
                        </div>
                    </div>

                    <!-- RISK METER -->
                    <div class="space-y-1.5">
                        <div class="flex justify-between text-[11px] text-slate-500 font-medium">
                            <span class="text-emerald-700 font-bold">Status Residu Terkendali</span>
                            <span class="text-amber-700">Dalam Pemantauan</span>
                            <span class="text-rose-700">Perlu Tindakan Segera</span>
                        </div>
                        <div class="h-2.5 w-full bg-slate-100 rounded-full flex overflow-hidden p-0.5">
                            <div class="{{ $residuBar }} {{ $residuLebar }} rounded-full transition-all duration-500"></div>
                        </div>
                    </div>

                    <!-- RINGKASAN EVALUASI TERTYANGGI -->
                    <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Ringkasan Evaluasi (tersimpan di Komponen 8)</span>
                        @if(!empty($residuTerpilih['rangkumanEvaluasi']))
                            <p class="text-xs text-slate-700 leading-relaxed font-medium whitespace-pre-line">{{ $residuTerpilih['rangkumanEvaluasi'] }}</p>
                        @else
                            <p class="text-[11px] text-slate-400 italic">Belum ada ringkasan evaluasi untuk tahun ini.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
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
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-md transition flex flex-col justify-between group">
            <div class="space-y-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wide bg-blue-100 text-blue-900 rounded-lg">Komponen 8</span>
                        <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">{{ $daftar8->count() }} file</span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @auth
                            <button onclick="openUploadModal('evaluasi', @js('Komponen 8 — ' . $meta8['judul']), {{ $tahun8 }})"
                                    class="inline-flex items-center space-x-1 px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-900 rounded-lg text-[10px] font-bold transition">
                                <i data-lucide="upload" class="w-3 h-3"></i><span>Upload</span>
                            </button>
                        @endauth
                        <i data-lucide="trending-up" class="w-5 h-5 text-blue-900"></i>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900">8. Evaluasi & Rencana Perbaikan</h3>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $deskripsiKomponen['evaluasi'] }}</p>
                </div>

                <!-- PEMILIH TAHUN -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Tahun:</span>
                    <select onchange="if(this.value) window.location.href = '{{ $urlGantiTahun('evaluasi', '__TAHUN__') }}'.replace('__TAHUN__', this.value)"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold border border-slate-200 bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                        @foreach(array_reverse($meta8['tahunTersedia']) as $tahun)
                            <option value="{{ $tahun }}" {{ $tahun8 == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endforeach
                    </select>
                    @if(in_array($tahun8, $tahunAdaDokumen->all(), true))
                        <span class="text-[10px] text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-lg font-semibold">Punya dokumen</span>
                    @else
                        <span class="text-[10px] text-slate-400 bg-slate-50 border border-slate-200 px-2 py-0.5 rounded-lg">Belum ada dokumen</span>
                    @endif
                </div>

                <!-- FORM RINGKASAN EVALUASI (data nyata, bukan dummy) -->
                @auth
                <form action="{{ route('crmc.update', $slug) }}" method="POST" class="space-y-2 pt-1">
                    @csrf
                    <input type="hidden" name="tahun_pelaksanaan" value="{{ $tahun8 }}">
                    <input type="hidden" name="residu" value="{{ $residuTerpilih['nilai'] }}">

                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500">Ringkasan Evaluasi Tahun {{ $tahun8 }}</label>
                    <textarea name="evaluasi" rows="4"
                              placeholder="Tulis hasil evaluasi dan rencana perbaikan tahun {{ $tahun8 }}. Kosongkan bila belum ada."
                              class="w-full p-3 rounded-xl border border-slate-300 text-xs leading-relaxed focus:ring-2 focus:ring-amber-500 focus:outline-none">{{ old('evaluasi', $residuTerpilih['rangkumanEvaluasi']) }}</textarea>
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-[10px] font-bold rounded-xl transition">
                            <i data-lucide="save" class="w-3.5 h-3.5"></i><span>Simpan Ringkasan</span>
                        </button>
                    </div>
                </form>
                @else
                    <div class="pt-1">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Ringkasan Evaluasi Tahun {{ $tahun8 }}</span>
                        <p class="text-xs text-slate-700 leading-relaxed mt-1 whitespace-pre-line">{{ $residuTerpilih['rangkumanEvaluasi'] ?: 'Belum ada ringkasan evaluasi untuk tahun ini.' }}</p>
                    </div>
                @endauth

                <!-- DAFTAR DOKUMEN -->
                @forelse($daftar8 as $idx => $lamp)
                    <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center space-x-3 min-w-0">
                                <div class="w-9 h-9 rounded-lg {{ $badgeFile($lamp->tipe_file) }} border flex items-center justify-center text-[9px] font-bold shrink-0 uppercase">{{ $lamp->tipe_file }}</div>
                                <div class="truncate min-w-0">
                                    <h5 class="text-xs font-bold text-slate-900 truncate" title="{{ $lamp->nama_file }}">{{ $lamp->nama_file }}</h5>
                                    <span class="text-[10px] text-slate-400">{{ $idx + 1 }} / {{ $daftar8->count() }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <a href="{{ $lamp->file_path }}" target="_blank" rel="noopener" class="p-1.5 bg-blue-900 hover:bg-blue-950 text-white rounded-lg transition" title="Buka / preview"><i data-lucide="eye" class="w-3.5 h-3.5"></i></a>
                                <a href="{{ $lamp->file_path }}" download class="p-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg transition" title="Unduh"><i data-lucide="download" class="w-3.5 h-3.5"></i></a>
                                <button onclick="openKeteranganModal({{ $lamp->id }}, @js($lamp->nama_file), @js($lamp->keterangan ?? ''))" class="p-1.5 bg-slate-200 hover:bg-amber-100 text-slate-600 rounded-lg transition" title="Keterangan"><i data-lucide="text-quote" class="w-3.5 h-3.5"></i></button>
                                @if($isAdmin)
                                    <form action="{{ route('crmc.lampiran.delete', $lamp->id) }}" method="POST"
                                          data-konfirmasi="Hapus dokumen &quot;{{ $lamp->nama_file }}&quot;? File fisik di storage ikut terhapus dan tidak bisa dibatalkan.">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Hapus"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        @if(!empty($lamp->keterangan))
                            <div class="flex items-start space-x-2 pt-2 border-t border-slate-200/80">
                                <i data-lucide="info" class="w-3.5 h-3.5 text-amber-500 shrink-0 mt-0.5"></i>
                                <p class="text-[11px] text-slate-600 leading-relaxed">{{ $lamp->keterangan }}</p>
                            </div>
                        @else
                            <div class="pt-2 border-t border-slate-200/80"><span class="text-[10px] text-slate-400 italic">Tanpa keterangan</span></div>
                        @endif
                    </div>
                @empty
                    <div class="py-10 px-4 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/60 text-center space-y-2">
                        <i data-lucide="file-plus-2" class="w-8 h-8 text-slate-300 mx-auto"></i>
                        <p class="text-xs font-bold text-slate-600">Belum ada dokumen tahun {{ $tahun8 }}</p>
                        <p class="text-[11px] text-slate-400">Komponen ini hanya menampilkan berkas yang benar-benar diunggah.</p>
                        @auth
                            <button onclick="openUploadModal('evaluasi', @js('Komponen 8 — ' . $meta8['judul']), {{ $tahun8 }})"
                                    class="mt-1 inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold rounded-xl transition">
                                <i data-lucide="upload" class="w-3.5 h-3.5"></i><span>Upload {{ $tahun8 }}</span>
                            </button>
                        @endauth
                    </div>
                @endforelse
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
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
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 bg-amber-500 rounded-xl flex items-center justify-center text-slate-950 font-bold shrink-0">
                    <i data-lucide="settings-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-white">Manajemen Tahun & Penghapusan Dokumen</h3>
                    <p class="text-[11px] text-slate-400">Khusus Administrator</p>
                </div>
            </div>
            <button onclick="openTahunModal()" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 text-xs font-bold rounded-xl transition">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i><span>Tambah Tahun</span>
            </button>
        </div>

        <div class="p-6 space-y-5 text-xs">
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
                                    <button type="submit" class="p-1 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Hapus dari daftar">
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
                    <div class="w-9 h-9 bg-amber-100 text-amber-700 rounded-xl flex items-center justify-center shrink-0">
                        <i data-lucide="folder-minus" class="w-5 h-5"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-slate-800">Hapus Dokumen Sub-Bidang Ini per Tahun</h4>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            Menghapus dokumen tahun tertentu pada sub-bidang <strong>{{ $subBidangName }}</strong> saja.
                            Record database dan file fisik di storage ikut terhapus. Residu tahun tersebut juga ikut hilang.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Tahun</label>
                        <select name="tahun" required class="px-3 py-2 rounded-xl border border-slate-300 font-semibold bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            @foreach(array_reverse($daftarTahun) as $tahun)
                                <option value="{{ $tahun }}" {{ $tahun == $selectedTahun ? 'selected' : '' }}>{{ $tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[16rem]">
                        <label class="block font-bold text-slate-800 mb-1">Cakupan Komponen</label>
                        <select name="kategori" class="w-full px-3 py-2 rounded-xl border border-slate-300 font-semibold bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <option value="">Semua komponen (2,3,4,5,6,8)</option>
                            @foreach($komponen as $kat => $m)
                                <option value="{{ $kat }}">Hanya Komponen {{ $m['nomor'] }} — {{ $m['judul'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" data-konfirmasi="Hapus dokumen tahun {{ $selectedTahun }} pada sub-bidang &quot;{{ $subBidangName }}&quot;? Record database dan file fisik di storage ikut terhapus. Tindakan ini tidak bisa dibatalkan."
                            class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl transition shadow-sm inline-flex items-center gap-1.5">
                        <i data-lucide="trash-2" class="w-4 h-4"></i><span>Hapus di Sub-Bidang Ini</span>
                    </button>
                </div>
            </form>

            <div class="border-t border-slate-100"></div>

            <!-- HAPUS DOKUMEN: SEMUA SUB-BIDANG -->
            <form action="{{ route('crmc.hapus.tahun.semua') }}" method="POST" class="space-y-3">
                @csrf
                <div class="flex items-start space-x-3">
                    <div class="w-9 h-9 bg-rose-100 text-rose-700 rounded-xl flex items-center justify-center shrink-0">
                        <i data-lucide="triangle-alert" class="w-5 h-5"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="font-bold text-rose-900">Hapus Seluruh Dokumen Satu Tahun (Semua Sub-Bidang)</h4>
                        <p class="text-[11px] text-rose-700 mt-0.5">
                            Menghapus semua dokumen tahun terpilih di seluruh {{ $rangkuman['totalSubMenu'] }} sub-bidang, beserta file fisiknya.
                           Dokumen tahun tersebut juga akan hilang dari halaman sub-bidang lain.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Tahun</label>
                        <select name="tahun" required class="px-3 py-2 rounded-xl border border-rose-300 font-semibold bg-white focus:ring-2 focus:ring-rose-400 focus:outline-none">
                            @foreach(array_reverse($daftarTahun) as $tahun)
                                <option value="{{ $tahun }}">{{ $tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            data-konfirmasi="PERINGATAN: seluruh dokumen tahun {{ $selectedTahun }} di SEMUA sub-bidang akan dihapus permanen, termasuk file fisik di storage. Tindakan ini tidak bisa dibatalkan. Lanjutkan?"
                            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl transition shadow-sm inline-flex items-center gap-1.5">
                        <i data-lucide="trash-2" class="w-4 h-4"></i><span>Hapus Semua Dokumen Tahun Tersebut</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- BOTTOM ACTION BAR -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                <i data-lucide="info" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold text-slate-900">Kelengkapan Tahun {{ $selectedTahun }}</h4>
                <p class="text-[11px] text-slate-500">
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
            <a href="{{ url('/') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                Kembali ke Dashboard
            </a>
            @if($isAdmin)
                <a href="{{ route('admin.pegawai.index') }}" class="px-4 py-2 bg-blue-900 hover:bg-blue-950 text-white font-bold rounded-xl text-xs transition shadow-sm">
                    Kelola Akun Pegawai
                </a>
            @endif
        </div>
    </div>

</main>
@endsection

@section('modals')
    <!-- ===================== MODAL UPLOAD (DENGAN KETERANGAN PER FILE) ===================== -->
    <div id="uploadDokumenModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
        <div class="bg-white w-full max-w-2xl max-h-[92vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-slate-100">
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
                    <input type="hidden" name="tahun_pelaksanaan" id="uploadTahun" value="{{ $selectedTahun }}">

                    <!-- PILIH TAHUN -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Tahun Anggaran *</label>
                        <select id="uploadTahunSelect" required onchange="document.getElementById('uploadTahun').value = this.value"
                                class="w-full p-2.5 rounded-xl border border-slate-300 font-semibold bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            @foreach(array_reverse($daftarTahun) as $tahun)
                                <option value="{{ $tahun }}" {{ $tahun == $selectedTahun ? 'selected' : '' }}>{{ $tahun }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-500 mt-1">Dokumen yang diunggah akan tersimpan pada tahun ini dan muncul di Kotak Komponen tersebut.</p>
                    </div>

                    <!-- PILIH FILE -->
                    <div>
                        <label class="block font-bold text-slate-800 mb-2">Pilih File Dokumen (Multi-Upload) *</label>
                        <div class="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:border-amber-400 transition cursor-pointer" onclick="document.getElementById('uploadFiles').click();">
                            <i data-lucide="cloud-upload" class="w-8 h-8 text-slate-400 mx-auto mb-2"></i>
                            <p class="text-slate-600 font-medium">Klik atau seret file ke sini</p>
                            <p class="text-[10px] text-slate-400 mt-1">PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, WebP (maks. 10MB per file)</p>
                        </div>
                        <input type="file" name="files[]" id="uploadFiles" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp" class="hidden" onchange="bangunDaftarBerkas(this)">
                    </div>

                    <!-- DAFTAR FILE + KETERANGAN -->
                    <div id="daftarBerkasWrap" class="hidden">
                        <label class="block font-bold text-slate-800 mb-2">
                            Keterangan per Berkas
                            <span class="font-normal text-slate-400">(opsional, tapi sangat disarankan)</span>
                        </label>
                        <div id="daftarBerkas" class="space-y-2"></div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex justify-end space-x-2">
                        <button type="button" onclick="closeUploadModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold shadow-sm transition">Upload Dokumen</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===================== MODAL KETERANGAN SATU DOKUMEN ===================== -->
    <div id="keteranganModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
        <div class="bg-white w-full max-w-lg max-h-[92vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-slate-100">
            <div class="bg-slate-900 text-white p-5 flex items-start justify-between border-b border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center text-slate-950 font-bold shrink-0">
                        <i data-lucide="text-quote" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest">Keterangan Berkas</span>
                        <h3 id="keteranganModalFile" class="text-base font-extrabold text-white truncate max-w-[16rem]">-</h3>
                    </div>
                </div>
                <button onclick="closeKeteranganModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-5">
                <form id="keteranganForm" method="POST" class="space-y-4 text-xs">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Keterangan</label>
                        <textarea id="keteranganInput" name="keterangan" rows="5" maxlength="1000"
                                  placeholder="Contoh: Daftar periksa Triwulan III, ditandatangani oleh Kajur, tanggal 12 September 2026."
                                  class="w-full p-3 rounded-xl border border-slate-300 text-xs leading-relaxed focus:ring-2 focus:ring-amber-500 focus:outline-none"></textarea>
                        <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                            <span>Maksimal 1000 karakter.</span>
                            <span id="keteranganCounter">0 / 1000</span>
                        </div>
                    </div>
                    <div class="pt-3 border-t border-slate-200 flex justify-end space-x-2">
                        <button type="button" onclick="closeKeteranganModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold shadow-sm transition">Simpan Keterangan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===================== MODAL TAMBAH TAHUN (ADMIN) ===================== -->
    @if($isAdmin)
    <div id="tahunModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
        <div class="bg-white w-full max-w-md max-h-[92vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-slate-100">
            <div class="bg-slate-900 text-white p-5 flex items-start justify-between border-b border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center text-slate-950 font-bold shrink-0">
                        <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest">Khusus Admin</span>
                        <h3 class="text-lg font-extrabold text-white">Tambah Tahun Anggaran</h3>
                    </div>
                </div>
                <button onclick="closeTahunModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-5">
                <form action="{{ route('crmc.tahun.store') }}" method="POST" class="space-y-4 text-xs">
                    @csrf

                    <div class="bg-blue-50 p-3 rounded-2xl border border-blue-200 text-blue-900">
                        <p class="font-bold flex items-center gap-1.5"><i data-lucide="info" class="w-4 h-4 text-blue-600"></i> Kapan perlu tombol ini?</p>
                        <p class="mt-1">
                            Tahun <strong>{{ date('Y') }}</strong> dan <strong>{{ date('Y') + 1 }}</strong> sudah tersedia otomatis setiap tahun, jadi
                            tidak perlu ditambah manual. Tombol ini hanya untuk <strong>tahun khusus</strong> di luar rentang itu,
                            misalnya tahun anggaran khusus atau membuka tahun lama yang belum pernah ada datanya.
                        </p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Tahun *</label>
                        <input type="number" name="tahun" required min="2000" max="2100"
                               placeholder="mis. {{ date('Y') + 2 }}"
                               class="w-full p-2.5 rounded-xl border border-slate-300 font-semibold bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Keterangan <span class="font-normal text-slate-400">(opsional)</span></label>
                        <input type="text" name="keterangan" maxlength="255"
                               placeholder="mis. Tahun Anggaran Khusus / Tahun Lampau"
                               class="w-full p-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex justify-end space-x-2">
                        <button type="button" onclick="closeTahunModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold shadow-sm transition">Tambah Tahun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- ===================== MODAL RESIDU (KOMPONEN 7 - ADMIN) ===================== -->
    @if($isAdmin)
    <div id="residuModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
        <div class="bg-white w-full max-w-md max-h-[92vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-slate-100">
            <div class="bg-slate-900 text-white p-5 flex items-start justify-between border-b border-slate-800">
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

            <div class="flex-1 overflow-y-auto p-5">
                <form action="{{ route('crmc.update.residu', $slug) }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <input type="hidden" name="tahun_pelaksanaan" id="residuTahunInput" value="{{ $residuTerpilih['tahun'] }}">

                    <div class="bg-amber-50 p-3 rounded-2xl border border-amber-200 text-amber-900">
                        <p class="font-bold flex items-center gap-1.5"><i data-lucide="info" class="w-4 h-4 text-amber-600"></i> Perhatian:</p>
                        <p class="mt-1">Status residu disimpan <strong>per tahun</strong>. Nilai ini berlaku untuk tahun yang dipilih, bukan untuk semua tahun sekaligus.</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Tahun *</label>
                        <select name="tahun_display" id="residuTahunSelect" onchange="document.getElementById('residuTahunInput').value = this.value"
                                class="w-full p-2.5 rounded-xl border border-slate-300 font-semibold bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            @foreach(array_reverse($residuTerpilih['tahunTersedia']) as $tahun)
                                <option value="{{ $tahun }}" {{ $residuTerpilih['tahun'] == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Status Residu Risiko *</label>
                        <select name="residu" required class="w-full p-2.5 rounded-xl border border-slate-300 font-semibold bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <option value="">-- Belum ditentukan --</option>
                            <option value="Rendah" {{ strtolower((string) $residuNilai) == 'rendah' ? 'selected' : '' }}>Rendah (Low Risk)</option>
                            <option value="Sedang" {{ strtolower((string) $residuNilai) == 'sedang' ? 'selected' : '' }}>Sedang (Medium Risk)</option>
                            <option value="Tinggi" {{ strtolower((string) $residuNilai) == 'tinggi' ? 'selected' : '' }}>Tinggi (High Risk)</option>
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

    <!-- ===================== MODAL PENUGASAN (ADMIN) ===================== -->
    @if($isAdmin)
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

            <div class="bg-amber-50 px-6 py-3 border-b border-amber-200 text-xs text-amber-900 space-y-1">
                <p class="font-bold flex items-center gap-1.5"><i data-lucide="info" class="w-4 h-4 text-amber-600"></i> Ketentuan Penetapan PIC CRMC:</p>
                <ul class="text-[11px] list-disc list-inside space-y-0.5 text-amber-800">
                    <li><strong>Pemilik Risiko:</strong> Hanya ada <strong>1 orang</strong> (Kepala Pusat).</li>
                    <li><strong>Pengendali Mutu:</strong> Hanya ada <strong>1 orang</strong> di tiap bidang (Kabag / Kabid).</li>
                    <li><strong>Pengendali Risiko:</strong> Dapat ditugaskan <strong>banyak orang</strong> (Tim Pelaksana Staf).</li>
                </ul>
            </div>

            <div class="flex-1 overflow-y-auto p-5 sm:p-6">
                <form action="{{ route('crmc.penugasan.update', $slug) }}" method="POST" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-bold text-slate-800 mb-1">1. Pemilik Risiko (Pilih 1 Orang) *</label>
                        <select name="pemilik_risiko_id" required class="w-full p-2.5 rounded-xl border border-slate-300 font-semibold bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            @foreach($allUsers as $u)
                                <option value="{{ $u->id }}" {{ (isset($pemilikRisiko) && $pemilikRisiko->id == $u->id) ? 'selected' : '' }}>
                                    {{ $u->name }} - {{ $u->jabatan }} (NIP. {{ $u->nip }})
                                </option>
                            @endforeach
                        </select>
                        <div class="mt-1 flex items-start gap-1.5 text-[10px] text-blue-800 bg-blue-50 border border-blue-200 rounded-lg px-2 py-1.5">
                            <i data-lucide="globe-2" class="w-3 h-3 shrink-0 mt-0.5"></i>
                            <span><strong>Berlaku untuk semua sub-bidang.</strong> Hanya ada 1 Pemilik Risiko untuk seluruh CRMC.</span>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-800 mb-1">2. Pengendali Mutu (Pilih 1 Orang per Bidang) *</label>
                        <select name="pengendali_mutu_id" required class="w-full p-2.5 rounded-xl border border-slate-300 font-semibold bg-white focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            @foreach($allUsers as $u)
                                <option value="{{ $u->id }}" {{ (isset($pengendaliMutu) && $pengendaliMutu->id == $u->id) ? 'selected' : '' }}>
                                    {{ $u->name }} - {{ $u->jabatan }} (NIP. {{ $u->nip }})
                                </option>
                            @endforeach
                        </select>
                        <div class="mt-1 flex items-start gap-1.5 text-[10px] text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-2 py-1.5">
                            <i data-lucide="building-2" class="w-3 h-3 shrink-0 mt-0.5"></i>
                            <span><strong>Berlaku untuk 1 bidang saja:</strong> {{ $parentBidang }}.</span>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block font-bold text-slate-800">3. Tim Pengendali Risiko (Bisa Memilih Banyak Staf) *</label>
                            <span class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-bold">Multi-Selection</span>
                        </div>

                        @php $selectedPengendaliIds = $pengendaliRisikoList->pluck('id')->toArray(); @endphp

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
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold shadow-sm transition">Simpan Penugasan Pegawai</button>
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
            row.className = 'p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-2';

            const head = document.createElement('div');
            head.className = 'flex items-center gap-2';
            head.innerHTML = `
                <span class="px-2 py-0.5 bg-slate-900 text-white text-[9px] font-bold rounded-lg">#${idx + 1}</span>
                <span class="truncate font-medium text-slate-700 text-[11px]">${escapeHtml(file.name)}</span>
                <span class="text-[10px] text-slate-400 shrink-0 ml-auto">${(file.size / 1024).toFixed(1)} KB</span>
            `;

            const textarea = document.createElement('textarea');
            textarea.name = `keterangan[${idx}]`;
            textarea.rows = 2;
            textarea.maxLength = 1000;
            textarea.placeholder = 'Keterangan berkas ini (mis. tanggal, penanggung jawab, nomor surat)…';
            textarea.className = 'w-full p-2.5 rounded-xl border border-slate-300 text-[11px] leading-relaxed focus:ring-2 focus:ring-amber-500 focus:outline-none';

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
