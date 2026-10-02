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

    <div id="tab-umum-tu" class="tab-content hidden">
        @include('crmc.partials.tab-umum-tu')
    </div>

    <div id="tab-sda" class="tab-content hidden">
        @include('crmc.partials.tab-sda')
    </div>

    <div id="tab-ckps" class="tab-content hidden">
        @include('crmc.partials.tab-ckps')
    </div>

</main>
@endsection

@section('modals')
    @include('crmc.components.crmc-modal')
    @include('crmc.components.admin-settings-modal')
@endsection

@push('scripts')
<script>
    // Semua grid memakai markup yang sama supaya konsisten. Warna tidak
    // dibedakan per bidang lagi: satu aksen biru untuk semuanya.
    function kartuSubBidang(item, idx, prefiks, parent) {
        return `
            <div onclick="navigateToCRMC('${item}')" class="card click-card p-3 flex flex-col justify-between gap-3 min-h-[7rem]">
                <div class="flex items-start justify-between gap-2">
                    <span class="badge">${prefiks} #${idx + 1}</span>
                    <button onclick="event.stopPropagation(); openCRMCModal('${item}', '${parent}')" title="Form Input / Edit" class="icon-btn">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
                <h4 class="text-xs sm:text-sm font-semibold text-slate-800 leading-snug line-clamp-2">${item}</h4>
                <div class="flex items-center justify-between border-t border-slate-200 pt-2 text-[11px] text-slate-500">
                    <span class="badge badge-ok">8 Komponen</span>
                    <span class="inline-flex items-center gap-0.5 text-slate-700 font-medium">
                        Buka <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
            </div>
        `;
    }

    function renderBentoGrids() {
        document.getElementById('grid-umum-tu').innerHTML = umumTuItems
            .map((item, idx) => kartuSubBidang(item, idx, 'SUB', 'Bagian Umum & Tata Usaha'))
            .join('');

        document.getElementById('grid-sda').innerHTML = sdaItems
            .map((item, idx) => kartuSubBidang(item, idx, 'SDA', 'Bidang SDA'))
            .join('');

        document.getElementById('grid-ckps').innerHTML = ckpsItems
            .map((item, idx) => kartuSubBidang(item, idx, 'CKPS', 'Bidang CKPS'))
            .join('');

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
</script>
@endpush