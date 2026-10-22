{{--
    Modal admin untuk mengganti banner pada panel "Navigasi Bidang".

    Satu form dipakai untuk semua tab bidang. Bidang yang sedang disunting
    ditentukan lewat atribut data-* pada tombol "Ganti Banner", lalu action
    form diarahkan ke rute bidang tersebut. Banner boleh diganti atau
    dikembalikan ke gambar bawaan (opsi hapus).

    Rute sudah dibungkus middleware 'auth' dan diperiksa ulang di
    BidangController::wajibAdmin(), jadi menyembunyikan modal ini bukan
    satu-satunya pertahanan.
--}}
<div id="modalBannerBidang" class="modal hidden">
    <div class="modal-card !max-w-lg">
        <div class="modal-head">
            <div>
                <h3 class="modal-title">Ganti Banner Navigasi</h3>
                <p class="page-sub mt-0.5" id="subjudulModalBannerBidang">
                    Pilih gambar untuk banner bidang ini
                </p>
            </div>
            <button onclick="tutupModalBannerBidang()" title="Tutup" class="icon-btn shrink-0">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="formBannerBidang"
              action=""
              method="POST"
              enctype="multipart/form-data"
              class="flex-1 flex flex-col min-h-0">
            <div class="modal-body space-y-3">
                @csrf

                {{-- Dipakai hanya untuk membuka kembali modal bidang yang
                     sama bila validasi gagal. --}}
                <input type="hidden" name="_bidang_id" id="inputBannerBidangId" value="">

                <div id="errorBannerBidang" class="hidden note !border-rose-200 !bg-rose-50 !text-rose-800">
                    <p class="text-[11px] font-bold flex items-center gap-1.5">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        Periksa kembali isian berikut
                    </p>
                    <ul id="daftarErrorBannerBidang" class="mt-1.5 space-y-0.5 text-[11px] text-rose-700"></ul>
                </div>

                {{-- Pratinjau memakai gaya yang sama dengan banner dashboard:
                     teks di atas gambar, tanpa overlay gelap. --}}
                <div>
                    <p class="label !mb-1.5">Pratinjau Banner</p>
                    <div class="relative overflow-hidden rounded-lg h-28 bg-blue-950 border border-blue-900">
                        <img id="gambarPratinjauBanner" src="" alt="Pratinjau banner bidang"
                             class="absolute inset-0 h-full w-full object-cover object-center">
                        <div class="relative h-full flex flex-col justify-center px-4 drop-shadow-lg">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-blue-900 text-blue-200 rounded text-[9px] font-bold border border-blue-800 uppercase tracking-wider w-fit">
                                <i data-lucide="map" class="w-3 h-3"></i>
                                Navigasi Bidang
                            </span>
                            <p id="teksPratinjauBanner" class="text-sm font-bold text-white mt-1.5 truncate">
                                Nama Bidang
                            </p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="label" for="inputBannerBidang">Gambar Banner</label>
                    <input type="file" name="gambar" id="inputBannerBidang"
                           accept="image/jpeg,image/png,image/webp"
                           class="w-full text-[11px] text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:hover:bg-slate-200 file:text-[11px] file:font-bold file:text-slate-700 cursor-pointer border border-slate-300 rounded-lg p-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="hint" id="petunjukBannerBidang">
                        JPG, PNG, atau WebP. Maksimal 4 MB. Gambar otomatis
                        diperkecil supaya halaman tetap ringan.
                    </p>
                </div>

                <label id="opsiHapusBannerBidang"
                       class="hidden items-start gap-2.5 p-2.5 rounded-lg border border-rose-200 bg-rose-50 cursor-pointer select-none">
                    <input type="checkbox" name="hapus_gambar" value="1" id="inputHapusBannerBidang"
                           class="w-4 h-4 rounded text-rose-600 focus:ring-rose-400 border-slate-300 shrink-0 mt-0.5">
                    <span class="text-[11px] text-rose-900">
                        <strong class="font-bold">Hapus banner kustom</strong><br>
                        Banner kembali memakai foto bawaan PUSBANGKOM. Berkas
                        gambar yang pernah diunggah akan dihapus dari server.
                    </span>
                </label>
            </div>

            <div class="modal-foot">
                <button type="button" onclick="tutupModalBannerBidang()" class="btn btn-quiet">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    Simpan Banner
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // ===================== BANNER NAVIGASI BIDANG =====================
    // Fungsi global (dideklarasikan di layout) yang dipakai ulang di sini:
    //   tampilkanError(idBlock, idList, pesan)  menampilkan pesan validasi
    //   segarkanIkon()                         menggambar ulang ikon lucide

    const FORM_BANNER_BIDANG = @json(route('bidang.banner.simpan', ['id' => '__ID__']));

    /** Buka modal dari tombol "Ganti Banner" di panel navigasi bidang. */
    function bukaModalBannerBidang(tombol) {
        const form = document.getElementById('formBannerBidang');
        form.reset();
        tampilkanError('errorBannerBidang', 'daftarErrorBannerBidang', null);

        form.action = FORM_BANNER_BIDANG.replace('__ID__', tombol.dataset.bannerId);
        document.getElementById('inputBannerBidangId').value = tombol.dataset.bannerId;

        const punyaGambar = tombol.dataset.bannerPunyaGambar === '1';
        const urlGambar = tombol.dataset.bannerGambar || '';

        document.getElementById('subjudulModalBannerBidang').textContent =
            'Banner untuk ' + (tombol.dataset.bannerNama || 'bidang ini');
        document.getElementById('teksPratinjauBanner').textContent =
            tombol.dataset.bannerNama || 'Nama Bidang';

        const imgPratinjau = document.getElementById('gambarPratinjauBanner');
        if (urlGambar) {
            imgPratinjau.src = urlGambar;
        } else {
            imgPratinjau.src = @json(asset('images/crmc_hero.jpg'));
        }

        // Opsi hapus hanya relevan bila bidang sudah punya banner kustom.
        const labelHapus = document.getElementById('opsiHapusBannerBidang');
        labelHapus.classList.toggle('hidden', !punyaGambar);
        labelHapus.classList.toggle('flex', punyaGambar);
        document.getElementById('inputHapusBannerBidang').checked = false;

        document.getElementById('petunjukBannerBidang').textContent = punyaGambar
            ? 'Kosongkan bila tidak ingin mengganti banner saat ini.'
            : 'Bidang ini masih memakai foto bawaan. Pilih gambar untuk menggantinya.';

        const inputGambar = document.getElementById('inputBannerBidang');
        inputGambar.onchange = function () {
            document.getElementById('inputHapusBannerBidang').checked = false;
            if (this.files && this.files[0]) tampilkanPratinjauBannerLokal(this.files[0]);
        };

        segarkanIkon();
        document.getElementById('modalBannerBidang').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function tutupModalBannerBidang() {
        if (objectUrlBanner) {
            URL.revokeObjectURL(objectUrlBanner);
            objectUrlBanner = null;
        }

        document.getElementById('modalBannerBidang').classList.add('hidden');
        document.body.style.overflow = '';
    }

    /** Tampilkan berkas yang baru dipilih sebelum benar-benar diunggah. */
    let objectUrlBanner = null;

    function tampilkanPratinjauBannerLokal(berkas) {
        if (objectUrlBanner) {
            URL.revokeObjectURL(objectUrlBanner);
        }
        objectUrlBanner = URL.createObjectURL(berkas);
        document.getElementById('gambarPratinjauBanner').src = objectUrlBanner;
    }

    // Tekan Escape menutup modal yang sedang terbuka.
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        const modal = document.getElementById('modalBannerBidang');
        if (modal && !modal.classList.contains('hidden')) tutupModalBannerBidang();
    });

    // Klik pada area gelap di luar modal ikut menutupnya.
    document.addEventListener('DOMContentLoaded', function () {
        const el = document.getElementById('modalBannerBidang');
        if (!el) return;
        el.addEventListener('mousedown', function (e) {
            if (e.target === el) tutupModalBannerBidang();
        });

        @if ($errors->bannerBidang->any())
            // Buka kembali modal terakhir supaya admin bisa langsung
            // memperbaiki gambar tanpa mencari tab dan tombolnya lagi.
            const idTerakhir = @json(old('_bidang_id'));
            const pemicu = idTerakhir
                ? document.querySelector('[data-banner-id="' + idTerakhir + '"]')
                : document.querySelector('[data-banner-id]');
            if (pemicu) bukaModalBannerBidang(pemicu);

            // Pesan ditampilkan setelah modal dibuka, karena membuka modal
            // selalu membersihkan blok error terlebih dahulu.
            tampilkanError('errorBannerBidang', 'daftarErrorBannerBidang', @json($errors->bannerBidang->all()));

            el.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        @endif
    });
</script>
@endpush
