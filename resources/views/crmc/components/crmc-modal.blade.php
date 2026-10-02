<div id="crmcModal" class="modal hidden">
    <div class="modal-card !max-w-3xl">

        <div class="modal-head">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-blue-50 text-blue-700 rounded-md flex items-center justify-center shrink-0">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                </div>
                <div class="min-w-0">
                    <span id="modalParentCategory" class="eyebrow">Bagian Umum Program &amp; Tata Usaha</span>
                    <h3 id="modalSubTitle" class="modal-title mt-0.5">Form Kelola Dokumen CRMC</h3>
                </div>
            </div>
            <button onclick="closeCRMCModal()" title="Tutup" class="icon-btn shrink-0">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        {{-- Ajakan membuka halaman rincian 8 komponen --}}
        <div class="px-4 py-2.5 border-b border-blue-100 bg-blue-50/70 flex flex-wrap items-center justify-between gap-2 text-[11px]">
            <div class="flex items-center gap-1.5 text-blue-900">
                <i data-lucide="info" class="w-3.5 h-3.5 shrink-0"></i>
                <span class="font-medium">Ingin melihat rincian instrumen lengkap?</span>
            </div>
            <a id="modalFullPageBtn" href="#" class="btn btn-sm btn-dark">
                <span>Buka Halaman 8 Komponen</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="flex-1 flex flex-col min-h-0">
            <div class="modal-body">
                <form id="crmcModalForm" onsubmit="handleFormSubmit(event)" class="space-y-3">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="label" for="inputModalPegawai">1. Identitas Pegawai / PIC Terkait *</label>
                            <input type="text" id="inputModalPegawai" required value="Muhammad Fajar Syaffiqri" class="input">
                        </div>
                        <div>
                            <label class="label" for="inputModalRegister">2. Kode Risk Register Spesifik *</label>
                            <input type="text" id="inputModalRegister" required value="RR-CRMC-2026-V1" class="input">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="label">3. Unggah Dokumen SOP (.pdf)</label>
                            <input type="file" accept=".pdf" class="input !py-1.5">
                        </div>
                        <div>
                            <label class="label">4. Unggah Formulir Daftar Periksa (.pdf / .xlsx)</label>
                            <input type="file" accept=".pdf,.xlsx,.xls" class="input !py-1.5">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="label" for="inputModalJadwal">5. Jadwal Rencana Pelaksanaan</label>
                            <input type="text" id="inputModalJadwal" value="Tahun Anggaran 2026 (Triwulan I - IV)" class="input">
                        </div>
                        <div>
                            <label class="label" for="inputModalBukti">6. Tautan Bukti Pelaksanaan (URL Cloud)</label>
                            <input type="url" id="inputModalBukti" value="https://drive.pupr.go.id/s/crmc-2026" class="input">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="label" for="inputModalResidu">7. Status Residu Risiko *</label>
                            <select id="inputModalResidu" class="input">
                                <option value="rendah" selected>Rendah (Low Risk)</option>
                                <option value="sedang">Sedang (Medium Risk)</option>
                                <option value="tinggi">Tinggi (High Risk)</option>
                            </select>
                        </div>
                        <div>
                            <label class="label" for="inputModalEvaluasi">8. Evaluasi &amp; Rencana Perbaikan</label>
                            <textarea id="inputModalEvaluasi" rows="2" class="input">Pengendalian berjalan efektif dan terpantau secara kontinu.</textarea>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-foot">
                <span class="mr-auto text-[11px] text-slate-500">Status Akses:
                    @auth
                        <strong class="{{ Auth::user()->isAdmin() ? 'text-blue-700 font-semibold' : 'text-slate-700 font-semibold' }}">
                            {{ Auth::user()->isAdmin() ? 'Administrator (Akses Penuh)' : 'Pegawai (Read/Submit)' }}
                        </strong>
                    @else
                        <strong class="text-slate-500">Tamu / Publik</strong>
                    @endauth
                </span>
                @auth
                    @if(Auth::user()->isAdmin())
                        <button onclick="deleteCRMCItem()" class="btn btn-danger">Hapus Item (Admin)</button>
                    @endif
                @endauth
                <button type="button" onclick="closeCRMCModal()" class="btn btn-quiet">Batal</button>
                <button onclick="closeCRMCModal()" class="btn btn-dark">Tutup</button>
                {{-- Atribut form="..." menautkan tombol ini ke form di atas,
                     sehingga tetap menjadi tombol submit walau letaknya di
                     luar elemen <form>. --}}
                <button type="submit" form="crmcModalForm" class="btn btn-primary">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan Dokumen CRMC
                </button>
            </div>
        </div>
    </div>
</div>
