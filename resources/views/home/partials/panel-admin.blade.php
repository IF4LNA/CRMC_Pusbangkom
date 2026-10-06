{{--
    Panel admin: modal galeri saja.

    Struktur organisasi tidak lagi punya modal karena isinya kini berupa satu
    gambar yang diunggah lewat form di dalam section-nya sendiri (lihat
    home/struktur.blade.php). Jadi galeri tetap memakai modal, sedangkan
    gambar struktur memakai form di dalam section-nya sendiri.

    Modal ini hanya dirender bila pengguna adalah admin. Otorisasi tetap
    ditegur ulang di HomeController::wajibAdmin() pada setiap aksi, jadi
    menyembunyikan tombol di sini bukan satu-satunya pertahanan.
--}}

{{-- ===================== MODAL GALERI ===================== --}}
<div id="modalGaleri" class="modal hidden">
    <div class="modal-card !max-w-lg">
        <div class="modal-head">
            <div>
                <h3 class="modal-title" id="judulModalGaleri">Unggah Gambar</h3>
                <p class="page-sub mt-0.5" id="subjudulModalGaleri">
                    Sarana dan prasarana untuk carousel Beranda
                </p>
            </div>
            <button onclick="tutupModalGaleri()" title="Tutup" class="icon-btn shrink-0">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="formGaleri"
              action="{{ route('admin.beranda.galeri.simpan') }}"
              method="POST"
              enctype="multipart/form-data"
              class="flex-1 flex flex-col min-h-0">
            <div class="modal-body space-y-3">
                @csrf
                <input type="hidden" name="_method" id="metodeGaleri" value="">

                <div id="errorGaleri" class="hidden note !border-rose-200 !bg-rose-50 !text-rose-800">
                    <p class="text-[11px] font-bold flex items-center gap-1.5">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        Periksa kembali isian berikut
                    </p>
                    <ul id="daftarErrorGaleri" class="mt-1.5 space-y-0.5 text-[11px] text-rose-700"></ul>
                </div>

                <div>
                    <label class="label" for="inputGambar">File Gambar <span class="text-rose-500" id="tandaWajibGambar">*</span></label>
                    <input type="file" name="gambar" id="inputGambar" accept="image/jpeg,image/png,image/webp"
                           class="w-full text-[11px] text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:hover:bg-slate-200 file:text-[11px] file:font-bold file:text-slate-700 cursor-pointer border border-slate-300 rounded-lg p-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="hint" id="petunjukGambar">
                        JPG, PNG, atau WebP. Maksimal 4 MB. Gambar otomatis diperkecil
                        ke ukuran yang tetap tajam supaya halaman ringan.
                    </p>
                </div>

                <div>
                    <label class="label" for="inputJudul">Judul <span class="text-rose-500">*</span></label>
                    <input type="text" name="judul" id="inputJudul" required maxlength="150"
                           placeholder="mis. Ruang Rapat Bidang SDA"
                           class="input">
                </div>

                <div>
                    <label class="label" for="inputKeteranganGaleri">Keterangan</label>
                    <textarea name="keterangan" id="inputKeteranganGaleri" rows="2" maxlength="500"
                              placeholder="Deskripsi singkat (opsional)"
                              class="input"></textarea>
                </div>
            </div>

            <div class="modal-foot">
                <button type="button" onclick="tutupModalGaleri()" class="btn btn-quiet">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span id="labelTombolGaleri">Unggah</span>
                </button>
            </div>
        </form>
    </div>
</div>

<form id="formHapusGaleri" method="POST" action="" class="hidden">
    @csrf
    <input type="hidden" name="_method" value="DELETE">
</form>

@push('scripts')
<script>
    // ===================== PANEL ADMIN HALAMAN BERANDA =====================
    // Fungsi tampilkanError() dan segarkanIkon() sudah ada di layout utama.

    const FORM_GALERI_SIMPAN = @json(route('admin.beranda.galeri.simpan'));
    const FORM_GALERI_UBAH = @json(route('admin.beranda.galeri.ubah', ['id' => '__ID__']));
    const FORM_GALERI_HAPUS = @json(route('admin.beranda.galeri.hapus', ['id' => '__ID__']));

    // ---------- GALERI ----------

    function bukaModalGaleri(data) {
        const form = document.getElementById('formGaleri');
        form.reset();
        tampilkanError('errorGaleri', 'daftarErrorGaleri', null);

        const menyunting = data && data.id;

        form.action = menyunting ? FORM_GALERI_UBAH.replace('__ID__', data.id) : FORM_GALERI_SIMPAN;
        document.getElementById('metodeGaleri').value = menyunting ? 'PUT' : '';
        document.getElementById('judulModalGaleri').textContent =
            menyunting ? 'Ubah Gambar' : 'Unggah Gambar';
        document.getElementById('labelTombolGaleri').textContent =
            menyunting ? 'Simpan Perubahan' : 'Unggah';

        document.getElementById('inputJudul').value = data?.judul || '';
        document.getElementById('inputKeteranganGaleri').value = data?.keterangan || '';

        // Saat menyunting, file tidak wajib: kosongkan saja berarti gambar
        // lama dipertahankan.
        document.getElementById('inputGambar').required = !menyunting;
        document.getElementById('tandaWajibGambar').textContent = menyunting ? '' : '*';
        document.getElementById('petunjukGambar').textContent = menyunting
            ? 'Kosongkan bila tidak ingin mengganti gambar yang sekarang.'
            : 'JPG, PNG, atau WebP. Maksimal 4 MB. Gambar otomatis diperkecil ke ukuran yang tetap tajam.';

        segarkanIkon();
        document.getElementById('modalGaleri').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function tutupModalGaleri() {
        document.getElementById('modalGaleri').classList.add('hidden');
        document.body.style.overflow = '';
    }

    function konfirmasiHapusGaleri(id, judul) {
        if (!confirm('Hapus gambar "' + judul + '"?\n\nBerkas gambar juga akan dihapus dari server.')) return;
        const form = document.getElementById('formHapusGaleri');
        form.action = FORM_GALERI_HAPUS.replace('__ID__', id);
        form.submit();
    }

    // ---------- PENANGANAN ERROR VALIDASI DARI SERVER ----------

    @if ($errors->any())
        window.addEventListener('DOMContentLoaded', function () {
            // Galeri adalah satu-satunya modal yang tersisa, jadi error
            // validasi dari server selalu milik modal ini.
            tampilkanError('errorGaleri', 'daftarErrorGaleri', @json($errors->all()));
            document.getElementById('modalGaleri').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    @endif

    // Tekan Escape menutup modal yang sedang terbuka.
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        const modal = document.getElementById('modalGaleri');
        if (modal && !modal.classList.contains('hidden')) tutupModalGaleri();
    });

    // Klik pada area gelap di luar modal ikut menutupnya.
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('modalGaleri');
        if (!el) return;
        el.addEventListener('mousedown', function (e) {
            if (e.target === el) tutupModalGaleri();
        });
    });
</script>
@endpush
