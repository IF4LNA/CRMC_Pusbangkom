@extends('layouts.app')

@section('title', 'Beranda - CRMC PUSBANGKOM')

@section('content')
<main class="flex-1">

    {{-- Notifikasi hasil aksi admin --}}
    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-5">
            <div class="bg-emerald-50 border border-emerald-200 rounded-2xl px-4 py-3 flex items-start gap-3">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5"></i>
                <p class="text-xs font-semibold text-emerald-900">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- ============ HERO / BANNER ============ --}}
    <section class="relative bg-blue-950 text-white overflow-hidden">
        {{-- Banner foto gedung PUSBANGKOM. Lapisan biru di atasnya menjaga
             teks tetap terbaca tanpa menutupi gambar sepenuhnya. --}}
        <img src="{{ asset('images/gedung_pusbangkom.jpg') }}"
             alt="Gedung PUSBANGKOM"
             class="absolute inset-0 h-full w-full object-cover object-center">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-950/95 via-blue-950/85 to-blue-900/50"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-blue-300 text-[11px] font-bold tracking-wide uppercase mb-6">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    Kementerian Pekerjaan Umum
                </span>

                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.1]">
                    Continuous Monitoring
                    <span class="block text-blue-300">on Risk Control</span>
                </h1>

                <p class="text-sm md:text-base text-slate-300 leading-relaxed mt-6 max-w-2xl">
                    Pemantauan berkelanjutan atas pengendalian risiko di Pusat
                    Sumber Daya Air dan Prasarana Wilayah III Bandung. Satu alur
                    kerja yang menyatukan perencanaan, pelaksanaan, dan evaluasi.
                </p>

                <div class="flex flex-wrap items-center gap-3 mt-8">
                    <a href="#tentang-crmc"
                       class="inline-flex items-center gap-2 px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition shadow-sm">
                        <i data-lucide="book-open" class="w-4 h-4"></i>
                        Pelajari CRMC
                    </a>
                    <a href="{{ route('home') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-xs rounded-xl transition">
                        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                        Dashboard CRMC
                    </a>
                    @guest
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-2 px-5 py-3 text-slate-300 hover:text-white font-bold text-xs transition">
                            <i data-lucide="log-in" class="w-4 h-4"></i>
                            Masuk
                        </a>
                    @endguest
                </div>

                {{-- Angka ringkas. Semua dihitung dari data nyata; tidak ada
                     angka dummy yang harus dijaga manual. --}}
                <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-12 max-w-2xl">
                    @php
                        $ringkasan = [
                            ['3', 'Tujuan Utama', 'target'],
                            ['8', 'Komponen', 'layers'],
                            [(string) $jumlahSubBidang, 'Sub-Bidang', 'layers-2'],
                            [(string) $jumlahSop, 'Dokumen SOP', 'book-open-text'],
                        ];
                    @endphp
                    @foreach ($ringkasan as $r)
                        <div class="bg-white/5 border border-white/10 rounded-2xl px-4 py-3.5">
                            <dt class="text-[10px] text-slate-400 uppercase tracking-wide flex items-center gap-1.5">
                                <i data-lucide="{{ $r[2] }}" class="w-3 h-3"></i>
                                {{ $r[1] }}
                            </dt>
                            <dd class="text-2xl font-extrabold text-white mt-1">{{ $r[0] }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>
    </section>

    {{-- ============ ISI HALAMAN ============ --}}
    @include('home.penjelasan')
    @include('home.struktur')
    @include('home.partials.galeri')
    @include('home.partials.peta')

    {{-- ============ CTA PENUTUP ============ --}}
    <section class="bg-blue-950 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-xl md:text-2xl font-extrabold tracking-tight">Siap mengisi instrumen pengendalian?</h2>
                <p class="text-sm text-slate-400 mt-1.5">
                    Buka dashboard CRMC untuk melihat seluruh sub-bidang dan
                    kelengkapan komponen yang menjadi tanggung jawab Anda.
                </p>
            </div>
            <a href="{{ route('home') }}"
               class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition shadow-sm shrink-0">
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
                Masuk ke Dashboard
            </a>
        </div>
    </section>
</main>

{{-- ============ FOOTER ============ --}}
@include('layouts.footer')

{{-- ============ MODAL ADMIN ============ --}}
@if (Auth::check() && Auth::user()->isAdmin())
    @include('home.partials.panel-admin')
@endif
@endsection
