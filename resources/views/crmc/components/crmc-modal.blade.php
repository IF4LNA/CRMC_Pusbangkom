<div id="crmcModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
    <div class="bg-white w-full max-w-4xl max-h-[92vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-slate-100">
        
        <div class="bg-slate-900 text-white p-5 sm:p-6 flex items-start justify-between border-b border-slate-800">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center text-slate-950 font-bold shrink-0">
                    <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <span id="modalParentCategory" class="text-[11px] font-bold text-amber-400 uppercase tracking-widest">BAGIAN UMUM PROGRAM & TATA USAHA</span>
                    <h3 id="modalSubTitle" class="text-lg sm:text-xl font-extrabold text-white">Manajemen Risiko</h3>
                </div>
            </div>
            <button onclick="closeCRMCModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <div class="bg-slate-100 px-6 py-2.5 border-b border-slate-200 flex items-center justify-between text-xs">
            <div class="flex space-x-2">
                <button id="modalTab1" onclick="switchModalTab('view')" class="px-3 py-1.5 rounded-lg font-bold bg-white text-slate-900 shadow-sm border border-slate-200">1. Lihat 8 Komponen CRMC</button>
                <button id="modalTab2" onclick="switchModalTab('form')" class="px-3 py-1.5 rounded-lg font-semibold text-slate-600 hover:text-slate-900">2. Form Input / Edit Dokumen</button>
            </div>
            <span class="hidden sm:inline text-slate-400 font-medium">Tahun Anggaran: 2026</span>
        </div>

        <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-6">

            <!-- 8 Komponen View -->
            <div id="modalViewContent" class="space-y-4">
                <div class="bg-amber-50/70 border border-amber-200/80 rounded-2xl p-4 flex items-start space-x-3">
                    <i data-lucide="info" class="w-5 h-5 text-amber-600 shrink-0 mt-0.5"></i>
                    <p class="text-xs text-amber-900 leading-relaxed">Berikut adalah <strong>8 Komponen Wajib CRMC</strong> yang terintegrasi secara real-time.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <span class="text-[10px] font-bold text-blue-900 bg-blue-100 px-2 py-0.5 rounded">Komponen 1</span>
                        <h4 class="text-xs font-bold text-slate-900 mt-2">1. Identitas Pegawai Terkait</h4>
                        <p class="text-xs text-slate-600 bg-white p-2.5 rounded-xl border border-slate-200 mt-2 font-mono">Muhammad Fajar Syaffiqri - System Administrator</p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <span class="text-[10px] font-bold text-blue-900 bg-blue-100 px-2 py-0.5 rounded">Komponen 2</span>
                        <h4 class="text-xs font-bold text-slate-900 mt-2">2. Risk Register Spesifik Acuan</h4>
                        <p class="text-xs text-slate-600 bg-white p-2.5 rounded-xl border border-slate-200 mt-2">RR-CRMC-2026-V1</p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <span class="text-[10px] font-bold text-blue-900 bg-blue-100 px-2 py-0.5 rounded">Komponen 3</span>
                        <h4 class="text-xs font-bold text-slate-900 mt-2">3. Standar Operasional Prosedur (SOP)</h4>
                        <div class="flex items-center justify-between bg-white p-2.5 rounded-xl border border-slate-200 mt-2 text-xs">
                            <span class="text-slate-700 font-medium">SOP-CRMC-PUPR.pdf</span>
                            <a href="#" class="text-blue-900 font-bold hover:underline">Unduh</a>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <span class="text-[10px] font-bold text-blue-900 bg-blue-100 px-2 py-0.5 rounded">Komponen 4</span>
                        <h4 class="text-xs font-bold text-slate-900 mt-2">4. Formulir Pengendalian</h4>
                        <p class="text-xs text-slate-600 bg-white p-2.5 rounded-xl border border-slate-200 mt-2">Checklist Kepatuhan Dokumen (100% Sesuai)</p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <span class="text-[10px] font-bold text-blue-900 bg-blue-100 px-2 py-0.5 rounded">Komponen 5</span>
                        <h4 class="text-xs font-bold text-slate-900 mt-2">5. Jadwal Rencana Pelaksanaan</h4>
                        <p class="text-xs text-slate-600 bg-white p-2.5 rounded-xl border border-slate-200 mt-2">Tahun Anggaran 2026 (Triwulan I - IV)</p>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <span class="text-[10px] font-bold text-blue-900 bg-blue-100 px-2 py-0.5 rounded">Komponen 6</span>
                        <h4 class="text-xs font-bold text-slate-900 mt-2">6. Tautan Bukti Pelaksanaan</h4>
                        <div class="bg-white p-2.5 rounded-xl border border-slate-200 mt-2">
                            <a href="#" class="text-xs text-blue-800 font-semibold underline truncate block">https://drive.pupr.go.id/s/crmc-2026</a>
                        </div>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <span class="text-[10px] font-bold text-blue-900 bg-blue-100 px-2 py-0.5 rounded">Komponen 7</span>
                        <h4 class="text-xs font-bold text-slate-900 mt-2">7. Status Residu Risiko</h4>
                        <div class="mt-2"><span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">RENDAH (Low Risk)</span></div>
                    </div>
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <span class="text-[10px] font-bold text-blue-900 bg-blue-100 px-2 py-0.5 rounded">Komponen 8</span>
                        <h4 class="text-xs font-bold text-slate-900 mt-2">8. Evaluasi & Rencana Perbaikan</h4>
                        <p class="text-xs text-slate-600 bg-white p-2.5 rounded-xl border border-slate-200 mt-2">Pengendalian berjalan efektif dan terpantau secara kontinu.</p>
                    </div>
                </div>
            </div>

            <!-- Form Input -->
            <div id="modalFormContent" class="space-y-4 hidden">
                <form onsubmit="handleFormSubmit(event)" class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">1. Identitas Pegawai Terkait *</label>
                            <input type="text" required value="Muhammad Fajar Syaffiqri" class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">2. Kode Risk Register Spesifik *</label>
                            <input type="text" required value="RR-CRMC-2026-V1" class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">3. Unggah Dokumen SOP (.pdf)</label>
                            <input type="file" class="w-full p-2 bg-slate-50 rounded-xl border border-slate-300">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">4. Unggah Formulir Daftar Periksa</label>
                            <input type="file" class="w-full p-2 bg-slate-50 rounded-xl border border-slate-300">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">7. Status Residu Risiko *</label>
                            <select class="w-full p-2.5 rounded-xl border border-slate-300 font-semibold bg-white">
                                <option value="rendah" selected>Rendah</option>
                                <option value="sedang">Sedang</option>
                                <option value="tinggi">Tinggi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">8. Evaluasi & Rencana Perbaikan</label>
                            <textarea rows="2" class="w-full p-2.5 rounded-xl border border-slate-300">Pengendalian berjalan efektif.</textarea>
                        </div>
                    </div>
                    <div class="pt-3 border-t border-slate-200 flex justify-end space-x-2">
                        <button type="button" onclick="switchModalTab('view')" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-semibold">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold shadow-sm">Simpan Dokumen CRMC</button>
                    </div>
                </form>
            </div>

        </div>

        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200 flex items-center justify-between text-xs">
            <span class="text-slate-500">Status Akses: <strong id="modalAccessStatus" class="text-slate-800">Pegawai (Read/Submit)</strong></span>
            <div class="flex items-center space-x-2">
                <button id="adminDeleteBtn" onclick="deleteCRMCItem()" class="hidden px-3 py-1.5 bg-rose-600 text-white font-semibold rounded-lg hover:bg-rose-700">Hapus Item (Admin)</button>
                <button onclick="closeCRMCModal()" class="px-4 py-1.5 bg-slate-900 text-white font-semibold rounded-lg">Tutup</button>
            </div>
        </div>

    </div>
</div>