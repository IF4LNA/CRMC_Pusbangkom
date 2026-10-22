{{--
    Isi satu tab bidang: daftar sub-bidang di bawah {{ $bidang->nama_bidang }}.

    Semua data berasal dari database (tabel `bidang` & `sub_menu`), bukan dari
    daftar yang ditulis manual. Karena itu admin bisa menambah sub-bidang baru
    dan memasang gambar latar pada kartunya langsung dari halaman ini.

    Parameter:
      $bidang  App\Models\Bidang, sudah-eager-load relasi subMenus
      $isAdmin bool, dari BidangController::dashboard()
--}}
@php
    $urlCrtc = route('crmc.show', ['slug' => '__SLUG__']);
    // Banner navigasi bidang: gambar unggahan admin, atau foto bawaan
    // PUSBANGKOM selama admin belum menggantinya.
    $bannerBidang = $bidang->gambar_latar_url ?? asset('images/banner_bidang.png');
    $bannerKustom = (bool) $bidang->gambar_latar_url;
@endphp
<!-- //banner navigasi bidang -->
<div class="space-y-3">
    {{-- Banner kembali memakai gaya awal: teks berada di atas gambar.
         Overlay/gradasi gelap tidak dipakai, jadi gambar tampil apa adanya;
         bayangan teks tipis menjaga judul tetap terbaca. --}}
    <div class="relative overflow-hidden rounded-xl bg-blue-950 text-white border border-blue-900 min-h-[10rem]">
        <img src="{{ $bannerBidang }}"
             alt="Banner {{ $bidang->nama_bidang }}"
             loading="lazy" decoding="async"
             class="absolute inset-0 h-full w-full object-cover object-center">

        <div class="relative flex flex-col md:flex-row md:items-center justify-between gap-3 p-4 sm:p-5 min-h-[10rem] drop-shadow-lg">
            <div class="min-w-0">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-900 text-blue-200 rounded-md text-[11px] font-bold border border-blue-800 uppercase tracking-wider">
                    <i data-lucide="map" class="w-3.5 h-3.5"></i>
                    Navigasi Bidang
                </span>
                <h2 class="text-lg sm:text-xl font-bold tracking-tight text-white mt-2">
                    {{ $bidang->nama_bidang }}
                </h2>
                <p class="text-xs text-blue-100 max-w-2xl mt-1 leading-relaxed">
                    {{ $bidang->sub_menus_count }} Pengendalian Resiko
                    ({{ $bidang->jumlah_dokumen }} sudah punya dokumen).
                    Klik pada kartu untuk membuka &amp; mengelola 8 komponen CRMC.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-blue-900/80 border border-blue-800 text-[11px] font-semibold text-blue-200">
                    <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                    {{ $bidang->sub_menus_count }} Pengendalian Resiko
                </span>

                @if ($isAdmin)
                    {{-- Banner bisa diganti admin kapan saja. Data dikirim
                         lewat atribut data-* agar tidak terpotong parser HTML. --}}
                    <button type="button"
                            data-banner-id="{{ $bidang->id }}"
                            data-banner-nama="{{ $bidang->nama_bidang }}"
                            data-banner-punya-gambar="{{ $bannerKustom ? '1' : '0' }}"
                            data-banner-gambar="{{ $bidang->gambar_latar_url ?? '' }}"
                            onclick="bukaModalBannerBidang(this)"
                            class="btn btn-sm btn-outline"
                            title="Ganti gambar banner navigasi bidang">
                        <i data-lucide="image" class="w-3.5 h-3.5"></i>
                        Ganti Banner
                    </button>

                    {{-- Data dikirim lewat atribut data-*, BUKAN JSON yang disisipkan
                             ke dalam onclick. Atribut onclick yang memuat JSON
                             terpotong kutip pertamanya oleh parser HTML, sehingga
                             tombolnya diam-diam mati saat diklik. --}}
                    <button type="button"
                            data-bidang-sub="{{ $bidang->id }}"
                            onclick="bukaModalSubBidang({ id: null, bidang_id: this.dataset.bidangSub })"
                            class="btn btn-sm btn-primary">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        Tambah Sub-Bidang
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if ($bidang->subMenus->isEmpty())
        <div class="empty">
            <i data-lucide="folder-plus" class="w-7 h-7 text-slate-300 mx-auto mb-2"></i>
            <p class="font-semibold text-slate-600">Belum Ada Sub-Bidang</p>
            <p class="text-[11px] text-slate-400 mt-1 max-w-md mx-auto leading-relaxed">
                @if ($isAdmin)
                    Tambahkan sub-bidang pertama pada bidang ini lewat tombol
                    "Tambah Sub-Bidang" di atas.
                @else
                    Data sub-bidang pada bidang ini belum diisi oleh administrator.
                @endif
            </p>
        </div>
    @else
        {{-- Kartu gelap: dasar biru tua, teks putih.

             Foto (bila ada) diletakkan di atas dasar itu, jadi teksnya
             selalu kontras apa pun isi gambarnya. Overlay memakai gradasi
             VERTIKAL: bagian atas dibiarkan terang supaya foto terlihat,
             lalu makin gelap ke bawah tepat di tempat judul dan petunjuk
             "Buka" berada. Gradasi ke samping seperti banner Beranda tidak
             dipakai karena pada kartu kecil sebagian teks akan jatuh di
             atas foto yang terang dan sulit dibaca.

             Kartu tanpa foto tetap biru tua lewat kelas --polos, supaya
             satu baris kartu seragam dan admin langsung tahu mana yang
             belum diberi foto. --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            @foreach ($bidang->subMenus as $i => $sub)
                @php
                    $urlSub = str_replace('__SLUG__', rawurlencode($sub->slug), $urlCrtc);
                    $dataUbah = [
                        'id' => $sub->id,
                        'bidang_id' => $sub->bidang_id,
                        'nama_sub_menu' => $sub->nama_sub_menu,
                        'punya_gambar' => (bool) $sub->gambar_latar,
                        'gambar_latar_url' => $sub->gambar_latar_url,
                    ];
                    $punyaLatar = (bool) $sub->gambar_latar_url;
                @endphp

                <div onclick="window.location.href='{{ $urlSub }}'"
                     class="card click-card sub-bidang-banner relative overflow-hidden p-4 flex flex-col justify-between gap-3 min-h-[9rem] {{ $punyaLatar ? '' : 'sub-bidang-banner--polos' }}">

                    {{-- Foto latar yang diunggah admin, ditutup gradasi vertikal
                         yang makin gelap ke bawah supaya teks putih tetap
                         terbaca pada foto yang terang maupun gelap.
                         Overlay hanya ada pada kartu yang punya foto. --}}
                    @if ($punyaLatar)
                        <img src="{{ $sub->gambar_latar_url }}" alt="" loading="lazy" decoding="async"
                             class="absolute inset-0 h-full w-full object-cover object-center">
                        <div class="absolute inset-0 bg-gradient-to-b from-blue-900/20 via-blue-950/45 to-blue-950/95"></div>
                    @endif

                    <div class="relative flex items-start justify-between gap-2">
                        <span class="sub-bidang-chip">SUB #{{ $i + 1 }}</span>

                        {{-- Hanya dua aksi: ubah (termasuk ganti gambar latar)
                             dan hapus. Tombol "Form Input / Edit" yang dulu ada
                             membuka modal isian yang isinya sudah digantikan
                             halaman 8 Komponen, jadi tidak ada gunanya lagi. --}}
                        @if ($isAdmin)
                            <div class="flex items-center gap-1">
                                <button type="button"
                                        data-sub-id="{{ $sub->id }}"
                                        data-sub-bidang="{{ $sub->bidang_id }}"
                                        data-sub-nama="{{ $sub->nama_sub_menu }}"
                                        data-sub-punya-gambar="{{ $punyaLatar ? '1' : '0' }}"
                                        data-sub-gambar="{{ $sub->gambar_latar_url ?? '' }}"
                                        onclick="event.stopPropagation(); bukaModalSubBidangSub(this)"
                                        title="Ubah Sub-Bidang / Ganti Latar" class="icon-btn-gelap">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button"
                                        data-sub-id="{{ $sub->id }}"
                                        data-sub-nama="{{ $sub->nama_sub_menu }}"
                                        onclick="event.stopPropagation(); konfirmasiHapusSubBidang(this.dataset.subId, this.dataset.subNama)"
                                        title="Hapus Sub-Bidang" class="icon-btn-gelap icon-btn-gelap-bahaya">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="relative">
                        <h4 class="text-sm font-bold text-white leading-snug line-clamp-3">
                            {{ $sub->nama_sub_menu }}
                        </h4>

                        <div class="flex items-center justify-end border-t border-white/15 pt-2 mt-2 text-[11px]">
                            <span class="inline-flex items-center gap-1 font-semibold text-white">
                                Buka <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>