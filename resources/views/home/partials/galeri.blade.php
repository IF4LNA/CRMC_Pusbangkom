{{--
    Carousel "Sarana dan Prasarana". Gambar diunggah admin lewat panel di
    `home/panel-admin.blade.php`, lalu diurutkan lewat tombol panah.

    Carousel ditulis tangan (tanpa library) supaya tidak menambah
    ketergantungan baru pada halaman yang juga memuat Leaflet.
--}}
@php
    $bisaKelola = Auth::check() && Auth::user()->isAdmin();
    $total = $galeri->count();
@endphp

<section id="galeri" class="scroll-mt-32 py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-10">
            <div class="max-w-2xl">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-900 text-[11px] font-bold tracking-wide uppercase mb-4">
                    <i data-lucide="images" class="w-3.5 h-3.5"></i>
                    Sarana dan Prasarana
                </span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Pendukung Pelaksanaan Pengendalian
                </h2>
                <p class="text-sm text-slate-600 leading-relaxed mt-3">
                    Sarana dan prasarana yang mendukung pelaksanaan pengendalian
                    risiko, mulai dari ruang kerja, perangkat, hingga infrastruktur
                    digital.
                </p>
            </div>

            @if ($bisaKelola)
                <div class="flex items-center gap-2 shrink-0">
                    @if ($total > 1)
                        <button onclick="geserGaleri(-1)" title="Gambar sebelumnya"
                                class="w-9 h-9 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl flex items-center justify-center transition">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                        </button>
                        <button onclick="geserGaleri(1)" title="Gambar berikutnya"
                                class="w-9 h-9 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl flex items-center justify-center transition">
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </button>
                    @endif
                    <button onclick="bukaModalGaleri()"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition shadow-sm">
                        <i data-lucide="image-plus" class="w-4 h-4"></i>
                        Unggah Gambar
                    </button>
                </div>
            @endif
        </div>

        @if ($total === 0)
            {{-- Empty state --}}
            <div class="border-2 border-dashed border-slate-300 rounded-3xl p-12 text-center bg-slate-50">
                <div class="w-16 h-16 mx-auto bg-white border border-slate-200 rounded-2xl flex items-center justify-center mb-4">
                    <i data-lucide="image-off" class="w-8 h-8 text-slate-400"></i>
                </div>
                <h3 class="text-base font-bold text-slate-800 mb-2">Belum Ada Gambar</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
                    @if ($bisaKelola)
                        Belum ada gambar sarana dan prasarana. Gunakan tombol
                        "Unggah Gambar" di atas untuk menambahkannya. Gambar
                        otomatis diperkecil ke ukuran yang tetap tajam supaya halaman
                        tetap ringan.
                    @else
                        Galeri sarana dan prasarana belum diisi oleh administrator.
                    @endif
                </p>
            </div>
        @else
            {{-- ===================== CAROUSEL ===================== --}}
            <div id="galeriGaleri" class="relative"
                 data-total="{{ $total }}"
                 data-interval="6000"
                 onmouseenter="jedaGaleri(true)"
                 onmouseleave="jedaGaleri(false)">

                {{-- Viewport: hanya satu slide terlihat, slide lain
                     absolut dan translate-geser. --}}
                <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-slate-100 shadow-sm">
                    <div id="galeriTrack" class="flex transition-transform duration-500 ease-out">
                        @foreach ($galeri as $g)
                            @php
                                $dataUbah = [
                                    'id' => $g->id,
                                    'judul' => $g->judul,
                                    'keterangan' => $g->keterangan,
                                ];
                            @endphp
                            <div class="w-full shrink-0 relative">
                                <div class="relative w-full h-64 sm:h-80 md:h-[420px] bg-slate-200">
                                    {{-- object-cover menjaga seluruh area terisi
                                         walau rasio gambar berbeda-beda. --}}
                                    <img src="{{ $g->url }}"
                                         alt="{{ $g->judul }}"
                                         width="1200"
                                         height="800"
                                         loading="lazy"
                                         decoding="async"
                                         class="w-full h-full object-cover">

                                    {{-- Gradien agar teks tetap terbaca di atas
                                         foto yang terang. --}}
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/25 to-transparent"></div>

                                    {{-- Keterangan slide --}}
                                    <div class="absolute bottom-0 left-0 right-0 p-5 md:p-7 text-white">
                                        <h3 class="text-lg md:text-2xl font-extrabold leading-tight">
                                            {{ $g->judul }}
                                        </h3>
                                        @if ($g->keterangan)
                                            <p class="text-xs md:text-sm text-slate-200 leading-relaxed mt-1.5 max-w-2xl">
                                                {{ $g->keterangan }}
                                            </p>
                                        @endif
                                    </div>

                                    <span class="absolute top-4 right-4 px-2.5 py-1 bg-slate-950/70 backdrop-blur text-white text-[10px] font-bold rounded-full border border-white/20">
                                        {{ $loop->iteration }} / {{ $total }}
                                    </span>
                                </div>

                                {{-- Tombol admin per gambar --}}
                                @if ($bisaKelola)
                                    {{-- Argumen @json ditulis dalam satu baris:
                                         Blade hanya mengenali argumen direktif
                                         sampai tanda kurung tertutup pertama. --}}
                                    <div class="absolute top-4 left-4 flex items-center gap-1.5">
                                        <button type="button"
                                                onclick='bukaModalGaleri(@json($dataUbah))'
                                                title="Ubah gambar ini"
                                                class="w-8 h-8 bg-white/90 hover:bg-white text-slate-800 rounded-lg shadow-sm flex items-center justify-center transition">
                                            <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <button type="button"
                                                onclick="konfirmasiHapusGaleri({{ $g->id }}, @js($g->judul))"
                                                title="Hapus gambar ini"
                                                class="w-8 h-8 bg-white/90 hover:bg-rose-500 hover:text-white text-rose-700 rounded-lg shadow-sm flex items-center justify-center transition">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Titik navigasi --}}
                @if ($total > 1)
                    <div id="galeriTitik" class="flex items-center justify-center gap-2 mt-5">
                        @for ($i = 0; $i < $total; $i++)
                            <button type="button"
                                    onclick="tampilGaleri({{ $i }})"
                                    data-titik="{{ $i }}"
                                    aria-label="Gambar {{ $i + 1 }}"
                                    class="h-2 rounded-full transition-all {{ $i === 0 ? 'w-7 bg-amber-500' : 'w-2 bg-slate-300 hover:bg-slate-400' }}"></button>
                        @endfor
                    </div>
                @endif
            </div>

            {{-- Daftar judul (berguna sebagai teks alternatif dan agar
                 gambar dapat dicari lewat Ctrl+F) --}}
            <ul class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach ($galeri as $g)
                    <li class="flex items-center gap-2 text-[11px] text-slate-500">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300 shrink-0"></span>
                        <span class="truncate">{{ $g->judul }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>

@push('scripts')
<script>
    // ===================== CAROUSEL SARANA DAN PRASARANA =====================
    // Berjalan sendiri, berhenti saat kursor di atas carousel, dan berhenti
    // juga sementara ketika tab browser tidak aktif supaya tidak berjalan
    // di belakang layar.
    (function () {
        const root = document.getElementById('galeriGaleri');
        if (!root) return;

        const track = document.getElementById('galeriTrack');
        const titikWrap = document.getElementById('galeriTitik');
        const total = parseInt(root.dataset.total || '1', 10);
        const interval = parseInt(root.dataset.interval || '6000', 10);

        let index = 0;
        let timer = null;
        let dijeda = false;

        window.tampilGaleri = function (i) {
            index = ((i % total) + total) % total;
            track.style.transform = 'translateX(' + (-index * 100) + '%)';
            if (titikWrap) {
                titikWrap.querySelectorAll('[data-titik]').forEach(function (btn) {
                    const aktif = parseInt(btn.dataset.titik, 10) === index;
                    btn.className = 'h-2 rounded-full transition-all ' +
                        (aktif ? 'w-7 bg-amber-500' : 'w-2 bg-slate-300 hover:bg-slate-400');
                });
            }
        };

        window.geserGaleri = function (arah) {
            tampilGaleri(index + arah);
            mulai();
        };

        window.jedaGaleri = function (nyata) {
            dijeda = nyata;
            if (nyata) berhenti();
            else mulai();
        };

        function mulai() {
            berhenti();
            if (total < 2 || dijeda || document.hidden) return;
            timer = setInterval(function () { tampilGaleri(index + 1); }, interval);
        }

        function berhenti() {
            if (timer) { clearInterval(timer); timer = null; }
        }

        // Geser dengan keyboard saat carousel difokuskan, supaya
        // navigasi tetap tersedia tanpa mouse.
        track.setAttribute('tabindex', '0');
        track.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') { window.geserGaleri(-1); e.preventDefault(); }
            if (e.key === 'ArrowRight') { window.geserGaleri(1); e.preventDefault(); }
        });

        document.addEventListener('visibilitychange', mulai);

        // Geser dengan sentuhan di perangkat layar sentuh.
        let SentuhAwalX = 0;
        track.addEventListener('touchstart', function (e) {
            SentuhAwalX = e.changedTouches[0].clientX;
        }, { passive: true });
        track.addEventListener('touchend', function (e) {
            const delta = e.changedTouches[0].clientX - SentuhAwalX;
            if (Math.abs(delta) > 45) window.geserGaleri(delta < 0 ? 1 : -1);
        }, { passive: true });

        mulai();
    })();
</script>
@endpush
