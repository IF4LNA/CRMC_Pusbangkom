<div class="space-y-3">
    <div class="card p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <span class="eyebrow">Regulasi</span>
            <h2 class="card-title mt-0.5 flex items-center gap-1.5">
                <i data-lucide="scale" class="w-4 h-4 text-slate-400"></i>
                Dasar Hukum &amp; Regulasi CRMC
            </h2>
            <p class="page-sub mt-1">Landasan peraturan dan pedoman teknis penerapan Continuous Monitoring on Risk Control</p>
        </div>
        <span class="badge shrink-0">4 Peraturan Utama Terkait</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        @php
            $peraturan = [
                ['book-open', 'Peraturan Menteri', 'Permen PUPR No. 20/PRT/M/2018',
                 'Penyelenggaraan Sistem Pengendalian Intern Pemerintah (SPIP) di Lingkungan Kementerian PUPR.', '2.4 MB'],
                ['file-check-2', 'Surat Edaran', 'Surat Edaran Menteri PUPR Nomor 04/SE/M/2021',
                 'Pedoman Penerapan Manajemen Risiko di Lingkungan Kementerian Pekerjaan Umum.', '1.8 MB'],
            ];
        @endphp
        @foreach ($peraturan as [$ikon, $jenis, $judul, $isi, $ukuran])
            <div class="card p-5 flex flex-col justify-between gap-4">
                <div class="flex items-start justify-between gap-2">
                    <div class="w-9 h-9 bg-blue-50 text-blue-700 rounded-md flex items-center justify-center">
                        <i data-lucide="{{ $ikon }}" class="w-4 h-4"></i>
                    </div>
                    <span class="badge badge-info">{{ $jenis }}</span>
                </div>
                <div>
                    <h3 class="font-semibold text-slate-900 text-sm">{{ $judul }}</h3>
                    <p class="page-sub mt-1 leading-relaxed">{{ $isi }}</p>
                </div>
                <div class="flex items-center justify-between border-t border-slate-200 pt-2 text-[11px]">
                    <span class="text-slate-400">PDF &bull; {{ $ukuran }}</span>
                    <a href="#" class="text-blue-700 font-semibold hover:text-blue-900 flex items-center gap-1">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i> Unduh Salinan
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
