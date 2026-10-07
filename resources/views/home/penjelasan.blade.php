{{--
    Bagian "Tentang CRMC": penjelasan singkat apa itu CRMC dan tiga
    tujuan utamanya. Teks di sini bersifat institusional sehingga
    ditulis langsung di view, bukan disimpan ke database.
--}}
<section id="tentang-crmc" class="scroll-mt-20 py-10 bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center max-w-3xl mx-auto mb-8">
            <span class="badge badge-accent mb-3">
                <i data-lucide="info" class="w-3.5 h-3.5"></i>
                Tentang CRMC
            </span>
            <h2 class="page-title">
                Continuous Monitoring on
                <span class="text-blue-700">Risk Control</span>
            </h2>
            <p class="page-sub mt-3 leading-relaxed">
                CRMC adalah aplikasi pemantauan berkelanjutan atas pengendalian
                risiko. Aplikasi ini menyatukan rencana pengendalian risiko,
                pelaksanaan aktivitas pengendalian, sampai evaluasinya dalam satu
                alur kerja yang dapat ditelusuri.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

            {{-- 1. Pembagian tugas komitmen --}}
            <article class="card click-card p-5 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <span class="inline-flex w-8 h-8 items-center justify-center rounded-md bg-blue-900 text-white text-xs font-bold">1</span>
                    <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
                </div>
                <h3 class="section-title text-base">Pembagian Tugas Komitmen</h3>
                <p class="page-sub leading-relaxed">
                    Pembagian tugas komitmen pengendalian risiko (Risk Controlling)
                    kepada setiap pegawai guna mendukung penerapan Enterprise Risk
                    Management (ERM).
                </p>
            </article>

            {{-- 2. Reminder / Pengingat komitmen --}}
            <article class="card click-card p-5 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <span class="inline-flex w-8 h-8 items-center justify-center rounded-md bg-blue-700 text-white text-xs font-bold">2</span>
                    <i data-lucide="bell-ring" class="w-4 h-4 text-slate-400"></i>
                </div>
                <h3 class="section-title text-base">Reminder Komitmen</h3>
                <p class="page-sub leading-relaxed">
                    Pengingat komitmen dalam pelaksanaan pengendalian risiko melalui
                    aktivitas pengisian formulir instrumen pengendalian.
                </p>
            </article>

            {{-- 3. Evaluasi efektifitas --}}
            <article class="card click-card p-5 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <span class="inline-flex w-8 h-8 items-center justify-center rounded-md bg-blue-500 text-white text-xs font-bold">3</span>
                    <i data-lucide="clipboard-check" class="w-4 h-4 text-slate-400"></i>
                </div>
                <h3 class="section-title text-base">Evaluasi Efektifitas</h3>
                <p class="page-sub leading-relaxed">
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
        <div class="card mt-4 p-5 md:p-6">
            <div class="flex flex-col md:flex-row md:items-center gap-5">
                <div class="md:w-64 shrink-0">
                    <h3 class="eyebrow mb-2">Kerangka CRMC</h3>
                    <p class="page-sub leading-relaxed">
                        Ketiga tujuan di atas dijalankan melalui delapan komponen yang
                        saling melengkapi. Setiap komponen punya bukti dukungannya
                        masing-masing dan dapat dipantau terpisah.
                    </p>
                </div>
                <div class="flex-1 grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach ($komponen as $no => $judul)
                        <div class="card p-2.5 text-center flex flex-col items-center gap-1.5">
                            <span class="inline-flex w-6 h-6 items-center justify-center rounded-md bg-blue-900 text-white text-[10px] font-bold">{{ $no + 1 }}</span>
                            <span class="text-[11px] font-medium text-slate-700 leading-tight">{{ $judul }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="mt-5 pt-4 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2">
                <p class="page-sub">Siap mengelola seluruh instrumen CRMC</p>
                <a href="{{ route('crmc.dashboard') }}" class="btn btn-primary">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    Buka Dashboard CRMC
                </a>
            </div>
        </div>
    </div>
</section>
