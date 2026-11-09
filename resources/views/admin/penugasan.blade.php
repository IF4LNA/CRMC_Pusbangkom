@extends('admin.layout')

@section('title', 'Penugasan Pegawai - Admin CRMC')

@section('admin-content')

    {{-- ===================== KEPALA HALAMAN ===================== --}}
    <div class="relative overflow-hidden rounded-xl border border-blue-900 min-h-[8rem] p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <img src="{{ asset('images/banner_bidang.png') }}" alt="Banner Admin" loading="lazy" decoding="async"
             class="absolute inset-0 h-full w-full object-cover object-center">
        <div class="relative min-w-0 drop-shadow-lg">
            <span class="eyebrow text-blue-200">Panel Administrator</span>
            <h1 class="page-title mt-0.5 text-white">Penugasan Pegawai (Per Tahun)</h1>
            <p class="page-sub mt-1 leading-relaxed text-blue-100">
                Tampilkan pegawai yang belum memiliki penugasan pada tahun tertentu,
                lalu tambahkan penugasannya langsung dari halaman ini.
            </p>
        </div>
        <div class="relative z-10 flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('admin.index') }}" class="btn btn-outline">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Panel Admin</span>
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="note !border-emerald-200 !bg-emerald-50 !text-emerald-800">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="note !border-rose-200 !bg-rose-50 !text-rose-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- ===================== FILTER TAHUN ===================== --}}
    <div class="card p-4">
        <form method="GET" action="{{ route('admin.penugasan.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <label class="label !mb-0 sm:w-auto" for="filterTahunPenugasan">Tahun Pelaksanaan</label>
            <select name="tahun" id="filterTahunPenugasan" class="input sm:w-40">
                @foreach ($daftarTahun as $t)
                    <option value="{{ $t }}" {{ (int) $t === (int) $tahunDipilih ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
            <input type="search" name="q" value="{{ $kataKunci }}" placeholder="Cari nama, NIP, jabatan..." class="input sm:flex-1">
            <button type="submit" class="btn btn-primary shrink-0">
                <i data-lucide="search" class="w-3.5 h-3.5"></i> Tampilkan
            </button>
        </form>
    </div>

    {{-- ===================== FORM TAMBAH PENUGASAN ===================== --}}
    <div class="card p-4">
        <h2 class="card-title flex items-center gap-1.5 mb-3">
            <i data-lucide="user-plus" class="w-4 h-4 text-slate-400"></i>
            Tambah Penugasan Pegawai
        </h2>
        <form method="POST" action="{{ route('admin.penugasan.store') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            @csrf
            <input type="hidden" name="tahun_pelaksanaan" value="{{ $tahunDipilih }}">
            <div>
                <label class="label" for="inputPegawaiPenugasan">Pegawai (belum ditugaskan)</label>
                <select name="user_id" id="inputPegawaiPenugasan" required class="input">
                    <option value="">-- Pilih Pegawai --</option>
                    @foreach ($pegawaiBelum as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} - {{ $u->jabatan }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="inputSubMenuPenugasan">Pengendalian Resiko</label>
                <select name="sub_menu_id" id="inputSubMenuPenugasan" required class="input">
                    <option value="">-- Pilih --</option>
                    @foreach ($daftarSubMenu as $sm)
                        <option value="{{ $sm->id }}">{{ $sm->bidang->nama_bidang ?? '-' }} - {{ $sm->nama_sub_menu }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="inputPeranPenugasan">Peran</label>
                <select name="peran" id="inputPeranPenugasan" required class="input">
                    <option value="pengendali_risiko">Pengendali Risiko</option>
                    <option value="pengendali_mutu">Pengendali Mutu</option>
                    <option value="pemilik_risiko">Pemilik Risiko</option>
                </select>
            </div>
            <div class="sm:col-span-2 lg:col-span-2 flex items-end">
                <button type="submit" class="btn btn-primary w-full sm:w-auto">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    Tambah Penugasan
                </button>
            </div>
        </form>
    </div>

    {{-- ===================== DAFTAR PEGAWAI BELUM DITUGASKAN ===================== --}}
    <div class="card overflow-hidden">
        <div class="card-head flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="card-title flex items-center gap-1.5">
                <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
                Pegawai Belum Memiliki Penugasan ({{ $tahunDipilih }})
                <span class="badge">{{ $pegawaiBelum->count() }}</span>
            </h2>
        </div>

        @if ($pegawaiBelum->isEmpty())
            <div class="empty">
                <i data-lucide="check-circle-2" class="w-7 h-7 text-emerald-300 mx-auto mb-2"></i>
                <p class="font-semibold text-slate-600">Semua pegawai sudah memiliki penugasan</p>
                <p class="text-[11px] text-slate-400 mt-1 max-w-md mx-auto leading-relaxed">
                    Tidak ada pegawai yang belum ditugaskan pada tahun {{ $tahunDipilih }}.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="tbl">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama Lengkap</th>
                            <th>NIP</th>
                            <th>Jabatan</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pegawaiBelum as $u)
                            <tr>
                                <td>
                                    <img src="{{ $u->foto_url }}" alt="{{ $u->name }}" class="w-10 h-10 rounded-md object-cover border border-slate-200">
                                </td>
                                <td class="font-semibold text-slate-900">{{ $u->name }}</td>
                                <td>{{ $u->nip }}</td>
                                <td>{{ $u->jabatan }}</td>
                                <td>{{ $u->email }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection