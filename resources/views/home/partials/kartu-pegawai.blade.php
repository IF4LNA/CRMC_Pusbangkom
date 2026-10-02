{{--
    Satu kartu orang di dalam org chart struktur organisasi.

    Parameter:
      $row        App\Models\StrukturOrganisasi
      $peran      kunci warna pita: pemilik_risiko | pengendali_mutu | pengendali_risiko
      $pita       array [warna pita, warna teks pita]
      $bisaKelola bool, menampilkan tombol ubah dan hapus
      $subjudul   keterangan bidang di bawah nama
      $kecil      bool, versi ringkas untuk kartu tingkat 3
--}}
@php
    // $kecil hanya dikirim untuk kartu tingkat 3. Default-nya false supaya
    // pemanggilan yang tidak menyebutnya tidak memicu undefined variable.
    $kecil = $kecil ?? false;
    $meta = \App\Models\StrukturOrganisasi::PERAN[$peran] ?? ['label' => $peran, 'warna' => 'slate'];
    [$warnaPita, $teksPita] = $pita[$peran] ?? ['bg-slate-600', 'text-white'];

    // kursi boleh kosong: admin belum menunjuk orangnya.
    $user = $row->user;
    $kosong = $user === null;

    // Data untuk mengisi modal ubah. Argumen @json harus berada dalam satu
    // baris, karena Blade memotong argumen direktif pada tanda kurung
    // tertutup pertama.
    $dataUbah = [
        'id' => $row->id,
        'peran' => $row->peran,
        'nama_jabatan' => $row->nama_jabatan,
        'user_id' => $row->user_id,
        'bidang_id' => $row->bidang_id,
        'urutan' => $row->urutan,
        'keterangan' => $row->keterangan,
    ];
@endphp

<div class="card relative group overflow-hidden">

    {{-- Pita jabatan --}}
    <div class="{{ $warnaPita }} {{ $teksPita }} px-3 py-1.5 flex items-center justify-between gap-2">
        <span class="text-[10px] font-extrabold uppercase tracking-wide truncate">
            {{ $row->nama_jabatan ?: $meta['label'] }}
        </span>
        <span class="text-[9px] font-bold opacity-70 shrink-0">T{{ $meta['tingkat'] ?? '-' }}</span>
    </div>

    <div class="p-3.5">

        <div class="flex items-start gap-3">
            {{-- Foto: property foto_url sudah mengembalikan placeholder SVG
                 lokal bila pegawai belum mengunggah foto. --}}
            <img src="{{ $user?->foto_url }}"
                 alt="{{ $user?->name ?? 'Belum ditunjuk' }}"
                 width="{{ $kecil ? 48 : 64 }}"
                 height="{{ $kecil ? 48 : 64 }}"
                 loading="lazy"
                 decoding="async"
                 class="{{ $kecil ? 'w-12 h-12' : 'w-16 h-16' }} rounded-lg object-cover shrink-0 border border-slate-200 {{ $kosong ? 'opacity-40 grayscale' : '' }}">

            <div class="min-w-0 flex-1">
                <h4 class="text-xs sm:text-sm font-bold text-slate-900 leading-tight">
                    {{ $user?->name ?? 'Belum Ditunjuk' }}
                </h4>

                @if ($kosong)
                    <p class="page-sub italic mt-0.5">Kursi kosong</p>
                @else
                    @if ($user->nip)
                        <p class="text-[10px] text-slate-400 font-mono mt-0.5">NIP {{ $user->nip }}</p>
                    @endif
                    @if ($user->jabatan && $user->jabatan !== '-')
                        <p class="page-sub leading-snug mt-0.5 line-clamp-2">
                            {{ $user->jabatan }}
                        </p>
                    @endif
                @endif
            </div>
        </div>

        <p class="text-[10px] text-slate-400 mt-3 pt-3 border-t border-slate-100 truncate">
            {{ $subjudul }}
        </p>

        @if ($row->keterangan)
            <p class="page-sub leading-relaxed mt-2">{{ $row->keterangan }}</p>
        @endif
    </div>

    {{-- Tombol admin: disembunyikan sampai kursor di atas kartu, supaya
         tampilan pengunjung tetap bersih. --}}
    @if ($bisaKelola)
        <div class="absolute top-1.5 right-1.5 flex items-center gap-1 opacity-0 group-hover:opacity-100 focus-within:opacity-100 transition">
            <button type="button"
                    onclick='bukaModalStruktur(@json($dataUbah))'
                    title="Ubah"
                    class="icon-btn">
                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
            </button>
            <button type="button"
                    onclick="konfirmasiHapusStruktur({{ $row->id }}, @js($user?->name ?? $row->nama_jabatan))"
                    title="Hapus"
                    class="icon-btn icon-btn-danger">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
            </button>
        </div>
    @endif
</div>
