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
    // penghubung supaya konsisten. Semua pita memakai nuansa biru
    // agar tema visual tetap satu warna.
    $pita = [
        'pemilik_risiko' => ['bg-blue-900', 'text-blue-100'],
        'pengendali_mutu' => ['bg-blue-700', 'text-white'],
        'pengendali_risiko' => ['bg-blue-500', 'text-white'],
    ];
@endphp

<section id="struktur-organisasi" class="scroll-mt-20 py-10 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Judul section --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-6">
            <div class="max-w-2xl">
                <span class="eyebrow mb-2 block">Struktur Organisasi</span>
                <h2 class="section-title">Rantai Tanggung Jawab Pengendalian Risiko</h2>
                <p class="page-sub mt-2 leading-relaxed">
                    Preventive control dijalankan berjenjang. Pemilik Risiko
                    bertanggung jawab atas seluruh CRMC, setiap bidang memiliki satu
                    Pengendali Mutu, dan pelaksanaan pengendalian harian diserahkan
                    kepada Tim Pengendali Risiko di masing-masing bidang.
                </p>
            </div>

            @if ($bisaKelola)
                <button onclick="bukaModalStruktur()" class="btn btn-dark shrink-0">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    Tambah Peran
                </button>
            @endif
        </div>

        @if ($pemilikRisiko->isEmpty() && $pengendaliMutu->isEmpty() && $pengendaliRisiko->isEmpty())
            {{-- Empty state --}}
            <div class="empty">
                <i data-lucide="users" class="w-7 h-7 text-slate-300 mx-auto mb-2"></i>
                <p class="font-semibold text-slate-600">Belum Ada Struktur Organisasi</p>
                <p class="text-[11px] text-slate-400 mt-1 max-w-md mx-auto leading-relaxed">
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
                    <div class="card p-4 sm:p-5">
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span class="badge badge-accent">Tingkat 1</span>
                            <h3 class="card-title">Pemilik Risiko</h3>
                            <span class="page-sub">
                                Penanggung jawab atas seluruh pengendalian risiko CRMC
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
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

                {{-- ---------- TINGKAT 2: PENGENDALI MUTU ---------- --}}
                @if ($pengendaliMutu->isNotEmpty())
                    @php $mutuPerBidang = $pengendaliMutu->groupBy('bidang_id'); @endphp

                    <div class="space-y-4">
                        @foreach ($mutuPerBidang as $bidangId => $daftarMutu)
                            @php
                                $namaBidang = $daftarMutu->first()->bidang?->nama_bidang ?? 'Tanpa Bidang';
                                $risikoBidang = $risikoPerBidang->get($bidangId, collect());
                            @endphp

                            <div class="card">
                                {{-- Pita nama bidang --}}
                                <div class="card-head flex flex-wrap items-center gap-2 !bg-blue-950 !border-blue-900 text-white">
                                    <i data-lucide="landmark" class="w-4 h-4 text-blue-400"></i>
                                    <span class="text-xs font-semibold">{{ $namaBidang }}</span>
                                    <span class="ml-auto flex items-center gap-2">
                                        <span class="badge !bg-blue-900 !text-blue-100">{{ $daftarMutu->count() }} pengendali mutu</span>
                                        <span class="badge !bg-blue-900 !text-blue-100">{{ $risikoBidang->count() }} pengendali risiko</span>
                                    </span>
                                </div>

                                <div class="p-4 sm:p-5">
                                    <div class="flex flex-wrap items-center gap-2 mb-4">
                                        <span class="badge badge-accent">Tingkat 2</span>
                                        <span class="text-xs font-semibold text-slate-700">Pengendali Mutu</span>
                                        <span class="page-sub">
                                            Menjamin kualitas pengendalian risiko di {{ $namaBidang }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
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

                                    {{-- Tim pengendali risiko di bawah bidang ini --}}
                                    @if ($risikoBidang->isNotEmpty())
                                        <div class="mt-4 p-3.5 bg-blue-50 border border-blue-100 rounded-lg">
                                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                                <span class="badge badge-accent">Tingkat 3</span>
                                                <span class="text-xs font-semibold text-blue-900">
                                                    Tim Pengendali Risiko
                                                </span>
                                                <span class="page-sub">
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
                    <div class="card p-4 sm:p-5">
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span class="badge badge-accent">Tingkat 3</span>
                            <h3 class="card-title">Pengendali Risiko Belum Ditempatkan</h3>
                            <span class="page-sub">
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
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
                @php
                    $ket = [
                        [
                            'ikon' => 'crown',
                            'judul' => 'Pemilik Risiko',
                            'isi' => 'Memastikan pengendalian risiko berjalan di seluruh bidang dan menjadi rujukan utama',
                        ],
                        [
                            'ikon' => 'badge-check',
                            'judul' => 'Pengendali Mutu',
                            'isi' => 'Memastikan kualitas dan kelengkapan instrumen pengendalian yang diisi',
                        ],
                        [
                            'ikon' => 'shield-check',
                            'judul' => 'Pengendali Risiko',
                            'isi' => 'Melaksanakan aktivitas pengendalian dan mengisi formulir instrumen',
                        ],
                    ];
                @endphp
                @foreach ($ket as $k)
                    <div class="card p-4 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-md bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                            <i data-lucide="{{ $k['ikon'] }}" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800">{{ $k['judul'] }}</h4>
                            <p class="page-sub leading-relaxed mt-0.5">{{ $k['isi'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
