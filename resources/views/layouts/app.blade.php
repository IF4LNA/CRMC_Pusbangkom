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
    </style>
</head>
<body class="h-full text-slate-800 font-sans flex flex-col antialiased bg-slate-50">

    <!-- TOP HEADER / NAVBAR -->
    <header class="sticky top-0 z-40 bg-slate-900 text-white shadow-md border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('home') }}" class="w-11 h-11 flex items-center justify-center shrink-0">
                        <img src="{{ asset('images/Logo_PU.svg') }}" alt="Logo PUPR" class="w-full h-full object-contain">
                    </a>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-bold tracking-widest text-amber-400 uppercase">Kementerian Pekerjaan Umum</span>
                            @auth
                                @if(Auth::user()->isAdmin())
                                    <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-500 text-slate-950 rounded-full shadow-sm">Mode Admin</span>
                                @else
                                    <span class="px-2 py-0.5 text-[10px] font-semibold bg-blue-900 text-blue-200 rounded-full border border-blue-700">Mode Pegawai</span>
                                @endif
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-medium bg-slate-800 text-slate-300 rounded-full border border-slate-700">Mode Publik</span>
                            @endauth
                        </div>
                        <h1 class="text-lg font-extrabold tracking-tight text-white flex items-center gap-1.5">
                            CRMC <span class="text-xs font-normal text-slate-400 hidden sm:inline">(Continuous Monitoring on Risk Control)</span>
                        </h1>
                    </div>
                </div>

                <!-- Global Search & Actions -->
                <div class="flex items-center space-x-3">
                    <div class="relative hidden md:block w-56 lg:w-72">
                        <input type="text" id="searchInput" oninput="handleSearch(this.value)" placeholder="Cari bidang, SOP, dokumen..." 
                               class="w-full bg-slate-800/80 text-sm text-slate-100 placeholder-slate-400 pl-9 pr-4 py-2 rounded-xl border border-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500 transition">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    </div>

                    @auth
                        @if(Auth::user()->isAdmin())
                            <button onclick="openAdminModal()" class="flex items-center space-x-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-3 py-2 rounded-xl text-xs transition shadow-sm">
                                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                <span class="hidden sm:inline">Tambah Item</span>
                            </button>
                        @endif

                        <!-- User Profile Pill & Logout -->
                        <div class="flex items-center space-x-2 bg-slate-800/90 border border-slate-700 pl-1.5 pr-2.5 py-1 rounded-2xl">
                            <img src="{{ Auth::user()->foto_url }}" alt="{{ Auth::user()->name }}" class="w-8 h-8 rounded-xl object-cover border border-amber-400/60 shrink-0">
                            <div class="text-left hidden sm:block">
                                <span class="text-xs font-bold text-white block leading-tight truncate max-w-[120px]">{{ Auth::user()->name }}</span>
                                <span class="text-[10px] text-slate-400 block leading-none">{{ Auth::user()->isAdmin() ? 'Super Admin' : 'Pegawai' }}</span>
                            </div>

                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" title="Keluar / Logout" class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-slate-700 rounded-xl transition">
                                    <i data-lucide="log-out" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Tombol Masuk / Login -->
                        <a href="{{ route('login') }}" class="flex items-center space-x-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold px-3.5 py-2 rounded-xl text-xs transition shadow-sm">
                            <i data-lucide="log-in" class="w-4 h-4"></i>
                            <span>Masuk / Login</span>
                        </a>
                    @endauth
                </div>
            </div>

            <!-- SUB NAVIGATION BAR -->
            <nav class="flex space-x-1 sm:space-x-2 overflow-x-auto py-2 border-t border-slate-800/80 no-scrollbar">
                <button onclick="switchTab('dashboard')" id="nav-dashboard" class="nav-btn flex items-center space-x-2 px-3.5 py-2 rounded-lg text-xs font-semibold text-amber-400 bg-slate-800 transition shrink-0">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Dashboard</span>
                </button>
                <button onclick="switchTab('dasar-hukum')" id="nav-dasar-hukum" class="nav-btn flex items-center space-x-2 px-3.5 py-2 rounded-lg text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition shrink-0">
                    <i data-lucide="scale" class="w-4 h-4"></i>
                    <span>Dasar Hukum</span>
                </button>
                <button onclick="switchTab('umum-tu')" id="nav-umum-tu" class="nav-btn flex items-center space-x-2 px-3.5 py-2 rounded-lg text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition shrink-0">
                    <i data-lucide="briefcase" class="w-4 h-4"></i>
                    <span>Bagian Umum & TU</span>
                    <span class="bg-slate-700 text-slate-300 text-[10px] px-1.5 py-0.2 rounded-full">29</span>
                </button>
                <button onclick="switchTab('sda')" id="nav-sda" class="nav-btn flex items-center space-x-2 px-3.5 py-2 rounded-lg text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition shrink-0">
                    <i data-lucide="waves" class="w-4 h-4"></i>
                    <span>Bidang SDA</span>
                    <span class="bg-slate-700 text-slate-300 text-[10px] px-1.5 py-0.2 rounded-full">5</span>
                </button>
                <button onclick="switchTab('ckps')" id="nav-ckps" class="nav-btn flex items-center space-x-2 px-3.5 py-2 rounded-lg text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition shrink-0">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    <span>Bidang CKPS</span>
                    <span class="bg-slate-700 text-slate-300 text-[10px] px-1.5 py-0.2 rounded-full">7</span>
                </button>
            </nav>
        </div>
    </header>

    <!-- CONTENT YIELD -->
    @yield('content')

    <!-- MODALS YIELD -->
    @yield('modals')

    <!-- TOAST NOTIFICATION -->
    <div id="toast" class="fixed bottom-5 right-5 bg-slate-900 text-white px-4 py-3 rounded-2xl shadow-xl flex items-center space-x-3 text-xs border border-slate-800 transition transform translate-y-20 opacity-0 z-50">
        <i data-lucide="check-circle-2" class="w-5 h-5 text-amber-400"></i>
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

                document.querySelectorAll('.nav-btn').forEach(btn => {
                    btn.className = "nav-btn flex items-center space-x-2 px-3.5 py-2 rounded-lg text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition shrink-0";
                });
                const activeBtn = document.getElementById(`nav-${tabId}`);
                if (activeBtn) activeBtn.className = "nav-btn flex items-center space-x-2 px-3.5 py-2 rounded-lg text-xs font-semibold text-amber-400 bg-slate-800 transition shrink-0";
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
                roleBadge.className = 'px-2 py-0.5 text-[10px] font-bold bg-amber-500 text-slate-950 rounded-full shadow-sm';
                roleToggleText.textContent = 'Ganti Ke Pegawai';
                if (adminAddBtn) adminAddBtn.classList.remove('hidden');
                if (modalAccessStatus) { modalAccessStatus.textContent = 'Super Admin (Akses Penuh Edit/Hapus)'; modalAccessStatus.className = 'text-amber-600 font-bold'; }
                if (adminDeleteBtn) adminDeleteBtn.classList.remove('hidden');
                showToast('Dialihkan ke Mode Admin.');
            } else {
                currentRole = 'pegawai';
                roleBadge.textContent = 'Mode Pegawai';
                roleBadge.className = 'px-2 py-0.5 text-[10px] font-semibold bg-blue-900 text-blue-200 rounded-full border border-blue-700';
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
                    <div onclick="navigateToCRMC('${item.title}')" class="p-3 bg-slate-50 hover:bg-amber-50 rounded-xl border border-slate-200 cursor-pointer transition flex items-center justify-between group">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 block">${item.parent}</span>
                            <h5 class="text-xs font-bold text-slate-800 group-hover:text-blue-900 transition">${item.title}</h5>
                        </div>
                        <i data-lucide="arrow-right" class="w-4 h-4 text-slate-400 group-hover:text-amber-500 group-hover:translate-x-1 transition shrink-0 ml-2"></i>
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