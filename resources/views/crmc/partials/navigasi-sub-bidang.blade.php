{{--
    Navigasi antar sub-bidang dalam bidang yang sama (halaman 8 komponen).

    Dipakai di bagian atas dan bawah halaman crmc.show. Sub-bidang di ujung
    daftar dirender sebagai tombol nonaktif supaya posisi pengguna tetap
    terbaca, dan seluruh kelompok disembunyikan bila bidang itu hanya punya
    satu sub-bidang (tidak ada tujuan berpindah).

    Tahun yang sedang dibuka ikut dibawa lewat query "tahun" supaya berpindah
    sub-bidang tidak mengembalikan pengguna ke tahun default. Halaman tujuan
    tetap memvalidasi tahunnya, jadi tahun yang tidak ada akan jatuh ke default.

    Variabel yang diharapkan: $subBidangSebelumnya, $subBidangBerikutnya
    (keduanya ?App\Models\SubMenu), dan opsional $selectedTahun.
--}}
@php
    $sebelumnya = $subBidangSebelumnya ?? null;
    $berikutnya = $subBidangBerikutnya ?? null;
    $tahunNav = $selectedTahun ?? null;
@endphp

@if($sebelumnya || $berikutnya)
<div class="inline-flex items-center gap-1.5" data-peran="navigasi-sub-bidang">
    @if($sebelumnya)
        <a href="{{ route('crmc.show', array_filter(['slug' => $sebelumnya->slug, 'tahun' => $tahunNav])) }}"
           class="btn btn-outline btn-sm"
           title="Sub-bidang sebelumnya: {{ $sebelumnya->nama_sub_menu }}"
           aria-label="Sub-bidang sebelumnya: {{ $sebelumnya->nama_sub_menu }}"
           data-arah="sebelumnya">
            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
            <span>Sebelumnya</span>
        </a>
    @else
        <span class="btn btn-outline btn-sm opacity-40 cursor-not-allowed"
              aria-disabled="true" title="Sudah di sub-bidang pertama"
              data-arah="sebelumnya">
            <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
            <span class="hidden sm:inline">Sebelumnya</span>
        </span>
    @endif

    @if($berikutnya)
        <a href="{{ route('crmc.show', array_filter(['slug' => $berikutnya->slug, 'tahun' => $tahunNav])) }}"
           class="btn btn-outline btn-sm"
           title="Sub-bidang berikutnya: {{ $berikutnya->nama_sub_menu }}"
           aria-label="Sub-bidang berikutnya: {{ $berikutnya->nama_sub_menu }}"
           data-arah="berikutnya">
            <span>Berikutnya</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
        </a>
    @else
        <span class="btn btn-outline btn-sm opacity-40 cursor-not-allowed"
              aria-disabled="true" title="Sudah di sub-bidang terakhir"
              data-arah="berikutnya">
            <span class="hidden sm:inline">Berikutnya</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
        </span>
    @endif
</div>
@endif
