{{-- ==========================================================================
     DAFTAR DOKUMEN SATU KOMPONEN, SATU FILE SATU HALAMAN (CAROUSEL)

     Dipakai Komponen 1, 2, 3, 4, 5, 6, dan 8. Kalau komponen punya lebih
     dari satu file, file berikutnya tidak lagi ditumpuk ke bawah. Hanya
     satu file yang tampil pada satu waktu, lengkap dengan tombol
     Sebelumnya/Berikutnya, titik navigasi, dan penomoran "File 2 dari 5".
     Satu file saja tetap tampil utuh, tanpa navigasi yang tidak berguna.

     Variabel yang dikirim saat partial dipanggil:
        $kategori : kategori komponen, sekaligus kunci carousel di JS
        $nomor    : nomor komponen (untuk judul & pesan)
        $judul    : judul komponen (untuk judul modal upload)
        $tahun    : tahun dokumen yang sedang ditampilkan
        $daftar   : kumpulan LampiranCrmc pada kategori + tahun tersebut

     Variabel yang diwarisi dari crmc/show.blade.php:
        $badgeFile, $ikonTipeFile, $namaTipeFile, $tipePratinjau : closure
        $isAdmin : apakah pengguna adalah Administrator
     ========================================================================== --}}
@php
    // Nilai ulang dibuat di sini supaya penomoran titik navigasi selalu
    // mulai dari 0, apa pun kunci asli dari query.
    $daftar = $daftar->values();
    $jumlahFile = $daftar->count();
    $judulKomponenLengkap = 'Komponen ' . $nomor . ' — ' . $judul;
@endphp

@if($jumlahFile > 0)
    <div id="dok-{{ $kategori }}"
         data-peran="akar-dokumen"
         data-kategori="{{ $kategori }}"
         data-total="{{ $jumlahFile }}"
         class="space-y-2.5">

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
        <div class="relative overflow-hidden rounded-xl bg-slate-100">
            <div data-peran="track" tabindex="0" role="group"
                 aria-label="Dokumen {{ $judulKomponenLengkap }} tahun {{ $tahun }}"
                 class="flex transition-transform duration-300 ease-out">
                @foreach($daftar as $idx => $lamp)
                    <div data-peran="slide-dokumen" class="w-full shrink-0">
                        @include('crmc.partials.kartu-dokumen', [
                            'index' => $idx + 1,
                            'total' => $jumlahFile,
                        ])
                    </div>
                @endforeach
            </div>
        </div>

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
        @auth
            <button onclick="openUploadModal(@js($kategori), @js($judulKomponenLengkap), {{ (int) $tahun }})"
                    class="btn btn-sm btn-primary mt-1">
                <i data-lucide="upload" class="w-3 h-3"></i><span>Upload {{ $tahun }}</span>
            </button>
        @endauth
    </div>
@endif