{{-- ==========================================================================
     PRATINJAU SATU BERKAS

     Satu tempat untuk semua cara menampilkan berkas, dipakai kartu dokumen
     "8 Komponen" maupun tab "Dasar Hukum" supaya keduanya tidak pernah
     berbeda perilaku. Aturan pengelompokan tipe ada di App\Support\TipeBerkas.

       pdf    -> iframe viewer bawaan peramban
       gambar -> tag img
       kantor -> wadah kosong yang diisi JavaScript di peramban
                 (docx-preview untuk DOCX, SheetJS untuk XLS/XLSX)
       null   -> kotak informasi plus tombol unduh (mis. DOC lama)

     Berkas format kantor TIDAK dirender di server. Skripnya dimuat sekali
     per halaman lewat blok "once" di bawah, walau partial ini dipanggil
     puluhan kali (satu berkas satu panggilan).

     Variabel yang dibutuhkan:
       $kelompok : 'pdf' | 'gambar' | 'kantor' | null
       $url      : URL berkas
       $tipe     : ekstensi berkas, mis. "docx"
       $judul    : teks untuk title/alt, mis. nama berkas atau nomor naskah

     Variabel opsional:
       $namaFile : nama asli berkas, dipakai tombol unduh
       $tinggi   : kelas tinggi Tailwind, default h-[32rem]
       $ikon     : nama ikon lucide untuk kotak fallback, default file
       $badge    : kelas lencana untuk kotak fallback

     CATATAN: jangan menulis nama direktif Blade di dalam komentar ini,
     termasuk yang diawali tanda "@". Blade memproses blok kode mentah
     sebelum menghapus komentar, sehingga nama direktif yang tertulis di
     komentar bisa ikut tertangkap dan merusak isi partial.
     ========================================================================== --}}
@php
    $namaFile = $namaFile ?? null;
    $tinggi = $tinggi ?? 'h-[32rem]';
    $ikon = $ikon ?? 'file';
    $badge = $badge ?? 'bg-slate-100 text-slate-700 border-slate-200';
    $label = \App\Support\TipeBerkas::label($tipe);

    // URL berkas diturunkan ke jalur relatifnya. URL yang tersimpan bisa
    // memuat host lama (mis. http://crmc_pusbangkom.test) sehingga
    // permintaan lintas-asal; untuk DOCX/XLS/XLSX yang dibaca lewat fetch()
    // hal itu diblokir peramban dan pratinjau tampak gagal. Dengan jalur
    // relatif, permintaan selalu menuju host yang sedang dibuka.
    $url = \App\Support\TautanBerkas::relatif($url);
@endphp

@if ($kelompok === 'pdf')
    {{-- loading="lazy" supaya berkas yang belum terlihat tidak ikut dimuat. --}}
    <iframe src="{{ $url }}#view=FitH"
            title="Pratinjau {{ $judul }}"
            loading="lazy"
            class="w-full {{ $tinggi }} border-0 bg-white"></iframe>

@elseif ($kelompok === 'gambar')
    <div class="w-full {{ $tinggi }} p-3 flex items-center justify-center bg-slate-100">
        <img src="{{ $url }}" alt="Pratinjau {{ $judul }}"
             loading="lazy" decoding="async"
             class="max-w-full max-h-full object-contain rounded-lg border border-slate-300 bg-white">
    </div>

@elseif ($kelompok === 'kantor')
    {{-- Wadah pratinjau sisi klien. Isinya diisi skrip di bagian bawah
         halaman. Selagi menunggu, pesan "Menyiapkan pratinjau" terbaca;
         kalau peramban gagal memuat isinya, skrip menggantinya dengan
         kotak informasi + tombol unduh. --}}
    <div class="{{ $tinggi }} w-full overflow-auto bg-white relative"
         data-pratinjau-kantor
         data-src="{{ $url }}"
         data-tipe="{{ strtolower((string) $tipe) }}"
         data-nama="{{ $namaFile }}">
        <div data-pratinjau-pesan
             class="w-full h-full flex flex-col items-center justify-center gap-1.5 p-6 text-center">
            <i data-lucide="loader-circle" class="w-5 h-5 text-slate-400 animate-spin"></i>
            <p class="text-xs font-semibold text-slate-500">Menyiapkan pratinjau&hellip;</p>
            <p class="text-[10px] text-slate-400">Isi berkas dibaca langsung di peramban Anda.</p>
        </div>
        <div data-pratinjau-isi class="text-slate-800 p-3"></div>
    </div>

    {{-- Skrip pemuat library dipasang HANYA bila ada berkas kantor, dan
         sekali saja per halaman walau ada banyak berkas. --}}
    @once('crmc-pratinjau-berkas-skrip')
        @push('scripts')
            @include('crmc.partials.pratinjau-berkas-skrip')
        @endpush
    @endonce

@else
    {{-- Format yang tidak bisa dibaca peramban sama sekali. Yang ditampilkan
         adalah informasi berkas plus jalur keluar yang jelas, bukan kotak
         kosong yang membingungkan. --}}
    <div class="w-full {{ $tinggi }} p-4 flex items-center justify-center">
        <div class="w-full max-w-lg rounded-xl border-2 border-dashed border-slate-300 bg-white p-6 text-center space-y-2.5">
            <div class="w-12 h-12 mx-auto rounded-xl {{ $badge }} border flex items-center justify-center">
                <i data-lucide="{{ $ikon }}" class="w-6 h-6"></i>
            </div>
            <p class="text-sm font-bold text-slate-700">Pratinjau tidak tersedia untuk format {{ $label }}</p>
            <p class="text-xs text-slate-500 leading-relaxed">
                Peramban tidak bisa menampilkan isi file {{ $label }} di dalam halaman.
                Unduh berkasnya lalu buka dengan aplikasi yang sesuai di komputer Anda.
            </p>
            <div class="flex flex-wrap items-center justify-center gap-2 pt-1">
                <a href="{{ $url }}" download class="btn btn-sm btn-primary">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    <span>Unduh Berkas</span>
                </a>
                <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    <span>Buka di Tab Baru</span>
                </a>
            </div>
        </div>
    </div>
@endif
