@extends('admin.layout')

@section('title', 'Pemulihan Dokumen - Admin CRMC')

@section('admin-content')

    {{-- ===================== KEPALA HALAMAN (BANNER) ===================== --}}
    <div class="relative overflow-hidden rounded-xl border border-blue-900 min-h-[8rem] p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <img src="{{ asset('images/banner_bidang.png') }}" alt="Banner Admin" loading="lazy" decoding="async"
             class="absolute inset-0 h-full w-full object-cover object-center">
        <div class="relative min-w-0 drop-shadow-lg">
            <span class="eyebrow text-blue-200">Panel Administrator</span>
            <h1 class="page-title mt-0.5 text-white">Pemulihan Dokumen</h1>
            <p class="page-sub mt-1 leading-relaxed text-blue-100">
                Pindai file yang masih ada di private storage tetapi belum
                terdaftar di database, lalu pulihkan record lampirannya tanpa
                mengunggah ulang.
            </p>
        </div>
        <div class="relative z-10 flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('admin.pemulihan.index') }}" class="btn btn-outline shrink-0">
                <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                <span>Pindai Ulang</span>
            </a>
            <a href="{{ route('admin.index') }}" class="btn btn-outline shrink-0">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Panel Admin</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="note !border-emerald-200 !bg-emerald-50 !text-emerald-800">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="note !border-rose-200 !bg-rose-50 !text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- STATS --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="stat">
            <p class="stat-label">Total Dipindai</p>
            <p class="stat-value">{{ $stats['total'] }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Terdaftar</p>
            <p class="stat-value">{{ $stats['terdaftar'] }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Belum Terdaftar</p>
            <p class="stat-value">{{ $stats['belum_terdaftar'] }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Siap Dipulihkan</p>
            <p class="stat-value">{{ $stats['siap'] }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Perlu Pemeriksaan</p>
            <p class="stat-value">{{ $stats['perlu_pemeriksaan'] }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Tidak Dapat</p>
            <p class="stat-value">{{ $stats['tidak_dapat'] }}</p>
        </div>
    </div>

    {{-- FILTER --}}
    <form method="GET" action="{{ route('admin.pemulihan.index') }}"
          class="card p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
        <div class="sm:col-span-2">
            <label class="label" for="q">Cari Nama File</label>
            <input type="search" name="q" id="q" value="{{ $kataKunci }}" placeholder="Nama file..." class="input">
        </div>
        <div>
            <label class="label" for="tahun">Tahun</label>
            <select name="tahun" id="tahun" class="input">
                <option value="">Semua Tahun</option>
                @foreach($daftarTahun as $t)
                    <option value="{{ $t }}" {{ (string)$tahun === (string)$t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="label" for="kategori">Kategori</label>
            <select name="kategori" id="kategori" class="input">
                <option value="">Semua Kategori</option>
                @foreach($kategoriDokumen as $key => $judul)
                    <option value="{{ $key }}" {{ $kategori === $key ? 'selected' : '' }}>{{ $judul }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap items-center gap-2">
            <button type="submit" class="btn btn-primary shrink-0">
                <i data-lucide="search" class="w-3.5 h-3.5"></i>
                Terapkan Filter
            </button>
            <a href="{{ route('admin.pemulihan.index') }}" class="btn btn-quiet shrink-0">Reset</a>
            <button type="button" onclick="kirimPemulihanTerpilih()" class="btn btn-primary shrink-0">
                <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                Pulihkan Terpilih
            </button>
        </div>
    </form>

    {{-- TABLE --}}
    <div class="card overflow-hidden">
        <div class="card-head flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="card-title flex items-center gap-1.5">
                <i data-lucide="list" class="w-4 h-4 text-slate-400"></i>
                Daftar File Belum Terdaftar
                <span class="badge">{{ $items->total() }} dari {{ $stats['belum_terdaftar'] }}</span>
            </h2>
        </div>

        @if($items->isEmpty())
            <div class="empty">
                <i data-lucide="search-x" class="w-7 h-7 text-slate-300 mx-auto mb-2"></i>
                <p class="font-semibold text-slate-600">Tidak ada file yang cocok</p>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th class="w-8">
                            <input type="checkbox" id="centangSemua" title="Pilih semua yang siap">
                        </th>
                        <th>Nama File</th>
                        <th>Tahun</th>
                        <th>Sub-Bidang</th>
                        <th>Kategori</th>
                        <th>Tipe</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td>
                            @if($item['status'] === 'ready')
                                <input type="checkbox" name="pilih[]" value="{{ $item['path'] }}" class="centang-item rounded border-slate-300">
                            @endif
                        </td>
                        <td class="font-semibold text-slate-900 break-all">{{ $item['nama_file'] }}</td>
                        <td>{{ $item['tahun'] ?? '-' }}</td>
                        <td>
                            @if($item['sub_menu'])
                                {{ $item['sub_menu']->nama_sub_menu }}
                                @if($item['bidang'])
                                    <span class="text-[10px] text-slate-400 block">{{ $item['bidang'] }}</span>
                                @endif
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td>{{ $item['kategori_judul'] }}</td>
                        <td class="font-mono text-xs">{{ $item['ekstensi'] ?: '-' }}</td>
                        <td>
                            @if($item['status'] === 'ready')
                                <span class="badge badge-ok">Siap</span>
                            @elseif($item['status'] === 'already_registered')
                                <span class="badge badge-info">Terdaftar</span>
                            @elseif($item['status'] === 'needs_review')
                                <span class="badge badge-accent">Perlu Periksa</span>
                            @else
                                <span class="badge badge-bad">Tidak Dapat</span>
                            @endif
                        </td>
                        <td class="text-xs text-slate-500">{{ $item['reason'] ?? '-' }}</td>
                        <td class="text-center">
                            @if($item['status'] === 'ready')
                                <form action="{{ route('admin.pemulihan.recover') }}" method="POST" class="inline-block"
                                      data-konfirmasi="Pulihkan file ini?">
                                    @csrf
                                    <input type="hidden" name="paths[]" value="{{ $item['path'] }}">
                                    <button type="submit" class="icon-btn" title="Pulihkan">
                                        <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $items->links() }}
        </div>
    </div>

    <form id="formPulihkanTerpilih" action="{{ route('admin.pemulihan.recover') }}" method="POST" class="hidden">
        @csrf
    </form>

@endsection

@push('admin-scripts')
<script>
    document.getElementById('centangSemua')?.addEventListener('change', function () {
        document.querySelectorAll('.centang-item').forEach(function (cb) {
            cb.checked = document.getElementById('centangSemua').checked;
        });
    });

    function kirimPemulihanTerpilih() {
        const terpilih = document.querySelectorAll('.centang-item:checked');
        if (terpilih.length === 0) {
            alert('Pilih minimal satu file yang siap dipulihkan.');
            return;
        }
        if (!window.confirm('Pulihkan ' + terpilih.length + ' file terpilih?')) {
            return;
        }
        const form = document.getElementById('formPulihkanTerpilih');
        form.querySelectorAll('input[name="paths[]"]').forEach(function (el) { el.remove(); });
        terpilih.forEach(function (cb) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'paths[]';
            input.value = cb.value;
            form.appendChild(input);
        });
        form.submit();
    }
</script>
@endpush