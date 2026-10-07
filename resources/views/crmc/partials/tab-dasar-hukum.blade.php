{{-- ==========================================================================
     TAB "DASAR HUKUM"

     Daftar regulasi dibaca dari tabel `dokumen_dasar_hukum` (lihat
     BidangController::dashboard), jadi naskah yang berlaku bisa diganti
     lewat seeder tanpa menyentuh view.

     Untuk tiap regulasi ada dua kondisi:
       1. Berkas sudah diunggah admin -> pratinjau langsung di dalam kartu
          (PDF pakai iframe, gambar pakai img, format kantor pakai kotak
          informasi plus tombol unduh).
       2. Belum ada berkas -> kartu tetap menampilkan identitas naskah
          lengkap, dan hanya admin yang melihat form unggah.

     Form memakai POST biasa supaya setelah simpan halaman dimuat ulang dan
     pratinjau langsung terlihat.
     ========================================================================== --}}
@php
    // Helper kecil supaya penentuan "bisa dirender" tidak ditulis ulang
    // di tiap kartu. Mengembalikan 'pdf', 'gambar', atau null.
    $tipePratinjau = function (?string $tipe): ?string {
        $tipe = strtolower((string) $tipe);

        return match (true) {
            $tipe === 'pdf' => 'pdf',
            in_array($tipe, ['jpg', 'jpeg', 'png', 'webp'], true) => 'gambar',
            default => null,
        };
    };
@endphp

<div class="space-y-3">

    {{-- Notifikasi hasil aksi admin --}}
    @if (session('success'))
        <div class="note !border-emerald-200 !bg-emerald-50 !text-emerald-900 flex items-start gap-2">
            <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
            <p class="text-xs font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="note !border-rose-200 !bg-rose-50 !text-rose-900">
            <p class="font-semibold flex items-center gap-1.5">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                Dokumen tidak tersimpan
            </p>
            <ul class="mt-1.5 space-y-0.5 list-disc list-inside text-rose-700">
                @foreach ($errors->all() as $pesan)
                    <li>{{ $pesan }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Kepala tab --}}
    <div class="card p-4 flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <span class="eyebrow">Regulasi</span>
            <h2 class="card-title mt-0.5 flex items-center gap-1.5">
                <i data-lucide="scale" class="w-4 h-4 text-slate-400"></i>
                Dasar Hukum &amp; Regulasi CRMC
            </h2>
            <p class="page-sub mt-1">Landasan peraturan dan pedoman teknis penerapan Continuous Monitoring on Risk Control</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <span class="badge badge-info">{{ $daftarDasarHukum->count() }} Peraturan Utama Terkait</span>
            {{-- filter(), bukan where(): where() pada Collection mengaktifkan
                 kunci sebagai nama atribut, bukan memanggil method. --}}
            <span class="badge badge-ok">
                {{ $daftarDasarHukum->filter(fn ($d) => $d->sudahAdaBerkas())->count() }} Dokumen Terunggah
            </span>
        </div>
    </div>

    @forelse ($daftarDasarHukum as $dokumen)
        @php
            $bisaDirender = $tipePratinjau($dokumen->tipe_file);
            $urlBerkas = $dokumen->url;
        @endphp

        <article class="card overflow-hidden">
            {{-- Kepala: jenis naskah, penerbit, dan nomor --}}
            <header class="card-head flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <span class="badge badge-info flex items-center gap-1">
                            <i data-lucide="{{ $dokumen->ikon() }}" class="w-3 h-3"></i>
                            {{ $dokumen->jenis }}
                        </span>
                        <span class="badge">{{ $dokumen->penerbit }}</span>
                    </div>
                    <h3 class="card-title leading-snug">
                        {{ $dokumen->jenis }} {{ $dokumen->penerbit }}
                        <span class="block font-mono text-xs text-blue-700 mt-0.5">NOMOR: {{ $dokumen->nomor }}</span>
                    </h3>
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
                    @if ($dokumen->sudahAdaBerkas())
                        <a href="{{ $urlBerkas }}" target="_blank" rel="noopener" class="icon-btn" title="Buka di tab baru">
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        </a>
                        <a href="{{ $urlBerkas }}" download class="icon-btn" title="Unduh">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </header>

            {{-- Isi naskah: bagian "TENTANG ..." --}}
            <div class="p-4 space-y-3">
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Tentang</p>
                    <p class="text-xs font-semibold text-slate-900 leading-relaxed">{{ $dokumen->tentang }}</p>
                </div>

                {{-- Pratinjau berkas --}}
                @if ($dokumen->sudahAdaBerkas())
                    <div class="rounded-lg border border-slate-200 overflow-hidden bg-slate-100">
                        @if ($bisaDirender === 'pdf')
                            <iframe src="{{ $urlBerkas }}#view=FitH"
                                    title="Pratinjau {{ $dokumen->nomor }}"
                                    loading="lazy"
                                    class="w-full h-[28rem] border-0 bg-white"></iframe>

                        @elseif ($bisaDirender === 'gambar')
                            <div class="w-full h-[28rem] p-3 flex items-center justify-center">
                                <img src="{{ $urlBerkas }}" alt="Pratinjau {{ $dokumen->nomor }}"
                                     loading="lazy" decoding="async"
                                     class="max-w-full max-h-full object-contain rounded-lg border border-slate-300 bg-white">
                            </div>

                        @else
                            {{-- Format kantor (doc/xls) tidak bisa dirender browser.
                             Tampilkan informasinya, bukan kotak kosong yang membingungkan. --}}
                            <div class="w-full h-[28rem] p-4 flex items-center justify-center">
                                <div class="w-full max-w-lg rounded-xl border-2 border-dashed border-slate-300 bg-white p-6 text-center space-y-2.5">
                                    <div class="w-12 h-12 mx-auto rounded-xl bg-blue-50 text-blue-700 border border-blue-200 flex items-center justify-center">
                                        <i data-lucide="file-text" class="w-6 h-6"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700">Pratinjau tidak tersedia untuk format {{ strtoupper($dokumen->tipe_file) }}</p>
                                    <p class="text-xs text-slate-500 leading-relaxed">
                                        Browser tidak bisa menampilkan isi file ini di dalam halaman.
                                        Unduh berkasnya lalu buka dengan aplikasi yang sesuai.
                                    </p>
                                    <div class="flex flex-wrap items-center justify-center gap-2 pt-1">
                                        <a href="{{ $urlBerkas }}" download class="btn btn-sm btn-primary">
                                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                            <span>Unduh Berkas</span>
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

                    <p class="text-[11px] text-slate-500">
                        <i data-lucide="file-check-2" class="w-3 h-3 inline align-[-2px]"></i>
                        {{ $dokumen->nama_file }} &bull; {{ strtoupper($dokumen->tipe_file) }}
                        @if ($dokumen->ukuranTerbaca())
                            &bull; {{ $dokumen->ukuranTerbaca() }}
                        @endif
                    </p>

                @else
                    <div class="rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center">
                        <i data-lucide="file-up" class="w-7 h-7 text-slate-300 mx-auto mb-2"></i>
                        <p class="text-xs font-semibold text-slate-500">Dokumen belum diunggah</p>
                        <p class="text-[11px] text-slate-400 mt-1">
                            Naskah di atas tetap berlaku sebagai landasan. Admin dapat mengunggah
                            salinannya supaya bisa dibaca langsung di halaman ini.
                        </p>
                    </div>
                @endif
            </div>

            {{-- Form unggah: hanya untuk admin --}}
            @if ($isAdmin)
                <footer class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                    <form method="POST" enctype="multipart/form-data"
                          action="{{ route('admin.dasar-hukum.berkas.simpan', $dokumen->id) }}"
                          class="flex flex-col sm:flex-row sm:items-center gap-2">
                        @csrf
                        <input type="file" name="berkas" required
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                               class="input !py-1.5 text-xs flex-1 min-w-0">

                        <div class="flex items-center gap-2 shrink-0">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                <span>{{ $dokumen->sudahAdaBerkas() ? 'Ganti Dokumen' : 'Unggah Dokumen' }}</span>
                            </button>

                            @if ($dokumen->sudahAdaBerkas())
                                <button type="submit" class="btn btn-sm btn-danger"
                                        form="hapus-dh-{{ $dokumen->id }}"
                                        onclick="return confirm('Hapus dokumen {{ addslashes($dokumen->nomor) }}? Naskah regulasinya tetap ada, hanya berkasnya yang dihapus.')">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    <span>Hapus</span>
                                </button>
                            @endif
                        </div>
                    </form>

                    {{-- Form hapus dipisah karena atribut `form` pada tombol di
                         atas menunjuk ke sini, bukan ke form unggah. --}}
                    @if ($dokumen->sudahAdaBerkas())
                        <form id="hapus-dh-{{ $dokumen->id }}" method="POST"
                              action="{{ route('admin.dasar-hukum.berkas.hapus', $dokumen->id) }}"
                              class="hidden">
                            @csrf @method('DELETE')
                        </form>
                    @endif

                    <p class="text-[10px] text-slate-400 mt-1.5">
                        PDF, DOC, DOCX, XLS, XLSX, JPG, atau PNG. Maksimal 10 MB.
                        Unggahan baru otomatis menggantikan berkas lama.
                    </p>
                </footer>
            @endif
        </article>
    @empty
        <div class="empty">
            <i data-lucide="scale" class="w-7 h-7 text-slate-300 mx-auto mb-2"></i>
            <p class="font-semibold text-slate-600">Belum Ada Dasar Hukum</p>
            <p class="text-[11px] text-slate-400 mt-1">
                Jalankan seeder <span class="font-mono">DokumenDasarHukumSeeder</span> untuk
                mengisi daftar regulasi yang berlaku.
            </p>
        </div>
    @endforelse
</div>