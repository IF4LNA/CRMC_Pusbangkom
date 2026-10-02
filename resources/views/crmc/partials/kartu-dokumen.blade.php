{{-- ==========================================================================
     SATU KARTU DOKUMEN DENGAN PRATINJAU LANGSUNG

     Dipakai Komponen 2, 3, 4, 5, 6, dan 8. Pratinjau ditampilkan langsung
     tanpa tombol mata:
       - PDF    -> iframe (viewer bawaan browser)
       - Gambar -> img
       - Format kantor (doc/xls) -> kotak info, karena browser tidak bisa
         merender file tersebut. Tombol Unduh tetap tersedia.

     Variabel yang dibutuhkan:
       $lamp          : model LampiranCrmc
       $badgeFile     : closure warna lencana tipe file
       $ikonTipeFile  : closure nama ikon lucide per tipe file
       $namaTipeFile  : closure label tipe file yang terbaca
       $tipePratinjau : closure 'pdf' | 'gambar' | null (null = tidak bisa dirender)

     Kelima closure di atas disiapkan di crmc/show.blade.php, jadi partial
     ini tidak memanggil closure berulang untuk tiap dokumen. Dua variabel
     sisanya dikirim bersama saat partial dipanggil:
       $index : urutan dokumen pada komponen ini (mulai dari 1)
       $total : jumlah dokumen pada komponen ini

     CATATAN PENTING: jangan menulis nama directive Blade di dalam
     komentar ini, termasuk directive yang diawali tanda "@". Blade
     memproses blok kode mentah SEBELUM menghapus komentar, sehingga
     "@" + "php" yang ditulis di dalam komentar ikut tertangkap sebagai
     awal blok kode. Akibatnya penutup komentar berpindah ke komentar
     berikutnya dan isi partial terhapus diam-diam.
     ========================================================================== --}}
@php
    $bisaDirender = $tipePratinjau($lamp->tipe_file);
    $urlBerkas = $lamp->file_path;
    $namaPendek = $namaTipeFile($lamp->tipe_file);
@endphp

<article class="rounded-xl border border-slate-200 bg-white overflow-hidden">

    {{-- KEPALA: lencana tipe, nama file, jumlah, dan aksi --}}
    <header class="flex items-center justify-between gap-3 px-4 py-3 bg-slate-50 border-b border-slate-200">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 rounded-md {{ $badgeFile($lamp->tipe_file) }} border flex items-center justify-center shrink-0">
                <i data-lucide="{{ $ikonTipeFile($lamp->tipe_file) }}" class="w-4 h-4"></i>
            </div>
            <div class="truncate min-w-0">
                <h5 class="text-xs font-bold text-slate-900 truncate" title="{{ $lamp->nama_file }}">{{ $lamp->nama_file }}</h5>
                <span class="text-[10px] text-slate-400">
                    {{ $namaPendek }} &middot; {{ $index }} dari {{ $total }} berkas
                    @if($lamp->dokumenCrmc) &middot; Tahun {{ $lamp->dokumenCrmc->tahun_pelaksanaan }} @endif
                </span>
            </div>
        </div>

        <div class="flex items-center gap-1.5 shrink-0">
            <span class="badge {{ $bisaDirender ? 'badge-ok' : '' }} hidden sm:inline-flex" title="Format ini ditampilkan langsung di halaman">
                {{ $bisaDirender ? 'Pratinjau langsung' : 'Perlu diunduh' }}
            </span>
            <a href="{{ $urlBerkas }}" target="_blank" rel="noopener" class="icon-btn" title="Buka di tab baru"><i data-lucide="external-link" class="w-3.5 h-3.5"></i></a>
            <a href="{{ $urlBerkas }}" download class="icon-btn" title="Unduh"><i data-lucide="download" class="w-3.5 h-3.5"></i></a>
            <button onclick="openKeteranganModal({{ $lamp->id }}, @js($lamp->nama_file), @js($lamp->keterangan ?? ''))" class="icon-btn" title="Keterangan"><i data-lucide="text-quote" class="w-3.5 h-3.5"></i></button>
            @if($isAdmin)
                <form action="{{ route('crmc.lampiran.delete', $lamp->id) }}" method="POST"
                      data-konfirmasi="Hapus dokumen &quot;{{ $lamp->nama_file }}&quot;? File fisik di storage ikut terhapus dan tidak bisa dibatalkan.">
                    @csrf @method('DELETE')
                    <button type="submit" class="icon-btn icon-btn-danger" title="Hapus"><i data-lucide="trash-2" class="w-3.5 h-3.5"></i></button>
                </form>
            @endif
        </div>
    </header>

    {{-- PRATINJAU --}}
    <div class="bg-slate-100">
        @if($bisaDirender === 'pdf')
            {{-- loading="lazy" supaya browser baru memuat dokumen yang sedang
                 terlihat. Tanpa itu, banyak PDF dalam satu halaman membuat
                 halaman berat dan lambat dibuka. --}}
            <iframe src="{{ $urlBerkas }}#view=FitH"
                    title="Pratinjau {{ $lamp->nama_file }}"
                    loading="lazy"
                    class="w-full h-[32rem] border-0 bg-white"></iframe>

        @elseif($bisaDirender === 'gambar')
            <div class="w-full h-[32rem] p-3 flex items-center justify-center bg-slate-100">
                <img src="{{ $urlBerkas }}" alt="Pratinjau {{ $lamp->nama_file }}"
                     loading="lazy" decoding="async"
                     class="max-w-full max-h-full object-contain rounded-lg border border-slate-300 bg-white">
            </div>

        @else
            {{-- Format tidak bisa dirender browser. Yang ditampilkan adalah
                 informasi berkas plus jalur keluar yang jelas, bukan kotak
                 kosong yang membingungkan. --}}
            <div class="w-full h-[32rem] p-4 flex items-center justify-center">
                <div class="w-full max-w-lg rounded-xl border-2 border-dashed border-slate-300 bg-white p-6 text-center space-y-2.5">
                    <div class="w-12 h-12 mx-auto rounded-xl {{ $badgeFile($lamp->tipe_file) }} border flex items-center justify-center">
                        <i data-lucide="{{ $ikonTipeFile($lamp->tipe_file) }}" class="w-6 h-6"></i>
                    </div>
                    <p class="text-sm font-bold text-slate-700">Pratinjau tidak tersedia untuk format {{ $namaPendek }}</p>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Browser tidak bisa menampilkan isi file {{ $namaPendek }} di dalam halaman.
                        Unduh berkasnya lalu buka dengan aplikasi yang sesuai di komputer Anda.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-2 pt-1">
                        <a href="{{ $urlBerkas }}" download class="btn btn-sm btn-primary">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>Unduh {{ $namaPendek }}</span>
                        </a>
                        <a href="{{ $urlBerkas }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                            <span>Buka di Tab Baru</span>
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- KETERANGAN --}}
    <footer class="px-4 py-3 border-t border-slate-200 bg-white">
        <div class="flex items-start space-x-2">
            <i data-lucide="{{ !empty($lamp->keterangan) ? 'info' : 'text-quote' }}" class="w-3.5 h-3.5 {{ !empty($lamp->keterangan) ? 'text-blue-500' : 'text-slate-300' }} shrink-0 mt-0.5"></i>
            @if(!empty($lamp->keterangan))
                <p class="text-[11px] text-slate-600 leading-relaxed whitespace-pre-line">{{ $lamp->keterangan }}</p>
            @else
                <button onclick="openKeteranganModal({{ $lamp->id }}, @js($lamp->nama_file), '')" class="text-[11px] text-slate-400 hover:text-blue-600 italic transition">
                    Belum ada keterangan. Klik untuk menambah.
                </button>
            @endif
        </div>
    </footer>
</article>
