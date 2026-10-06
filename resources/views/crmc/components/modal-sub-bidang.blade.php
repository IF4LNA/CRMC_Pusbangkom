{{--
    Modal admin untuk menambah / menyunting sub-bidang.

    Satu form dipakai untuk dua mode; mode ditentukan lewat `_method` yang
    diisi JavaScript. Kolom `gambar_latar` opsional: saat menyunting,
    mengosongkannya berarti gambar lama dipertahankan.

    Rute sudah dibungkus middleware 'auth' dan diperiksa ulang di
    BidangController::wajibAdmin(), jadi menyembunyikan modal ini bukan
    satu-satunya pertahanan.
--}}
<div id="modalSubBidang" class="modal hidden">
    <div class="modal-card !max-w-lg">
        <div class="modal-head">
            <div>
                <h3 class="modal-title" id="judulModalSubBidang">Tambah Sub-Bidang</h3>
                <p class="page-sub mt-0.5" id="subjudulModalSubBidang">
                    Sub-bidang baru di bawah bidang yang dipilih
                </p>
            </div>
            <button onclick="tutupModalSubBidang()" title="Tutup" class="icon-btn shrink-0">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="formSubBidang"
              action="{{ route('bidang.sub-bidang.simpan') }}"
              method="POST"
              enctype="multipart/form-data"
              class="flex-1 flex flex-col min-h-0">
            <div class="modal-body space-y-3">
                @csrf
                {{-- Diisi lewat JS saat menyunting supaya tidak perlu satu
                     form terpisah per mode. --}}
                <input type="hidden" name="_method" id="metodeSubBidang" value="">

                <div id="errorSubBidang" class="hidden note !border-rose-200 !bg-rose-50 !text-rose-800">
                    <p class="text-[11px] font-bold flex items-center gap-1.5">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        Periksa kembali isian berikut
                    </p>
                    <ul id="daftarErrorSubBidang" class="mt-1.5 space-y-0.5 text-[11px] text-rose-700"></ul>
                </div>

                <div>
                    <label class="label" for="inputSubBidang">Bidang <span class="text-rose-500">*</span></label>
                    <select name="bidang_id" id="inputSubBidangBidang" required class="input">
                        @foreach ($daftarBidang as $b)
                            <option value="{{ $b->id }}">{{ $b->nama_bidang }}</option>
                        @endforeach
                    </select>
                    <p class="hint">Menentukan tab dan letak kartu pada dashboard.</p>
                </div>

                <div>
                    <label class="label" for="inputSubBidangNama">Nama Sub-Bidang <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_sub_menu" id="inputSubBidangNama" required maxlength="150"
                           placeholder="mis. Pengelolaan Aset dan Inventaris"
                           class="input">
                    <p class="hint">
                        Nama ini juga menjadi alamat halaman (/crmc/...),
                        jadi gunakan nama yang singkat dan jelas.
                    </p>
                </div>

                {{-- Pratinjau. Selalu tampil saat menyunting, termasuk ketika sub-bidang
                     belum punya gambar: admin perlu melihat kartu kosong seperti apa
                     hasilnya sebelum memilih berkas.

                     Styling-nya memakai kelas yang sama persis dengan kartu di
                     dashboard, supaya hasil pratinjau tidak berbeda dari yang
                     benar-benar tampil nanti. --}}
                <div id="pratinjauLatarSubBidang" class="hidden">
                    <p class="label !mb-1.5">Pratinjau Kartu</p>
                    <div class="sub-bidang-banner relative overflow-hidden rounded-lg h-24 p-3 flex flex-col justify-between">
                        <img id="gambarPratinjauLatar" src="" alt="Latar sub-bidang saat ini"
                             class="absolute inset-0 h-full w-full object-cover object-center">
                        <div id="overlayPratinjauLatar" class="absolute inset-0 bg-blue-950/75"></div>
                        <span class="relative sub-bidang-chip w-fit">SUB #1</span>
                        <div class="relative">
                            <p id="teksPratinjauLatar" class="text-sm font-bold text-white truncate">
                                Nama Sub-Bidang
                            </p>
                            <p class="text-[11px] text-blue-200 mt-0.5">Tampilan di dashboard</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="label" for="inputSubBidangGambar">
                        Gambar Latar <span class="text-rose-500" id="tandaWajibLatar">*</span>
                    </label>
                    <input type="file" name="gambar_latar" id="inputSubBidangGambar"
                           accept="image/jpeg,image/png,image/webp"
                           class="w-full text-[11px] text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:hover:bg-slate-200 file:text-[11px] file:font-bold file:text-slate-700 cursor-pointer border border-slate-300 rounded-lg p-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="hint" id="petunjukLatar">
                        JPG, PNG, atau WebP. Maksimal 4 MB. Gambar otomatis
                        diperkecil supaya halaman tetap ringan.
                    </p>
                </div>

                {{-- Hapus latar. Tanpa opsi ini, gambar yang pernah
                     terunggah tidak bisa dilepas lagi kecuali seluruh
                     sub-bidang dihapus. --}}
                <label id="opsiHapusLatarSubBidang"
                       class="hidden items-start gap-2.5 p-2.5 rounded-lg border border-rose-200 bg-rose-50 cursor-pointer select-none">
                    <input type="checkbox" name="hapus_gambar_latar" value="1" id="inputHapusLatarSubBidang"
                           class="w-4 h-4 rounded text-rose-600 focus:ring-rose-400 border-slate-300 shrink-0 mt-0.5">
                    <span class="text-[11px] text-rose-900">
                        <strong class="font-bold">Hapus gambar latar</strong><br>
                        Berkas gambar akan dihapus dari server dan kartu
                        kembali polos. Berbeda dari mengosongkan kolom di atas,
                        yang berarti mempertahankan gambar lama.
                    </span>
                </label>
            </div>

            <div class="modal-foot">
                <button type="button" onclick="tutupModalSubBidang()" class="btn btn-quiet">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span id="labelTombolSubBidang">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Form hapus dikirim lewat JS supaya tidak perlu satu form per kartu. --}}
<form id="formHapusSubBidang" method="POST" action="" class="hidden">
    @csrf
    <input type="hidden" name="_method" value="DELETE">
</form>

@push('scripts')
<script>
    // ===================== KELOLA SUB-BIDANG (DASHBOARD) =====================
    // Fungsi global (dideklarasikan di layout) yang dipakai ulang di sini:
    //   tampilkanError(idBlock, idList, pesan)  menampilkan pesan validasi
    //   segarkanIkon()                         menggambar ulang ikon lucide

    const FORM_SUBBIDANG_SIMPAN = @json(route('bidang.sub-bidang.simpan'));
    const FORM_SUBBIDANG_UBAH = @json(route('bidang.sub-bidang.ubah', ['id' => '__ID__']));
    const FORM_SUBBIDANG_HAPUS = @json(route('bidang.sub-bidang.hapus', ['id' => '__ID__']));

    /**
     * Buka modal dari kartu sub-bidang.
     *
     * Data diambil dari atribut data-* tombol, BUKAN disisipkan ke onclick
     * sebagai JSON. Atribut onclick yang memuat JSON akan terpotong pada
     * kutip pertama, sehingga tombolnya mati tanpa error. Atribut data-* aman
     * karena isinya dibaca sebagai teks biasa oleh dataset.
     */
    function bukaModalSubBidangSub(tombol) {
        bukaModalSubBidang({
            id: tombol.dataset.subId ? parseInt(tombol.dataset.subId, 10) : null,
            bidang_id: parseInt(tombol.dataset.subBidang, 10),
            nama_sub_menu: tombol.dataset.subNama || '',
            punya_gambar: tombol.dataset.subPunyaGambar === '1',
            gambar_latar_url: tombol.dataset.subGambar || null,
        });
    }

    function bukaModalSubBidang(data) {
        const form = document.getElementById('formSubBidang');
        form.reset();
        tampilkanError('errorSubBidang', 'daftarErrorSubBidang', null);

        const menyunting = data && data.id;

        form.action = menyunting
            ? FORM_SUBBIDANG_UBAH.replace('__ID__', data.id)
            : FORM_SUBBIDANG_SIMPAN;
        document.getElementById('metodeSubBidang').value = menyunting ? 'PUT' : '';
        document.getElementById('judulModalSubBidang').textContent =
            menyunting ? 'Ubah Sub-Bidang' : 'Tambah Sub-Bidang';
        document.getElementById('labelTombolSubBidang').textContent =
            menyunting ? 'Simpan Perubahan' : 'Simpan';

        document.getElementById('inputSubBidangBidang').value = data?.bidang_id || '';
        document.getElementById('inputSubBidangNama').value = data?.nama_sub_menu || '';

        // Saat menyunting, file tidak wajib: kosongkan saja berarti gambar
        // lama dipertahankan.
        const inputGambar = document.getElementById('inputSubBidangGambar');
        inputGambar.required = !menyunting;
        document.getElementById('tandaWajibLatar').textContent = menyunting ? '' : '*';
        document.getElementById('petunjukLatar').textContent = menyunting
            ? (data?.punya_gambar
                ? 'Ada gambar latar. Kosongkan bila tidak ingin menggantinya.'
                : 'Belum ada gambar latar. Biarkan kosong atau pilih gambar.')
            : 'JPG, PNG, atau WebP. Maksimal 4 MB. Gambar otomatis diperkecil supaya halaman tetap ringan.';

        // Pratinjau kartu. Saat menyunting, pratinjau selalu ditampilkan â€”
        // termasuk untuk sub-bidang yang belum punya gambar, karena itu
        // justru keadaan yang perlu Dicek sebelum memilih berkas.
        const blokPratinjau = document.getElementById('pratinjauLatarSubBidang');
        const labelHapus = document.getElementById('opsiHapusLatarSubBidang');
        const imgPratinjau = document.getElementById('gambarPratinjauLatar');

        document.getElementById('teksPratinjauLatar').textContent =
            data?.nama_sub_menu || 'Nama Sub-Bidang';

        if (data?.punya_gambar && data?.gambar_latar_url) {
            imgPratinjau.src = data.gambar_latar_url;
        } else {
            imgPratinjau.removeAttribute('src');
        }

        if (menyunting) {
            blokPratinjau.classList.remove('hidden');
        } else {
            blokPratinjau.classList.add('hidden');
        }

        // Opsi "hapus gambar latar" hanya relevan bila memang ada gambar.
        if (menyunting && data?.punya_gambar) {
            labelHapus.classList.remove('hidden');
            labelHapus.classList.add('flex');
        } else {
            labelHapus.classList.add('hidden');
            labelHapus.classList.remove('flex');
            document.getElementById('inputHapusLatarSubBidang').checked = false;
        }

        // Memilih berkas baru membuat pilihan "hapus" tidak relevan.
        inputGambar.onchange = function () {
            document.getElementById('inputHapusLatarSubBidang').checked = false;
            if (this.files && this.files[0]) tampilkanPratinjauLokal(this.files[0]);
        };

        segarkanIkon();
        document.getElementById('modalSubBidang').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        // Fokus ke kolom nama, bukan ke input berkas: memfokuskan input
        // berkas langsung memunculkan dialogchoose file di sebagian browser,
        // sehingga admin tidak sempat membaca pratinjau lebih dulu.
        document.getElementById('inputSubBidangNama').focus();
    }

    function tutupModalSubBidang() {
        // Lepas object URL pratinjau supaya tidak menggantung di memori
        // selama halaman tetap terbuka.
        if (objectUrlPratinjau) {
            URL.revokeObjectURL(objectUrlPratinjau);
            objectUrlPratinjau = null;
        }

        document.getElementById('modalSubBidang').classList.add('hidden');
        document.body.style.overflow = '';
    }

    /**
     * Tampilkan berkas yang baru dipilih di kotak pratinjau.
     *
     * Memakai object URL supaya admin langsung melihat hasilnya tanpa
     * menunggu upload. Objek URL ini dilepas lagi supaya tidak menumpuk
     * di memori selama modal terbuka.
     */
    let objectUrlPratinjau = null;

    function tampilkanPratinjauLokal(berkas) {
        if (objectUrlPratinjau) {
            URL.revokeObjectURL(objectUrlPratinjau);
        }
        objectUrlPratinjau = URL.createObjectURL(berkas);

        const blok = document.getElementById('pratinjauLatarSubBidang');
        blok.classList.remove('hidden');
        document.getElementById('gambarPratinjauLatar').src = objectUrlPratinjau;
    }

    function konfirmasiHapusSubBidang(id, nama) {
        const pesan =
            'Hapus sub-bidang "' + nama + '"?\n\n' +
            'Seluruh dokumen, lampiran, dan gambar latar milik sub-bidang ini ' +
            'akan ikut terhapus dari server. Tindakan ini tidak dapat dibatalkan.';
        if (!confirm(pesan)) return;

        const form = document.getElementById('formHapusSubBidang');
        form.action = FORM_SUBBIDANG_HAPUS.replace('__ID__', id);
        form.submit();
    }

    // Tekan Escape menutup modal yang sedang terbuka.
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        const modal = document.getElementById('modalSubBidang');
        if (modal && !modal.classList.contains('hidden')) tutupModalSubBidang();
    });

    // Klik pada area gelap di luar modal ikut menutupnya.
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('modalSubBidang');
        if (!el) return;
        el.addEventListener('mousedown', function (e) {
            if (e.target === el) tutupModalSubBidang();
        });

        @if ($errors->any())
            tampilkanError('errorSubBidang', 'daftarErrorSubBidang', @json($errors->all()));
            el.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        @endif
    });
</script>
@endpush
