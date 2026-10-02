{{--
    Panel admin: dua modal (struktur organisasi dan galeri) yang hanya
    dirender bila pengguna adalah admin. Otorisasi tetap ditegur ulang di
    HomeController::wajibAdmin() pada setiap aksi, jadi menyembunyikan
    tombol di sini bukan satu-satunya pertahanan.
--}}
@php
    $daftarUserJson = $daftarUser ?? collect();
@endphp

{{-- ===================== MODAL STRUKTUR ORGANISASI ===================== --}}
<div id="modalStruktur" class="modal hidden">
    <div class="modal-card !max-w-lg">
        <div class="modal-head">
            <div>
                <h3 class="modal-title" id="judulModalStruktur">Tambah Peran</h3>
                <p class="page-sub mt-0.5" id="subjudulModalStruktur">
                    Susunan jabatan pada org chart CRMC
                </p>
            </div>
            <button onclick="tutupModalStruktur()" title="Tutup" class="icon-btn shrink-0">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="formStruktur"
              action="{{ route('admin.beranda.struktur.simpan') }}"
              method="POST"
              class="flex-1 flex flex-col min-h-0">
            <div class="modal-body space-y-3">
                @csrf
                {{-- Diisi lewat JS saat menyunting supaya tidak perlu membuat
                     form terpisah per mode. --}}
                <input type="hidden" name="_method" id="metodeStruktur" value="">

                {{-- Pesan validasi --}}
                <div id="errorStruktur" class="hidden note !border-rose-200 !bg-rose-50 !text-rose-800">
                    <p class="text-[11px] font-bold flex items-center gap-1.5">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                        Periksa kembali isian berikut
                    </p>
                    <ul id="daftarErrorStruktur" class="mt-1.5 space-y-0.5 text-[11px] text-rose-700"></ul>
                </div>

                <div>
                    <label class="label" for="inputPeran">Peran</label>
                    <select name="peran" id="inputPeran" required onchange="peranBerubah()" class="input">
                        @foreach (\App\Models\StrukturOrganisasi::PERAN as $key => $meta)
                            <option value="{{ $key }}">{{ $meta['label'] }} (Tingkat {{ $meta['tingkat'] }})</option>
                        @endforeach
                    </select>
                    <p class="hint" id="petunjukPeran">
                        Berlaku untuk seluruh CRMC, tidak perlu memilih bidang.
                    </p>
                </div>

                <div>
                    <label class="label" for="inputJabatan">Label Jabatan <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_jabatan" id="inputJabatan" required maxlength="150"
                           placeholder="mis. Pengendali Mutu Bidang SDA"
                           class="input">
                    <p class="hint">
                        Teks yang tampil pada pita kartu. Boleh dibedakan dari peran
                        di atas bila perlu menyebut bidang tertentu.
                    </p>
                </div>

                <div>
                    <label class="label" for="inputBidang">Bidang</label>
                    <select name="bidang_id" id="inputBidang" class="input">
                        <option value="">-- Tanpa Bidang (Seluruh CRMC) --</option>
                        @foreach ($daftarBidang as $b)
                            <option value="{{ $b->id }}">{{ $b->nama_bidang }}</option>
                        @endforeach
                    </select>
                    <p class="hint" id="petunjukBidang">
                        Wajib dipilih untuk Pengendali Mutu dan Pengendali Risiko.
                    </p>
                </div>

                <div>
                    <label class="label" for="inputUser">Pegawai</label>
                    <select name="user_id" id="inputUser" class="input">
                        <option value="">-- Belum Ditunjuk --</option>
                        @foreach ($daftarUserJson as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}{{ $u->nip ? ' - ' . $u->nip : '' }}</option>
                        @endforeach
                    </select>
                    <p class="hint">
                        Boleh dikosongkan. Kartu akan tampil sebagai "Belum Ditunjuk"
                        supaya kursinya terlihat di org chart.
                    </p>
                </div>

                <div>
                    <label class="label" for="inputKeterangan">Keterangan</label>
                    <textarea name="keterangan" id="inputKeterangan" rows="2" maxlength="500"
                              placeholder="Tugas atau ruang lingkup jabatan (opsional)"
                              class="input"></textarea>
                </div>

                <input type="hidden" name="urutan" id="inputUrutan" value="0">
            </div>

            <div class="modal-foot">
                <button type="button" onclick="tutupModalStruktur()" class="btn btn-quiet">Batal</button>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span id="labelTombolStruktur">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Form hapus struktur: dikirim lewat JS supaya tidak perlu satu form
     per kartu di dalam org chart. --}}
<form id="formHapusStruktur" method="POST" action="" class="hidden">
    @csrf
    <input type="hidden" name="_method" value="DELETE">
</form>

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

    // ---------- STRUKTUR ORGANISASI ----------

    const FORM_STRUKTUR_SIMPAN = @json(route('admin.beranda.struktur.simpan'));
    const FORM_STRUKTUR_UBAH = @json(route('admin.beranda.struktur.ubah', ['id' => '__ID__']));
    const FORM_STRUKTUR_HAPUS = @json(route('admin.beranda.struktur.hapus', ['id' => '__ID__']));
    const FORM_GALERI_SIMPAN = @json(route('admin.beranda.galeri.simpan'));
    const FORM_GALERI_UBAH = @json(route('admin.beranda.galeri.ubah', ['id' => '__ID__']));
    const FORM_GALERI_HAPUS = @json(route('admin.beranda.galeri.hapus', ['id' => '__ID__']));

    function tampilkanError(idBlock, idList, pesan) {
        const block = document.getElementById(idBlock);
        const list = document.getElementById(idList);
        if (!block || !list) return;

        list.innerHTML = '';
        Object.entries(pesan || {}).forEach(function (pair) {
            const li = document.createElement('li');
            li.textContent = pair[1];
            list.appendChild(li);
        });
        block.classList.toggle('hidden', Object.keys(pesan || {}).length === 0);
    }

    function segarkanIkon() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function bukaModalStruktur(data) {
        const form = document.getElementById('formStruktur');
        form.reset();
        tampilkanError('errorStruktur', 'daftarErrorStruktur', null);

        const menyunting = data && data.id;

        document.getElementById('formStruktur').action =
            menyunting ? FORM_STRUKTUR_UBAH.replace('__ID__', data.id) : FORM_STRUKTUR_SIMPAN;
        document.getElementById('metodeStruktur').value = menyunting ? 'PUT' : '';
        document.getElementById('judulModalStruktur').textContent =
            menyunting ? 'Ubah Peran' : 'Tambah Peran';
        document.getElementById('labelTombolStruktur').textContent =
            menyunting ? 'Simpan Perubahan' : 'Simpan';

        document.getElementById('inputPeran').value = data?.peran || 'pemilik_risiko';
        document.getElementById('inputJabatan').value = data?.nama_jabatan || '';
        document.getElementById('inputBidang').value = data?.bidang_id || '';
        document.getElementById('inputUser').value = data?.user_id || '';
        document.getElementById('inputKeterangan').value = data?.keterangan || '';
        document.getElementById('inputUrutan').value = data?.urutan || 0;

        peranBerubah();
        segarkanIkon();
        document.getElementById('modalStruktur').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function tutupModalStruktur() {
        document.getElementById('modalStruktur').classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Menyesuaikan aturan field, apakah bidang boleh dipilih dan memberi contoh label.
    function peranBerubah() {
        const peran = document.getElementById('inputPeran').value;
        const selectBidang = document.getElementById('inputBidang');
        const inputJabatan = document.getElementById('inputJabatan');
        const petunjukBidang = document.getElementById('petunjukBidang');
        const petunjukPeran = document.getElementById('petunjukPeran');

        if (peran === 'pemilik_risiko') {
            selectBidang.disabled = true;
            selectBidang.value = '';
            selectBidang.classList.add('opacity-50', 'cursor-not-allowed');
            petunjukBidang.textContent = 'Pemilik Risiko berlaku untuk seluruh CRMC.';
            petunjukPeran.textContent = 'Idealnya hanya satu orang pada tingkat ini.';
            if (!inputJabatan.value) inputJabatan.value = 'Pemilik Risiko';
        } else {
            selectBidang.disabled = false;
            selectBidang.classList.remove('opacity-50', 'cursor-not-allowed');
            if (peran === 'pengendali_mutu') {
                petunjukBidang.textContent = 'Wajib dipilih. Satu pengendali mutu per bidang.';
                petunjukPeran.textContent = 'Menempatkan satu orang pengendali mutu untuk sebuah bidang.';
                if (!inputJabatan.value) inputJabatan.value = 'Pengendali Mutu';
            } else {
                petunjukBidang.textContent = 'Disarankan dipilih agar muncul di bawah pengendali mutu bidangnya.';
                petunjukPeran.textContent = 'Tim yang melaksanakan pengendalian risiko harian.';
                if (!inputJabatan.value) inputJabatan.value = 'Pengendali Risiko';
            }
        }
    }

    function konfirmasiHapusStruktur(id, nama) {
        if (!confirm('Hapus "' + nama + '" dari struktur organisasi?\n\nPeran ini akan hilang dari org chart.')) return;
        const form = document.getElementById('formHapusStruktur');
        form.action = FORM_STRUKTUR_HAPUS.replace('__ID__', id);
        form.submit();
    }

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
            const pesan = @json($errors->all());
            tampilkanError('errorStruktur', 'daftarErrorStruktur', pesan);
            document.getElementById('modalStruktur').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });
    @endif

    // Tekan Escape menutup modal yang sedang terbuka.
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        if (!document.getElementById('modalStruktur').classList.contains('hidden')) tutupModalStruktur();
        if (!document.getElementById('modalGaleri').classList.contains('hidden')) tutupModalGaleri();
    });

    // Klik pada area gelap di luar modal ikut menutupnya.
    ['modalStruktur', 'modalGaleri'].forEach(function (id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('mousedown', function (e) {
            if (e.target === el) {
                id === 'modalStruktur' ? tutupModalStruktur() : tutupModalGaleri();
            }
        });
    });
</script>
@endpush
