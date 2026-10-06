@extends('layouts.app')

@section('title', 'Kumpulan SOP - CRMC PUSBANGKOM')

@section('content')
<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-4 space-y-4">

    {{-- ===================== KEPALA HALAMAN ===================== --}}
    <div class="card p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="min-w-0">
            <span class="eyebrow">Komponen 3 CRMC</span>
            <h1 class="page-title mt-0.5">Kumpulan Standar Operasional Prosedur (SOP)</h1>
            <p class="page-sub mt-1 leading-relaxed">
                Seluruh dokumen SOP yang sudah diunggah pada sub-bidang mana pun,
                dihimpun di satu halaman. Total {{ number_format($totalSop, 0, ',', '.') }}
                dokumen; sedang tampil {{ number_format($daftarSop->total(), 0, ',', '.') }} dokumen.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            {{-- Pemilih tampilan: grid atau daftar. Hanya mengubah kelas,
                 tidak memuat ulang halaman. --}}
            <div class="inline-flex rounded-lg border border-slate-300 overflow-hidden">
                <button type="button" onclick="setTampilanSop('grid')" id="tombolSopGrid"
                        class="px-3 py-2 text-[11px] font-bold inline-flex items-center gap-1.5 bg-blue-600 text-white">
                    <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i>
                    Grid
                </button>
                <button type="button" onclick="setTampilanSop('daftar')" id="tombolSopDaftar"
                        class="px-3 py-2 text-[11px] font-bold inline-flex items-center gap-1.5 bg-white text-slate-600 hover:bg-slate-50 border-l border-slate-300">
                    <i data-lucide="list" class="w-3.5 h-3.5"></i>
                    Daftar
                </button>
            </div>
        </div>
    </div>

    {{-- ===================== FILTER ===================== --}}
    <form action="{{ route('sop.index') }}" method="GET"
          class="card p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
        <div class="sm:col-span-2">
            <label class="label" for="filterSopCari">Kata Kunci</label>
            <div class="relative">
                <input type="search" name="q" id="filterSopCari" value="{{ $filter['q'] }}"
                       placeholder="Nama berkas, keterangan, atau nama sub-bidang"
                       class="input pl-8">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5"></i>
            </div>
        </div>

        <div>
            <label class="label" for="filterSopBidang">Bidang</label>
            <select name="bidang" id="filterSopBidang" class="input">
                <option value="">-- Semua Bidang --</option>
                @foreach ($daftarBidang as $b)
                    <option value="{{ $b->id }}" {{ $filter['bidang_id'] === $b->id ? 'selected' : '' }}>
                        {{ $b->nama_bidang }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="label" for="filterSopTahun">Tahun</label>
            <select name="tahun" id="filterSopTahun" class="input">
                <option value="">-- Semua Tahun --</option>
                @foreach ($daftarTahun as $t)
                    <option value="{{ $t }}" {{ $filter['tahun'] === (int) $t ? 'selected' : '' }}>
                        {{ $t }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap items-center gap-2 pt-1">
            <button type="submit" class="btn btn-sm btn-primary">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                Terapkan Filter
            </button>

            @if ($filter['bidang_id'] || $filter['tahun'] || $filter['q'] !== '')
                <a href="{{ route('sop.index') }}" class="btn btn-sm btn-quiet">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    Reset
                </a>
                <span class="page-sub">
                    Menampilkan {{ $daftarSop->total() }} dari {{ $totalSop }} dokumen SOP.
                </span>
            @endif
        </div>
    </form>

    {{-- ===================== DAFTAR SOP ===================== --}}
    @if ($daftarSop->isEmpty())
        <div class="empty">
            <i data-lucide="book-open-text" class="w-7 h-7 text-slate-300 mx-auto mb-2"></i>
            <p class="font-semibold text-slate-600">
                {{ $totalSop === 0 ? 'Belum Ada Dokumen SOP' : 'Tidak Ada SOP yang Cocok' }}
            </p>
            <p class="text-[11px] text-slate-400 mt-1 max-w-md mx-auto leading-relaxed">
                @if ($totalSop === 0)
                    SOP diunggah dari halaman 8 Komponen tiap sub-bidang,
                    pada Komponen 3 "Standar Operasional Prosedur (SOP)".
                @else
                    Ubah kata kunci atau filter bidang/tahun di atas.
                @endif
            </p>
        </div>
    @else
        <div id="daftarSop" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($daftarSop as $i => $sop)
                @php
                    $dokumen = $sop->dokumenCrmc;
                    $sub = $dokumen?->subMenu;
                @endphp

                <div class="card p-4 flex flex-col gap-3">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-md bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug break-words">
                                {{ $sop->nama_file }}
                            </h3>
                            <p class="page-sub mt-0.5 truncate">
                                {{ $sub?->nama_sub_menu ?? 'Sub-bidang tidak dikenal' }}
                            </p>
                        </div>
                    </div>

                    @if ($sop->keterangan)
                        <p class="page-sub leading-relaxed line-clamp-3">{{ $sop->keterangan }}</p>
                    @endif

                    <div class="flex flex-wrap items-center gap-1.5">
                        <span class="badge badge-accent">{{ $dokumen?->tahun_pelaksanaan ?? '-' }}</span>
                        <span class="badge">{{ $sub?->bidang?->nama_bidang ?? 'Tanpa Bidang' }}</span>
                        <span class="badge">{{ strtoupper($sop->tipe_file ?: 'BERKAS') }}</span>
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between gap-2">
                        <span class="page-sub truncate">
                            Diunggah {{ $sop->created_at?->translatedFormat('d M Y') ?? '-' }}
                        </span>

                        <a href="{{ $sop->file_path }}" target="_blank" rel="noopener"
                           class="btn btn-sm btn-primary shrink-0">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            Buka
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ===================== PAGINASI ===================== --}}
        @if ($daftarSop->hasPages())
            <div class="flex justify-center">
                {{ $daftarSop->onEachSide(1)->links() }}
            </div>
        @endif
    @endif
</main>
@endsection

@push('scripts')
<script>
    // Tampilan grid atau daftar. Hanya menukar kelas pada satu wadah,
    // jadi tidak ada permintaan baru ke server.
    (function () {
        // Pilihan disimpan di localStorage supaya tetap sama saat dibuka lagi.
        const KUNCI = 'tampilanSop';
        const wadah = document.getElementById('daftarSop');

        function setTampilanSop(mode) {
            if (wadah) {
                wadah.classList.toggle('grid-cols-1', mode === 'grid');
                wadah.classList.toggle('sm:grid-cols-2', mode === 'grid');
                wadah.classList.toggle('lg:grid-cols-3', mode === 'grid');
                // Mode daftar: satu kolom penuh, kartu tetap boleh merebut
                // tinggi dengan justify-between.
                wadah.classList.toggle('grid-cols-1', mode === 'daftar');
                wadah.classList.toggle('sm:grid-cols-1', mode === 'daftar');
                wadah.classList.toggle('lg:grid-cols-1', mode === 'daftar');
            }

            const tombolGrid = document.getElementById('tombolSopGrid');
            const tombolDaftar = document.getElementById('tombolSopDaftar');
            [tombolGrid, tombolDaftar].forEach(function (t) {
                if (!t) return;
                const aktif = t.id === (mode === 'grid' ? 'tombolSopGrid' : 'tombolSopDaftar');
                t.classList.toggle('bg-blue-600', aktif);
                t.classList.toggle('text-white', aktif);
                t.classList.toggle('bg-white', !aktif);
                t.classList.toggle('text-slate-600', !aktif);
                t.classList.toggle('hover:bg-slate-50', !aktif);
            });

            try { localStorage.setItem(KUNCI, mode); } catch (e) { /* penyimpanan dinonaktifkan */ }
            if (typeof segarkanIkon === 'function') segarkanIkon();
        }

        window.setTampilanSop = setTampilanSop;

        document.addEventListener('DOMContentLoaded', function () {
            let mode = 'grid';
            try { mode = localStorage.getItem(KUNCI) || 'grid'; } catch (e) { /* abaikan */ }
            setTampilanSop(mode === 'daftar' ? 'daftar' : 'grid');
        });
    })();
</script>
@endpush
