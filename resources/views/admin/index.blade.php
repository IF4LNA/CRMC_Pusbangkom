@extends('admin.layout')

@section('title', 'Panel Administrator - CRMC')

@section('admin-content')
{{-- ===================== KEPALA HALAMAN (BANNER) ===================== --}}
<div class="relative overflow-hidden rounded-xl border border-blue-900 min-h-[8rem] p-4 sm:p-5">
    <img src="{{ asset('images/banner_bidang.png') }}" alt="Banner Admin" loading="lazy" decoding="async"
         class="absolute inset-0 h-full w-full object-cover object-center">
    <div class="relative min-w-0 drop-shadow-lg">
        <span class="eyebrow text-blue-200">Khusus Administrator</span>
        <h1 class="page-title mt-0.5 text-white">Panel Administrator</h1>
        <p class="page-sub mt-1 leading-relaxed text-blue-100">
            Selamat datang, {{ Auth::user()->name }}. Kelola akun pegawai dan
            kelengkapan dokumen CRMC dari satu tempat.
        </p>
    </div>
</div>

{{-- ===================== RINGKASAN PENGGUNA ===================== --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
    <div class="stat">
        <p class="stat-label">Total Pegawai</p>
        <p class="stat-value">{{ $totalPegawai }}</p>
    </div>
    <div class="stat">
        <p class="stat-label">Administrator</p>
        <p class="stat-value">{{ $totalAdmin }}</p>
    </div>
    <div class="stat">
        <p class="stat-label">Pegawai</p>
        <p class="stat-value">{{ $totalPegawaiRole }}</p>
    </div>
</div>

{{-- ===================== MENU PANEL ===================== --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <a href="{{ route('admin.pegawai.index') }}" class="card click-card p-4 sm:p-5 flex flex-col justify-between">
        <div>
            <div class="w-9 h-9 bg-blue-50 text-blue-700 rounded-lg flex items-center justify-center shrink-0 mb-3">
                <i data-lucide="users" class="w-4 h-4"></i>
            </div>
            <h2 class="text-base font-bold text-slate-900">Kelola Pegawai</h2>
            <p class="page-sub mt-1 leading-relaxed">
                Buat, ubah, dan hapus akun pegawai. Atur bidang akses serta
                role Admin atau Pegawai.
            </p>
        </div>
        <span class="mt-4 inline-flex items-center gap-1.5 text-[11px] font-bold text-blue-700">
            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            Buka Kelola Pegawai
        </span>
    </a>

    <a href="{{ route('admin.dokumen.index') }}" class="card click-card p-4 sm:p-5 flex flex-col justify-between">
        <div>
            <div class="w-9 h-9 bg-blue-50 text-blue-700 rounded-lg flex items-center justify-center shrink-0 mb-3">
                <i data-lucide="file-stack" class="w-4 h-4"></i>
            </div>
            <h2 class="text-base font-bold text-slate-900">Kelola Dokumen</h2>
            <p class="page-sub mt-1 leading-relaxed">
                Lihat sub-bidang yang dokumennya belum lengkap per tahun, lalu
                kelola daftar tahun dan hapus dokumen satu tahun sekaligus.
            </p>
        </div>
        <span class="mt-4 inline-flex items-center gap-1.5 text-[11px] font-bold text-blue-700">
            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            Buka Kelola Dokumen
        </span>
    </a>
</div>
@endsection
