@extends('admin.layout')

@section('title', 'Kelola Dokumen - Admin CRMC')

@section('admin-content')
{{-- ===================== KEPALA HALAMAN (BANNER) ===================== --}}
<div class="relative overflow-hidden rounded-xl border border-blue-900 min-h-[8rem] p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <img src="{{ asset('images/banner_bidang.png') }}" alt="Banner Admin" loading="lazy" decoding="async"
         class="absolute inset-0 h-full w-full object-cover object-center">
    <div class="relative min-w-0 drop-shadow-lg">
        <span class="eyebrow text-blue-200">Khusus Administrator</span>
        <h1 class="page-title mt-0.5 text-white">Kelola Dokumen</h1>
        <p class="page-sub mt-1 leading-relaxed text-blue-100">
            Daftar sub-bidang yang dokumennya belum lengkap untuk tahun yang
            dipilih, beserta komponen mana saja yang masih kosong.
        </p>
    </div>

    <a href="{{ route('admin.index') }}" class="relative z-10 btn btn-outline shrink-0">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5 text-slate-500"></i>
        <span>Kembali ke Panel Admin</span>
    </a>
</div>

{{-- ===================== FILTER ===================== --}}
<form action="{{ route('admin.dokumen.index') }}" method="GET"
      class="card p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
    <div>
        <label class="label" for="filterTahun">Tahun</label>
        <select name="tahun" id="filterTahun" class="input" onchange="this.form.submit()">
            @foreach(array_reverse($daftarTahun) as $tahun)
                <option value="{{ $tahun }}" {{ $selectedTahun == $tahun ? 'selected' : '' }}>
                    {{ $tahun }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="sm:col-span-1 lg:col-span-2">
        <label class="label" for="filterCari">Cari Sub-Bidang</label>
        <div class="relative">
            <input type="search" name="q" id="filterCari" value="{{ $kataKunci }}"
                   placeholder="Nama sub-bidang atau bidang"
                   class="input pl-8">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5"></i>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <button type="submit" class="btn btn-primary">
            <i data-lucide="filter" class="w-3.5 h-3.5"></i>
            Terapkan
        </button>
        @if ($kataKunci !== '')
            <a href="{{ route('admin.dokumen.index', ['tahun' => $selectedTahun, 'semua' => $tampilkanSemua ? 1 : null]) }}"
               class="btn btn-quiet">
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                Reset
            </a>
        @endif
        <label class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-slate-600 cursor-pointer">
            <input type="checkbox" name="semua" value="1" class="rounded border-slate-300"
                   {{ $tampilkanSemua ? 'checked' : '' }} onchange="this.form.submit()">
            Tampilkan yang sudah lengkap
        </label>
    </div>
</form>

{{-- ===================== RINGKASAN ===================== --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
    <div class="stat">
        <p class="stat-label">Tahun Dipilih</p>
        <p class="stat-value">{{ $selectedTahun }}</p>
    </div>
    <div class="stat">
        <p class="stat-label">Belum Lengkap</p>
        <p class="stat-value text-rose-700">{{ $jumlahBelumLengkap }}</p>
        <p class="stat-note">dari {{ $totalSubBidang }} sub-bidang</p>
    </div>
    <div class="stat">
        <p class="stat-label">Sudah Lengkap</p>
        <p class="stat-value text-emerald-700">{{ $jumlahLengkap }}</p>
        <p class="stat-note">{{ $totalKomponen }} komponen dicek per sub-bidang</p>
    </div>
</div>

{{-- ===================== DAFTAR SUB-BIDANG ===================== --}}
<div class="card overflow-hidden">
    <div class="card-head flex flex-wrap items-center justify-between gap-2">
        <h2 class="card-title flex items-center gap-1.5">
            <i data-lucide="file-stack" class="w-4 h-4 text-slate-400"></i>
            Sub-Bidang Belum Lengkap — Tahun {{ $selectedTahun }}
        </h2>
        <span class="badge {{ $subBidang->isEmpty() ? 'badge-ok' : 'badge-bad' }}">
            {{ $subBidang->count() }} sub-bidang ditampilkan
        </span>
    </div>

    @if ($subBidang->isEmpty())
        <div class="empty m-4">
            @if ($kataKunci !== '')
                <i data-lucide="search-x" class="w-7 h-7 text-slate-300 mx-auto mb-2"></i>
                <p class="font-semibold text-slate-600">Tidak ada sub-bidang yang cocok</p>
                <p class="text-[11px] text-slate-400 mt-1 max-w-md mx-auto leading-relaxed">
                    Coba kata kunci lain, atau tekan Reset untuk melihat seluruh sub-bidang.
                </p>
            @elseif ($jumlahBelumLengkap === 0)
                <i data-lucide="check-circle-2" class="w-7 h-7 text-emerald-500 mx-auto mb-2"></i>
                <p class="font-semibold text-slate-600">Semua sub-bidang sudah lengkap</p>
                <p class="text-[11px] text-slate-400 mt-1 max-w-md mx-auto leading-relaxed">
                    Seluruh {{ $totalSubBidang }} sub-bidang sudah memiliki dokumen untuk
                    tahun {{ $selectedTahun }}.
                </p>
            @else
                <i data-lucide="inbox" class="w-7 h-7 text-slate-300 mx-auto mb-2"></i>
                <p class="font-semibold text-slate-600">Tidak ada sub-bidang untuk ditampilkan</p>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 p-4">
            @foreach ($subBidang as $sb)
                <div class="card {{ $sb['selesai'] ? 'border-emerald-200' : 'border-slate-200' }}">
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <span class="badge {{ $sb['selesai'] ? 'badge-ok' : 'badge-bad' }}">
                                    {{ $sb['selesai'] ? 'Lengkap' : $sb['jumlah_belum'].' komponen kosong' }}
                                </span>
                                <h3 class="text-sm font-bold text-slate-900 leading-snug mt-1.5">
                                    {{ $sb['nama_sub_menu'] }}
                                </h3>
                                <p class="text-[10px] text-slate-500 mt-0.5">{{ $sb['bidang'] }}</p>
                            </div>
                            <a href="{{ route('crmc.show', ['slug' => $sb['slug'], 'tahun' => $selectedTahun]) }}"
                               class="icon-btn shrink-0" title="Buka halaman 8 Komponen">
                                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>

                        @if ($sb['belum_diisi']->isNotEmpty())
                            <div class="mt-3 space-y-1">
                                @foreach ($sb['belum_diisi'] as $komponen)
                                    <div class="flex items-center gap-2 rounded-lg bg-slate-50 border border-slate-100 px-2 py-1.5">
                                        <span class="text-[10px] font-bold text-slate-500 bg-slate-100 rounded px-1.5 py-0.5">
                                            {{ $komponen['nomor'] }}
                                        </span>
                                        <span class="text-[11px] text-slate-700 leading-tight">{{ $komponen['judul'] }}</span>
                                        <span class="ml-auto text-[9px] font-bold text-rose-600 whitespace-nowrap">Kosong</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-3 rounded-lg bg-emerald-50 border border-emerald-100 p-3 text-center">
                                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 mx-auto mb-1"></i>
                                <p class="text-[10px] text-emerald-700 font-semibold">Semua komponen sudah terisi</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- ===================== DAFTAR TAHUN ===================== --}}
<div class="card overflow-hidden">
    <div class="card-head">
        <h3 class="card-title flex items-center gap-1.5">
            <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
            Daftar Tahun Pilihan
        </h3>
    </div>

    <div class="p-4 space-y-3">
        <div class="space-y-2">
            <div class="flex items-center justify-between gap-2">
                <span class="label !mb-0">Tahun Ditambahkan Manual</span>
                <span class="text-[10px] text-slate-400">{{ $tahunManual->count() }} tahun</span>
            </div>

            @if ($tahunManual->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    @foreach ($tahunManual as $ta)
                        <div class="inline-flex items-center gap-2 px-2.5 py-1.5 bg-slate-50 border border-slate-200 rounded-lg">
                            <span class="text-xs font-bold text-slate-800">{{ $ta->tahun }}</span>
                            @if ($ta->keterangan)
                                <span class="text-[10px] text-slate-500 truncate max-w-[14rem]"
                                      title="{{ $ta->keterangan }}">{{ $ta->keterangan }}</span>
                            @endif
                            <form action="{{ route('admin.dokumen.tahun.destroy', $ta->id) }}" method="POST"
                                  data-konfirmasi="Hapus tahun {{ $ta->tahun }} dari daftar pilihan? Dokumen tahun {{ $ta->tahun }} yang sudah ada TIDAK ikut terhapus.">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn icon-btn-danger !w-5 !h-5" title="Hapus dari daftar">
                                    <i data-lucide="x" class="w-3 h-3"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-[11px] text-slate-400 italic">
                    Belum ada tahun manual. Tahun {{ date('Y') }} dan {{ date('Y') + 1 }} sudah otomatis tersedia.
                </p>
            @endif
        </div>

        <form action="{{ route('admin.dokumen.tahun.store') }}" method="POST"
              class="border-t border-slate-100 pt-3 grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            @csrf
            <div>
                <label class="label" for="tahunBaru">Tahun Baru *</label>
                <input type="number" name="tahun" id="tahunBaru" required min="2000" max="2100"
                       placeholder="mis. {{ date('Y') + 2 }}" class="input">
            </div>
            <div class="sm:col-span-2">
                <label class="label" for="keteranganTahun">Keterangan (opsional)</label>
                <input type="text" name="keterangan" id="keteranganTahun" maxlength="255"
                       placeholder="mis. Tahun Anggaran Khusus" class="input">
            </div>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Tambah Tahun</span>
            </button>
        </form>

        <p class="hint !mt-0">
            Menghapus tahun dari daftar hanya menghilangkan pilihannya. Dokumen
            tahun tersebut tidak ikut terhapus.
        </p>
    </div>
</div>

{{-- ===================== HAPUS DOKUMEN SATU TAHUN ===================== --}}
<div class="card overflow-hidden">
    <div class="card-head !bg-rose-50 !border-rose-200 flex items-center gap-2">
        <i data-lucide="triangle-alert" class="w-4 h-4 text-rose-600 shrink-0"></i>
        <div class="min-w-0">
            <h3 class="text-sm font-semibold text-rose-900">Hapus Seluruh Dokumen Satu Tahun</h3>
            <p class="text-[11px] text-rose-700/80">
                Berlaku untuk semua sub-bidang, termasuk file fisik di storage.
            </p>
        </div>
    </div>

    <form action="{{ route('admin.dokumen.hapus.semua') }}" method="POST"
          class="p-4 flex flex-wrap items-end gap-3">
        @csrf
        <div>
            <label class="label" for="tahunHapus">Tahun</label>
            <select name="tahun" id="tahunHapus" required class="input">
                @foreach (array_reverse($daftarTahun) as $tahun)
                    <option value="{{ $tahun }}" {{ $selectedTahun == $tahun ? 'selected' : '' }}>
                        {{ $tahun }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit"
                data-konfirmasi="PERINGATAN: seluruh dokumen tahun yang dipilih di SEMUA sub-bidang akan dihapus permanen, termasuk file fisik di storage. Tindakan ini tidak bisa dibatalkan. Lanjutkan?"
                class="btn !bg-rose-600 !border-rose-600 !text-white hover:!bg-rose-700">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
            <span>Hapus Semua Dokumen Tahun Tersebut</span>
        </button>
    </form>
</div>
@endsection

@push('admin-scripts')
<script>
    document.addEventListener('submit', function (e) {
        const pesan = e.target.dataset && e.target.dataset.konfirmasi;
        if (pesan && !window.confirm(pesan)) {
            e.preventDefault();
        }
    }, true);
</script>
@endpush
