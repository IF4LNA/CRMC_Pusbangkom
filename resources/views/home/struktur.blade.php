{{--
    Section "Struktur Organisasi" pada halaman Beranda.

    Isinya satu gambar bagan yang diunggah admin, bukan lagi daftar kartu
    orang hasil input manual. Karena itu tabel `struktur_organisasi` tidak
    lagi dipakai di halaman ini: yang aktif hanyalah baris terbaru pada
    tabel `gambar_struktur` (lihat HomeController::simpanGambarStruktur).
--}}
@php
    $bisaKelola = Auth::check() && Auth::user()->isAdmin();
@endphp

<section id="struktur-organisasi" class="scroll-mt-20 py-10 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Judul section --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-6">
            <div class="max-w-2xl">
                <span class="eyebrow mb-2 block">Struktur Organisasi</span>
                <h2 class="section-title">Bagan Rantai Tanggung Jawab Pengendalian Risiko</h2>
                <p class="page-sub mt-2 leading-relaxed">
                    Preventive control dijalankan berjenjang. Pemilik Risiko
                    bertanggung jawab atas seluruh CRMC, setiap bidang memiliki satu
                    Pengendali Mutu, dan pelaksanaan pengendalian harian diserahkan
                    kepada Tim Pengendali Risiko di masing-masing bidang.
                </p>
            </div>

            @if ($bisaKelola)
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <button type="button" form="formGambarStruktur"
                            class="btn btn-dark">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        {{ $gambarStruktur ? 'Ganti Gambar' : 'Unggah Gambar' }}
                    </button>

                    @if ($gambarStruktur)
                        <button type="submit" form="formHapusGambarStruktur"
                                class="btn btn-danger">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                            Hapus
                        </button>
                    @endif
                </div>
            @endif
        </div>

        {{-- Bagan --}}
        @if ($gambarStruktur)
            <figure class="card p-3 sm:p-4">
                <img src="{{ $gambarStruktur->url }}"
                     alt="Bagan struktur organisasi PUSBANGKOM"
                     loading="lazy" decoding="async"
                     class="w-full rounded-lg border border-slate-200 bg-white">

                @if ($gambarStruktur->keterangan)
                    <figcaption class="page-sub mt-3 text-center">{{ $gambarStruktur->keterangan }}</figcaption>
                @endif
            </figure>

            {{-- Penjelasan singkat tiap tingkat --}}
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
                @php
                    $ket = [
                        [
                            'ikon' => 'crown',
                            'judul' => 'Pemilik Risiko',
                            'isi' => 'Memastikan pengendalian risiko berjalan di seluruh bidang dan menjadi rujukan utama',
                        ],
                        [
                            'ikon' => 'badge-check',
                            'judul' => 'Pengendali Mutu',
                            'isi' => 'Memastikan kualitas dan kelengkapan instrumen pengendalian yang diisi',
                        ],
                        [
                            'ikon' => 'shield-check',
                            'judul' => 'Pengendali Risiko',
                            'isi' => 'Melaksanakan aktivitas pengendalian dan mengisi formulir instrumen',
                        ],
                    ];
                @endphp
                @foreach ($ket as $k)
                    <div class="card p-4 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-md bg-blue-50 text-blue-700 flex items-center justify-center shrink-0">
                            <i data-lucide="{{ $k['ikon'] }}" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-800">{{ $k['judul'] }}</h4>
                            <p class="page-sub leading-relaxed mt-0.5">{{ $k['isi'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Empty state --}}
            <div class="empty">
                <i data-lucide="image-plus" class="w-7 h-7 text-slate-300 mx-auto mb-2"></i>
                <p class="font-semibold text-slate-600">Bagan Belum Diunggah</p>
                <p class="text-[11px] text-slate-400 mt-1 max-w-md mx-auto leading-relaxed">
                    @if ($bisaKelola)
                        Klik "Unggah Gambar" di atas untuk memasang gambar bagan
                        struktur organisasi. Format JPG, PNG, atau WebP, maksimal 4 MB.
                    @else
                        Bagan struktur organisasi belum diunggah oleh administrator.
                    @endif
                </p>
            </div>
        @endif
    </div>

    {{-- ============ FORM UNGGAH GAMBAR STRUKTUR (khusus admin) ============ --}}
    @if ($bisaKelola)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <form id="formGambarStruktur"
                  action="{{ route('admin.beranda.struktur.gambar.simpan') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  class="card p-4 grid grid-cols-1 sm:grid-cols-[1fr_16rem_auto] gap-3 items-end">
                @csrf

                @if ($errors->any())
                    <div class="sm:col-span-3 note !border-rose-200 !bg-rose-50 !text-rose-800">
                        <p class="text-[11px] font-bold flex items-center gap-1.5">
                            <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                            Periksa kembali isian berikut
                        </p>
                        <ul class="mt-1.5 space-y-0.5 text-[11px] text-rose-700">
                            @foreach ($errors->all() as $pesan)
                                <li>{{ $pesan }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label class="label" for="strukturGambar">
                        File Gambar <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" name="gambar" id="strukturGambar" required
                           accept="image/jpeg,image/png,image/webp"
                           class="w-full text-[11px] text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:hover:bg-slate-200 file:text-[11px] file:font-bold file:text-slate-700 cursor-pointer border border-slate-300 rounded-lg p-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="hint">
                        JPG, PNG, atau WebP. Maksimal 4 MB. Gambar otomatis
                        diperkecil ke ukuran yang tetap tajam supaya halaman ringan.
                    </p>
                </div>

                <div>
                    <label class="label" for="strukturKeterangan">Keterangan</label>
                    <input type="text" name="keterangan" id="strukturKeterangan" maxlength="300"
                           value="{{ old('keterangan', $gambarStruktur->keterangan ?? '') }}"
                           placeholder="mis. Bagan struktur per 1 Januari 2026"
                           class="input">
                </div>

                <button type="submit" class="btn btn-primary">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    {{ $gambarStruktur ? 'Ganti' : 'Unggah' }}
                </button>
            </form>

            {{-- Form hapus dikirim terpisah supaya tombol "Hapus" tidak ikut
                 mengirim berkas gambar yang belum dipilih. --}}
            <form id="formHapusGambarStruktur" method="POST"
                  action="{{ route('admin.beranda.struktur.gambar.hapus') }}"
                  class="hidden"
                  data-konfirmasi="Hapus gambar struktur organisasi? Berkas gambar juga akan dihapus dari server.">
                @csrf
                @method('DELETE')
            </form>
        </div>
    @endif
</section>

@push('scripts')
<script>
    // Konfirmasi hapus dibaca dari atribut data-konfirmasi, bukan disisipkan
    // ke dalam string confirm(). Nama berkas bisa mengandung tanda kutip,
    // sehingga penyisipan teks ke JS akan merusak halamannya.
    document.addEventListener('submit', function (e) {
        const pesan = e.target.getAttribute('data-konfirmasi');
        if (!pesan) return;
        if (!confirm(pesan)) e.preventDefault();
    });
</script>
@endpush
