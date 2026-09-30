{{--
    Bagian "Tentang CRMC": penjelasan singkat apa itu CRMC dan tiga
    tujuan utamanya. Teks di sini bersifat institusional sehingga
    ditulis langsung di view, bukan disimpan ke database.
--}}
<section id="tentang-crmc" class="scroll-mt-32 py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-100 text-amber-800 text-[11px] font-bold tracking-wide uppercase mb-4">
                <i data-lucide="info" class="w-3.5 h-3.5"></i>
                Tentang CRMC
            </span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight">
                Continuous Monitoring on
                <span class="text-blue-800">Risk Control</span>
            </h2>
            <p class="text-sm text-slate-600 leading-relaxed mt-4">
                CRMC adalah aplikasi pemantauan berkelanjutan atas pengendalian
                risiko. Aplikasi ini menyatukan rencana pengendalian risiko,
                pelaksanaan aktivitas pengendalian, sampai evaluasinya dalam satu
                alur kerja yang dapat ditelusuri.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- 1. Pembagian tugas komitmen --}}
            <article class="group relative bg-slate-50 hover:bg-blue-50/50 border border-slate-200 hover:border-blue-200 rounded-2xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="absolute -top-3 -right-3 w-12 h-12 bg-blue-800 text-white rounded-xl flex items-center justify-center font-extrabold text-lg shadow-lg group-hover:bg-blue-900 transition">
                    1
                </div>
                <div class="w-12 h-12 bg-blue-100 text-blue-800 rounded-xl flex items-center justify-center mb-4">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Pembagian Tugas Komitmen</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Pembagian tugas komitmen pengendalian risiko (Risk Controlling)
                    kepada setiap pegawai guna mendukung penerapan Enterprise Risk
                    Management (ERM).
                </p>
            </article>

            {{-- 2. Reminder / Pengingat komitmen --}}
            <article class="group relative bg-slate-50 hover:bg-amber-50/50 border border-slate-200 hover:border-amber-200 rounded-2xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="absolute -top-3 -right-3 w-12 h-12 bg-amber-500 text-slate-950 rounded-xl flex items-center justify-center font-extrabold text-lg shadow-lg group-hover:bg-amber-600 transition">
                    2
                </div>
                <div class="w-12 h-12 bg-amber-100 text-amber-700 rounded-xl flex items-center justify-center mb-4">
                    <i data-lucide="bell-ring" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Reminder Komitmen</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Pengingat komitmen dalam pelaksanaan pengendalian risiko melalui
                    aktivitas pengisian formulir instrumen pengendalian.
                </p>
            </article>

            {{-- 3. Evaluasi efektifitas --}}
            <article class="group relative bg-slate-50 hover:bg-emerald-50/50 border border-slate-200 hover:border-emerald-200 rounded-2xl p-6 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                <div class="absolute -top-3 -right-3 w-12 h-12 bg-emerald-600 text-white rounded-xl flex items-center justify-center font-extrabold text-lg shadow-lg group-hover:bg-emerald-700 transition">
                    3
                </div>
                <div class="w-12 h-12 bg-emerald-100 text-emerald-700 rounded-xl flex items-center justify-center mb-4">
                    <i data-lucide="clipboard-check" class="w-6 h-6"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-2">Evaluasi Efektifitas</h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Evaluasi efektifitas aktivitas pengendalian risiko guna
                    perbaikan Sistem Manajemen Risiko.
                </p>
            </article>
        </div>

        {{-- Kerangka 8 Komponen --}}
        @php
            $komponen = [
                'Identitas Pegawai',
                'Risk Register Spesifik Acuan',
                'Standar Operasional Prosedur (SOP)',
                'Formulir Pengendalian',
                'Jadwal Rencana Pelaksanaan',
                'Tautan Bukti Pelaksanaan',
                'Status Residu Risiko',
                'Evaluasi dan Rencana Perbaikan',
            ];
        @endphp
        <div class="mt-10 bg-slate-900 text-white rounded-2xl p-6 md:p-8 border border-slate-800">
            <div class="flex flex-col md:flex-row md:items-center gap-6">
                <div class="md:w-64 shrink-0">
                    <h3 class="text-sm font-extrabold uppercase tracking-wider text-amber-400 mb-2">
                        Kerangka CRMC
                    </h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Ketiga tujuan di atas dijalankan melalui delapan komponen yang
                        saling melengkapi. Setiap komponen punya bukti dukungannya
                        masing-masing dan dapat dipantau terpisah.
                    </p>
                </div>
                <div class="flex-1 grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    @foreach ($komponen as $no => $judul)
                        <div class="flex flex-col items-center gap-2 p-3 bg-slate-800/70 border border-slate-700 rounded-xl text-center">
                            <span class="w-7 h-7 rounded-lg bg-amber-500 text-slate-950 font-extrabold text-xs flex items-center justify-center shrink-0">
                                {{ $no + 1 }}
                            </span>
                            <span class="text-[11px] font-semibold text-slate-200 leading-tight">
                                {{ $judul }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-6 pt-5 border-t border-slate-800">
                <a href="{{ route('home') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl transition shadow-sm">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    Buka Dashboard CRMC
                </a>
            </div>
        </div>
    </div>
</section>
