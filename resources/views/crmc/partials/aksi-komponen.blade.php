{{-- ==========================================================================
     AKSI SATU KOMPONEN: UNGGAH DOKUMEN + TAUTAN GOOGLE DRIVE

     Dipakai Komponen 1, 2, 3, 4, 5, 6, dan 8. Komponen 7 tidak memakai
     partial ini karena isinya status residu, bukan kumpulan berkas.

     Prinsip tampilannya:
       - Tombol "Buka Google Drive" hanya muncul bila tautan sudah diisi,
         dan itu tombol yang benar-benar membuka tautannya (dibungkus
         <a target="_blank">). Pengunjung pun bisa menekan tombol tersebut.
       - Tombol isi/ubah tautan hanya untuk pengguna yang sudah login,
         karena menyimpan tautan termasuk pekerjaan yang sama dengan
         mengunggah dokumen.
       - Ikon besar komponen tetap di paling kanan supaya urutannya
         upload -> link drive -> ikon, sama seperti sebelumnya.

     Variabel yang dikirim saat partial dipanggil:
        $kategori : kategori komponen (dipakai oleh kedua modal)
        $nomor    : nomor komponen
        $judul    : judul komponen
        $ikon     : nama ikon lucide komponen
        $tahun    : tahun yang sedang dibuka komponen ini
        $tautan   : model TautanDriveCrmc|null untuk (kategori, tahun)

     Tombol hapus tautan tidak ada di sini, melainkan di dalam modal,
     karena satu modal dipakai bersama semua komponen.
     ========================================================================== --}}
@php
    $judulLengkap = 'Komponen ' . $nomor . ' — ' . $judul;
    $tahunInt = (int) $tahun;
@endphp

<div class="flex items-center gap-2 flex-wrap justify-end shrink-0">

    @auth
        <button onclick="openUploadModal(@js($kategori), @js($judulLengkap), {{ $tahunInt }})"
                class="btn btn-sm btn-quiet" title="Unggah {{ $judulLengkap }}">
            <i data-lucide="upload" class="w-3 h-3"></i><span>Upload</span>
        </button>

        <button onclick="openTautanDriveModal(@js($kategori), @js($judulLengkap), {{ $tahunInt }})"
                class="btn btn-sm btn-quiet" title="Tautkan folder Google Drive {{ $judulLengkap }}">
            <i data-lucide="folder-symlink" class="w-3 h-3"></i>
            <span>{{ $tautan ? 'Ubah Link Drive' : 'Link Drive' }}</span>
        </button>
    @endauth

    {{-- Tautan yang sudah tersimpan ditampilkan sebagai tombol. Label
         teksnya diambil dari isi field label, atau teks bawaan kalau
         field-nya kosong. --}}
    @if($tautan)
        <a href="{{ $tautan->url }}" target="_blank" rel="noopener"
           class="btn btn-sm btn-primary"
           title="Buka {{ $tautan->label_tampil }} (Google Drive) di tab baru">
            <i data-lucide="external-link" class="w-3 h-3"></i>
            <span class="max-w-[14rem] truncate">{{ $tautan->label_tampil }}</span>
        </a>
    @endif

    <i data-lucide="{{ $ikon }}" class="w-4 h-4 text-slate-400"></i>
</div>