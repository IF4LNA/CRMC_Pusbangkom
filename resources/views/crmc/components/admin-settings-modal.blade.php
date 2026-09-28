<div id="adminSettingsModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white w-full max-w-md rounded-3xl p-6 shadow-2xl border border-slate-200 space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                <i data-lucide="settings" class="w-5 h-5 text-amber-500"></i>
                Panel Kelola Admin Website CRMC
            </h3>
            <button onclick="closeAdminModal()" class="text-slate-400 hover:text-slate-800"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <div class="space-y-3 text-xs">
            <button onclick="showToast('Fitur Tambah Sub-Kategori Baru')" class="w-full p-3 bg-slate-50 hover:bg-amber-50 border border-slate-200 text-slate-800 font-semibold rounded-xl text-left flex items-center justify-between">
                <span>+ Tambah Sub-Kategori / Bidang Baru</span>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
            </button>
            <button onclick="showToast('Kelola Akun Pegawai')" class="w-full p-3 bg-slate-50 hover:bg-amber-50 border border-slate-200 text-slate-800 font-semibold rounded-xl text-left flex items-center justify-between">
                <span>Kelola Pengguna (Hak Akses)</span>
                <i data-lucide="chevron-right" class="w-4 h-4 text-slate-400"></i>
            </button>
        </div>
        <button onclick="closeAdminModal()" class="w-full py-2 bg-slate-900 text-white font-bold rounded-xl text-xs">Selesai</button>
    </div>
</div>