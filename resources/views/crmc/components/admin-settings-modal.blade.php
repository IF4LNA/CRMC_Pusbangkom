<div id="adminSettingsModal" class="modal hidden">
    <div class="modal-card !max-w-md">
        <div class="modal-head">
            <h3 class="modal-title flex items-center gap-1.5">
                <i data-lucide="settings" class="w-4 h-4 text-slate-400"></i>
                Panel Kelola Admin CRMC
            </h3>
            <button onclick="closeAdminModal()" title="Tutup" class="icon-btn shrink-0">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="modal-body space-y-2">
            <button onclick="showToast('Fitur Tambah Sub-Kategori Baru')"
                    class="card click-card p-3 w-full text-left flex items-center justify-between">
                <span class="text-sm font-medium text-slate-800">+ Tambah Sub-Kategori / Bidang Baru</span>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
            </button>
            <button onclick="showToast('Kelola Akun Pegawai')"
                    class="card click-card p-3 w-full text-left flex items-center justify-between">
                <span class="text-sm font-medium text-slate-800">Kelola Pengguna (Hak Akses)</span>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
            </button>
        </div>

        <div class="modal-foot">
            <button onclick="closeAdminModal()" class="btn btn-dark w-full">Selesai</button>
        </div>
    </div>
</div>
