<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Main Welcome Bento -->
        <div class="lg:col-span-2 bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white relative overflow-hidden shadow-lg border border-slate-800 flex flex-col justify-between">
            <div>
                <div class="inline-flex items-center space-x-2 px-3 py-1 bg-amber-500/20 text-amber-400 rounded-full text-xs font-semibold mb-3 border border-amber-500/30">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    <span>Sistem Pengendalian Risiko Terintegrasi</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white mb-2">
                    Pusat Monitoring & Pengendalian Risiko CRMC
                </h2>
                <p class="text-slate-300 text-xs sm:text-sm max-w-xl leading-relaxed">
                    Akses terpadu pengawasan risiko, standar operasional prosedur, serta bukti pelaksanaan kegiatan di lingkungan Kementerian Pekerjaan Umum.
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
                <div class="flex items-center space-x-2">
                    <i data-lucide="quote" class="w-4 h-4 text-amber-400"></i>
                    <span class="italic font-medium text-amber-200">"Infrastruktur untuk Indonesia Maju"</span>
                </div>
                <button onclick="switchTab('umum-tu')" class="inline-flex items-center space-x-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-semibold px-4 py-2 rounded-xl transition text-xs">
                    <span>Mulai Kelola CRMC</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </div>

        <!-- Right Bento Quick Summary Stats -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Ringkasan Berkas</h3>
                <span class="text-xs bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full font-medium">Update 2026</span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100">
                    <p class="text-[11px] text-slate-500 font-medium">Total Dokumen</p>
                    <p class="text-2xl font-extrabold text-slate-900 mt-1">328</p>
                    <span class="text-[10px] text-emerald-600 font-medium flex items-center gap-0.5 mt-1">
                        <i data-lucide="trending-up" class="w-3 h-3"></i> Aktif
                    </span>
                </div>
                <div class="bg-emerald-50 p-3.5 rounded-2xl border border-emerald-100">
                    <p class="text-[11px] text-emerald-700 font-medium">Residu Rendah</p>
                    <p class="text-2xl font-extrabold text-emerald-800 mt-1">264</p>
                    <span class="text-[10px] text-emerald-700 font-medium mt-1 inline-block">80.5% Terkendali</span>
                </div>
                <div class="bg-amber-50 p-3.5 rounded-2xl border border-amber-100">
                    <p class="text-[11px] text-amber-700 font-medium">Residu Sedang</p>
                    <p class="text-2xl font-extrabold text-amber-800 mt-1">52</p>
                    <span class="text-[10px] text-amber-700 font-medium mt-1 inline-block">Perlu Monitoring</span>
                </div>
                <div class="bg-rose-50 p-3.5 rounded-2xl border border-rose-100">
                    <p class="text-[11px] text-rose-700 font-medium">Residu Tinggi</p>
                    <p class="text-2xl font-extrabold text-rose-800 mt-1">12</p>
                    <span class="text-[10px] text-rose-700 font-medium mt-1 inline-block">Evaluasi Khusus</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Division Shortcuts Bento Grid -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-base font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i data-lucide="grid" class="w-4 h-4 text-amber-500"></i>
                Eksplorasi Bidang & Sub-Kategori CRMC
            </h3>
            <span class="text-xs text-slate-500">Pilih salah satu divisi untuk melihat 8 komponen CRMC</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div onclick="switchTab('umum-tu')" class="bento-card cursor-pointer bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-md hover:border-amber-400 group relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-50 text-blue-900 rounded-2xl flex items-center justify-center group-hover:bg-amber-500 group-hover:text-slate-950 transition">
                            <i data-lucide="briefcase" class="w-6 h-6"></i>
                        </div>
                        <span class="bg-slate-100 group-hover:bg-amber-100 text-slate-700 group-hover:text-amber-900 text-xs font-bold px-3 py-1 rounded-full">29 Sub-Bidang</span>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-1 group-hover:text-blue-900 transition">Bagian Umum & Tata Usaha</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">Manajemen Risiko, SAKIP, Keuangan, Pengadaan BJ, Arsip, BMN, Perjalanan Dinas, & Layanan Umum.</p>
                </div>
                <div class="mt-6 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-blue-900 group-hover:text-amber-600">
                    <span>Buka Daftar Lengkap</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 transform group-hover:translate-x-1 transition"></i>
                </div>
            </div>

            <div onclick="switchTab('sda')" class="bento-card cursor-pointer bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-md hover:border-amber-400 group relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-cyan-50 text-cyan-800 rounded-2xl flex items-center justify-center group-hover:bg-amber-500 group-hover:text-slate-950 transition">
                            <i data-lucide="waves" class="w-6 h-6"></i>
                        </div>
                        <span class="bg-slate-100 group-hover:bg-amber-100 text-slate-700 group-hover:text-amber-900 text-xs font-bold px-3 py-1 rounded-full">5 Sub-Bidang</span>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-1 group-hover:text-blue-900 transition">Bidang Sumber Daya Air (SDA)</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">Bangkom SDA, Kerjasama Pendidikan (MSS), Evaluasi Pasca Pelatihan, E-Learning, Kurikulum & Modul.</p>
                </div>
                <div class="mt-6 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-blue-900 group-hover:text-amber-600">
                    <span>Buka Daftar Lengkap</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 transform group-hover:translate-x-1 transition"></i>
                </div>
            </div>

            <div onclick="switchTab('ckps')" class="bento-card cursor-pointer bg-white rounded-3xl p-6 border border-slate-200 shadow-sm hover:shadow-md hover:border-amber-400 group relative flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-indigo-50 text-indigo-800 rounded-2xl flex items-center justify-center group-hover:bg-amber-500 group-hover:text-slate-950 transition">
                            <i data-lucide="building-2" class="w-6 h-6"></i>
                        </div>
                        <span class="bg-slate-100 group-hover:bg-amber-100 text-slate-700 group-hover:text-amber-900 text-xs font-bold px-3 py-1 rounded-full">7 Sub-Bidang</span>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 mb-1 group-hover:text-blue-900 transition">Bidang Cipta Karya & Permukiman (CKPS)</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">Kurikulum CKPS, Monitoring & Evaluasi, Kerjasama Pelatihan, Penyiapan E-Learning, Skema Sertifikasi.</p>
                </div>
                <div class="mt-6 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-semibold text-blue-900 group-hover:text-amber-600">
                    <span>Buka Daftar Lengkap</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 transform group-hover:translate-x-1 transition"></i>
                </div>
            </div>
        </div>
    </div>
</div>