{{--
    Bagan struktur organisasi pengendalian risiko pada tab Dashboard.

    Hierarki dua tingkat:
      - Tingkat 1 : Pemilik Risiko (satu orang untuk seluruh CRMC).
      - Tingkat 2 : Pengendali Mutu tiap bidang, ditampilkan bersama nama
                    bidangnya supaya jelas siapa pengendali mutu bagian mana.

    Garis penghubung digambar oleh JavaScript ke dalam <svg> yang menumpuk
    di atas bagan (posisinya dihitung dari posisi kartu), sehingga hierarki
    tetap terlihat rapi walau jumlah bidang atau lebarnya berubah-ubah.
    Datanya dikirim dari BidangController::strukturOrganisasi().

    Parameter: $strukturOrganisasi (array{tahun, pemilikRisiko, pengendaliMutu})
--}}
@push('styles')
<style>
    /* ---------- Bagan struktur organisasi ----------
       Kontainer bagan bisa digeser ke samping di layar sempit supaya tiap
       kartu tetap punya lebar yang layak dan garis hierarkinya tidak
       berantakan. */
    .org-scroll { overflow-x: auto; padding-bottom: .25rem; }

    .org-chart {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        /* min-width: bagan bisa digeser ke samping di layar sempit.
           max-width: di layar lebar kartu tidak ikut melar terlalu panjang. */
        min-width: 44rem;
        max-width: 60rem;
        margin: 0 auto;
        padding: 1.25rem 0 .5rem;
    }

    /* Lapisan gambar garis. pointer-events:none supaya tidak menghalangi
       klik kartu di bawahnya; z-index:0 menaruhnya di belakang kartu. */
    .org-lines {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 0;
    }
    .org-lines line { stroke: #94a3b8; stroke-width: 2; stroke-linecap: round; }

    /* ---------- Kartu simpul ---------- */
    .org-card {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: .375rem;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: .75rem;
        padding: 1rem 1.125rem;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .05);
    }

    /* Tingkat 1: menonjol dengan latar biru tua. */
    .org-card--root {
        width: 17rem;
        max-width: 100%;
        background: linear-gradient(160deg, #172554, #1e3a8a);
        border-color: #1e3a8a;
    }
    .org-card--root .org-name { color: #fff; }
    .org-card--root .org-meta { color: #bfdbfe; }
    .org-card--root .org-jabatan { color: #dbeafe; }

    /* Tingkat 2: satu kolom per bidang, lebar sama rata.
       Latarnya kuning, warna yang sama dengan sorotan hover navigasi bidang
       (#fbbf24), dengan teks biru tua supaya tetap terbaca. */
    .org-children {
        display: flex;
        justify-content: center;
        gap: 1.5rem;
        width: 100%;
        margin-top: 3.5rem;
    }
    .org-card--child {
        flex: 1 1 0;
        min-width: 0;
        background: #fbbf24;
        border-color: #f59e0b;
    }
    .org-card--child .org-name { color: var(--navy); }
    .org-card--child .org-meta { color: #78350f; }
    .org-card--child .org-photo { border-color: #f59e0b; }

    /* Foto dibuat cukup besar (potret 4:5) supaya wajah pegawai terlihat
       jelas di bagan, bukan sekadar kotak kecil. */
    .org-photo {
        width: 8rem;
        height: 10rem;
        border-radius: .75rem;
        object-fit: cover;
        object-position: top center;
        border: 2px solid var(--line);
    }
    .org-photo--root { width: 9.5rem; height: 12rem; border-color: #3b82f6; }

    /* Kotak pengganti saat kursi belum ditunjuk: ukurannya sama dengan foto,
       hanya bergaris putus-putus dan tanpa gambar. */
    .org-photo--kosong {
        display: flex;
        align-items: center;
        justify-content: center;
        border-style: dashed;
        border-color: #cbd5e1;
        background: rgba(255, 255, 255, .5);
    }
    .org-card--root .org-photo--kosong {
        border-color: rgba(147, 197, 253, .45);
        background: rgba(255, 255, 255, .08);
    }

    .org-tag {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        padding: .125rem .5rem;
        border-radius: .375rem;
        font-size: .625rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .org-tag--root { background: rgba(251, 191, 36, .16); color: #fcd34d; border: 1px solid rgba(251, 191, 36, .35); }
    /* Lencana di atas latar kuning: biru tua dengan teks kuning terang. */
    .org-tag--child { background: var(--navy); color: #fcd34d; }

    .org-name { font-size: .8125rem; font-weight: 700; color: var(--ink); line-height: 1.3; }
    .org-meta { font-size: .6875rem; color: var(--mut); }
    .org-jabatan { font-size: .6875rem; font-weight: 600; color: #1d4ed8; }

    /* Keterangan bidang tempat pengendali mutu bertugas. */
    .org-bidang {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        margin-top: .125rem;
        padding: .2rem .55rem;
        border-radius: .375rem;
        background: #f1f5f9;
        border: 1px solid var(--line);
        color: #334155;
        font-size: .6875rem;
        font-weight: 600;
    }
    /* Keterangan bidang di atas kartu kuning: chip terang agar menonjol. */
    .org-card--child .org-bidang {
        background: rgba(255, 255, 255, .85);
        border-color: rgba(120, 53, 15, .25);
        color: #78350f;
    }

    /* Kursi yang belum ditunjuk: tetap netral, bukan biru/kuning, supaya
       berbeda dari kartu yang sudah terisi. Aturannya diletakkan paling
       akhir agar menimpa warna kartu root (biru) maupun child (kuning). */
    .org-card--kosong {
        border-style: dashed;
        border-color: #cbd5e1;
        background: #f8fafc;
        box-shadow: none;
    }
    .org-card--kosong .org-name { color: var(--ink); }
    .org-card--kosong .org-meta { color: var(--mut); }
</style>
@endpush

<div class="card p-4 sm:p-5">
    <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
        <div class="min-w-0">
            <h3 class="card-title flex items-center gap-1.5">
                <i data-lucide="network" class="w-4 h-4 text-blue-600"></i>
                Struktur Organisasi Pengendalian Risiko
            </h3>
            <p class="page-sub mt-0.5">
                Pemilik Risiko bertanggung jawab atas seluruh CRMC, sedangkan setiap
                bidang memiliki satu Pengendali Mutu.
            </p>
        </div>
        <span class="badge">Tahun {{ $strukturOrganisasi['tahun'] }}</span>
    </div>

    <div class="org-scroll">
        <div class="org-chart" data-org-chart>
            {{-- Garis hierarki digambar di sini oleh JavaScript. --}}
            <svg class="org-lines" data-org-lines preserveAspectRatio="none" aria-hidden="true"></svg>

            {{-- ===== TINGKAT 1: PEMILIK RISIKO ===== --}}
            @php $pemilikRisiko = $strukturOrganisasi['pemilikRisiko']; @endphp
            @if ($pemilikRisiko)
                <article class="org-card org-card--root" data-org-node="root">
                    <img src="{{ $pemilikRisiko->foto_url }}" alt="{{ $pemilikRisiko->name }}"
                         loading="lazy" decoding="async" class="org-photo org-photo--root">
                    <span class="org-tag org-tag--root">
                        <i data-lucide="crown" class="w-3 h-3"></i> Pemilik Risiko
                    </span>
                    <h4 class="org-name">{{ $pemilikRisiko->name }}</h4>
                    <p class="org-meta">NIP. {{ $pemilikRisiko->nip ?? '-' }}</p>
                    <p class="org-jabatan">{{ $pemilikRisiko->jabatan ?? '-' }}</p>
                </article>
            @else
                <article class="org-card org-card--root org-card--kosong" data-org-node="root">
                    <div class="org-photo org-photo--root org-photo--kosong">
                        <i data-lucide="user-round-x" class="w-14 h-14 text-blue-200"></i>
                    </div>
                    <span class="org-tag org-tag--root">
                        <i data-lucide="crown" class="w-3 h-3"></i> Pemilik Risiko
                    </span>
                    <h4 class="org-name">Belum Ditunjuk</h4>
                    <p class="org-meta">Atur lewat Komponen 1 halaman 8 Komponen.</p>
                </article>
            @endif

            {{-- ===== TINGKAT 2: PENGENDALI MUTU PER BIDANG ===== --}}
            <div class="org-children" data-org-children>
                @forelse ($strukturOrganisasi['pengendaliMutu'] as $baris)
                    @php
                        $bidang = $baris['bidang'];
                        $mutu = $baris['user'];
                    @endphp
                    <article class="org-card org-card--child {{ $mutu ? '' : 'org-card--kosong' }}"
                             data-org-node="child">
                        @if ($mutu)
                            <img src="{{ $mutu->foto_url }}" alt="{{ $mutu->name }}"
                                 loading="lazy" decoding="async" class="org-photo">
                        @else
                            <div class="org-photo org-photo--kosong">
                                <i data-lucide="user-round-x" class="w-12 h-12 text-slate-300"></i>
                            </div>
                        @endif

                        <span class="org-tag org-tag--child">
                            <i data-lucide="badge-check" class="w-3 h-3"></i> Pengendali Mutu
                        </span>

                        <h4 class="org-name">{{ $mutu->name ?? 'Belum Ditunjuk' }}</h4>
                        <p class="org-meta">NIP. {{ $mutu->nip ?? '-' }}</p>

                        {{-- Keterangan bagian: pengendali mutu ini bertugas di bidang mana. --}}
                        <span class="org-bidang">
                            {{-- Tanpa kelas warna: ikon mewarisi warna teks chip. --}}
                            <i data-lucide="{{ $bidang->ikon() }}" class="w-3 h-3"></i>
                            {{ $bidang->nama_bidang }}
                        </span>
                    </article>
                @empty
                    <p class="page-sub">Belum ada bidang.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    /**
     * Gambar garis hierarki bagan struktur organisasi.
     *
     * Titik acuan dihitung dengan offsetLeft/offsetTop terhadap kontainer
     * bagan (yang ber-position:relative), jadi nilainya tidak terpengaruh
     * posisi scroll. Tiga kelompok garis yang digambar:
     *   1. batang turun dari kartu Pemilik Risiko ke garis mendatar;
     *   2. garis mendatar yang menghubungkan pusat semua kartu Pengendali
     *      Mutu;
     *   3. batang turun dari garis mendatar ke tiap kartu Pengendali Mutu.
     */
    function gambarGaris(chart) {
        const svg = chart.querySelector('[data-org-lines]');
        if (!svg) return;

        const root = chart.querySelector('[data-org-node="root"]');
        const row = chart.querySelector('[data-org-children]');
        const anak = row ? Array.from(row.querySelectorAll('[data-org-node="child"]')) : [];

        const lebar = chart.clientWidth;
        const tinggi = chart.clientHeight;

        // Kontainer belum terlihat (mis. tab tersembunyi) -> lewati.
        if (!root || !row || lebar === 0 || tinggi === 0 || anak.length === 0) {
            svg.innerHTML = '';
            return;
        }

        svg.setAttribute('viewBox', '0 0 ' + lebar + ' ' + tinggi);
        svg.setAttribute('width', lebar);
        svg.setAttribute('height', tinggi);

        const pusatX = (el) => el.offsetLeft + el.offsetWidth / 2;
        const dasar = (el) => el.offsetTop + el.offsetHeight;

        const rootX = pusatX(root);
        const rootBawah = dasar(root);
        const barisAtas = row.offsetTop;
        const tengahY = rootBawah + (barisAtas - rootBawah) / 2;

        const pusatAnak = anak.map(pusatX);
        const kiri = Math.min.apply(null, pusatAnak.concat(rootX));
        const kanan = Math.max.apply(null, pusatAnak.concat(rootX));

        let markup = '';
        markup += '<line x1="' + rootX + '" y1="' + rootBawah + '" x2="' + rootX + '" y2="' + tengahY + '" />';
        markup += '<line x1="' + kiri + '" y1="' + tengahY + '" x2="' + kanan + '" y2="' + tengahY + '" />';
        pusatAnak.forEach(function (cx) {
            markup += '<line x1="' + cx + '" y1="' + tengahY + '" x2="' + cx + '" y2="' + barisAtas + '" />';
        });

        svg.innerHTML = markup;
    }

    function gambarSemua() {
        document.querySelectorAll('[data-org-chart]').forEach(gambarGaris);
    }

    // Gambar ulang saat ukuran berubah (mis. jendela di-resize atau tab
    // ditampilkan). ResizeObserver dipakai bila tersedia.
    if (window.ResizeObserver) {
        const pengamat = new ResizeObserver(gambarSemua);
        document.querySelectorAll('[data-org-chart]').forEach((c) => pengamat.observe(c));
    }
    window.addEventListener('resize', gambarSemua);
    window.addEventListener('load', gambarSemua);
    document.addEventListener('DOMContentLoaded', gambarSemua);
})();
</script>
@endpush
