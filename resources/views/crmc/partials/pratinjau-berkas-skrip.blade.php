{{-- ==========================================================================
     SKRIP PRATINJAU SISI KLIEN (LAZY PDF/GAMBAR + DOCX/XLS/XLSX)

     Untuk PDF dan gambar di carousel komponen, elemen sengaja tidak diberi
     src di markup. Skrip mengisi src saat slide-nya aktif, lalu menandai
     status agar berkas yang sudah dimuat tidak diproses ulang. Untuk berkas
     format kantor, isinya dibaca di sini memakai library JS lalu ditulis ke
     wadah [data-pratinjau-kantor] yang dibuat crmc/partials/pratinjau-berkas.

     Library diambil dari CDN, sama seperti Tailwind dan lucide di layout.
     Yang penting:
       - docx-preview butuh JSZip dimuat lebih dulu (global window.JSZip).
       - SheetJS mengekspor global window.XLSX.

     Library dimuat HANYA saat ada berkas kantor yang benar-benar terlihat,
     dan sekali saja per halaman. Wadah di luar layar (slide carousel yang
     belum aktif, atau tab yang belum dibuka) baru diproses setelah masuk
     layar, supaya halaman tidak berat.

     Kalau library gagal dimuat (mis. server tidak punya internet) atau isi
     berkas rusak, wadah diganti kotak informasi + tombol unduh, bukan
     dibiarkan menampilkan "Menyiapkan pratinjau" selamanya.
     ========================================================================== --}}
<style>
    /* Tabel spreadsheet hasil SheetJS: garis, jarak, dan gulir horizontal. */
    .pratinjau-tabel table { border-collapse: collapse; }
    .pratinjau-tabel th,
    .pratinjau-tabel td {
        border: 1px solid #cbd5e1;
        padding: 2px 6px;
        font-size: 12px;
        line-height: 1.4;
        vertical-align: top;
        white-space: nowrap;
    }
    .pratinjau-lembar + .pratinjau-lembar { margin-top: 1rem; }
    .pratinjau-lembar-nama {
        font-size: 11px;
        font-weight: 700;
        color: #475569;
        margin-bottom: .35rem;
    }
    /* Halaman DOCX dari docx-preview diberi latar supaya tidak menempel tepi. */
    [data-pratinjau-isi] .docx-wrapper { background: #f1f5f9; padding: 12px; }
</style>

<script>
    (function () {
        // Library ditarik saat dibutuhkan, bukan di awal halaman.
        var SUMBER = {
            jszip: 'https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js',
            docx: 'https://cdn.jsdelivr.net/npm/docx-preview@0.3.6/dist/docx-preview.min.js',
            xlsx: 'https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js'
        };

        // Janji pemuatan per URL, supaya satu library tidak ditarik dua kali.
        var termuat = {};

        function muatSkrip(url) {
            if (termuat[url]) return termuat[url];

            termuat[url] = new Promise(function (selesai, gagal) {
                var skrip = document.createElement('script');
                skrip.src = url;
                skrip.async = true;
                skrip.onload = function () { selesai(); };
                skrip.onerror = function () { gagal(new Error('Gagal memuat ' + url)); };
                document.head.appendChild(skrip);
            });

            return termuat[url];
        }

        // Kotak pengganti bila pratinjau tak bisa dibuat. Memakai elemen DOM
        // (bukan innerHTML) supaya nama berkas tidak pernah ikut dieksekusi
        // sebagai HTML. Rincian teknis (bila ada) ditampilkan kecil di bawah
        // supaya penyebabnya gampang dilaporkan.
        function kotakGagal(wadah, rincian) {
            wadah.dataset.pratinjauStatus = 'gagal';

            var pesan = wadah.querySelector('[data-pratinjau-pesan]');
            var isi = wadah.querySelector('[data-pratinjau-isi]');
            if (isi) isi.innerHTML = '';
            if (!pesan) return;

            var nama = wadah.dataset.nama || '';
            var url = wadah.dataset.src;

            var kotak = document.createElement('div');
            kotak.className = 'w-full h-full flex items-center justify-center p-4';
            kotak.setAttribute('data-pratinjau-gagal', '1');

            var dalam = document.createElement('div');
            dalam.className = 'w-full max-w-md rounded-xl border-2 border-dashed border-slate-300 bg-white p-6 text-center space-y-2';

            var judul = document.createElement('p');
            judul.className = 'text-sm font-bold text-slate-700';
            judul.textContent = 'Pratinjau tidak dapat dimuat';

            var ket = document.createElement('p');
            ket.className = 'text-xs text-slate-500 leading-relaxed';
            // Teks lama dipertahankan untuk pemanggil non-lazy (mis. tab
            // Dasar Hukum), supaya tampilan kegagalannya tidak ikut berubah.
            ket.textContent = wadah.dataset.pratinjauRetry === '1'
                ? 'Peramban tidak berhasil menampilkan isi berkas ini. Silakan coba lagi atau unduh berkasnya.'
                : 'Peramban tidak berhasil menampilkan isi berkas ini. Silakan unduh lalu buka di komputer Anda.';

            var aksi = document.createElement('div');
            aksi.className = 'flex flex-wrap items-center justify-center gap-2 pt-1';

            // Tombol "Coba Lagi" dipasang untuk wadah lazy (semua komponen
            // berkarusel). Pemanggil non-lazy (mis. tab Dasar Hukum) tidak
            // memiliki atribut ini, sehingga kotak gagalnya tetap seperti dulu.
            if (wadah.dataset.pratinjauRetry === '1') {
                var ulang = document.createElement('button');
                ulang.type = 'button';
                ulang.className = 'btn btn-sm btn-primary';
                ulang.textContent = 'Coba Lagi';
                ulang.addEventListener('click', function () { muatUlangWadah(wadah); });
                aksi.appendChild(ulang);
            }

            var unduh = document.createElement('a');
            unduh.href = url;
            unduh.setAttribute('download', '');
            unduh.className = wadah.dataset.pratinjauRetry === '1' ? 'btn btn-sm btn-outline' : 'btn btn-sm btn-primary';
            unduh.textContent = nama ? 'Unduh ' + nama : 'Unduh Berkas';

            var buka = document.createElement('a');
            buka.href = url;
            buka.target = '_blank';
            buka.rel = 'noopener';
            buka.className = 'btn btn-sm btn-outline';
            buka.textContent = 'Buka di Tab Baru';

            aksi.appendChild(unduh);
            aksi.appendChild(buka);
            dalam.appendChild(judul);
            dalam.appendChild(ket);

            if (rincian) {
                var detail = document.createElement('p');
                detail.className = 'text-[10px] text-slate-400 break-words';
                detail.textContent = 'Detail: ' + rincian;
                dalam.appendChild(detail);
            }

            dalam.appendChild(aksi);
            kotak.appendChild(dalam);
            pesan.parentNode.replaceChild(kotak, pesan);
        }

        /** Pesan "memuat" sederhana untuk dipasang lagi setelah gagal. */
        function buatPesanMemuat() {
            var pesan = document.createElement('div');
            pesan.setAttribute('data-pratinjau-pesan', '');
            pesan.className = 'w-full h-full flex flex-col items-center justify-center gap-1.5 p-6 text-center';

            var judul = document.createElement('p');
            judul.className = 'text-xs font-semibold text-slate-500';
            judul.textContent = 'Menyiapkan pratinjau\u2026';

            pesan.appendChild(judul);
            return pesan;
        }

        /** Coba lagi satu wadah kantor yang sebelumnya gagal. */
        function muatUlangWadah(wadah) {
            var gagal = wadah.querySelector('[data-pratinjau-gagal]');
            if (gagal && gagal.parentNode) gagal.parentNode.removeChild(gagal);

            delete wadah.dataset.pratinjauDiproses;
            delete wadah.dataset.pratinjauStatus;

            if (!wadah.querySelector('[data-pratinjau-pesan]')) {
                wadah.insertBefore(buatPesanMemuat(), wadah.firstChild);
            }

            olahWadah(wadah);
        }

        function siapkanIsi(wadah) {
            var isi = wadah.querySelector('[data-pratinjau-isi]');
            if (isi) isi.innerHTML = '';
            return isi;
        }

        function tampilkanDocx(wadah, buffer) {
            return muatSkrip(SUMBER.jszip)
                .then(function () { return muatSkrip(SUMBER.docx); })
                .then(function () {
                    if (!window.docx || typeof window.docx.renderAsync !== 'function') {
                        throw new Error('docx-preview tidak tersedia');
                    }

                    var isi = siapkanIsi(wadah);
                    var blob = new Blob([buffer], {
                        type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    });

                    return window.docx.renderAsync(blob, isi, isi, {
                        className: 'docx-pratinjau',
                        inWrapper: true,
                        ignoreWidth: false,
                        ignoreHeight: true,
                        breakPages: true
                    });
                });
        }

        /**
         * Ubah satu lembar jadi elemen <table> yang aman dipasang ke halaman.
         *
         * HTML dari SheetJS dibersihkan dulu: hanya struktur tabel yang
         * dipertahankan, atribut selain colspan/rowspan dibuang. Tanpa ini,
         * isi berkas bisa menyelipkan elemen atau atribut apa pun ke halaman
         * (sel yang berisi kode HTML, misalnya).
         */
        function buatTabelLembar(buku, nama, indeks) {
            var html = window.XLSX.utils.sheet_to_html(buku.Sheets[nama], {
                id: 'pratinjau-lembar-' + indeks,
                editable: false
            });

            var dokumen = new DOMParser().parseFromString(html, 'text/html');
            var tabel = dokumen.querySelector('table');
            if (!tabel) return null;

            Array.prototype.forEach.call(tabel.querySelectorAll('*'), function (el) {
                if (!el.parentNode) return;

                var tag = el.tagName.toLowerCase();

                if (['thead', 'tbody', 'tfoot', 'tr', 'td', 'th', 'caption', 'colgroup', 'col'].indexOf(tag) === -1) {
                    el.parentNode.replaceChild(document.createTextNode(el.textContent || ''), el);
                    return;
                }

                Array.prototype.slice.call(el.attributes).forEach(function (atribut) {
                    var namaAtribut = atribut.name.toLowerCase();
                    if (namaAtribut !== 'colspan' && namaAtribut !== 'rowspan') {
                        el.removeAttribute(atribut.name);
                    }
                });
            });

            return document.importNode(tabel, true);
        }

        function tampilkanLembar(wadah, buffer) {
            return muatSkrip(SUMBER.xlsx).then(function () {
                if (!window.XLSX) throw new Error('SheetJS tidak tersedia');

                var isi = siapkanIsi(wadah);
                var buku = window.XLSX.read(new Uint8Array(buffer), { type: 'array' });

                buku.SheetNames.forEach(function (nama, indeks) {
                    var blok = document.createElement('div');
                    blok.className = 'pratinjau-lembar';

                    // Nama lembar hanya perlu ditulis bila bukunya lebih dari
                    // satu lembar; kalau hanya satu, judulnya cuma menambah
                    // baris tanpa informasi.
                    if (buku.SheetNames.length > 1) {
                        var label = document.createElement('div');
                        label.className = 'pratinjau-lembar-nama';
                        label.textContent = nama;
                        blok.appendChild(label);
                    }

                    var bungkus = document.createElement('div');
                    bungkus.className = 'pratinjau-tabel';

                    var tabel = buatTabelLembar(buku, nama, indeks);
                    if (tabel) bungkus.appendChild(tabel);

                    blok.appendChild(bungkus);
                    isi.appendChild(blok);
                });
            });
        }

        function olahWadah(wadah) {
            if (wadah.dataset.pratinjauDiproses === '1') return;
            wadah.dataset.pratinjauDiproses = '1';
            wadah.dataset.pratinjauStatus = 'memuat';

            var url = wadah.dataset.src;
            var tipe = (wadah.dataset.tipe || '').toLowerCase();

            fetch(url, { credentials: 'same-origin' })
                .then(function (respons) {
                    if (!respons.ok) {
                        throw new Error('Berkas tidak bisa dibaca (HTTP ' + respons.status + ')');
                    }
                    return respons.arrayBuffer();
                })
                .then(function (buffer) {
                    if (tipe === 'docx') return tampilkanDocx(wadah, buffer);
                    return tampilkanLembar(wadah, buffer);
                })
                .then(function () {
                    var pesan = wadah.querySelector('[data-pratinjau-pesan]');
                    if (pesan) pesan.parentNode.removeChild(pesan);
                    wadah.dataset.pratinjauStatus = 'selesai';
                })
                .catch(function (galat) {
                    // Sengaja tidak menghentikan halaman: satu berkas gagal
                    // tidak boleh memblokir berkas lain.
                    if (window.console && console.warn) {
                        console.warn('Pratinjau berkas gagal:', galat);
                    }
                    kotakGagal(wadah, galat && galat.message ? galat.message : null);
                });
        }

        // ------------------------------------------------------------------
        // PEMUAT LAZY PER SLIDE — DIPAKAI SEMUA KOMPONEN BERDOKUMEN
        // ------------------------------------------------------------------
        // PDF dan gambar pada carousel komponen sengaja tidak diberi src di
        // markup. Fungsi di bawah mengisi src saat slide-nya aktif, menandai
        // hasilnya di dataset (agar tidak diunduh/diproses ulang saat pengguna
        // kembali), dan menampilkan kotak gagal + tombol "Coba Lagi" bila
        // gagal. Elemen lain (mis. tab Dasar Hukum) tidak memiliki
        // [data-lazy-media], jadi tidak pernah menyentuh jalur ini.

        function pisahUrl(url) {
            var bagian = String(url || '').split('#');
            return { dasar: bagian.shift(), hash: bagian.length ? '#' + bagian.join('#') : '' };
        }

        function selesaiMedia(el) {
            el.dataset.muatStatus = 'selesai';
            el.classList.remove('hidden', 'opacity-0');

            var wadah = el.parentNode;
            var pesan = wadah ? wadah.querySelector('[data-lazy-pesan]') : null;
            if (pesan) pesan.classList.add('hidden');
        }

        function gagalMedia(el, teks) {
            el.dataset.muatStatus = 'gagal';
            el.classList.add('hidden');

            var wadah = el.parentNode;
            if (!wadah) return;

            var pesan = wadah.querySelector('[data-lazy-pesan]');
            if (pesan) pesan.classList.add('hidden');
            if (wadah.querySelector('[data-lazy-gagal]')) return;

            var bagian = pisahUrl(el.dataset.src);
            var nama = el.dataset.nama || '';

            var kotak = document.createElement('div');
            kotak.setAttribute('data-lazy-gagal', '1');
            kotak.className = 'absolute inset-0 z-20 flex items-center justify-center p-4 bg-slate-100';

            var dalam = document.createElement('div');
            dalam.className = 'w-full max-w-md rounded-xl border-2 border-dashed border-slate-300 bg-white p-5 text-center space-y-2';

            var judul = document.createElement('p');
            judul.className = 'text-sm font-bold text-slate-700';
            judul.textContent = 'Pratinjau tidak dapat dimuat';

            var ket = document.createElement('p');
            ket.className = 'text-xs text-slate-500 leading-relaxed';
            ket.textContent = teks || 'Berkas tidak berhasil dimuat. Silakan coba lagi atau unduh berkasnya.';

            var aksi = document.createElement('div');
            aksi.className = 'flex flex-wrap items-center justify-center gap-2 pt-1';

            var ulang = document.createElement('button');
            ulang.type = 'button';
            ulang.className = 'btn btn-sm btn-primary';
            ulang.textContent = 'Coba Lagi';
            ulang.addEventListener('click', function () { muatMedia(el, true); });

            var unduh = document.createElement('a');
            unduh.href = bagian.dasar;
            unduh.setAttribute('download', '');
            unduh.className = 'btn btn-sm btn-outline';
            unduh.textContent = nama ? 'Unduh ' + nama : 'Unduh Berkas';

            var buka = document.createElement('a');
            buka.href = bagian.dasar;
            buka.target = '_blank';
            buka.rel = 'noopener';
            buka.className = 'btn btn-sm btn-outline';
            buka.textContent = 'Buka di Tab Baru';

            aksi.appendChild(ulang);
            aksi.appendChild(unduh);
            aksi.appendChild(buka);
            dalam.appendChild(judul);
            dalam.appendChild(ket);
            dalam.appendChild(aksi);
            kotak.appendChild(dalam);
            wadah.appendChild(kotak);
        }

        function muatMedia(el, ulang) {
            if (!el) return;

            var status = el.dataset.muatStatus;
            if (!ulang && (status === 'selesai' || status === 'memuat' || status === 'gagal')) {
                return;
            }

            var bagian = pisahUrl(el.dataset.src);
            if (!bagian.dasar) return;

            var wadah = el.parentNode;
            var kotak = wadah ? wadah.querySelector('[data-lazy-gagal]') : null;
            if (kotak && kotak.parentNode) kotak.parentNode.removeChild(kotak);

            var pesan = wadah ? wadah.querySelector('[data-lazy-pesan]') : null;
            if (pesan) pesan.classList.remove('hidden');

            // Jangan menyembunyikan elemen dengan display:none saat memuat:
            // iframe/gambar ber-loading="lazy" bisa tidak pernah memicu load
            // selama tidak terlihat. Cukup tampilkan spinner di atasnya.
            el.classList.remove('hidden');
            el.classList.remove('opacity-0');
            el.dataset.muatStatus = 'memuat';

            var sufiks = ulang ? '?ulang=' + Date.now() : '';
            var jenis = el.dataset.lazyJenis;

            if (jenis === 'gambar') {
                el.onload = function () { selesaiMedia(el); };
                el.onerror = function () { gagalMedia(el, 'Gambar tidak dapat dimuat. Berkas mungkin sudah dipindahkan atau rusak.'); };
                el.src = bagian.dasar + sufiks + bagian.hash;
                return;
            }

            // PDF: cek dulu status berkas (HEAD) supaya kegagalan 404/403
            // bisa ditampilkan sebagai pesan + tombol coba lagi, bukan
            // halaman galat kosong di dalam iframe.
            fetch(bagian.dasar + sufiks, { method: 'HEAD', credentials: 'same-origin' })
                .then(function (respons) {
                    if (!respons.ok) {
                        throw new Error('HTTP ' + respons.status);
                    }
                })
                .then(function () {
                    el.onload = function () { selesaiMedia(el); };
                    el.onerror = function () { gagalMedia(el, 'Berkas PDF tidak dapat dimuat.'); };
                    el.src = bagian.dasar + sufiks + (bagian.hash || '#view=FitH');
                })
                .catch(function (galat) {
                    gagalMedia(el, 'Berkas PDF tidak dapat dimuat (' + (galat && galat.message ? galat.message : 'kesalahan jaringan') + ').');
                });
        }

        /** Muat pratinjau di satu slide carousel komponen. */
        function muatSlide(slide) {
            if (!slide) return;

            Array.prototype.forEach.call(slide.querySelectorAll('[data-lazy-media]'), function (el) {
                muatMedia(el, false);
            });

            Array.prototype.forEach.call(slide.querySelectorAll('[data-pratinjau-kantor][data-pratinjau-lazy="1"]'), function (wadah) {
                // Yang sudah berhasil tidak diproses ulang. Yang gagal
                // dibiarkan menampilkan tombol "Coba Lagi" miliknya.
                if (wadah.dataset.pratinjauStatus !== 'selesai' && wadah.dataset.pratinjauStatus !== 'gagal') {
                    olahWadah(wadah);
                }
            });
        }

        window.crmcMuatSlide = muatSlide;

        function mulai() {
            var semua = document.querySelectorAll('[data-pratinjau-kantor]');

            if ('IntersectionObserver' in window) {
                var pengamat = new IntersectionObserver(function (entri) {
                    entri.forEach(function (satu) {
                        if (satu.isIntersecting) {
                            pengamat.unobserve(satu.target);
                            olahWadah(satu.target);
                        }
                    });
                }, { rootMargin: '150px' });

                Array.prototype.forEach.call(semua, function (wadah) {
                    // Wadah di carousel komponen tidak diamati di sini:
                    // pemuatannya dikendalikan slide carousel (lihat
                    // muatSlide) supaya slide yang belum aktif tidak ikut
                    // diunduh.
                    if (wadah.dataset.pratinjauLazy === '1') return;
                    pengamat.observe(wadah);
                });
            } else {
                // Peramban lama tanpa IntersectionObserver: proses semuanya,
                // kecuali wadah lazy yang tetap menunggu slide.
                Array.prototype.forEach.call(semua, function (wadah) {
                    if (wadah.dataset.pratinjauLazy === '1') return;
                    olahWadah(wadah);
                });
            }

            // Aktifkan slide pertama yang terlihat di setiap carousel komponen
            // sekarang juga supaya dokumen pertama langsung tampil tanpa klik
            // (dan tanpa menunggu observer). Bila filter Komponen 6 sudah
            // dipulihkan peramban saat halaman dimuat, slide yang disembunyikan
            // tidak ikut dimuat. Slide lain menyusul saat pengguna berpindah.
            Array.prototype.forEach.call(document.querySelectorAll('[data-peran="akar-dokumen"][data-lazy-pratinjau="1"]'), function (akar) {
                var slide = akar.querySelector('[data-peran="slide-dokumen"]');
                var terlihat = null;

                Array.prototype.forEach.call(akar.querySelectorAll('[data-peran="slide-dokumen"]'), function (satu) {
                    if (!terlihat && !satu.classList.contains('hidden')) terlihat = satu;
                });

                muatSlide(terlihat || slide);
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', mulai);
        } else {
            mulai();
        }
    })();
</script>
