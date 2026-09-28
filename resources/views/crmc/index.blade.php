@extends('layouts.app')

@section('title', 'Dashboard Utama CRMC')

@section('content')
<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- Mobile Search Bar -->
    <div class="mb-4 md:hidden">
        <div class="relative">
            <input type="text" oninput="handleSearch(this.value)" placeholder="Cari bidang, SOP, dokumen..." 
                   class="w-full bg-white text-sm text-slate-800 placeholder-slate-400 pl-9 pr-4 py-2.5 rounded-xl border border-slate-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500">
            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
        </div>
    </div>

    <!-- SEARCH RESULTS CONTAINER -->
    <div id="searchResultsContainer" class="hidden mb-6 bg-white rounded-2xl p-6 border border-slate-200 shadow-lg">
        <div class="flex items-center justify-between mb-4 border-b pb-3">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <i data-lucide="search" class="w-4 h-4 text-amber-500"></i>
                Hasil Pencarian Sub-Bidang & Dokumen CRMC
            </h3>
            <button onclick="clearSearch()" class="text-xs text-slate-500 hover:text-slate-800">Tutup</button>
        </div>
        <div id="searchResultsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"></div>
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
    function renderBentoGrids() {
        document.getElementById('grid-umum-tu').innerHTML = umumTuItems.map((item, idx) => `
            <div onclick="navigateToCRMC('${item}')" class="bento-card cursor-pointer bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-amber-400 transition flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-bold text-slate-400 group-hover:text-amber-600">SUB #${idx + 1}</span>
                        <button onclick="event.stopPropagation(); openCRMCModal('${item}', 'Bagian Umum & Tata Usaha')" title="Form Input / Edit" class="p-1 text-slate-400 hover:text-amber-600 rounded-lg hover:bg-amber-50 transition">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                    <h4 class="font-bold text-slate-900 text-xs sm:text-sm group-hover:text-blue-900 transition leading-snug">${item}</h4>
                </div>
                <div class="mt-4 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span class="bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> 8 Komponen
                    </span>
                    <span class="inline-flex items-center gap-0.5 text-blue-900 font-bold group-hover:translate-x-0.5 transition">
                        Buka <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </span>
                </div>
            </div>
        `).join('');

        document.getElementById('grid-sda').innerHTML = sdaItems.map((item, idx) => `
            <div onclick="navigateToCRMC('${item}')" class="bento-card cursor-pointer bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-cyan-400 transition flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-bold text-cyan-700 bg-cyan-50 px-2 py-0.5 rounded">SDA #${idx + 1}</span>
                        <button onclick="event.stopPropagation(); openCRMCModal('${item}', 'Bidang SDA')" title="Form Input / Edit" class="p-1 text-slate-400 hover:text-cyan-600 rounded-lg hover:bg-cyan-50 transition">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                    <h4 class="font-bold text-slate-900 text-sm group-hover:text-cyan-900 transition leading-snug">${item}</h4>
                </div>
                <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span class="bg-cyan-50 text-cyan-800 px-2 py-0.5 rounded font-semibold">8 Komponen</span>
                    <span class="inline-flex items-center gap-0.5 text-cyan-800 font-bold group-hover:translate-x-0.5 transition">
                        Buka <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </span>
                </div>
            </div>
        `).join('');

        document.getElementById('grid-ckps').innerHTML = ckpsItems.map((item, idx) => `
            <div onclick="navigateToCRMC('${item}')" class="bento-card cursor-pointer bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-indigo-400 transition flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded">CKPS #${idx + 1}</span>
                        <button onclick="event.stopPropagation(); openCRMCModal('${item}', 'Bidang CKPS')" title="Form Input / Edit" class="p-1 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 transition">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                    <h4 class="font-bold text-slate-900 text-sm group-hover:text-indigo-900 transition leading-snug">${item}</h4>
                </div>
                <div class="mt-5 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span class="bg-indigo-50 text-indigo-800 px-2 py-0.5 rounded font-semibold">8 Komponen</span>
                    <span class="inline-flex items-center gap-0.5 text-indigo-800 font-bold group-hover:translate-x-0.5 transition">
                        Buka <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </span>
                </div>
            </div>
        `).join('');

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
</script>
@endpush