@extends('layouts.app')

@section('title', 'Dashboard Utama CRMC')

@section('content')
<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-4 space-y-4">

    <!-- Mobile Search Bar -->
    <div class="md:hidden">
        <div class="relative">
            <input type="text" oninput="handleSearch(this.value)" placeholder="Cari bidang, SOP, dokumen..."
                   class="input pl-8">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5"></i>
        </div>
    </div>

    <!-- SEARCH RESULTS CONTAINER -->
    <div id="searchResultsContainer" class="hidden">
        <div class="card">
            <div class="card-head flex items-center justify-between gap-2">
                <h3 class="card-title flex items-center gap-1.5">
                    <i data-lucide="search" class="w-4 h-4 text-slate-500"></i>
                    Hasil Pencarian
                </h3>
                <button onclick="clearSearch()" class="btn btn-sm btn-quiet">Tutup</button>
            </div>
            <div class="p-3">
                <div id="searchResultsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2"></div>
            </div>
        </div>
    </div>

    <!-- TAB CONTENTS -->
    <div id="tab-dashboard" class="tab-content">
        @include('crmc.partials.tab-dashboard')
    </div>

    <div id="tab-dasar-hukum" class="tab-content hidden">
        @include('crmc.partials.tab-dasar-hukum')
    </div>

    {{-- Satu tab per bidang. Daftar bidang dibaca dari tabel `bidang`,
         sehingga bidang baru otomatis muncul di sini tanpa perubahan kode. --}}
    @foreach ($daftarBidang as $bidang)
        <div id="tab-bidang-{{ $bidang->id }}" class="tab-content hidden">
            @include('crmc.partials.tab-bidang', ['bidang' => $bidang])
        </div>
    @endforeach
</main>
@endsection

@section('modals')
    @include('crmc.components.admin-settings-modal')

    {{-- Modal tambah/ubah sub-bidang hanya dirender untuk admin. Rutenya
         sendiri tetap memeriksa ulang peran admin di server. --}}
    @if ($isAdmin)
        @include('crmc.components.modal-sub-bidang')
    @endif
@endsection
