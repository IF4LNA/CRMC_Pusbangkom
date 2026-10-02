<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CRMC') | Kementerian Pekerjaan Umum</title>
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
        .nav-dark .nav-link:hover { background: rgba(255, 255, 255, .1); color: #fff; }
        .nav-dark .nav-link .count { color: #94a3b8; }
        .nav-dark .nav-link.is-active { background: #fff; color: var(--navy); }
        .nav-dark .nav-link.is-active .count { color: #64748b; }

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
            <div class="flex items-center justify-between gap-4 h-14">
                <!-- Logo & Brand -->
                <div class="flex items-center gap-2.5 min-w-0">
                    <a href="{{ route('home') }}" class="w-8 h-8 flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/Logo_PU.svg') }}" alt="Logo PUPR" class="w-full h-full object-contain">
                    </a>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold tracking-widest text-blue-300 uppercase">Kementerian Pekerjaan Umum</span>
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
                            BPSDM <span class="text-[11px] font-normal text-blue-200/80">Pusat Pengembangan Kompetensi Sumber Daya Air, Cipta Karya dan Prasarana Strategis</span>
                        </h1>
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

            <!-- SUB NAVIGATION BAR -->
            @if(request()->routeIs('beranda'))
                {{-- Di halaman Beranda, tombol tab tidak relevan. Diganti
                     lompat ke bagian (anchor) yang ada di halaman tersebut. --}}
                <nav class="nav nav-dark no-scrollbar">
                    <a href="{{ route('home') }}" class="nav-link">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        Dashboard CRMC
                    </a>
                    <a href="#tentang-crmc" class="nav-link">
                        <i data-lucide="book-open" class="w-4 h-4"></i>
                        <span>Tentang CRMC</span>
                    </a>
                    <a href="#struktur-organisasi" class="nav-link">
                        <i data-lucide="network" class="w-4 h-4"></i>
                        <span>Struktur Organisasi</span>
                    </a>
                    <a href="#galeri" class="nav-link">
                        <i data-lucide="images" class="w-4 h-4"></i>
                        <span>Sarana dan Prasarana</span>
                    </a>
                    <a href="#peta" class="nav-link">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                        <span>Lokasi</span>
                    </a>
                </nav>
            @else
            <nav class="nav nav-dark no-scrollbar">
                <a href="{{ route('beranda') }}" class="nav-link">
                    <i data-lucide="home" class="w-4 h-4"></i>
                    <span>Beranda</span>
                </a>
                <button onclick="switchTab('dashboard')" id="nav-dashboard" class="nav-link nav-btn">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Dashboard</span>
                </button>
                <button onclick="switchTab('dasar-hukum')" id="nav-dasar-hukum" class="nav-link nav-btn">
                    <i data-lucide="scale" class="w-4 h-4"></i>
                    <span>Dasar Hukum</span>
                </button>
                <button onclick="switchTab('umum-tu')" id="nav-umum-tu" class="nav-link nav-btn">
                    <i data-lucide="briefcase" class="w-4 h-4"></i>
                    <span>Bagian Umum &amp; TU</span>
                    <span class="count">29</span>
                </button>
                <button onclick="switchTab('sda')" id="nav-sda" class="nav-link nav-btn">
                    <i data-lucide="waves" class="w-4 h-4"></i>
                    <span>Bidang SDA</span>
                    <span class="count">5</span>
                </button>
                <button onclick="switchTab('ckps')" id="nav-ckps" class="nav-link nav-btn">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    <span>Bidang CKPS</span>
                    <span class="count">7</span>
                </button>
            </nav>
            @endif
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
        const umumTuItems = [
            "Manajemen Risiko", "Pengadaan Barang Jasa Konstruksi dan Non Konstruksi", "Implementasi SAKIP", "Pengelolaan Arsip",
            "Laporan Keuangan", "Pengadaan Jasa Konsultan", "Laporan Pengaduan Gratifikasi atau Suap", "Pengendalian Gratifikasi atau Suap",
            "Investigasi Laporan Pengaduan Gratifikasi atau Suap", "Perjalanan Dinas Dalam Negeri", "Pengajuan Uang Persediaan, GUP dan GUP Nihil",
            "Penyusunan Rencana Kerja Anggaran", "Pengelolaan Publikasi dan Informasi", "Pelaksanaan Pengajuan Cuti", "Pengelolaan BMN",
            "Penghapusan BMN", "Pengelolaan Pembayaran Uang Makan ASN", "Perhitungan Kehadiran Pegawai", "Pengajuan Daftar Supplier atau Kontrak",
            "Pengajuan LS Kontraktual", "Pengajuan LS Non Kontraktual", "Pengajuan LS Bendahara", "Survei Kepuasan Pelanggan Internal",
            "Pengelolaan Surat Masuk dan Surat Keluar", "Peminjaman KDO Roda 4 dan Roda 2", "Pengendalian Barang Persediaan",
            "Peminjaman Ruang Rapat, Gedung Dan Asrama", "Pengadaan Barang Jasa Secara Online", "Pemanfaatan BMN melalui Mekanisme Sewa dan Penatausahaan PNBP"
        ];
        const sdaItems = [
            "Penyelenggaraan Bangkom SDA", "Kerjasama Pendidikan (MSS)", "Evaluasi Pasca Pelatihan SDA",
            "Penyiapan Materi E-Learning Bangkom SDA", "Penyusunan dan Pengembangan Kurikulum dan Modul Pembelajaran bid. SDA"
        ];
        const ckpsItems = [
            "Kerjasama Pendidikan (MSS)", "Penyusunan Kurikulum dan Modul", "Monitoring dan Evaluasi Pengembangan Kompetensi CKPS",
            "Evaluasi Pasca Pelatihan CKPS", "Pembinaan Kerjasama Pelatihan", "Penyiapan Materi E-Learning", "Penyusunan Skema Sertifikasi"
        ];

        let currentRole = 'pegawai';
        let currentActiveItem = '';
        let currentActiveParent = '';

        function generateSlug(text) {
            return text.toString().toLowerCase()
                .replace(/&/g, 'dan')
                .replace(/[^\w\s-]/g, '')
                .trim()
                .replace(/\s+/g, '-');
        }

        function navigateToCRMC(title) {
            window.location.href = '/crmc/' + generateSlug(title);
        }

        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            if (tabParam) {
                switchTab(tabParam);
            }
            if (typeof renderBentoGrids === 'function') renderBentoGrids();
            if (typeof lucide !== 'undefined') lucide.createIcons();
        };

        function switchTab(tabId) {
            const target = document.getElementById(`tab-${tabId}`);
            if (target) {
                document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
                target.classList.remove('hidden');

                {{-- Hanya kelas .is-active yang ditukar, bukan seluruh
                     className, supaya gaya tombol tetap utuh. --}}
                document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('is-active'));
                const activeBtn = document.getElementById(`nav-${tabId}`);
                if (activeBtn) activeBtn.classList.add('is-active');
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

        function openCRMCModal(subTitle, parentTitle) {
            currentActiveItem = subTitle;
            currentActiveParent = parentTitle;
            const subTitleEl = document.getElementById('modalSubTitle');
            const parentCatEl = document.getElementById('modalParentCategory');
            const fullPageBtn = document.getElementById('modalFullPageBtn');

            if (subTitleEl) subTitleEl.textContent = subTitle;
            if (parentCatEl) parentCatEl.textContent = parentTitle;
            if (fullPageBtn) fullPageBtn.href = '/crmc/' + generateSlug(subTitle);

            const modal = document.getElementById('crmcModal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeCRMCModal() { 
            const modal = document.getElementById('crmcModal');
            if (modal) modal.classList.add('hidden'); 
        }

        function handleFormSubmit(event) { 
            event.preventDefault(); 
            showToast('Berhasil menyimpan pembaruan 8 Komponen CRMC!'); 
            closeCRMCModal(); 
        }
        function deleteCRMCItem() { if (currentRole === 'admin') { showToast(`Item "${currentActiveItem}" berhasil dihapus.`); closeCRMCModal(); } }
        function openAdminModal() { document.getElementById('adminSettingsModal').classList.remove('hidden'); }
        function closeAdminModal() { document.getElementById('adminSettingsModal').classList.add('hidden'); }

        function handleSearch(query) {
            const resultsContainer = document.getElementById('searchResultsContainer');
            const resultsGrid = document.getElementById('searchResultsGrid');
            if (!query.trim()) { if (resultsContainer) resultsContainer.classList.add('hidden'); return; }
            const q = query.toLowerCase();
            const allItems = [
                ...umumTuItems.map(i => ({ title: i, parent: 'Bagian Umum & Tata Usaha' })),
                ...sdaItems.map(i => ({ title: i, parent: 'Bidang SDA' })),
                ...ckpsItems.map(i => ({ title: i, parent: 'Bidang CKPS' }))
            ];
            const filtered = allItems.filter(item => item.title.toLowerCase().includes(q));
            if (filtered.length > 0) {
                resultsGrid.innerHTML = filtered.map(item => `
                    <div onclick="navigateToCRMC('${item.title}')" class="card click-card px-3 py-2.5 flex items-center justify-between gap-2">
                        <div class="min-w-0">
                            <span class="block text-[10px] font-bold uppercase tracking-wide text-slate-400">${item.parent}</span>
                            <h5 class="text-xs font-semibold text-slate-800 truncate">${item.title}</h5>
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