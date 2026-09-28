<div id="crmcModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
    <div class="bg-white w-full max-w-3xl max-h-[92vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-slate-100">
        
        <!-- MODAL HEADER -->
        <div class="bg-slate-900 text-white p-5 sm:p-6 flex items-start justify-between border-b border-slate-800">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center text-slate-950 font-bold shrink-0">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <span id="modalParentCategory" class="text-[11px] font-bold text-amber-400 uppercase tracking-widest">BAGIAN UMUM PROGRAM & TATA USAHA</span>
                    <h3 id="modalSubTitle" class="text-lg sm:text-xl font-extrabold text-white">Form Kelola Dokumen CRMC</h3>
                </div>
            </div>
            <button onclick="closeCRMCModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- CALLOUT LINK TO NEW FULL PAGE -->
        <div class="bg-blue-50/80 px-6 py-3 border-b border-blue-100 flex flex-wrap items-center justify-between gap-2 text-xs">
            <div class="flex items-center space-x-2 text-blue-900">
                <i data-lucide="info" class="w-4 h-4 text-blue-700 shrink-0"></i>
                <span class="font-medium">Ingin melihat rincian instrumen lengkap?</span>
            </div>
            <a id="modalFullPageBtn" href="#" class="inline-flex items-center space-x-1.5 px-3 py-1 bg-blue-900 hover:bg-blue-950 text-white font-bold rounded-lg transition shadow-sm">
                <span>Buka Halaman Baru 8 Komponen</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <!-- FORM CONTENT -->
        <div class="flex-1 overflow-y-auto p-5 sm:p-6">
            <form id="crmcModalForm" onsubmit="handleFormSubmit(event)" class="space-y-4 text-xs">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">1. Identitas Pegawai / PIC Terkait *</label>
                        <input type="text" id="inputModalPegawai" required value="Muhammad Fajar Syaffiqri" class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">2. Kode Risk Register Spesifik *</label>
                        <input type="text" id="inputModalRegister" required value="RR-CRMC-2026-V1" class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">3. Unggah Dokumen SOP (.pdf)</label>
                        <input type="file" accept=".pdf" class="w-full p-2 bg-slate-50 rounded-xl border border-slate-300 text-slate-600">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">4. Unggah Formulir Daftar Periksa (.pdf / .xlsx)</label>
                        <input type="file" accept=".pdf,.xlsx,.xls" class="w-full p-2 bg-slate-50 rounded-xl border border-slate-300 text-slate-600">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">5. Jadwal Rencana Pelaksanaan</label>
                        <input type="text" id="inputModalJadwal" value="Tahun Anggaran 2026 (Triwulan I - IV)" class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">6. Tautan Bukti Pelaksanaan (URL Cloud)</label>
                        <input type="url" id="inputModalBukti" value="https://drive.pupr.go.id/s/crmc-2026" class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">7. Status Residu Risiko *</label>
                        <select id="inputModalResidu" class="w-full p-2.5 rounded-xl border border-slate-300 font-semibold bg-white focus:ring-2 focus:ring-amber-500">
                            <option value="rendah" selected>Rendah (Low Risk)</option>
                            <option value="sedang">Sedang (Medium Risk)</option>
                            <option value="tinggi">Tinggi (High Risk)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">8. Evaluasi & Rencana Perbaikan</label>
                        <textarea id="inputModalEvaluasi" rows="2" class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500">Pengendalian berjalan efektif dan terpantau secara kontinu.</textarea>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-2">
                    <button type="button" onclick="closeCRMCModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold shadow-sm transition">Simpan Dokumen CRMC</button>
                </div>
            </form>
        </div>

        <!-- MODAL FOOTER -->
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex items-center justify-between text-xs">
            <span class="text-slate-500">Status Akses: 
                @auth
                    <strong class="{{ Auth::user()->isAdmin() ? 'text-amber-600 font-bold' : 'text-slate-800 font-semibold' }}">
                        {{ Auth::user()->isAdmin() ? 'Administrator (Akses Penuh)' : 'Pegawai (Read/Submit)' }}
                    </strong>
                @else
                    <strong class="text-slate-500">Tamu / Publik</strong>
                @endauth
            </span>
            <div class="flex items-center space-x-2">
                @auth
                    @if(Auth::user()->isAdmin())
                        <button onclick="deleteCRMCItem()" class="px-3 py-1.5 bg-rose-600 text-white font-semibold rounded-lg hover:bg-rose-700 transition">Hapus Item (Admin)</button>
                    @endif
                @endauth
                <button onclick="closeCRMCModal()" class="px-4 py-1.5 bg-slate-900 text-white font-semibold rounded-lg hover:bg-slate-800 transition">Tutup</button>
            </div>
        </div>

    </div>
</div>