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

@push('styles')
<style>
    .galeri-slide { will-change: transform, opacity; }
    .galeri-slide .slide-caption,
    .galeri-slide .slide-counter,
    .galeri-slide .slide-admin {
        transition: opacity .25s ease;
    }
    .galeri-slide:not(.is-active) .slide-caption,
    .galeri-slide:not(.is-active) .slide-counter,
    .galeri-slide:not(.is-active) .slide-admin {
        opacity: 0;
        pointer-events: none;
    }
</style>
@endpush

<section id="galeri" class="scroll-mt-20 py-10 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4 mb-6">
            <div class="max-w-2xl">
                <span class="eyebrow mb-2 block">Sarana dan Prasarana</span>
                <h2 class="text-xl md:text-2xl font-bold tracking-tight text-slate-900">
                    Pendukung Pelaksanaan Pengendalian
                </h2>
                <p class="page-sub mt-2 leading-relaxed max-w-xl">
                    Sarana dan prasarana yang mendukung pelaksanaan pengendalian
                    risiko, mulai dari ruang kerja, perangkat, hingga infrastruktur
                    digital.
                </p>
            </div>

            @if ($bisaKelola)
                <div class="flex items-center gap-2 shrink-0">
                    <button onclick="bukaModalGaleri()" class="btn btn-dark">
                        <i data-lucide="image-plus" class="w-4 h-4"></i>
                        Unggah Gambar
                    </button>
                </div>
            @endif
        </div>

        @if ($total === 0)
            {{-- Empty state --}}
            <div class="empty">
                <i data-lucide="image-off" class="w-7 h-7 text-slate-300 mx-auto mb-2"></i>
                <p class="font-semibold text-slate-600">Belum Ada Gambar</p>
                <p class="text-[11px] text-slate-400 mt-1 max-w-md mx-auto leading-relaxed">
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
            {{-- ===================== CAROUSEL (TUMPUKAN) ===================== --}}
            <div id="galeriGaleri" class="relative"
                 role="region"
                 aria-roledescription="carousel"
                 aria-label="Galeri sarana dan prasarana"
                 data-total="{{ $total }}"
                 data-interval="6000"
                 onmouseenter="jedaGaleri(true)"
                 onmouseleave="jedaGaleri(false)">

                {{-- Viewport --}}
                <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-100">
                    <div id="galeriTrack" class="relative w-full min-h-[220px]" tabindex="0">
                        @foreach ($galeri as $g)
                            @php
                                $dataUbah = [
                                    'id' => $g->id,
                                    'judul' => $g->judul,
                                    'keterangan' => $g->keterangan,
                                ];
                            @endphp
                            <article class="galeri-slide absolute inset-x-0 top-0 transition-all duration-500 ease-out motion-reduce:transition-none"
                                     data-slide="{{ $loop->index }}">
                                <div class="relative w-full rounded-2xl overflow-hidden bg-slate-100 border border-slate-200 shadow-sm">
                                    <img src="{{ $g->url }}"
                                         alt="{{ $g->judul }}"
                                         width="1200"
                                         height="800"
                                         loading="lazy"
                                         decoding="async"
                                         class="block w-full h-auto">

                                    {{-- Gradien bawah untuk keterbacaan teks --}}
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-slate-900/15 to-transparent pointer-events-none"></div>

                                    {{-- Keterangan slide --}}
                                    <div class="slide-caption absolute bottom-0 left-0 right-0 p-5 md:p-7 text-white">
                                        <h3 class="text-lg md:text-2xl font-bold leading-tight max-w-3xl">
                                            {{ $g->judul }}
                                        </h3>
                                        @if ($g->keterangan)
                                            <p class="text-xs md:text-sm text-slate-200 leading-relaxed mt-1.5 max-w-2xl">
                                                {{ $g->keterangan }}
                                            </p>
                                        @endif
                                    </div>

                                    <span class="slide-counter absolute top-3 right-3 px-2 py-1 bg-slate-900/70 text-white text-[10px] font-semibold rounded-md border border-white/20">
                                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} / {{ str_pad($total, 2, '0', STR_PAD_LEFT) }}
                                    </span>

                                    {{-- Tombol admin per gambar --}}
                                    @if ($bisaKelola)
                                        <div class="slide-admin absolute top-3 left-3 flex items-center gap-1.5 z-10">
                                            <button type="button"
                                                    onclick='bukaModalGaleri(@json($dataUbah))'
                                                    title="Ubah gambar ini"
                                                    aria-label="Ubah gambar ini"
                                                    class="icon-btn !bg-white/90 hover:!bg-white shadow-sm">
                                                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                            </button>
                                            <button type="button"
                                                    onclick="konfirmasiHapusGaleri({{ $g->id }}, @js($g->judul) )"
                                                    title="Hapus gambar ini"
                                                    aria-label="Hapus gambar ini"
                                                    class="icon-btn icon-btn-danger !bg-white/90 hover:!bg-white shadow-sm">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>

                    {{-- Tombol panah --}}
                    @if ($total > 1)
                        <button type="button"
                                onclick="geserGaleri(-1)"
                                aria-label="Gambar sebelumnya"
                                class="absolute left-3 top-1/2 -translate-y-1/2 z-20 inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/90 hover:bg-white text-slate-700 shadow-sm border border-slate-200 transition">
                            <i data-lucide="chevron-left" class="w-5 h-5"></i>
                        </button>
                        <button type="button"
                                onclick="geserGaleri(1)"
                                aria-label="Gambar berikutnya"
                                class="absolute right-3 top-1/2 -translate-y-1/2 z-20 inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/90 hover:bg-white text-slate-700 shadow-sm border border-slate-200 transition">
                            <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </button>
                    @endif
                </div>

                {{-- Kontrol: titik navigasi + jeda/putar --}}
                @if ($total > 1)
                    <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <p class="text-[11px] text-slate-400 order-2 sm:order-1">
                            Gunakan panah, titik, atau geser untuk berpindah gambar.
                        </p>
                        <div class="flex items-center gap-3 order-1 sm:order-2">
                            <div id="galeriTitik" class="flex items-center justify-center gap-1.5 flex-wrap">
                                @for ($i = 0; $i < $total; $i++)
                                    <button type="button"
                                            onclick="tampilGaleri({{ $i }})"
                                            data-titik="{{ $i }}"
                                            aria-label="Gambar {{ $i + 1 }}"
                                            class="h-2 rounded-full transition-all {{ $i === 0 ? 'w-7 bg-blue-600' : 'w-2 bg-slate-300 hover:bg-slate-400' }}"></button>
                                @endfor
                            </div>
                            <button type="button"
                                    id="galeriJedaPutar"
                                    onclick="togglePutarGaleri()"
                                    aria-pressed="true"
                                    aria-label="Jeda putar otomatis"
                                    title="Jeda putar otomatis"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md border border-slate-200 bg-white text-xs font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition">
                                <i data-lucide="pause" class="w-3.5 h-3.5 ikon-jeda"></i>
                                <i data-lucide="play" class="w-3.5 h-3.5 ikon-putar hidden"></i>
                                <span class="label-putar">Jeda</span>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>

@push('scripts')
<script>
    // ===================== CAROUSEL TUMPUKAN SARANA DAN PRASARANA =====================
    (function () {
        const root = document.getElementById('galeriGaleri');
        if (!root) return;

        const track = document.getElementById('galeriTrack');
        const titikWrap = document.getElementById('galeriTitik');
        const slides = Array.from(track.querySelectorAll('.galeri-slide'));
        const total = parseInt(root.dataset.total || '1', 10);
        const interval = parseInt(root.dataset.interval || '6000', 10);

        let index = 0;
        let timer = null;
        let dijedaHover = false;

        const reducedMotion = window.matchMedia &&
            window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let autoDiinginkan = !reducedMotion;

        function autoBerjalan() {
            return total >= 2 && autoDiinginkan && !dijedaHover && !document.hidden;
        }

        function tataTumpukan() {
            slides.forEach(function (slide, idx) {
                const pos = (idx - index + total) % total;
                let transform = '';
                let opacity = '0';
                let zIndex = '0';
                let pointer = 'none';

                if (pos === 0) {
                    transform = 'translate3d(0, 0, 0) scale(1)';
                    opacity = '1';
                    zIndex = String(total + 2);
                    pointer = 'auto';
                    slide.classList.add('is-active');
                } else if (pos === 1) {
                    transform = 'translate3d(16px, 12px, 0) scale(0.96)';
                    opacity = '0.72';
                    zIndex = String(total + 1);
                    slide.classList.remove('is-active');
                } else if (pos === 2) {
                    transform = 'translate3d(32px, 24px, 0) scale(0.92)';
                    opacity = '0.48';
                    zIndex = String(total);
                    slide.classList.remove('is-active');
                } else {
                    transform = 'translate3d(48px, 36px, 0) scale(0.88)';
                    opacity = '0';
                    zIndex = '1';
                    slide.classList.remove('is-active');
                }

                slide.style.transform = transform;
                slide.style.opacity = opacity;
                slide.style.zIndex = zIndex;
                slide.style.pointerEvents = pointer;
            });

            if (titikWrap) {
                titikWrap.querySelectorAll('[data-titik]').forEach(function (btn) {
                    const aktif = parseInt(btn.dataset.titik, 10) === index;
                    btn.className = 'h-2 rounded-full transition-all ' +
                        (aktif ? 'w-7 bg-blue-600' : 'w-2 bg-slate-300 hover:bg-slate-400');
                });
            }
        }

        function aturTinggiCarousel() {
            const slide = slides[index];
            if (!slide) return;
            const img = slide.querySelector('img');
            if (!img) return;

            const terapkan = function () {
                if (!img.naturalWidth || !img.naturalHeight) return;
                const lebar = track.clientWidth || track.getBoundingClientRect().width;
                if (!lebar) return;
                const tinggi = Math.round(lebar * img.naturalHeight / img.naturalWidth);
                track.style.height = tinggi + 'px';
            };

            if (img.complete && img.naturalWidth) {
                terapkan();
            } else {
                img.addEventListener('load', terapkan, { once: true });
            }
        }

        window.tampilGaleri = function (i) {
            index = ((i % total) + total) % total;
            tataTumpukan();
            aturTinggiCarousel();
        };

        window.geserGaleri = function (arah) {
            tampilGaleri(index + arah);
            mulai();
        };

        window.jedaGaleri = function (nyata) {
            dijedaHover = !!nyata;
            if (nyata) berhenti();
            else mulai();
        };

        window.togglePutarGaleri = function () {
            autoDiinginkan = !autoDiinginkan;
            perbaruiTombolPutar();
            if (autoDiinginkan) mulai();
            else berhenti();
        };

        function perbaruiTombolPutar() {
            const btn = document.getElementById('galeriJedaPutar');
            if (!btn) return;
            btn.setAttribute('aria-pressed', autoDiinginkan ? 'true' : 'false');
            btn.setAttribute('aria-label', autoDiinginkan ? 'Jeda putar otomatis' : 'Putar otomatis');
            btn.setAttribute('title', autoDiinginkan ? 'Jeda putar otomatis' : 'Putar otomatis');
            const ikonJeda = btn.querySelector('.ikon-jeda');
            const ikonPutar = btn.querySelector('.ikon-putar');
            const label = btn.querySelector('.label-putar');
            if (ikonJeda) ikonJeda.classList.toggle('hidden', !autoDiinginkan);
            if (ikonPutar) ikonPutar.classList.toggle('hidden', autoDiinginkan);
            if (label) label.textContent = autoDiinginkan ? 'Jeda' : 'Putar';
        }

        function mulai() {
            berhenti();
            if (!autoBerjalan()) return;
            timer = setInterval(function () { tampilGaleri(index + 1); }, interval);
        }

        function berhenti() {
            if (timer) { clearInterval(timer); timer = null; }
        }

        // Geser dengan keyboard saat carousel difokuskan.
        track.setAttribute('tabindex', '0');
        track.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') { window.geserGaleri(-1); e.preventDefault(); }
            if (e.key === 'ArrowRight') { window.geserGaleri(1); e.preventDefault(); }
        });

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) berhenti();
            else mulai();
        });

        // Geser dengan sentuhan di perangkat layar sentuh.
        let sentuhAwalX = 0;
        track.addEventListener('touchstart', function (e) {
            sentuhAwalX = e.changedTouches[0].clientX;
        }, { passive: true });
        track.addEventListener('touchend', function (e) {
            const delta = e.changedTouches[0].clientX - sentuhAwalX;
            if (Math.abs(delta) > 45) window.geserGaleri(delta < 0 ? 1 : -1);
        }, { passive: true });

        window.addEventListener('resize', aturTinggiCarousel);

        tampilGaleri(0);
        perbaruiTombolPutar();
        mulai();
    })();
</script>
@endpush