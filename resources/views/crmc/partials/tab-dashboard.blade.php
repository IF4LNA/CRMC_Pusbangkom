<div class="space-y-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">
        {{-- Hero CRMC --}}
        <div class="lg:col-span-2 bg-blue-950 text-white rounded-xl p-5 sm:p-6 border border-blue-900 flex flex-col justify-between">
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-900 text-blue-200 rounded-md text-[11px] font-semibold mb-3 border border-blue-800">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    <span>Sistem Pengendalian Risiko Terintegrasi</span>
                </span>
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-white mb-1.5">
                    Continuous Monitoring on Risk Control (CRMC)
                </h2>
                <p class="text-blue-200 text-xs sm:text-sm max-w-xl leading-relaxed">
                    Pusat Pengembangan Kompetensi Sumber Daya Air, Cipta Karya dan Prasarana Strategis
                </p>
            </div>
            <div class="mt-5 pt-4 border-t border-blue-900 flex flex-wrap items-center justify-between gap-3 text-xs text-blue-200">
                <div class="flex items-center gap-2">
                    <i data-lucide="quote" class="w-4 h-4 text-blue-400"></i>
                    <span class="italic font-medium">"Infrastruktur untuk Indonesia Maju"</span>
                </div>
                <button onclick="switchTab('umum-tu')" class="btn btn-sm btn-primary">
                    <span>Mulai Kelola CRMC</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        </div>

        {{-- Ringkasan berkas --}}
        <div class="card p-4 flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="card-title">Ringkasan Berkas</h3>
                <span class="badge">Update 2026</span>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div class="stat !p-2.5">
                    <p class="stat-label">Total Dokumen</p>
                    <p class="stat-value !text-xl">328</p>
                    <span class="stat-note flex items-center gap-0.5 text-emerald-600">
                        <i data-lucide="trending-up" class="w-3 h-3"></i> Aktif
                    </span>
                </div>
                <div class="stat !p-2.5">
                    <p class="stat-label">Residu Rendah</p>
                    <p class="stat-value !text-xl text-emerald-700">264</p>
                    <span class="stat-note text-emerald-600">80.5% Terkendali</span>
                </div>
                <div class="stat !p-2.5">
                    <p class="stat-label">Residu Sedang</p>
                    <p class="stat-value !text-xl text-amber-700">52</p>
                    <span class="stat-note text-amber-600">Perlu Monitoring</span>
                </div>
                <div class="stat !p-2.5">
                    <p class="stat-label">Residu Tinggi</p>
                    <p class="stat-value !text-xl text-rose-700">12</p>
                    <span class="stat-note text-rose-600">Evaluasi Khusus</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Pintasan ke tiap bidang --}}
    <div>
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h3 class="section-title">Eksplorasi Bidang &amp; Sub-Kategori CRMC</h3>
            <span class="page-sub">Pilih salah satu divisi untuk melihat 8 komponen CRMC</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @php
                $divisi = [
                    ['umum-tu', 'briefcase', '29 Sub-Bidang', 'Bagian Umum &amp; Tata Usaha',
                     'Manajemen Risiko, SAKIP, Keuangan, Pengadaan BJ, Arsip, BMN, Perjalanan Dinas, &amp; Layanan Umum.'],
                    ['sda', 'waves', '5 Sub-Bidang', 'Bidang Sumber Daya Air (SDA)',
                     'Bangkom SDA, Kerjasama Pendidikan (MSS), Evaluasi Pasca Pelatihan, E-Learning, Kurikulum &amp; Modul.'],
                    ['ckps', 'building-2', '7 Sub-Bidang', 'Bidang Cipta Karya &amp; Permukiman (CKPS)',
                     'Kurikulum CKPS, Monitoring &amp; Evaluasi, Kerjasama Pelatihan, Penyiapan E-Learning, Skema Sertifikasi.'],
                ];
            @endphp
            @foreach ($divisi as [$tab, $ikon, $jumlah, $judul, $deskripsi])
                <div onclick="switchTab('{{ $tab }}')" class="card click-card p-4 flex flex-col justify-between gap-4 min-h-[9rem]">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-9 h-9 bg-blue-50 text-blue-700 rounded-md flex items-center justify-center">
                                <i data-lucide="{{ $ikon }}" class="w-4 h-4"></i>
                            </div>
                            <span class="badge">{{ $jumlah }}</span>
                        </div>
                        <h4 class="section-title text-base">{!! $judul !!}</h4>
                        <p class="page-sub mt-1 leading-relaxed">{!! $deskripsi !!}</p>
                    </div>
                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-[11px] font-semibold text-slate-700">
                        <span>Buka Daftar Lengkap</span>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
