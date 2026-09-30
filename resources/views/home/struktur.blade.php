{{--
    Bagian "Struktur Organisasi": org chart dari Pemilik Risiko di puncak,
    Pengendali Mutu per bidang di tengah, sampai Pengendali Risiko di bawah
    masing-masing bidang. Data diambil dari tabel `struktur_organisasi` yang
    dikelola admin lewat panel di `home/panel-admin.blade.php`.
--}}
@php
    $bisaKelola = Auth::check() && Auth::user()->isAdmin();

    // Pengendali risiko yang tidak punya bidang, atau bidangnya belum punya
    // pengendali mutu. Ditampilkan terpisah supaya tidak ada yang hilang
    // diam-diam dari org chart.
    $bidangTerpakai = $pengendaliMutu->pluck('bidang_id')->filter()->all();
    $risikoTersisa = $pengendaliRisiko->filter(
        fn($r) => $r->bidang_id === null || !in_array($r->bidang_id, $bidangTerpakai)
    );

    // Warna pita per tingkat, dipakai bersama oleh kartu dan garis
    // penghubung supaya konsisten.
    $pita = [
        'pemilik_risiko' => ['bg-blue-800', 'text-blue-100'],
        'pengendali_mutu' => ['bg-amber-500', 'text-slate-950'],
        'pengendali_risiko' => ['bg-emerald-600', 'text-white'],
    ];
@endphp

<section id="struktur-organisasi" class="scroll-mt-32 py-16 bg-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Judul section --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100 text-blue-900 text-[11px] font-bold tracking-wide uppercase mb-4">
                    <i data-lucide="network" class="w-3.5 h-3.5"></i>
                    Struktur Organisasi
                </span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Rantai Tanggung Jawab Pengendalian Risiko
                </h2>
                <p class="text-sm text-slate-600 leading-relaxed mt-3">
                    Preventive control dijalankan berjenjang. Pemilik Risiko
                    bertanggung jawab atas seluruh CRMC, setiap bidang memiliki satu
                    Pengendali Mutu, dan pelaksanaan pengendalian harian diserahkan
                    kepada Tim Pengendali Risiko di masing-masing bidang.
                </p>
            </div>

            @if ($bisaKelola)
                <button onclick="bukaModalStruktur()"
                        class="inline-flex items-center gap-2 self-start md:self-auto px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition shadow-sm shrink-0">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    Tambah Peran
                </button>
            @endif
        </div>

        @if ($pemilikRisiko->isEmpty() && $pengendaliMutu->isEmpty() && $pengendaliRisiko->isEmpty())
            {{-- Empty state --}}
            <div class="bg-white border-2 border-dashed border-slate-300 rounded-3xl p-12 text-center">
                <div class="w-16 h-16 mx-auto bg-slate-100 rounded-2xl flex items-center justify-center mb-4">
                    <i data-lucide="users" class="w-8 h-8 text-slate-400"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800 mb-2">Belum Ada Struktur Organisasi</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                    @if ($bisaKelola)
                        Tambahkan pemilik risiko, pengendali mutu, dan pengendali
                        risiko melalui tombol "Tambah Peran" di atas. Susunannya akan
                        langsung tampil sebagai org chart.
                    @else
                        Data struktur organisasi belum diisi oleh administrator.
                    @endif
                </p>
            </div>
        @else

            {{-- ===================== ORG CHART ===================== --}}
            <div class="space-y-4">

                {{-- ---------- TINGKAT 1: PEMILIK RISIKO ---------- --}}
                @if ($pemilikRisiko->isNotEmpty())
                    <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm">
                        <div class="flex flex-wrap items-center gap-2 mb-5">
                            <span class="px-3 py-1 rounded-lg bg-blue-800 text-blue-100 text-[11px] font-extrabold uppercase tracking-wide">
                                Tingkat 1
                            </span>
                            <h3 class="text-sm font-bold text-slate-800">Pemilik Risiko</h3>
                            <span class="text-[11px] text-slate-400">
                                Penanggung jawab atas seluruh pengendalian risiko CRMC
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($pemilikRisiko as $row)
                                @include('home.partials.kartu-pegawai', [
                                    'row' => $row,
                                    'peran' => 'pemilik_risiko',
                                    'pita' => $pita,
                                    'bisaKelola' => $bisaKelola,
                                    'subjudul' => 'Seluruh CRMC',
                                ])
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Garis penghubung vertikal antar tingkat --}}
                @if ($pemilikRisiko->isNotEmpty() && ($pengendaliMutu->isNotEmpty() || $pengendaliRisiko->isNotEmpty()))
                    <div class="flex justify-center py-1">
                        <div class="w-0.5 h-10 bg-slate-300"></div>
                    </div>
                @endif

                {{-- ---------- TINGKAT 2: PENGENDALI MUTU ---------- --}}
                @if ($pengendaliMutu->isNotEmpty())
                    @php $mutuPerBidang = $pengendaliMutu->groupBy('bidang_id'); @endphp

                    <div class="space-y-6">
                        @foreach ($mutuPerBidang as $bidangId => $daftarMutu)
                            @php
                                $namaBidang = $daftarMutu->first()->bidang?->nama_bidang ?? 'Tanpa Bidang';
                                $risikoBidang = $risikoPerBidang->get($bidangId, collect());
                            @endphp

                            <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                                {{-- Pita nama bidang --}}
                                <div class="bg-slate-900 text-white px-5 py-3 flex flex-wrap items-center gap-2">
                                    <i data-lucide="landmark" class="w-4 h-4 text-amber-400"></i>
                                    <span class="text-xs font-bold tracking-wide">{{ $namaBidang }}</span>
                                    <span class="ml-auto flex items-center gap-2 text-[10px] text-slate-400">
                                        <span class="px-2 py-0.5 rounded-full bg-slate-800 border border-slate-700">
                                            {{ $daftarMutu->count() }} pengendali mutu
                                        </span>
                                        <span class="px-2 py-0.5 rounded-full bg-slate-800 border border-slate-700">
                                            {{ $risikoBidang->count() }} pengendali risiko
                                        </span>
                                    </span>
                                </div>

                                <div class="p-5 md:p-6">
                                    <div class="flex flex-wrap items-center gap-2 mb-4">
                                        <span class="px-3 py-1 rounded-lg bg-amber-500 text-slate-950 text-[11px] font-extrabold uppercase tracking-wide">
                                            Tingkat 2
                                        </span>
                                        <span class="text-xs font-bold text-slate-700">Pengendali Mutu</span>
                                        <span class="text-[11px] text-slate-400">
                                            Menjamin kualitas pengendalian risiko di {{ $namaBidang }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                        @foreach ($daftarMutu as $row)
                                            @include('home.partials.kartu-pegawai', [
                                                'row' => $row,
                                                'peran' => 'pengendali_mutu',
                                                'pita' => $pita,
                                                'bisaKelola' => $bisaKelola,
                                                'subjudul' => $namaBidang,
                                            ])
                                        @endforeach
                                    </div>

                                    {{-- Garis turun ke tim pengendali risiko --}}
                                    @if ($risikoBidang->isNotEmpty())
                                        <div class="flex justify-center py-4">
                                            <div class="w-0.5 h-8 bg-slate-200"></div>
                                        </div>

                                        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4">
                                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                                <span class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-[11px] font-extrabold uppercase tracking-wide">
                                                    Tingkat 3
                                                </span>
                                                <span class="text-xs font-bold text-emerald-900">
                                                    Tim Pengendali Risiko
                                                </span>
                                                <span class="text-[11px] text-emerald-700/80">
                                                    Melaksanakan pengendalian harian di {{ $namaBidang }}
                                                </span>
                                            </div>
                                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                                                @foreach ($risikoBidang as $row)
                                                    @include('home.partials.kartu-pegawai', [
                                                        'row' => $row,
                                                        'peran' => 'pengendali_risiko',
                                                        'pita' => $pita,
                                                        'bisaKelola' => $bisaKelola,
                                                        'subjudul' => $namaBidang,
                                                        'kecil' => true,
                                                    ])
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- ---------- PENGENDALI RISIKO YANG BELUM BERGANTUNG MUTU ---------- --}}
                @if ($risikoTersisa->isNotEmpty())
                    <div class="bg-white border border-slate-200 rounded-3xl p-6 md:p-8 shadow-sm">
                        <div class="flex flex-wrap items-center gap-2 mb-5">
                            <span class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-[11px] font-extrabold uppercase tracking-wide">
                                Tingkat 3
                            </span>
                            <h3 class="text-sm font-bold text-slate-800">Pengendali Risiko Belum Ditempatkan</h3>
                            <span class="text-[11px] text-slate-400">
                                Belum berada di bawah pengendali mutu bidang mana pun
                            </span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                            @foreach ($risikoTersisa as $row)
                                @include('home.partials.kartu-pegawai', [
                                    'row' => $row,
                                    'peran' => 'pengendali_risiko',
                                    'pita' => $pita,
                                    'bisaKelola' => $bisaKelola,
                                    'subjudul' => $row->bidang?->nama_bidang ?? 'Seluruh CRMC',
                                    'kecil' => true,
                                ])
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Penjelasan singkat tiap tingkat --}}
            <div class="mt-8 grid grid-cols-1 sm:grid-cols-3 gap-4">
                @php
                    $ket = [
                        [
                            'ikon' => 'crown',
                            'warna' => 'bg-blue-100 text-blue-800',
                            'judul' => 'Pemilik Risiko',
                            'isi' => 'Memastikan pengendalian risiko berjalan di seluruh bidang dan menjadi rujukan utama',
                        ],
                        [
                            'ikon' => 'badge-check',
                            'warna' => 'bg-amber-100 text-amber-700',
                            'judul' => 'Pengendali Mutu',
                            'isi' => 'Memastikan kualitas dan kelengkapan instrumen pengendalian yang diisi',
                        ],
                        [
                            'ikon' => 'shield-check',
                            'warna' => 'bg-emerald-100 text-emerald-700',
                            'judul' => 'Pengendali Risiko',
                            'isi' => 'Melaksanakan aktivitas pengendalian dan mengisi formulir instrumen',
                        ],
                    ];
                @endphp
                @foreach ($ket as $k)
                    <div class="flex items-start gap-3 bg-white border border-slate-200 rounded-2xl p-4">
                        <div class="w-9 h-9 rounded-xl {{ $k['warna'] }} flex items-center justify-center shrink-0">
                            <i data-lucide="{{ $k['ikon'] }}" class="w-[18px] h-[18px]"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800">{{ $k['judul'] }}</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed mt-0.5">{{ $k['isi'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
