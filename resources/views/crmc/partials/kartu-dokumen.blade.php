{{-- ==========================================================================
     SATU KARTU DOKUMEN DENGAN PRATINJAU LANGSUNG

     Dipakai Komponen 2, 3, 4, 5, 6, dan 8. Pratinjau ditampilkan langsung
     tanpa tombol mata. Markup dan skrip pratinjaunya ditangani satu partial
     bersama, crmc/partials/pratinjau-berkas, supaya aturannya sama dengan
     tab "Dasar Hukum":
       - PDF           -> iframe (viewer bawaan browser)
       - Gambar        -> img
       - DOCX/XLS/XLSX -> dirender di peramban oleh library JS
       - lainnya (DOC) -> kotak info, karena browser tidak bisa membukanya

     Variabel yang dibutuhkan:
       $lamp          : model LampiranCrmc
       $badgeFile     : closure warna lencana tipe file
       $ikonTipeFile  : closure nama ikon lucide per tipe file
       $namaTipeFile  : closure label tipe file yang terbaca

     Keempat closure di atas disiapkan di crmc/show.blade.php, jadi partial
     ini tidak memanggil closure berulang untuk tiap dokumen. Pengelompokan
     pratinjau (bisa dirender atau tidak) dibaca dari App\Support\TipeBerkas.
     Dua variabel sisanya dikirim bersama saat partial dipanggil:
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
    $bisaDirender = \App\Support\TipeBerkas::kelompok($lamp->tipe_file);
    // Jalur relatif supaya tautan dan pratinjau selalu menuju host yang
    // sedang dibuka, bukan host lama yang terekam saat berkas diunggah.
    $urlBerkas = \App\Support\TautanBerkas::relatif($lamp->file_path);
    $namaPendek = $namaTipeFile($lamp->tipe_file);
    // Semua komponen memakai pemuatan pratinjau per slide. Nilai default
    // false dipakai pemanggil lain (mis. tab Dasar Hukum) yang tidak
    // mengirimkan variabel ini.
    $pratinjauLazy = $pratinjauLazy ?? false;
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

    {{-- PRATINJAU: aturan dan markup-nya ada di satu partial bersama supaya
         cara menampilkan berkas sama dengan tab "Dasar Hukum". --}}
    <div class="bg-slate-100">
        @include('crmc.partials.pratinjau-berkas', [
            'kelompok' => $bisaDirender,
            'url' => $urlBerkas,
            'tipe' => $lamp->tipe_file,
            'judul' => $lamp->nama_file,
            'namaFile' => $lamp->nama_file,
            'ikon' => $ikonTipeFile($lamp->tipe_file),
            'badge' => $badgeFile($lamp->tipe_file),
            'pratinjauLazy' => $pratinjauLazy,
        ])
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
