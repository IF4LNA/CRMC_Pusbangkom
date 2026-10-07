<div class="space-y-4">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">

        {{-- Hero CRMC --}}
        {{-- Foto crmc_hero.jpg jadi latar, lalu dilapisi gradien biru tua
             supaya teks putih tetap terbaca. Perlakuannya sama dengan
             banner di halaman Beranda. Isi teks diletakkan pada div
             `relative` agar berada di atas foto dan gradasinya. --}}
        <div class="lg:col-span-2 relative overflow-hidden rounded-xl bg-blue-950 text-white border border-blue-900 flex flex-col justify-between">
            <img src="{{ asset('images/crmc_hero.jpg') }}"
                 alt="Ilustrasi Continuous Monitoring on Risk Control"
                 class="absolute inset-0 h-full w-full object-cover object-center">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-950/95 via-blue-950/85 to-blue-900/55"></div>

            {{-- Isi hero memakai `flex flex-col h-full` supaya baris tombol
                 bisa didorong ke dasar lewat `mt-auto`. Tanpa itu, tombol
                 menempel pada teks dan posisinya mengikuti tinggi
                 teks, bukan tinggi hero. --}}
            <div class="relative flex flex-col h-full p-5 sm:p-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-900 text-blue-200 rounded-md text-[11px] font-semibold mb-3 border border-blue-800">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                        <span>Sistem Pengendalian Risiko Terintegrasi</span>
                    </span>
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-white mb-1.5">
                        Continuous Monitoring on Risk Control (CRMC)
                    </h2>
                    <p class="text-blue-200 text-xs sm:text-sm max-w-xl leading-relaxed">
                        Pusat Pengembangan Kompetensi Sumber Daya Air, Cipta Karya dan Prasarana Strategis
                    </p>
                </div>

                {{-- Baris bawah hero: kutipan, lalu tombol aksi utama.
                     Tombol dipindah ke baris sendiri di bawah kutipan agar
                     tidak berebut ruang dengan teks panjangnya. --}}
                <div class="mt-auto pt-5 border-t border-blue-900/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <p class="flex items-center gap-2 text-xs text-blue-200">
                        <i data-lucide="quote" class="w-4 h-4 text-blue-400 shrink-0"></i>
                        <span class="italic font-medium">"Infrastruktur untuk Indonesia Maju"</span>
                    </p>
                    <button onclick="switchTab('umum-tu')" class="btn btn-sm btn-kuning shrink-0">
                        <span>Mulai Kelola CRMC</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Ringkasan pegawai & penugasan PIC --}}
        {{-- Kartu ini sengaja hanya menampilkan jumlah angka, bukan nama.
             Nama lengkap tiap PIC sudah tersedia di Komponen 1 pada halaman
             8 Komponen; di dashboard yang dibutuhkan hanya rekap jumlah per
             peran. Semua angka dibaca dari tabel `users` dan `penugasan_crmc`
             untuk tahun berjalan (lihat BidangController::ringkasanPersonel),
             jadi ikut terisi sendiri begitu admin menambah pegawai atau
             mengubah penugasan. --}}
        <div class="card p-4 flex flex-col justify-between space-y-3">
            <div class="flex items-center justify-between gap-2">
                <h3 class="card-title">Ringkasan Personel</h3>
                <span class="badge">Tahun {{ $ringkasanPersonel['tahun'] }}</span>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div class="stat !p-2.5">
                    <p class="stat-label">
                        <i data-lucide="shield-alert" class="w-3 h-3"></i>
                        Pemilik Risiko
                    </p>
                    <p class="stat-value !text-xl text-blue-700">{{ $ringkasanPersonel['jumlahPemilikRisiko'] }}</p>
                    <span class="stat-note">Seluruh CRMC</span>
                </div>
                <div class="stat !p-2.5">
                    <p class="stat-label">
                        <i data-lucide="badge-check" class="w-3 h-3"></i>
                        Pengendali Mutu
                    </p>
                    <p class="stat-value !text-xl text-sky-700">{{ $ringkasanPersonel['jumlahPengendaliMutu'] }}</p>
                    <span class="stat-note">Per bidang</span>
                </div>
                <div class="stat !p-2.5">
                    <p class="stat-label">
                        <i data-lucide="users-round" class="w-3 h-3"></i>
                        Pengendali Risiko
                    </p>
                    <p class="stat-value !text-xl text-slate-800">{{ $ringkasanPersonel['jumlahPengendaliRisiko'] }}</p>
                    <span class="stat-note">Per sub-bidang</span>
                </div>
                <div class="stat !p-2.5">
                    <p class="stat-label">
                        <i data-lucide="users" class="w-3 h-3"></i>
                        Jumlah Pegawai
                    </p>
                    <p class="stat-value !text-xl text-emerald-700">{{ $ringkasanPersonel['totalPengguna'] }}</p>
                    <span class="stat-note">{{ $ringkasanPersonel['totalPegawai'] }} pegawai · {{ $ringkasanPersonel['totalAdmin'] }} admin</span>
                </div>
            </div>

            <p class="text-[10px] text-slate-400 leading-snug">
                Angka mengikuti penugasan tahun {{ $ringkasanPersonel['tahun'] }}.
                Belum ada assignment berarti 0.
            </p>
        </div>
    </div>

    {{-- Pintasan ke tiap bidang --}}
    <div>
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h3 class="section-title">Eksplorasi Bidang &amp; Sub-Kategori CRMC</h3>
            <span class="page-sub">Pilih salah satu bidang untuk melihat 8 komponen CRMC</span>
        </div>

        {{-- Jumlah sub-bidang dibaca dari database, jadi ikut bertambah
             otomatis saat admin menambah sub-bidang baru. --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @forelse ($daftarBidang as $bidang)
                <div onclick="switchTab('bidang-{{ $bidang->id }}')" class="card click-card p-4 flex flex-col justify-between gap-4 min-h-[9rem]">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <div class="w-9 h-9 bg-blue-50 text-blue-700 rounded-md flex items-center justify-center">
                                <i data-lucide="{{ $bidang->ikon() }}" class="w-4 h-4"></i>
                            </div>
                            <span class="badge">{{ $bidang->sub_menus_count }} Sub-Bidang</span>
                        </div>
                        <h4 class="section-title text-base">{{ $bidang->nama_bidang }}</h4>
                        <p class="page-sub mt-1 leading-relaxed">
                            {{ $bidang->jumlah_dokumen }} sub-bidang sudah punya dokumen CRMC
                            pada satu atau lebih tahun pelaksanaan.
                        </p>
                    </div>
                    <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-[11px] font-semibold text-slate-700">
                        <span>Buka Daftar Lengkap</span>
                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                    </div>
                </div>
            @empty
                <div class="empty md:col-span-3">
                    <i data-lucide="folder-plus" class="w-7 h-7 text-slate-300 mx-auto mb-2"></i>
                    <p class="font-semibold text-slate-600">Belum Ada Bidang</p>
                    <p class="text-[11px] text-slate-400 mt-1">
                        Tambahkan data pada tabel <span class="font-mono">bidang</span> untuk
                        menampilkan bidang di dashboard.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</div>
