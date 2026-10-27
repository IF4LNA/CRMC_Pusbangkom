{{-- ==========================================================================
     DAFTAR DOKUMEN SATU KOMPONEN, SATU FILE SATU HALAMAN (CAROUSEL)

     Komponen 1, 2, 3, 4, 5, 6, dan 8. Kalau komponen punya lebih
     dari satu file, file berikutnya tidak lagi ditumpuk ke bawah. Hanya
     satu file yang tampil pada satu waktu, lengkap dengan tombol
     Sebelumnya/Berikutnya, titik navigasi, dan penomoran "File 2 dari 5".
     Satu file saja tetap tampil utuh, tanpa navigasi yang tidak berguna.

     Semua pemanggil memakai pratinjau lazy per slide. Khusus Komponen 6
     (kategori "bukti_pelaksanaan"), partial ini juga menampilkan pencarian
     nama/keterangan dan filter kelompok format berkas.

     Variabel yang dikirim saat partial dipanggil:
        $kategori : kategori komponen, sekaligus kunci carousel di JS
        $nomor    : nomor komponen (untuk judul & pesan)
        $judul    : judul komponen (untuk judul modal upload)
        $tahun    : tahun dokumen yang sedang ditampilkan
        $daftar   : kumpulan LampiranCrmc pada kategori + tahun tersebut

     Variabel yang diwarisi dari crmc/show.blade.php:
        $badgeFile, $ikonTipeFile, $namaTipeFile : closure
        $isAdmin : apakah pengguna adalah Administrator
     ========================================================================== --}}
@php
    // Nilai ulang dibuat di sini supaya penomoran titik navigasi selalu
    // mulai dari 0, apa pun kunci asli dari query.
    $daftar = $daftar->values();
    $jumlahFile = $daftar->count();
    $judulKomponenLengkap = 'Komponen ' . $nomor . ' — ' . $judul;

    // Semua komponen memakai pratinjau lazy per slide: hanya slide yang
    // sedang aktif yang diunduh/dirender, bukan seluruh berkas sekaligus.
    $pratinjauLazy = true;

    // Pencarian dan filter dokumen sengaja hanya untuk Komponen 6 — Bukti
    // Pelaksanaan. Komponen lain tetap memakai carousel tanpa alat cari agar
    // tampilannya tidak berubah.
    $khususCari = $kategori === 'bukti_pelaksanaan';
    $kelompokTersedia = $khususCari
        ? $daftar
            ->map(fn ($lamp) => \App\Support\TipeBerkas::kelompok($lamp->tipe_file) ?? 'lainnya')
            ->unique()
            ->values()
        : collect();

    $labelKelompok = [
        \App\Support\TipeBerkas::PDF => 'PDF',
        \App\Support\TipeBerkas::GAMBAR => 'Gambar',
        \App\Support\TipeBerkas::KANTOR => 'Word/Excel',
        'lainnya' => 'Lainnya',
    ];
@endphp

@if($jumlahFile > 0)
    <div id="dok-{{ $kategori }}"
         data-peran="akar-dokumen"
         data-kategori="{{ $kategori }}"
         data-total="{{ $jumlahFile }}"
         data-lazy-pratinjau="1"
         @if($khususCari) data-khusus-cari="1" @endif
         class="space-y-2.5">

        {{-- PENCARIAN & FILTER — HANYA KOMPONEN 6.
             Pencarian menyaring nama berkas dan keterangan. Filter menyaring
             kelompok format yang memang tersedia pada data (PDF, gambar,
             Word/Excel, lainnya). Kategori komponen, bidang, dan tahun tidak
             dibuat sebagai pilihan karena halaman ini sudah menampilkan satu
             kategori, satu bidang, dan tahun yang dipilih di atas. --}}
        @if($khususCari && $jumlahFile > 1)
            <div class="flex flex-col sm:flex-row sm:items-center gap-2" data-peran="alat-cari">
                <label class="relative flex-1 min-w-0">
                    <span class="sr-only">Cari dokumen berdasarkan nama atau keterangan</span>
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                    <input type="search"
                           data-peran="cari-dokumen"
                           autocomplete="off"
                           placeholder="Cari nama atau keterangan dokumen..."
                           class="input !pl-9 !py-1.5 !text-xs">
                </label>

                <label class="shrink-0">
                    <span class="sr-only">Filter format dokumen</span>
                    <select data-peran="filter-dokumen" class="input !w-full sm:!w-auto !py-1.5 !text-xs !px-2.5">
                        <option value="">Semua format</option>
                        @foreach($kelompokTersedia as $kelompok)
                            <option value="{{ $kelompok }}">{{ $labelKelompok[$kelompok] ?? strtoupper($kelompok) }}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <p data-peran="info-cari" class="text-[11px] text-slate-500 hidden"></p>
        @endif

        {{-- KEPALA CAROUSEL: penomoran file + tombol geser. Bagian kendali
             disembunyikan saat cetak (lihat blok @push('styles') di
             halaman), karena di kertas tidak ada tombol. --}}
        <div data-peran="kendali-dokumen" class="flex items-center justify-between gap-3 flex-wrap">
            <div class="flex items-center gap-2 min-w-0">
                <span class="badge badge-accent shrink-0" data-peran="counter">File 1 dari {{ $jumlahFile }}</span>
                @if($jumlahFile > 1)
                    <span class="text-[11px] text-slate-500 hidden sm:inline">
                        Geser ke file berikutnya supaya dokumen tidak menumpuk ke bawah.
                    </span>
                @endif
            </div>

            @if($jumlahFile > 1)
                <div class="flex items-center gap-1.5 shrink-0">
                    <button type="button" onclick="geserDokumen(@js($kategori), -1)" class="icon-btn"
                            title="File sebelumnya" aria-label="File sebelumnya">
                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                    </button>
                    <button type="button" onclick="geserDokumen(@js($kategori), 1)" class="icon-btn"
                            title="File berikutnya" aria-label="File berikutnya">
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </button>
                </div>
            @endif
        </div>

        {{-- SLIDE: satu file = satu slide selebar penuh container --}}
        <div data-peran="panggung-dokumen" class="relative overflow-hidden rounded-xl bg-slate-100">
            <div data-peran="track" tabindex="0" role="group"
                 aria-label="Dokumen {{ $judulKomponenLengkap }} tahun {{ $tahun }}"
                 class="flex transition-transform duration-300 ease-out">
                @foreach($daftar as $idx => $lamp)
                    @php
                        $teksCari = \Illuminate\Support\Str::lower(trim($lamp->nama_file.' '.($lamp->keterangan ?? '')));
                        $kelompokLampiran = \App\Support\TipeBerkas::kelompok($lamp->tipe_file) ?? 'lainnya';
                    @endphp
                    <div data-peran="slide-dokumen" class="w-full shrink-0"
                         @if($khususCari)
                             data-cari="{{ $teksCari }}"
                             data-kelompok="{{ $kelompokLampiran }}"
                         @endif>
                        @include('crmc.partials.kartu-dokumen', [
                            'index' => $idx + 1,
                            'total' => $jumlahFile,
                            'pratinjauLazy' => $pratinjauLazy,
                        ])
                    </div>
                @endforeach
            </div>
        </div>

        {{-- PESAN KOSONG SAAT PENCARIAN/FILTER TIDAK MENEMUKAN HASIL.
             Struktur carousel tetap ada; JS hanya menyembunyikan panggung
             dan navigasinya selama tidak ada hasil. --}}
        @if($khususCari)
            <div data-peran="kosong-cari" class="empty hidden">
                <i data-lucide="search-x" class="w-8 h-8 text-slate-300 mx-auto"></i>
                <p class="text-xs font-bold text-slate-600">Dokumen tidak ditemukan</p>
                <p class="text-[11px] text-slate-400">Ubah kata kunci atau pilihan format dokumen.</p>
            </div>
        @endif

        {{-- TITIK NAVIGASI: nama file ikut ditulis (tapi disembunyikan dari
             layar) supaya semua berkas komponen ini tetap bisa dibaca
             pembaca layar dan dicari lewat Ctrl+F. --}}
        @if($jumlahFile > 1)
            <div data-peran="kendali-dokumen" class="flex items-center justify-center gap-1.5 overflow-x-auto pb-1">
                @for($i = 0; $i < $jumlahFile; $i++)
                    <button type="button"
                            onclick="tampilDokumen(@js($kategori), {{ $i }})"
                            data-peran="titik"
                            data-index="{{ $i }}"
                            data-kelas="h-2 rounded-full shrink-0 transition-all"
                            aria-label="Tampilkan file {{ $i + 1 }}"
                            title="{{ $daftar[$i]->nama_file }}"
                            class="h-2 rounded-full shrink-0 transition-all {{ $i === 0 ? 'w-7 bg-blue-600' : 'w-2 bg-slate-300 hover:bg-slate-400' }}">
                        <span class="sr-only">File {{ $i + 1 }}: {{ $daftar[$i]->nama_file }}</span>
                    </button>
                @endfor
            </div>
        @endif
    </div>
@else
    {{-- KOSONG: hanya dokumen yang benar-benar diunggah yang ditampilkan,
         jadi kotak kosong berarti memang belum ada berkas tahun ini. --}}
    <div class="empty">
        <i data-lucide="file-plus-2" class="w-8 h-8 text-slate-300 mx-auto"></i>
        <p class="text-xs font-bold text-slate-600">Belum ada dokumen tahun {{ $tahun }}</p>
        <p class="text-[11px] text-slate-400">Komponen ini hanya menampilkan berkas yang benar-benar diunggah.</p>
        @if($bolehKelola ?? false)
            <button onclick="openUploadModal(@js($kategori), @js($judulKomponenLengkap), {{ (int) $tahun }})"
                    class="btn btn-sm btn-primary mt-1">
                <i data-lucide="upload" class="w-3 h-3"></i><span>Upload {{ $tahun }}</span>
            </button>
        @endif
    </div>
@endif