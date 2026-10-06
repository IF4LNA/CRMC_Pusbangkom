<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CRMC') | Kementerian Pekerjaan Umum</title>
    {{-- Favicon memakai logo Kementerian Pekerjaan Umum, bukan ikon
         bawaan Laravel. --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/Logo_PU.svg') }}">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pupr: { navy: '#0f172a', blue: '#1e3a8a', yellow: '#f59e0b', accent: '#2563eb' }
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        .bento-card { transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
        .bento-card:hover { transform: translateY(-3px); }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        {{-- ======================================================
             SISTEM DESAIN MINIMALIS

             Seluruh token warna, radius, dan komponen dasar (kartu,
             tombol, input, badge, tabel, modal) didefinisikan satu kali
             di sini. Halaman cukup menyebut nama komponennya, bukan
             rangkaian kelas utilitas yang panjang, sehingga tampilan
             antar halaman konsisten dan mudah dirawat.

             Aksen tunggal: biru. Sisanya netral abu-abu (slate).
        ======================================================= --}}
        :root {
            --ac: #2563eb;          /* biru = satu-satunya warna aksen */
            --ac-dark: #1d4ed8;
            --ac-soft: #dbeafe;
            --navy: #172554;        /* biru paling tua untuk header */
            --ink: #0f172a;
            --line: #e2e8f0;
            --mut: #64748b;
        }

        body { font-size: 14px; }

        /* Sembunyikan scrollbar pada navigasi yang bisa digeser */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

        /* ---------- Permukaan ---------- */
        .card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: .75rem;
        }
        .card-head {
            padding: .875rem 1rem;
            border-bottom: 1px solid var(--line);
        }
        .card-title {
            font-size: .875rem;
            font-weight: 600;
            color: var(--ink);
        }

        /* ---------- Tipografi ---------- */
        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -.015em;
            color: var(--ink);
        }
        .page-sub { font-size: .8125rem; color: var(--mut); }
        .eyebrow {
            font-size: .6875rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--mut);
        }
        .section-title {
            font-size: 1.125rem;
            font-weight: 700;
            letter-spacing: -.01em;
            color: var(--ink);
        }

        /* ---------- Tombol ---------- */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .375rem;
            padding: .5rem .875rem;
            border: 1px solid transparent;
            border-radius: .5rem;
            font-size: .8125rem;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
            cursor: pointer;
            transition: background .15s, border-color .15s, color .15s;
        }
        .btn-primary { background: var(--ac); color: #fff; }
        .btn-primary:hover { background: var(--ac-dark); }
        .btn-dark { background: var(--ink); color: #fff; }
        .btn-dark:hover { background: #1e293b; }
        .btn-outline { background: #fff; border-color: #cbd5e1; color: #334155; }
        .btn-outline:hover { background: #f8fafc; }
        .btn-quiet { background: #f1f5f9; color: #334155; }
        .btn-quiet:hover { background: #e2e8f0; }
        .btn-danger { background: #fff; border-color: #fecdd3; color: #be123c; }
        .btn-danger:hover { background: #fff1f2; }
        .btn-sm { padding: .3125rem .625rem; font-size: .75rem; }

        /* Tombol ikon hanya berisi satu ikon */
        .icon-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.875rem;
            height: 1.875rem;
            border: 1px solid var(--line);
            border-radius: .375rem;
            background: #fff;
            color: #475569;
            transition: background .15s, color .15s, border-color .15s;
        }
        .icon-btn:hover { background: #f8fafc; color: var(--ink); }
        .icon-btn-danger:hover { background: #fff1f2; border-color: #fecdd3; color: #be123c; }

        /* ---------- Form ---------- */
        .label {
            display: block;
            margin-bottom: .375rem;
            font-size: .6875rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--mut);
        }
        .input {
            width: 100%;
            padding: .5rem .75rem;
            border: 1px solid #cbd5e1;
            border-radius: .5rem;
            background: #fff;
            font-family: inherit;
            font-size: .8125rem;
            color: var(--ink);
        }
        .input:focus {
            outline: 0;
            border-color: var(--ac);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .2);
        }
        select.input { font-weight: 600; }
        .hint { margin-top: .375rem; font-size: .6875rem; color: var(--mut); }

        /* ---------- Badge ---------- */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            padding: .125rem .5rem;
            border-radius: .375rem;
            background: #f1f5f9;
            color: #475569;
            font-size: .6875rem;
            font-weight: 600;
        }
        .badge-accent { background: var(--ac-soft); color: #1e40af; }
        .badge-ok { background: #d1fae5; color: #065f46; }
        .badge-info { background: #e0e7ff; color: #3730a3; }
        .badge-bad { background: #ffe4e6; color: #9f1239; }

        /* ---------- Navigasi ---------- */
        .nav {
            display: flex;
            gap: .25rem;
            overflow-x: auto;
            padding: .375rem 0;
            border-top: 1px solid var(--line);
        }
        .nav-link {
            display: inline-flex;
            align-items: center;
            gap: .375rem;
            padding: .375rem .625rem;
            border-radius: .375rem;
            font-size: .8125rem;
            font-weight: 500;
            color: #475569;
            white-space: nowrap;
            transition: background .15s, color .15s;
        }
        .nav-link:hover { background: #f1f5f9; color: var(--ink); }
        .nav-link .count { font-size: .6875rem; color: #94a3b8; }
        .nav-link.is-active { background: var(--ink); color: #fff; }
        .nav-link.is-active .count { color: #cbd5e1; }

        /* Varian navigasi untuk header gelap (navy) */
        .nav-dark { border-top-color: rgba(255, 255, 255, .14); }
        .nav-dark .nav-link { color: #cbd5e1; }
        .nav-dark .nav-link .count { color: #94a3b8; }

        /* Hover memakai kuning, warna yang sama dengan logo Kementerian
           Pekerjaan Umum.

           :focus ditulis berpasangan dengan :hover karena tombol navigasi
           berbasis <button>. Tanpa itu, tombol yang barusan diklik tetap
           menyala setelah kursor berpindah: browser menahan status fokus
           pada elemen yang diklik. CSS tidak bisa membedakan "baru diklik"
           dari "disorot kursor", jadi kedua selector itu dipaksa punya
           aturan sama persis. */
        .nav-dark .nav-link:hover,
        .nav-dark .nav-link:focus {
            background: #fbbf24;
            color: var(--navy);
        }
        .nav-dark .nav-link:hover .count,
        .nav-dark .nav-link:focus .count { color: #78350f; }

        /* Tab aktif tetap putih, tidak ikut kuning saat disorot. */
        .nav-dark .nav-link.is-active { background: #fff; color: var(--navy); }
        .nav-dark .nav-link.is-active .count { color: #64748b; }
        .nav-dark .nav-link.is-active:hover,
        .nav-dark .nav-link.is-active:focus {
            background: #fff;
            color: var(--navy);
        }
        .nav-dark .nav-link.is-active:hover .count,
        .nav-dark .nav-link.is-active:focus .count { color: #64748b; }

        /* ---------- Statistik ---------- */
        .stat { padding: 1rem; background: #fff; border: 1px solid var(--line); border-radius: .75rem; }
        .stat-label {
            display: flex;
            align-items: center;
            gap: .375rem;
            font-size: .6875rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--mut);
        }
        .stat-value { margin-top: .25rem; font-size: 1.5rem; font-weight: 700; line-height: 1.1; color: var(--ink); }
        .stat-note { margin-top: .25rem; font-size: .6875rem; color: var(--mut); }
        .card.click-card { cursor: pointer; transition: border-color .15s, box-shadow .15s; }
        .card.click-card:hover { border-color: var(--ac); box-shadow: 0 1px 3px rgba(15, 23, 42, .08); }

        /* ---------- Kartu sub-bidang ----------
           Dasar gelap (navy) dengan teks putih. Foto (bila ada) diletakkan
           di atas dasar ini lewat <img> di dalam kartu, lalu ditutup
           lapisan gelap di view supaya teks tetap kontras.
           Ditulis di sini supaya tiap kartu tidak perlu menyalin rangkaian
           utilitas Tailwind yang sama. */
        .sub-bidang-banner {
            border-color: #1e3a5f;
            background-color: #0f172a;
            background-image: linear-gradient(135deg, #0f172a, #1e293b);
        }
        /* Tanpa foto: gradasi dibuat sedikit lebih terang supaya jelas
           berbeda dari kartu yang memakai fotonya. */
        .sub-bidang-banner--polos {
            background-image: linear-gradient(140deg, #0f172a, #1e3a8a);
        }
        .sub-bidang-banner:hover { border-color: #3b82f6; }
        /* Label "SUB #n" di sudut kartu. */
        .sub-bidang-chip {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            padding: .25rem .625rem;
            border-radius: .375rem;
            border: 1px solid rgba(255, 255, 255, .18);
            background: rgba(255, 255, 255, .1);
            color: #bfdbfe;
            font-size: .625rem;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
        }
        .sub-bidang-banner .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        /* Tombol aksi di atas kartu gelap. */
        .icon-btn-gelap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.875rem;
            height: 1.875rem;
            border: 1px solid rgba(255, 255, 255, .18);
            border-radius: .375rem;
            background: rgba(255, 255, 255, .1);
            color: #e2e8f0;
            transition: background .15s, color .15s, border-color .15s;
        }
        .icon-btn-gelap:hover { background: rgba(255, 255, 255, .22); color: #fff; }
        .icon-btn-gelap-bahaya { border-color: rgba(253, 164, 175, .3); color: #fda4af; }
        .icon-btn-gelap-bahaya:hover { background: rgba(190, 18, 60, .35); border-color: #fb7185; color: #fff; }

        /* ---------- Catatan ---------- */
        .note {
            padding: .75rem 1rem;
            border: 1px solid var(--line);
            border-radius: .5rem;
            background: #f8fafc;
            font-size: .8125rem;
            color: #334155;
        }
        .note-warn { background: #fffbeb; border-color: #fde68a; color: #92400e; }

        /* ---------- Keadaan kosong ---------- */
        .empty {
            padding: 2.25rem 1rem;
            border: 1px dashed #cbd5e1;
            border-radius: .75rem;
            background: #f8fafc;
            text-align: center;
            font-size: .8125rem;
            color: var(--mut);
        }

        /* ---------- Tabel ---------- */
        .tbl { width: 100%; font-size: .8125rem; }
        .tbl th {
            padding: .5rem 1rem;
            background: #f8fafc;
            border-bottom: 1px solid var(--line);
            text-align: left;
            font-size: .6875rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--mut);
        }
        .tbl td { padding: .625rem 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .tbl tbody tr:hover { background: #f8fafc; }

        /* ---------- Modal ---------- */
        .modal {
            position: fixed;
            inset: 0;
            z-index: 50;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: rgba(15, 23, 42, .6);
        }
        /* .modal memakai display:flex, jadi .hidden dari Tailwind
           harus dikuatkan agar modal tetap bisa disembunyikan. */
        .modal.hidden { display: none; }
        .modal-card {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 40rem;
            max-height: 92vh;
            background: #fff;
            border-radius: .75rem;
            overflow: hidden;
            box-shadow: 0 24px 48px -12px rgba(15, 23, 42, .3);
        }
        .modal-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid var(--line);
        }
        .modal-title { font-size: 1rem; font-weight: 700; color: var(--ink); }
        .modal-body { flex: 1; padding: 1rem; overflow-y: auto; }
        .modal-foot {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: .5rem;
            padding: .75rem 1rem;
            background: #f8fafc;
            border-top: 1px solid var(--line);
        }

        /* ---------- Toast ---------- */
        #toast { transition: transform .2s, opacity .2s; }
    </style>
    @stack('styles')
</head>
<body class="h-full text-slate-800 font-sans flex flex-col antialiased bg-slate-50">

    <!-- TOP HEADER / NAVBAR -->
    <header class="sticky top-0 z-40 bg-blue-950 text-white border-b border-blue-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Baris atas: identitas dan aksi pengguna --}}
            <div class="flex items-center justify-between gap-4 py-2.5 min-h-[3.75rem]">
                <!-- Logo & Brand -->
                {{-- Logo dibuat setinggi tiga baris teks di sebelahnya (sekitar
                     3rem), bukan kotak kecil 40px yang terlihat melayang di
                     tengah. --}}
                <div class="flex items-center gap-3 min-w-0">
                    <a href="{{ route('home') }}" class="h-12 w-12 flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/Logo_PU.svg') }}" alt="Logo Kementerian Pekerjaan Umum" class="w-full h-full object-contain">
                    </a>
                    {{-- Nama instansi ditumpuk tiga baris, bukan satu baris
                         memanjang, supaya tidak memakan ruang header di
                         layar sempit. Baris pertama memakai kuning warna
                         logo Kementerian Pekerjaan Umum. --}}
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold tracking-widest text-yellow-400 uppercase leading-tight">
                                Kementerian Pekerjaan Umum
                            </span>
                            @auth
                                @if(Auth::user()->isAdmin())
                                    <span class="badge badge-accent">Admin</span>
                                @else
                                    <span class="badge badge-accent">Pegawai</span>
                                @endif
                            @else
                                <span class="badge">Publik</span>
                            @endauth
                        </div>
                        <h1 class="text-sm font-bold tracking-tight text-white leading-tight truncate">
                            BPSDM
                        </h1>
                        <p class="text-[10px] leading-tight text-blue-200/80 truncate">
                            Pusat Pengembangan Kompetensi Sumber Daya Air, Cipta Karya dan Prasarana Strategis
                        </p>
                    </div>
                </div>

                <!-- Global Search & Actions -->
                <div class="flex items-center gap-3">
                    <div class="relative hidden md:block w-56 lg:w-64">
                        <input type="text" id="searchInput" oninput="handleSearch(this.value)" placeholder="Cari bidang, SOP, dokumen..." 
                               class="input pl-8 !bg-blue-900 !border-blue-800 !text-white placeholder-blue-300/70">
                        <i data-lucide="search" class="w-4 h-4 text-blue-300 absolute left-2.5 top-2.5"></i>
                    </div>

                    @auth
                        <!-- User Profile Pill & Logout -->
                        <div class="flex items-center gap-2 pl-0.5 pr-0.5 py-0.5 border border-blue-800 rounded-lg">
                            <img src="{{ Auth::user()->foto_url }}" alt="{{ Auth::user()->name }}" class="w-7 h-7 rounded-md object-cover shrink-0">
                            <div class="text-left hidden sm:block">
                                <span class="text-xs font-medium text-white block leading-tight truncate max-w-[130px]">{{ Auth::user()->name }}</span>
                                <span class="text-[10px] text-blue-300/80 block leading-none">{{ Auth::user()->isAdmin() ? 'Super Admin' : 'Pegawai' }}</span>
                            </div>

                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" title="Keluar / Logout" class="icon-btn !bg-transparent !border-0 hover:!bg-blue-800 hover:!text-white">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Tombol Masuk / Login -->
                        <a href="{{ route('login') }}" class="btn btn-primary">
                            <i data-lucide="log-in" class="w-4 h-4"></i>
                            <span>Masuk</span>
                        </a>
                    @endauth
                </div>
            </div>

            <!-- SUB NAVIGATION BAR (sama untuk Beranda & dashboard CRMC) -->
            @include('layouts.partials.nav-utama')
        </div>
    </header>

    <!-- CONTENT YIELD -->
    @yield('content')

    <!-- MODALS YIELD -->
    @yield('modals')

    <!-- TOAST NOTIFICATION -->
    <div id="toast" class="fixed bottom-5 right-5 z-50 flex items-center gap-2.5 rounded-lg bg-slate-900 px-4 py-2.5 text-xs text-white shadow-lg translate-y-20 opacity-0">
        <i data-lucide="check-circle-2" class="w-4 h-4 text-blue-400 shrink-0"></i>
        <span id="toastMessage">Pesan Notifikasi</span>
    </div>

    <!-- GLOBAL SCRIPT -->
    <script>
        // Daftar sub-bidang untuk kotak pencarian. Datanya dikirim dari
        // server (view composer `layouts.app`) supaya ikut berubah saat
        // admin menambah sub-bidang baru, tanpa perlu menyunting layout.
        const daftarSubBidang = @json($navSubBidang);

        // Pola URL halaman detail 8 Komponen. "__SLUG__" diganti saat
        // dipakai supaya tidak perlu menyusun ulang base URL tiap kali.
        const URL_CRTC_SHOW = @json(route('crmc.show', ['slug' => '__SLUG__']));

        let currentRole = 'pegawai';

        function navigateToCRMC(slug) {
            window.location.href = URL_CRTC_SHOW.replace('__SLUG__', encodeURIComponent(slug));
        }

        /**
         * Tampilkan daftar pesan validasi dari server pada blok modal.
         *
         * Dipakai bersama oleh semua panel admin (sub-bidang, struktur
         * organisasi, galeri) supaya tampilannya konsisten.
         */
        function tampilkanError(idBlock, idList, pesan) {
            const block = document.getElementById(idBlock);
            const list = document.getElementById(idList);
            if (!block || !list) return;

            list.innerHTML = '';
            Object.entries(pesan || {}).forEach(function (pair) {
                const li = document.createElement('li');
                li.textContent = pair[1];
                list.appendChild(li);
            });
            block.classList.toggle('hidden', Object.keys(pesan || {}).length === 0);
        }

        /** Gambar ulang ikon lucide setelah DOM berubah. */
        function segarkanIkon() {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        window.onload = function() {
            const tabParam = new URLSearchParams(window.location.search).get('tab');
            if (tabParam) {
                switchTab(tabParam);
            }
            if (typeof lucide !== 'undefined') lucide.createIcons();
        };

        function switchTab(tabId) {
            const target = document.getElementById(`tab-${tabId}`);
            if (target) {
                document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
                target.classList.remove('hidden');

                {{-- Hanya kelas .is-active yang ditukar, bukan seluruh
                     className, supaya gaya tombol tetap utuh.

                     Tombol yang diklik juga dilepas fokusnya: kalau tidak,
                     tombol itu tetap menyala setelah kursor berpindah karena
                     state :focus bertahan. --}}
                document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('is-active'));
                const activeBtn = document.getElementById(`nav-${tabId}`);
                if (activeBtn) {
                    activeBtn.classList.add('is-active');
                    if (activeBtn.blur) activeBtn.blur();
                }
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                window.location.href = '/?tab=' + tabId;
            }
        }

        function toggleRole() {
            const roleBadge = document.getElementById('roleBadge');
            const roleToggleText = document.getElementById('roleToggleText');
            const adminAddBtn = document.getElementById('adminAddBtn');
            const modalAccessStatus = document.getElementById('modalAccessStatus');
            const adminDeleteBtn = document.getElementById('adminDeleteBtn');

            if (currentRole === 'pegawai') {
                currentRole = 'admin';
                roleBadge.textContent = 'Mode Admin (Full Control)';
                roleBadge.className = 'badge badge-accent';
                roleToggleText.textContent = 'Ganti Ke Pegawai';
                if (adminAddBtn) adminAddBtn.classList.remove('hidden');
                if (modalAccessStatus) { modalAccessStatus.textContent = 'Super Admin (Akses Penuh Edit/Hapus)'; modalAccessStatus.className = 'text-blue-700 font-bold'; }
                if (adminDeleteBtn) adminDeleteBtn.classList.remove('hidden');
                showToast('Dialihkan ke Mode Admin.');
            } else {
                currentRole = 'pegawai';
                roleBadge.textContent = 'Mode Pegawai';
                roleBadge.className = 'badge badge-info';
                roleToggleText.textContent = 'Ganti Ke Admin';
                if (adminAddBtn) adminAddBtn.classList.add('hidden');
                if (modalAccessStatus) { modalAccessStatus.textContent = 'Pegawai (Read/Submit)'; modalAccessStatus.className = 'text-slate-800'; }
                if (adminDeleteBtn) adminDeleteBtn.classList.add('hidden');
                showToast('Dialihkan ke Mode Pegawai.');
            }
        }

        function openAdminModal() { document.getElementById('adminSettingsModal').classList.remove('hidden'); }
        function closeAdminModal() { document.getElementById('adminSettingsModal').classList.add('hidden'); }

        function handleSearch(query) {
            const resultsContainer = document.getElementById('searchResultsContainer');
            const resultsGrid = document.getElementById('searchResultsGrid');
            if (!query.trim()) { if (resultsContainer) resultsContainer.classList.add('hidden'); return; }
            const q = query.toLowerCase();
            const filtered = daftarSubBidang.filter(item =>
                item.judul.toLowerCase().includes(q) || item.parent.toLowerCase().includes(q)
            );
            if (filtered.length > 0) {
                resultsGrid.innerHTML = filtered.map(item => `
                    <div onclick="navigateToCRMC('${item.slug}')" class="card click-card px-3 py-2.5 flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <span class="block text-[10px] font-bold uppercase tracking-wide text-slate-400">${item.parent}</span>
                            <h5 class="text-xs font-semibold text-slate-800 truncate">${item.judul}</h5>
                        </div>
                        <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400 shrink-0"></i>
                    </div>
                `).join('');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            } else {
                resultsGrid.innerHTML = `<p class="text-xs text-slate-400 col-span-3">Tidak ditemukan sub-bidang sesuai kata kunci "${query}"</p>`;
            }
            if (resultsContainer) resultsContainer.classList.remove('hidden');
        }

        function clearSearch() {
            const input = document.getElementById('searchInput');
            if (input) input.value = '';
            const resultsContainer = document.getElementById('searchResultsContainer');
            if (resultsContainer) resultsContainer.classList.add('hidden');
        }

        function showToast(msg) {
            const toast = document.getElementById('toast');
            document.getElementById('toastMessage').textContent = msg;
            toast.classList.remove('translate-y-20', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 3500);
        }
    </script>
    @stack('scripts')
</body>
</html>
