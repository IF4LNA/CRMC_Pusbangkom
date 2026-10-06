{{--
    Navigasi utama, dipakai bersama oleh halaman Beranda dan dashboard CRMC.

    Satu partial supaya keduanya benar-benar sama: daftar bidang, jumlah
    sub-bidang, dan tautan SOP diambil dari view composer `layouts.app`
    (lihat AppServiceProvider), bukan ditulis manual per halaman.

    Tab dashboard memakai id bidang ("bidang-3") supaya nama bidang bebas
    diubah tanpa merusak tautan lama yang sudah dibagikan.
--}}
@php
    // Hanya halaman dashboard yang punya tab. Di halaman lain
    // (Beranda, SOP, detail sub-bidang) tidak ada tab yang aktif.
    $diDashboard = request()->routeIs('home');
    $tabAktif = $diDashboard ? (request('tab') ?: 'dashboard') : null;
@endphp

<nav id="navUtama" class="nav nav-dark no-scrollbar">
    <a href="{{ route('beranda') }}"
       class="nav-link {{ request()->routeIs('beranda') ? 'is-active' : '' }}">
        <i data-lucide="home" class="w-4 h-4"></i>
        <span>Beranda</span>
    </a>

    <a href="{{ route('home') }}"
       class="nav-link {{ $diDashboard && $tabAktif === 'dashboard' ? 'is-active' : '' }}">
        <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
        <span>Dashboard</span>
    </a>

    <button type="button" onclick="switchTab('dasar-hukum')" id="nav-dasar-hukum"
            class="nav-link nav-btn {{ $tabAktif === 'dasar-hukum' ? 'is-active' : '' }}">
        <i data-lucide="scale" class="w-4 h-4"></i>
        <span>Dasar Hukum</span>
    </button>

    @foreach ($navBidang as $bidang)
        <button type="button" onclick="switchTab('{{ $bidang['tab'] }}')" id="nav-{{ $bidang['tab'] }}"
                class="nav-link nav-btn {{ $tabAktif === $bidang['tab'] ? 'is-active' : '' }}">
            <i data-lucide="{{ $bidang['ikon'] }}" class="w-4 h-4"></i>
            <span>{{ $bidang['nama'] }}</span>
            <span class="count">{{ $bidang['jumlah'] }}</span>
        </button>
    @endforeach

    <a href="{{ route('sop.index') }}"
       class="nav-link {{ request()->routeIs('sop.index') ? 'is-active' : '' }}">
        <i data-lucide="book-open-text" class="w-4 h-4"></i>
        <span>SOP</span>
        <span class="count">{{ $navSopJumlah }}</span>
    </a>
</nav>
