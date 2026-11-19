# Buku Panduan Administrator CRMC PUSBANGKOM

<a id="cover"></a>

| Keterangan | Isi |
|---|---|
| Nama aplikasi | CRMC Pusbangkom (Continuous Monitoring on Risk Control) |
| Instansi | Pusat Pengembangan Kompetensi Sumber Daya Air, Cipta Karya dan Prasarana Strategis |
| Alamat aplikasi | `http://crmc_pusbangkom.test` |
| Sasaran pembaca | Administrator sistem |
| Cakupan | Seluruh pekerjaan administrasi harian, bulanan, dan tahunan |
| Bahasa | Indonesia |
| Versi dokumen | 1.0 |
| Tanggal penyusunan | 19 November 2026 |

> **Tentang buku ini**
>
> Buku ini menjelaskan cara mengoperasikan seluruh fitur administrator pada aplikasi CRMC PUSBANGKOM, langkah demi langkah. Pembaca diasumsikan sudah memiliki akun dengan role **Admin** dan memahami dasar penggunaan peramban web.
>
> Buku ini tidak membahas instalasi server, konfigurasi basis data, atau pengaturan jaringan. Untuk pekerjaan teknis tersebut, hubungi pengelola infrastruktur aplikasi.

---

<a id="daftar-isi"></a>

## Daftar Isi

1. [Pendahuluan](#bab-1)
2. [Masuk, Keluar, dan Lupa Kata Sandi](#bab-2)
3. [Mengenal Antarmuka Aplikasi](#bab-3)
4. [Delapan Komponen CRMC dan Logika Tahun](#bab-4)
5. [Mengelola Bidang, Sub-Bidang, dan Banner](#bab-5)
6. [Mengunggah dan Mengelola Dokumen](#bab-6)
7. [Menetapkan PIC melalui Komponen 1](#bab-7)
8. [Status Residu Risiko dan Ringkasan Evaluasi](#bab-8)
9. [Mengelola Daftar Tahun](#bab-9)
10. [Mengelola Akun Pegawai](#bab-10)
11. [Menambah Penugasan dari Panel Admin](#bab-11)
12. [Memantau Kelengkapan di Kelola Dokumen](#bab-12)
13. [Memulihkan Dokumen dari Private Storage](#bab-13)
14. [Mengelola Isi Halaman Beranda](#bab-14)
15. [Mengelola Berkas Dasar Hukum](#bab-15)
16. [Menggunakan Halaman SOP](#bab-16)
17. [Daftar Periksa Rutin Administrator](#bab-17)
18. [Ringkasan Aturan Berkas dan Batas Ukuran](#bab-18)
19. [Penanganan Masalah Umum](#bab-19)
20. [Keamanan dan Praktik Terbaik](#bab-20)

**Lampiran**

- [Lampiran A — Peta Alamat Halaman](#lampiran-a)
- [Lampiran B — Glosarium](#lampiran-b)
- [Lampiran C — Lembar Periksa Kelengkapan per Sub-Bidang](#lampiran-c)
- [Lampiran D — Contoh Template Keterangan Berkas](#lampiran-d)
- [Lampiran E — Riwayat Revisi](#lampiran-e)

---

<a id="bab-1"></a>

## Bab 1 — Pendahuluan

### 1.1 Apa itu CRMC PUSBANGKOM?

CRMC adalah singkatan dari **Continuous Monitoring on Risk Control**, yaitu instrumen pemantauan pengendalian risiko secara berkelanjutan. Aplikasi ini dipakai untuk mendokumentasikan, memantau, dan mengevaluasi pengendalian risiko pada setiap **pengendalian risiko** (sub-bidang) di lingkungan PUSBANGKOM.

Setiap sub-bidang memiliki satu halaman berisi **delapan komponen**:

1. Identitas Pegawai dan penugasan PIC tiga lapis.
2. Risk Register Spesifik Acuan.
3. Standar Operasional Prosedur (SOP).
4. Formulir Pengendalian / Daftar Periksa.
5. Jadwal Rencana Pelaksanaan.
6. Bukti Pelaksanaan.
7. Status Residu Risiko.
8. Evaluasi dan Rencana Perbaikan.

Administrator bertanggung jawab memastikan delapan komponen tersebut terisi, akun pegawai tersedia, penugasan PIC benar, dan data antar-tahun tidak tertukar.

### 1.2 Siapa saja pengguna aplikasi?

Aplikasi membedakan **role akun** dan **peran penugasan**. Keduanya berbeda dan tidak saling menggantikan.

**Role akun** hanya ada dua:

- **Admin** — memiliki akses penuh ke seluruh bidang, seluruh dokumen, seluruh tahun, dan seluruh menu administrasi.
- **Pegawai** — hanya dapat membuka bidang yang ditugaskan kepadanya melalui daftar **Bidang yang Dapat Diakses**.

**Peran penugasan (PIC)** ada tiga dan disimpan per tahun:

- **Pemilik Risiko** — satu orang untuk seluruh CRMC.
- **Pengendali Mutu** — satu orang untuk setiap bidang.
- **Pengendali Risiko** — boleh banyak orang untuk setiap sub-bidang.

Seorang pegawai bisa memiliki role akun **Pegawai**, tetapi menjabat sebagai **Pengendali Mutu** pada tahun tertentu. Sebaliknya, role akun **Admin** tidak otomatis menjadikan seseorang PIC. Penugasan PIC harus tetap diisi melalui Komponen 1.

### 1.3 Matriks hak akses

| Fitur | Tamu (belum login) | Pegawai | Admin |
|---|---|---|---|
| Membaca Beranda | Ya | Ya | Ya |
| Membaca tab Dasar Hukum | Ya | Ya | Ya |
| Membuka Dashboard | Halaman terbuka, isi terkunci | Ya, hanya bidang yang ditugaskan | Ya, semua bidang |
| Membuka halaman 8 Komponen | Tidak (403) | Ya, hanya bidang yang ditugaskan | Ya, semua bidang |
| Mengunggah dokumen | Tidak | Ya, hanya di bidang yang ditugaskan | Ya, semua bidang |
| Menambah atau mengubah tautan Google Drive | Tidak | Ya, hanya di bidang yang ditugaskan | Ya, semua bidang |
| Menghapus tautan Google Drive | Tidak | Hanya tautan yang ia buat sendiri | Semua tautan |
| Mengubah keterangan berkas | Tidak | Tidak | Ya |
| Menghapus berkas satuan | Tidak | Tidak | Ya |
| Melihat Komponen 7 dan 8 | Tidak | Ya (hanya baca) | Ya (baca dan ubah) |
| Mengubah status residu dan ringkasan evaluasi | Tidak | Tidak | Ya |
| Mengatur penugasan PIC | Tidak | Tidak | Ya |
| Menambah atau menghapus tahun | Tidak | Tidak | Ya |
| Mengelola akun pegawai | Tidak | Tidak | Ya |
| Mengelola dokumen per tahun | Tidak | Tidak | Ya |
| Memulihkan dokumen | Tidak | Tidak | Ya |
| Mengelola isi Beranda | Tidak | Tidak | Ya |
| Mengunggah berkas Dasar Hukum | Tidak | Tidak | Ya |
| Membuka halaman SOP | Tidak (403) | Ya, terfilter bidangnya | Ya, semua bidang |
| Membuka Panel Admin | Tidak | Tidak | Ya |

> **Penting:** Tamu hanya boleh membaca Beranda dan Dasar Hukum. Tautan Dashboard dan SOP memang tidak ditampilkan di navigasi untuk tamu, tetapi bila alamatnya diketik langsung, halaman SOP dan halaman 8 Komponen tetap menolak dengan kode 403.

### 1.4 Istilah yang dipakai di aplikasi

| Istilah | Arti |
|---|---|
| **Bidang** | Unit organisasi tingkat pertama, misalnya Bagian Umum Program & Tata Usaha, Bidang SDA, dan Bidang CKPS. |
| **Sub-bidang / Pengendalian Risiko** | Proses kerja di bawah bidang yang menjadi objek pemantauan. Contoh: Manajemen Risiko, Pengelolaan Arsip, Evaluasi Pasca Pelatihan SDA. |
| **Komponen** | Delapan bagian isi halaman sub-bidang, bernomor 1 sampai 8. |
| **Tahun anggaran / tahun pelaksanaan** | Tahun yang menempel pada dokumen, penugasan, tautan Drive, status residu, dan ringkasan evaluasi. |
| **Tahun manual** | Tahun yang ditambahkan administrator di luar tahun otomatis. |
| **Residu risiko** | Tingkat sisa risiko setelah pengendalian, dipilih dari skala lima tingkat. |
| **PIC** | Person in Charge, yaitu Pemilik Risiko, Pengendali Mutu, atau Pengendali Risiko. |
| **Private storage** | Penyimpanan berkas di server yang tidak dapat diakses langsung lewat URL publik. Semua akses berkas melewati pemeriksaan hak akses aplikasi. |
| **Drive link** | Tautan ke folder atau berkas Google Drive yang ditampilkan sebagai tombol pada komponen. Aplikasi hanya menyimpan tautannya, bukan isi Drive. |

### 1.5 Alur kerja administrator secara ringkas

1. Pastikan akun pegawai dan bidang aksesnya sudah benar.
2. Pastikan daftar bidang dan sub-bidang sesuai struktur organisasi.
3. Tetapkan PIC melalui Komponen 1 untuk tahun yang sedang berjalan.
4. Pantau kelengkapan dokumen melalui **Kelola Dokumen**.
5. Tindak lanjuti sub-bidang yang masih kosong.
6. Perbarui status residu dan ringkasan evaluasi pada akhir periode.
7. Siapkan tahun berikutnya dengan menambah tahun manual bila diperlukan.
8. Nonaktifkan akun pegawai yang sudah tidak bertugas.

---

<a id="bab-2"></a>

## Bab 2 — Masuk, Keluar, dan Lupa Kata Sandi

### 2.1 Masuk ke aplikasi

1. Buka peramban dan ketik alamat `http://crmc_pusbangkom.test`.
2. Klik tombol **Masuk** pada navigasi, atau buka langsung `http://crmc_pusbangkom.test/login`.
3. Isi **Alamat Email Dinas** dengan email yang terdaftar.
4. Isi **Kata Sandi**.
5. Centang **Ingat saya** bila Anda memakai perangkat pribadi dan ingin tetap masuk setelah peramban ditutup. Jangan centang pada perangkat bersama.
6. Bila muncul verifikasi keamanan Cloudflare Turnstile, selesaikan verifikasi tersebut.
7. Klik **Masuk ke Akun Saya**.

Setelah berhasil, Anda diarahkan ke **Dashboard** dengan pesan selamat datang yang menyebut nama dan mode akun Anda.

> **Catatan:** Akun dibuat oleh administrator. Tidak ada pendaftaran mandiri. Jika akun belum aktif, Anda tidak dapat masuk meskipun email dan kata sandi benar.

### 2.2 Jika gagal masuk

| Pesan yang muncul | Penyebab | Tindakan |
|---|---|---|
| "Kombinasi email dan kata sandi yang Anda masukkan tidak sesuai." | Email atau kata sandi salah | Periksa ejaan email, huruf besar/kecil pada kata sandi, lalu coba lagi. |
| "Akun Anda tidak aktif. Silakan hubungi administrator." | Akun dinonaktifkan | Minta administrator lain mengaktifkan akun melalui **Kelola Pegawai**. |
| "Verifikasi keamanan belum selesai." | Turnstile belum diselesaikan | Selesaikan verifikasi, lalu kirim ulang formulir. |
| "Terlalu banyak percobaan." | Batas percobaan login terlampaui | Tunggu sekitar satu menit. Sistem membatasi 5 percobaan per menit per email dan 10 percobaan per menit per alamat IP. |

### 2.3 Lupa kata sandi

1. Buka `http://crmc_pusbangkom.test/forgot-password`.
2. Isi **Alamat Email Dinas** yang terdaftar.
3. Klik tombol permintaan tautan atur ulang.
4. Buka kotak masuk email dinas Anda dan cari pesan berisi tautan atur ulang.
5. Klik tautan tersebut. Halaman **Atur Ulang Kata Sandi** akan terbuka.
6. Isi kata sandi baru dan konfirmasinya.
7. Klik tombol simpan. Anda akan diarahkan ke halaman login dengan pesan bahwa kata sandi berhasil diubah.
8. Masuk kembali memakai kata sandi baru.

Aturan kata sandi baru:

- Minimal 8 karakter.
- Harus mengandung minimal satu angka.
- Harus mengandung minimal satu simbol (karakter selain huruf dan angka).
- Harus sama dengan kolom konfirmasi.

> **Keamanan:** Pesan permintaan tautan sengaja dibuat sama untuk semua kondisi. Aplikasi tidak memberi tahu apakah sebuah email terdaftar atau tidak. Ini mencegah orang lain menebak daftar akun.
>
> Permintaan tautan dibatasi 3 kali per menit per email dan 10 kali per menit per alamat IP. Bila tautan sudah kedaluwarsa, minta tautan baru.

### 2.4 Keluar dari aplikasi

1. Klik menu akun atau tombol **Keluar** pada navigasi.
2. Aplikasi mengakhiri sesi dan mengembalikan Anda ke Beranda.

Selalu keluar setelah memakai komputer bersama atau komputer fasilitas umum.

---

<a id="bab-3"></a>

## Bab 3 — Mengenal Antarmuka Aplikasi

### 3.1 Navigasi utama

Navigasi atas berisi:

- **Beranda** — halaman publik berisi penjelasan CRMC, bagan struktur organisasi, galeri sarana dan prasarana, serta peta lokasi.
- **Dashboard** — pintu masuk pekerjaan CRMC. Hanya tampil untuk pengguna yang sudah masuk.
- **Dasar Hukum** — tab berisi empat regulasi utama beserta berkasnya.
- **Tab setiap bidang** — misalnya **Bagian Umum Program & Tata Usaha**, **Bidang SDA**, dan **Bidang CKPS**. Angka di samping nama bidang adalah jumlah sub-bidang.
- **SOP** — kumpulan seluruh dokumen Komponen 3 dari bidang yang boleh Anda lihat.
- **Panel Admin** — hanya tampil untuk akun dengan role Admin.

### 3.2 Halaman Beranda

Beranda dapat dibaca tanpa login. Bagian utamanya:

1. Banner pengantar CRMC.
2. Penjelasan singkat mengenai CRMC.
3. Bagan struktur organisasi berupa satu gambar yang diunggah administrator.
4. Galeri sarana dan prasarana berbentuk carousel.
5. Peta lokasi kantor.

Pengelolaan gambar struktur dan galeri dijelaskan pada [Bab 14](#bab-14).

### 3.3 Dashboard

Dashboard menampilkan:

- Ringkasan jumlah personel dan penugasan PIC tahun berjalan.
- Bagan struktur organisasi pengendalian risiko untuk tahun berjalan.
- Satu tab untuk setiap bidang yang boleh Anda buka.
- Tab **Dasar Hukum**.

Di dalam tab bidang terdapat:

- Banner navigasi bidang.
- Tombol **Ganti Banner** dan **Tambah Pengendalian Resiko** (khusus admin).
- Kartu setiap sub-bidang. Klik kartu untuk membuka halaman 8 Komponen.
- Tombol pensil untuk mengubah nama atau gambar latar sub-bidang.
- Tombol tempat sampah untuk menghapus sub-bidang.

> **Perhatian:** Bagi pegawai tanpa penugasan bidang, Dashboard tetap dapat dibuka tetapi tidak menampilkan satu bidang pun. Pesan yang muncul menjelaskan bahwa akun tersebut belum mendapat akses bidang.

### 3.4 Halaman 8 Komponen

Halaman ini adalah pusat pekerjaan dokumen. Isinya:

1. Kepala halaman berisi nama bidang dan nama sub-bidang.
2. Ringkasan tahun, jumlah komponen terisi, dan jumlah berkas.
3. Pemilih **Tahun Default Halaman**.
4. Kartu ringkasan residu, dokumen, dan personel.
5. Komponen 1 sampai 8 secara berurutan.
6. Tombol navigasi cepat berbentuk ikon daftar di kanan bawah untuk melompat ke komponen tertentu.
7. Tombol **Sebelumnya** dan **Berikutnya** untuk berpindah sub-bidang dalam bidang yang sama.
8. Tombol **Kembali ke Dashboard**.

Setiap komponen dokumen memiliki:

- Pemilih tahun sendiri.
- Tombol **Upload**.
- Tombol **Link Drive** atau **Ubah Link Drive**.
- Tombol **Buka Google Drive** bila tautan sudah tersimpan.
- Daftar berkas dengan tombol buka, unduh, keterangan, dan hapus.

### 3.5 Panel Admin

Panel Admin berisi empat kartu:

1. **Kelola Pegawai** — membuat, mengubah, menonaktifkan, dan menghapus akun.
2. **Kelola Dokumen** — memantau kelengkapan, mengelola tahun manual, dan menghapus dokumen satu tahun.
3. **Pemulihan Dokumen** — memulihkan berkas yang ada di private storage tetapi belum terdaftar di basis data.
4. **Penugasan Pegawai** — menambah penugasan per tahun dari satu halaman.

Panel juga menampilkan tiga angka ringkasan: **Total Pegawai**, **Administrator**, dan **Pegawai**. Akun yang ditandai sebagai akun tamu tidak dihitung dalam angka tersebut.

### 3.6 Pesan dan notifikasi

Aplikasi memakai dua jenis pesan:

- **Pesan hijau** — tindakan berhasil, misalnya dokumen berhasil diunggah.
- **Pesan merah** — tindakan gagal, misalnya berkas terlalu besar atau tautan bukan dari Google.

Bacalah pesan sampai selesai. Beberapa pesan menyebut nama berkas yang gagal agar Anda dapat memperbaikinya satu per satu.

---

<a id="bab-4"></a>

## Bab 4 — Delapan Komponen CRMC dan Logika Tahun

### 4.1 Fungsi setiap komponen

| No | Komponen | Isi | Siapa yang mengisi |
|---|---|---|---|
| 1 | Identitas Pegawai | Penugasan Pemilik Risiko, Pengendali Mutu, dan Pengendali Risiko; berkas pendukung seperti SK atau surat delegasi; tautan Drive | Admin untuk penugasan; Admin dan Pegawai untuk berkas dan Drive |
| 2 | Risk Register Spesifik Acuan | Dokumen risk register; tautan Drive | Admin dan Pegawai |
| 3 | Standar Operasional Prosedur (SOP) | Dokumen SOP; tautan Drive. Semua berkas SOP juga tampil di halaman **SOP** | Admin dan Pegawai |
| 4 | Formulir Pengendalian / Daftar Periksa | Formulir pengendalian; tautan Drive | Admin dan Pegawai |
| 5 | Jadwal Rencana Pelaksanaan | Jadwal rencana; tautan Drive | Admin dan Pegawai |
| 6 | Bukti Pelaksanaan | Bukti kegiatan berupa dokumen atau gambar; tautan Drive | Admin dan Pegawai |
| 7 | Status Residu Risiko | Satu status per tahun; tidak memiliki berkas | Admin saja |
| 8 | Evaluasi dan Rencana Perbaikan | Ringkasan evaluasi berupa teks dan dokumen pendukung; tautan Drive | Admin untuk teks; Admin dan Pegawai untuk berkas dan Drive |

> **Catatan penting:** Komponen 7 tidak memiliki tombol Upload atau Link Drive karena isinya status, bukan kumpulan berkas.

### 4.2 Tahun berjalan dan tahun otomatis

Aplikasi selalu menyediakan dua tahun secara otomatis:

- Tahun berjalan.
- Tahun berjalan ditambah satu, untuk perencanaan tahun berikutnya.

Contoh: bila tahun berjalan 2026, pilihan tahun otomatis adalah 2026 dan 2027.

Halaman 8 Komponen terbuka pada **tahun berjalan**, bukan tahun terbaru. Ini disengaja agar Anda tidak membuka halaman yang masih kosong pada tahun depan.

### 4.3 Sumber pilihan tahun

Daftar tahun pada pemilih tahun merupakan gabungan dari:

1. Tahun berjalan dan tahun berikutnya.
2. Tahun yang sudah memiliki dokumen pada sub-bidang tersebut.
3. Tahun manual yang ditambahkan administrator.
4. Tahun yang sudah dipakai pada penugasan PIC.
5. Tahun yang sudah dipakai pada tautan Google Drive.

Semua tahun dibatasi pada rentang 2000 sampai 2100.

### 4.4 Tahun default dan tahun per komponen

Ada dua tingkat pemilihan tahun:

**Tahun default halaman**

- Tombol tahun di bagian atas halaman.
- Berlaku untuk semua komponen yang belum memilih tahun sendiri.
- Mengganti tahun default akan memuat ulang halaman dan mengatur ulang pilihan tahun komponen.
- Ikon database di samping tahun menandakan tahun tersebut sudah memiliki dokumen.

**Tahun per komponen**

- Setiap komponen dokumen memiliki dropdown **Tahun** sendiri.
- Komponen 7 memiliki dropdown tahun sendiri.
- Komponen 8 memiliki dropdown tahun sendiri.
- Pilihan ini tidak mengubah komponen lain.

> **Tips:** Gunakan tahun default untuk pekerjaan sehari-hari. Gunakan pemilih tahun per komponen hanya ketika Anda perlu membandingkan dokumen dari tahun berbeda pada satu layar.

### 4.5 Tahun pada Komponen 1

Komponen 1 memiliki aturan khusus:

- Tahun penugasan dipilih melalui dropdown **Tahun identitas** di kartu Komponen 1.
- Tahun tersebut menentukan siapa PIC yang ditampilkan.
- Tahun tersebut juga menentukan berkas identitas dan tautan Drive mana yang ditampilkan.
- Mengubah tahun Komponen 1 tidak mengubah tahun komponen lain.

---

<a id="bab-5"></a>

## Bab 5 — Mengelola Bidang, Sub-Bidang, dan Banner

### 5.1 Menambah sub-bidang

1. Buka **Dashboard**.
2. Pilih tab bidang yang dituju.
3. Klik **Tambah Pengendalian Resiko** pada banner bidang.
4. Isi **Nama Pengendalian Resiko**. Maksimal 150 karakter.
5. Pilih **Bidang** bila modal memintanya.
6. Unggah **Gambar Latar** bila ingin kartu sub-bidang memiliki foto. Gambar bersifat opsional.
7. Klik tombol simpan.

Nama sub-bidang dipakai untuk membentuk alamat halaman. Beberapa spasi berlebih akan dirapikan otomatis. Hindari mengganti nama sub-bidang yang tautannya sudah banyak dibagikan, karena alamat lamanya tidak lagi cocok.

### 5.2 Mengubah sub-bidang

1. Buka tab bidang yang dituju.
2. Pada kartu sub-bidang, klik ikon pensil.
3. Ubah nama, gambar latar, atau keduanya.
4. Bila ingin mempertahankan gambar lama, biarkan kolom gambar kosong.
5. Bila ingin menghapus gambar latar, centang opsi hapus gambar.
6. Simpan perubahan.

### 5.3 Menghapus sub-bidang

1. Pada kartu sub-bidang, klik ikon tempat sampah.
2. Baca konfirmasi yang muncul.
3. Setujui penghapusan.

> **Peringatan keras:** Menghapus sub-bidang juga menghapus:
>
> - Seluruh dokumen pada semua tahun.
> - Seluruh berkas fisik dokumen di penyimpanan.
> - Seluruh penugasan PIC pada sub-bidang tersebut.
> - Seluruh tautan Google Drive pada sub-bidang tersebut.
> - Gambar latar sub-bidang.
>
> Tindakan ini tidak dapat dibatalkan. Pastikan Anda memilih kartu yang benar.

### 5.4 Mengganti banner navigasi bidang

1. Buka tab bidang yang dituju.
2. Klik **Ganti Banner** pada banner bidang.
3. Pilih berkas gambar.
4. Simpan.

Bila ingin kembali ke banner bawaan, centang opsi hapus gambar pada modal yang sama.

Aturan gambar banner:

- Format JPG, PNG, atau WebP.
- Maksimal 4 MB.
- Gambar diperkecil otomatis agar halaman tetap ringan.

### 5.5 Aturan gambar untuk sub-bidang dan banner

| Jenis gambar | Format | Ukuran maksimal | Keterangan |
|---|---|---|---|
| Gambar latar sub-bidang | JPG, PNG, WebP | 4 MB | Opsional; otomatis diperkecil |
| Banner navigasi bidang | JPG, PNG, WebP | 4 MB | Menggantikan banner bawaan |
| Gambar struktur organisasi | JPG, PNG, WebP | 4 MB | Satu gambar aktif |
| Galeri sarana dan prasarana | JPG, PNG, WebP | 4 MB | Judul wajib, keterangan opsional |
| Foto profil pegawai | JPG, PNG, WebP | 4 MB | Dapat dipotong sebelum disimpan |

---

<a id="bab-6"></a>

## Bab 6 — Mengunggah dan Mengelola Dokumen

### 6.1 Mengunggah dokumen ke satu komponen

1. Buka **Dashboard**.
2. Pilih tab bidang.
3. Klik kartu sub-bidang yang dituju.
4. Pada komponen yang dituju, pastikan dropdown **Tahun** sudah menunjukkan tahun yang benar.
5. Klik **Upload**.
6. Pada modal **Upload Dokumen**, periksa kembali **Tahun Anggaran**.
7. Klik area **Klik atau seret file ke sini**, lalu pilih satu atau beberapa berkas.
8. Isi **Keterangan per Berkas** bila perlu. Keterangan bersifat opsional, tetapi sangat disarankan.
9. Klik **Upload Dokumen**.

Setelah berhasil, Anda kembali ke komponen yang sama pada tahun yang dipilih. Pesan hijau menyebut jumlah berkas yang berhasil diunggah dan jumlah berkas yang disertai keterangan.

Bila sebagian berkas gagal, pesan merah menyebut nama berkas yang gagal. Berkas yang berhasil tetap tersimpan. Anda tidak perlu mengulang berkas yang sudah berhasil.

### 6.2 Format dan batas ukuran berkas dokumen

| Komponen | Format yang diterima | Batas unggahan | Batas akhir setelah kompresi |
|---|---|---|---|
| 1, 2, 3, 4, 5, 8 | PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, WEBP | 50 MB per berkas | 20 MB per berkas |
| 6 (Bukti Pelaksanaan) | PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, WEBP | 100 MB per berkas | Tidak dikompres, tetap 100 MB |

Cara kerja kompresi otomatis pada komponen 1, 2, 3, 4, 5, dan 8:

- Berkas sampai dengan 20 MB disimpan apa adanya.
- Berkas di atas 20 MB dikompres otomatis.
- Gambar dikompres dengan menurunkan kualitas dan, bila perlu, memperkecil dimensi.
- Berkas DOCX dan XLSX dikompres ulang sebagai arsip.
- PDF dikompres bila server memiliki dukungan yang diperlukan. Bila tidak, PDF dibiarkan apa adanya.
- Berkas DOC dan XLS lama umumnya tidak dapat dikompres otomatis.
- Bila setelah kompresi ukurannya masih di atas 20 MB, berkas ditolak dan namanya disebut pada pesan kesalahan.

> **Praktik yang disarankan:** Kompres sendiri berkas berukuran besar sebelum diunggah. Ini lebih cepat dan lebih pasti daripada mengandalkan kompresi otomatis.

### 6.3 Keterangan berkas

- Setiap berkas dapat memiliki keterangan sendiri.
- Keterangan diambil berdasarkan urutan berkas saat diunggah.
- Maksimal 1.000 karakter per keterangan.
- Keterangan membantu pencarian di halaman SOP dan memudahkan orang lain memahami isi berkas.

Contoh keterangan yang baik:

- "SK Kepala Pusat Nomor 07/KPTS/Ma/2026 tentang Penetapan PIC CRMC Tahun 2026."
- "Risk Register Reviu Januari 2026, disetujui pada rapat bidang 12 Januari 2026."
- "Bukti pelaksanaan monitoring triwulan I, berisi daftar hadir dan notula."

### 6.4 Melihat dan mengunduh berkas

Setiap berkas memiliki tombol:

- **Buka di tab baru** — membuka berkas di tab peramban baru.
- **Unduh** — mengunduh berkas ke komputer.

Perilaku pratinjau:

| Format | Tampilan |
|---|---|
| PDF | Pratinjau langsung di dalam halaman |
| JPG, JPEG, PNG, WEBP | Gambar langsung di dalam halaman |
| DOCX | Pratinjau di dalam halaman memakai pembaca dokumen |
| XLS, XLSX | Pratinjau di dalam halaman memakai pembaca spreadsheet |
| DOC | Tidak dapat dipratinjau; hanya dapat diunduh |
| Format lain | Hanya dapat diunduh |

Bila pratinjau PDF kosong, gunakan tombol **Buka di tab baru** atau **Unduh**. Sebagian peramban memblokir pratinjau PDF di dalam halaman.

### 6.5 Mengubah keterangan berkas (Admin)

1. Pada kartu berkas, klik ikon **Keterangan**.
2. Ubah teks keterangan. Maksimal 1.000 karakter.
3. Klik **Simpan Keterangan**.

Mengosongkan kolom keterangan akan menghapus keterangan berkas tersebut.

### 6.6 Menghapus satu berkas (Admin)

1. Pada kartu berkas, klik ikon **Hapus**.
2. Baca konfirmasi.
3. Setujui penghapusan.

> **Peringatan:** Menghapus berkas satuan bersifat permanen. Berkas fisik di penyimpanan ikut dihapus dan tidak dapat dipulihkan melalui aplikasi. Status residu dan ringkasan evaluasi tetap tersimpan karena keduanya bukan berkas.

### 6.7 Menambahkan tautan Google Drive

1. Pada komponen yang dituju, pastikan tahun sudah benar.
2. Klik **Link Drive** atau **Ubah Link Drive**.
3. Isi **Tahun Anggaran**.
4. Tempel tautan Google Drive pada kolom **Tautan Google Drive**.
5. Isi **Teks Tombol** bila ingin label khusus. Maksimal 120 karakter. Bila dikosongkan, tombol memakai teks "Buka Google Drive".
6. Klik **Simpan Tautan**.

Aturan tautan:

- Satu komponen hanya boleh memiliki satu tautan per tahun.
- Menyimpan tautan baru pada tahun yang sama akan menggantikan tautan lama, bukan menambah baris baru.
- Tautan harus menunjuk ke domain Google, misalnya `drive.google.com`, `docs.google.com`, atau `sheets.google.com`.
- Awalan `https://` boleh tidak ditulis; aplikasi menambahkannya otomatis.
- Tautan maksimal 500 karakter.
- Komponen 7 tidak memiliki tautan Drive.

Contoh tautan yang diterima:

```text
https://drive.google.com/drive/folders/1AbCdEfGhIjKlMnOp
drive.google.com/file/d/1AbCdEfGhIjKlMnOp/view
https://docs.google.com/spreadsheets/d/1AbCdEfGhIjKlMnOp/edit
```

Contoh tautan yang ditolak:

```text
https://example.com/dokumen
https://dropbox.com/s/abc123
```

> **Penting:** Aplikasi tidak memeriksa izin akses folder Drive. Pastikan folder atau berkas Drive dapat dibuka oleh pihak yang berhak. Jangan membagikan tautan ke folder pribadi yang bersifat sensitif.

### 6.8 Menghapus tautan Google Drive

1. Buka Modal **Link Drive** pada komponen yang bersangkutan.
2. Klik **Hapus Tautan**.
3. Setujui konfirmasi.

Admin dapat menghapus tautan siapa pun. Pegawai hanya dapat menghapus tautan yang ia buat sendiri.

Menghapus tautan **tidak** menghapus berkas atau folder di Google Drive. Yang hilang hanya tombol tautan di aplikasi.

### 6.9 Apakah tautan Drive dihitung sebagai kelengkapan dokumen?

Tidak. Kelengkapan dokumen dihitung dari **berkas yang diunggah ke aplikasi**. Komponen yang hanya memiliki tautan Drive tetap dianggap kosong pada halaman **Kelola Dokumen**.

### 6.10 Kebiasaan baik saat mengunggah

- Unggah berkas dengan nama yang jelas dan konsisten.
- Isi keterangan untuk setiap berkas penting.
- Kelompokkan berkas per tahun dengan benar.
- Jangan mengunggah berkas duplikat hanya karena lupa tahun.
- Periksa pesan merah setelah unggah; jangan anggap semua berkas berhasil.
- Untuk berkas yang sangat besar, gunakan tautan Google Drive daripada mengunggah ke aplikasi.

---

<a id="bab-7"></a>

## Bab 7 — Menetapkan PIC melalui Komponen 1

### 7.1 Tiga lapis penugasan

| Peran | Jumlah | Cakupan | Ditulis ke |
|---|---|---|---|
| Pemilik Risiko | 1 orang | Seluruh CRMC | Semua sub-bidang pada tahun tersebut |
| Pengendali Mutu | 1 orang per bidang | Seluruh sub-bidang di bidang tersebut | Semua sub-bidang pada bidang itu |
| Pengendali Risiko | Boleh banyak orang | Satu sub-bidang | Sub-bidang yang sedang dibuka |

Ketiga peran disimpan **per tahun**. Mengisi penugasan tahun 2027 tidak mengubah penugasan tahun 2026.

### 7.2 Menetapkan PIC dari halaman 8 Komponen

1. Buka halaman 8 Komponen sub-bidang yang dituju.
2. Pada kartu Komponen 1, pilih tahun melalui dropdown **Tahun identitas**.
3. Klik **Atur Penugasan (tahun yang dipilih)**.
4. Pada modal **Atur Penugasan Pegawai (Komponen 1)**, periksa **Tahun Pelaksanaan Penugasan**.
5. Pilih **Pemilik Risiko**. Hanya satu orang.
6. Pilih **Pengendali Mutu**. Hanya satu orang.
7. Pilih satu atau beberapa **Pengendali Risiko**. Minimal satu orang.
8. Klik tombol simpan.

Setelah tersimpan, halaman kembali ke tahun yang sama dan menampilkan ringkasan:

- Nama Pemilik Risiko dan jumlah sub-bidang yang tercakup.
- Nama Pengendali Mutu dan nama bidang yang tercakup.
- Jumlah anggota Pengendali Risiko untuk sub-bidang tersebut.

### 7.3 Perilaku penyimpanan yang wajib dipahami

Menyimpan penugasan dari Komponen 1 **mengganti** penugasan tahun tersebut, bukan menambahkan.

Rinciannya:

1. Seluruh baris **Pemilik Risiko** pada tahun itu dihapus, lalu diisi ulang untuk semua sub-bidang.
2. Seluruh baris **Pengendali Mutu** pada tahun itu untuk **bidang yang sedang dibuka** dihapus, lalu diisi ulang untuk semua sub-bidang di bidang tersebut.
3. Seluruh baris **Pengendali Risiko** pada tahun itu untuk **sub-bidang yang sedang dibuka** dihapus, lalu diisi ulang sesuai pilihan baru.
4. Pengendali Risiko pada sub-bidang lain tidak tersentuh.
5. Pengendali Mutu pada bidang lain tidak tersentuh.
6. Penugasan tahun lain tidak tersentuh.

> **Peringatan:** Karena Pemilik Risiko bersifat global, menyimpan penugasan dari sub-bidang mana pun akan mengganti Pemilik Risiko untuk seluruh CRMC pada tahun itu. Pastikan pilihan Anda benar sebelum menyimpan.
>
> Karena Pengendali Mutu bersifat per bidang, menyimpan dari sub-bidang lain di bidang yang sama juga akan mengganti Pengendali Mutu bidang tersebut.

### 7.4 Melihat hasil penugasan

- **Komponen 1** menampilkan nama dan foto setiap PIC untuk tahun yang dipilih.
- **Dashboard** menampilkan bagan Pemilik Risiko dan Pengendali Mutu untuk tahun berjalan.
- **Panel Admin → Penugasan Pegawai** menampilkan daftar pegawai yang belum memiliki penugasan pada tahun yang dipilih.

### 7.5 Kapan penugasan perlu diperbarui?

Perbarui penugasan ketika:

- Terjadi pergantian pejabat atau staf.
- Ada pegawai baru yang masuk tim.
- Ada pegawai yang pindah bidang.
- Memasuki tahun anggaran baru.
- Struktur bidang atau sub-bidang berubah.

### 7.6 Perbedaan dengan halaman Penugasan Pegawai

| Aspek | Komponen 1 | Panel Admin → Penugasan Pegawai |
|---|---|---|
| Cakupan | Semua peran sekaligus | Satu peran per satu waktu |
| Penggantian | Mengganti seluruh penugasan tahun itu untuk cakupan terkait | Mengganti atau menambah sesuai peran |
| Pilihan pegawai | Semua akun, termasuk admin | Hanya akun dengan role Pegawai yang belum ditugaskan pada tahun itu |
| Pilihan tahun | Tahun berjalan, tahun berikutnya, dan tahun lain yang tersedia | Hanya tahun manual; bila belum ada tahun manual, hanya tahun berjalan |
| Penghapusan penugasan | Dengan mengganti susunan | Tidak tersedia |

Karena halaman Panel Admin tidak menyediakan tombol hapus penugasan, gunakan Komponen 1 untuk mengganti susunan PIC secara menyeluruh.

---

<a id="bab-8"></a>

## Bab 8 — Status Residu Risiko dan Ringkasan Evaluasi

### 8.1 Skala residu risiko

Komponen 7 memakai lima tingkat, dari yang paling parah sampai paling terkendali:

| Urutan | Status | Makna | Warna |
|---|---|---|---|
| 1 | **Bahaya** | Tanpa dokumen | Merah |
| 2 | **Waspada II** | Tidak ada dokumen Risk Register | Oranye |
| 3 | **Waspada I** | Belum ada formulir pengendalian | Kuning |
| 4 | **Siaga** | Sudah ada bukti pengendalian | Biru |
| 5 | **Terkendali** | Tuntas | Hijau |

Skala ini adalah panduan. Aplikasi tidak menghitung status secara otomatis. Administrator memilih tingkat yang paling sesuai dengan kelengkapan dokumen dan kondisi pengendalian pada tahun tersebut.

### 8.2 Mengubah status residu

1. Buka halaman 8 Komponen.
2. Pada Komponen 7, pilih tahun melalui dropdown **Tahun**.
3. Klik tombol ubah status.
4. Pada modal **Ubah Status Residu Risiko**, periksa tahun.
5. Pilih salah satu tingkat, atau pilih **Belum Diisi** untuk mengosongkan status.
6. Klik **Simpan Status**.

Status disimpan per tahun. Mengubah status tahun 2026 tidak mengubah status tahun 2025.

### 8.3 Ringkasan evaluasi

Komponen 8 memiliki kolom **Ringkasan Evaluasi** berupa teks:

- Maksimal 5.000 karakter.
- Hanya dapat diubah oleh admin.
- Disimpan per tahun.
- Teks yang sama juga tampil pada Komponen 7 untuk tahun yang sama.

Langkah mengisi:

1. Buka Komponen 8.
2. Pilih tahun yang benar.
3. Isi ringkasan evaluasi dan rencana perbaikan.
4. Simpan.

### 8.4 Hubungan Komponen 7 dan Komponen 8

- Status residu (Komponen 7) dan ringkasan evaluasi (Komponen 8) disimpan pada record dokumen tahun yang sama.
- Keduanya bukan berkas, sehingga tidak dihitung dalam kelengkapan dokumen.
- Menghapus seluruh dokumen pada satu tahun untuk satu sub-bidang dapat menghapus record induk bila tidak ada berkas tersisa. Bila record induk terhapus, status residu dan ringkasan evaluasi tahun tersebut ikut hilang.
- Menghapus satu berkas satuan tidak menghapus record induk, sehingga status residu dan ringkasan evaluasi tetap aman.

### 8.5 Saran pengisian

- Isi status residu setelah dokumen komponen 1 sampai 6 dan 8 lengkap.
- Gunakan **Terkendali** hanya bila seluruh dokumen pendukung benar-benar tersedia.
- Tulis ringkasan evaluasi dengan struktur: temuan, penyebab, tindakan perbaikan, target waktu, dan penanggung jawab.
- Periksa kembali konsistensi antara status residu dan ringkasan evaluasi.

---

<a id="bab-9"></a>

## Bab 9 — Mengelola Daftar Tahun

### 9.1 Tahun otomatis

Tahun berjalan dan tahun berikutnya selalu tersedia tanpa tindakan apa pun. Karena itu, tombol **Tambah Tahun** hanya diperlukan untuk tahun di luar rentang otomatis, misalnya tahun lampau atau tahun khusus.

### 9.2 Menambah tahun manual

Ada dua tempat untuk menambah tahun:

**Dari halaman 8 Komponen**

1. Buka halaman 8 Komponen.
2. Klik **Tambah Tahun** di bagian pemilih tahun atau di kartu **Manajemen Tahun & Penghapusan Dokumen**.
3. Isi **Tahun**. Nilai harus antara 2000 dan 2100.
4. Isi **Keterangan** bila perlu. Maksimal 255 karakter.
5. Klik **Tambah Tahun**.

**Dari Panel Admin → Kelola Dokumen**

1. Buka **Panel Admin**.
2. Klik **Kelola Dokumen**.
3. Gulir ke kartu **Daftar Tahun Pilihan**.
4. Isi **Tahun Baru**.
5. Isi **Keterangan** bila perlu.
6. Klik **Tambah Tahun**.

Tahun yang sudah ada di daftar manual tidak dapat ditambahkan dua kali.

### 9.3 Menghapus tahun manual

1. Pada daftar **Tahun Ditambahkan Manual**, klik ikon silang di samping tahun.
2. Baca konfirmasi.
3. Setujui.

> **Penting:** Menghapus tahun dari daftar **tidak menghapus dokumen** tahun tersebut. Aplikasi akan memberi tahu bila masih ada dokumen pada tahun itu.
>
> Tahun yang dihapus dari daftar manual masih dapat muncul kembali bila tahun tersebut memiliki dokumen, penugasan, atau tautan Drive.

### 9.4 Dampak ke halaman Penugasan Pegawai

Halaman **Panel Admin → Penugasan Pegawai** hanya menampilkan tahun yang ada di daftar tahun manual. Bila daftar tahun manual kosong, halaman hanya menampilkan tahun berjalan.

Konsekuensinya:

- Bila Anda ingin menambah penugasan untuk tahun tertentu dari Panel Admin, tambahkan tahun tersebut ke daftar tahun manual terlebih dahulu.
- Bila daftar tahun manual sudah berisi tahun lain dan tahun berjalan belum ada di dalamnya, tahun berjalan tidak muncul di halaman tersebut.
- Komponen 1 tidak memiliki keterbatasan ini karena memakai gabungan semua sumber tahun.

### 9.5 Kapan sebaiknya menambah tahun manual?

- Saat menyiapkan tahun anggaran berikutnya yang lebih dari satu tahun ke depan.
- Saat memasukkan dokumen lama dari tahun lampau.
- Saat ada tahun anggaran khusus yang tidak mengikuti kalender biasa.
- Saat perlu menambah penugasan dari Panel Admin untuk tahun di luar tahun berjalan.

---

<a id="bab-10"></a>

## Bab 10 — Mengelola Akun Pegawai

### 10.1 Membuka Kelola Pegawai

1. Buka **Panel Admin**.
2. Klik **Kelola Pegawai**.

Halaman ini menampilkan:

- Tiga angka ringkasan: Total Pegawai, Administrator, dan Pegawai.
- Kotak pencarian berdasarkan nama, NIP, jabatan, atau email.
- Filter role: Semua Role, Pegawai, Administrator.
- Tabel seluruh akun dengan kolom foto, nama, NIP, jabatan, email, role, status, bidang akses, dan aksi.

### 10.2 Menambah akun pegawai

1. Klik **Tambah Pegawai Baru**.
2. Isi data pada modal **Tambah Akun Pegawai Baru**.
3. Klik **Simpan Akun**.

Kolom yang harus diisi:

| Kolom | Aturan |
|---|---|
| Nama Lengkap | Wajib, maksimal 255 karakter |
| NIP | Wajib, maksimal 30 karakter, harus unik |
| Jabatan | Wajib, maksimal 255 karakter |
| Email Dinas | Wajib, format email valid, harus unik |
| Password | Wajib, 8–64 karakter, mengandung angka dan simbol |
| Role | Wajib, pilih **Pegawai** atau **Administrator** |
| Akun aktif | Centang agar akun dapat login |
| Hitung dalam ringkasan pegawai | Centang untuk pegawai biasa; kosongkan untuk akun tamu |
| Foto Profil | Opsional, JPG/PNG/WebP, maksimal 4 MB |
| Bidang yang Dapat Diakses | Boleh kosong; pilih satu atau beberapa bidang |

### 10.3 Memahami role dan bidang akses

- **Administrator** selalu memiliki akses penuh ke semua bidang. Centang bidang tidak diperlukan.
- **Pegawai** hanya dapat membuka bidang yang dicentang.
- Bila tidak ada bidang yang dicentang, pegawai tetap dapat login tetapi tidak melihat bidang apa pun.
- Bidang akses hanya mengatur **bidang**, bukan penugasan PIC. Penugasan PIC tetap diatur terpisah.
- Pegawai dapat mengunggah dokumen dan menambah tautan Drive pada bidang yang dapat diaksesnya.
- Pegawai tidak dapat menghapus berkas, mengubah keterangan, mengubah status residu, atau mengubah tahun.

### 10.4 Akun tamu

Centang **Hitung dalam ringkasan pegawai** menentukan apakah akun dihitung dalam statistik.

- Tercentang: akun dihitung sebagai pegawai.
- Tidak tercentang: akun diberi badge **Tamu** dan tidak menambah angka Total Pegawai.

Gunakan akun tamu untuk:

- Akun peninjau atau observer.
- Akun sementara.
- Akun yang tidak mewakili pegawai aktif.

### 10.5 Mengatur foto profil

1. Pada modal tambah atau edit, klik kolom **Foto Profil**.
2. Pilih berkas JPG, PNG, atau WebP. Maksimal 4 MB.
3. Setelah berkas dipilih, modal penyesuaian foto akan terbuka.
4. Geser dan perbesar foto sesuai kebutuhan.
5. Simpan potongan foto.
6. Simpan akun.

Foto akan diperkecil otomatis agar ringan. Format HEIC dari iPhone perlu dikonversi ke JPG terlebih dahulu.

### 10.6 Mengubah akun

1. Pada baris akun, klik ikon pensil.
2. Ubah data yang diperlukan.
3. Kosongkan kolom password bila tidak ingin mengganti kata sandi.
4. Periksa kembali status **Akun aktif**.
5. Simpan perubahan.

> **Perhatian:** Pada mode edit, menghilangkan centang **Akun aktif** akan menonaktifkan akun tersebut. Pastikan centang tetap ada bila Anda hanya ingin mengubah nama atau bidang.

### 10.7 Menonaktifkan dan mengaktifkan akun

1. Pada baris akun, klik ikon tombol daya.
2. Baca konfirmasi.
3. Setujui.

Akun nonaktif:

- Tidak dapat login.
- Tetap tampil di daftar dengan badge **Nonaktif**.
- Tidak dihapus, sehingga dapat diaktifkan kembali.

Gunakan penonaktifan, bukan penghapusan, untuk pegawai yang berhenti sementara atau mutasi sementara.

### 10.8 Menghapus akun

1. Pada baris akun, klik ikon tempat sampah.
2. Baca konfirmasi.
3. Setujui.

> **Peringatan:** Penghapusan akun bersifat permanen. Foto profil ikut dihapus. Penugasan PIC milik akun tersebut ikut terhapus. Tautan Drive yang pernah dibuat tetap ada, tetapi tidak lagi tercatat pembuatnya.
>
> Gunakan penghapusan hanya untuk akun yang benar-benar tidak diperlukan lagi.

### 10.9 Perlindungan terhadap akun sendiri

Administrator tidak dapat:

- Menonaktifkan akunnya sendiri.
- Menghapus akunnya sendiri.

Tombol untuk kedua tindakan tersebut disembunyikan pada baris akun Anda sendiri. Ini mencegah administrator terkunci di luar sistem.

### 10.10 Alur offboarding pegawai

1. Nonaktifkan akun terlebih dahulu.
2. Ganti atau hapus penugasan PIC-nya melalui Komponen 1.
3. Pastikan dokumen yang pernah diunggah tetap tersedia; dokumen tidak terhapus saat akun dinonaktifkan.
4. Hapus akun hanya bila sudah dipastikan tidak diperlukan.

---

<a id="bab-11"></a>

## Bab 11 — Menambah Penugasan dari Panel Admin

### 11.1 Kapan memakai halaman ini?

Halaman **Penugasan Pegawai** berguna untuk:

- Melihat daftar pegawai yang belum memiliki penugasan pada tahun tertentu.
- Menambahkan satu penugasan tanpa membuka halaman 8 Komponen.
- Menambahkan Pengendali Risiko tambahan pada sub-bidang tertentu.

Untuk mengganti susunan PIC secara menyeluruh, gunakan Komponen 1.

### 11.2 Membuka halaman

1. Buka **Panel Admin**.
2. Klik **Penugasan Pegawai**.

### 11.3 Memilih tahun

1. Pada filter **Tahun Pelaksanaan**, pilih tahun.
2. Klik **Tampilkan**.

> **Penting:** Pilihan tahun di halaman ini berasal dari daftar tahun manual. Bila daftar tahun manual kosong, hanya tahun berjalan yang tersedia. Bila daftar tahun manual berisi tahun lain, hanya tahun-tahun manual itulah yang muncul. Tambahkan tahun yang diinginkan melalui **Kelola Dokumen → Tambah Tahun** terlebih dahulu.

### 11.4 Menambah penugasan

1. Pada kartu **Tambah Penugasan Pegawai**, pilih **Pegawai (belum ditugaskan)**.
2. Pilih **Pengendalian Resiko**.
3. Pilih **Peran**.
4. Klik **Tambah Penugasan**.

Daftar pegawai yang tersedia hanya berisi akun dengan role **Pegawai** yang belum memiliki penugasan pada tahun tersebut. Akun dengan role **Admin** tidak muncul di daftar ini, meskipun dapat ditugaskan melalui Komponen 1.

### 11.5 Perilaku setiap peran

| Peran | Perilaku saat disimpan |
|---|---|
| **Pengendali Risiko** | Menambahkan satu baris untuk sub-bidang yang dipilih. Bila kombinasi pegawai, peran, sub-bidang, dan tahun sudah ada, tidak terjadi duplikasi. |
| **Pengendali Mutu** | Menghapus seluruh Pengendali Mutu pada bidang yang memiliki sub-bidang terpilih untuk tahun itu, lalu menetapkan pegawai tersebut untuk semua sub-bidang di bidang tersebut. |
| **Pemilik Risiko** | Menghapus seluruh Pemilik Risiko pada tahun itu, lalu menetapkan pegawai tersebut untuk semua sub-bidang. Pilihan **Pengendalian Resiko** tidak memengaruhi cakupan, tetapi tetap wajib dipilih untuk validasi. |

### 11.6 Keterbatasan yang perlu diketahui

- Tidak ada tombol hapus penugasan di halaman ini.
- Tidak ada daftar penugasan yang sudah ada; halaman hanya menampilkan pegawai yang belum ditugaskan.
- Akun nonaktif masih dapat muncul di daftar pegawai yang belum ditugaskan.
- Untuk menghapus atau mengganti penugasan, buka Komponen 1.
- Untuk menugaskan akun admin sebagai PIC, buka Komponen 1.

### 11.7 Memeriksa hasil

Setelah menyimpan, Anda kembali ke halaman yang sama pada tahun yang dipilih dengan pesan berhasil. Untuk melihat susunan lengkap:

1. Buka halaman 8 Komponen sub-bidang terkait.
2. Pilih tahun yang sama pada Komponen 1.
3. Periksa nama Pemilik Risiko, Pengendali Mutu, dan daftar Pengendali Risiko.

---

<a id="bab-12"></a>

## Bab 12 — Memantau Kelengkapan di Kelola Dokumen

### 12.1 Fungsi halaman

Halaman **Kelola Dokumen** menjawab satu pertanyaan: **sub-bidang mana yang dokumennya belum lengkap pada tahun tertentu, dan komponen apa saja yang masih kosong?**

Halaman ini juga menjadi tempat untuk:

- Menambah dan menghapus tahun manual.
- Menghapus seluruh dokumen satu tahun di semua sub-bidang.

### 12.2 Membuka halaman

1. Buka **Panel Admin**.
2. Klik **Kelola Dokumen**.

### 12.3 Membaca ringkasan

Tiga angka di bagian atas:

- **Tahun Dipilih** — tahun yang sedang dianalisis.
- **Belum Lengkap** — jumlah sub-bidang yang masih memiliki komponen kosong.
- **Sudah Lengkap** — jumlah sub-bidang yang seluruh komponen dokumennya sudah terisi.

Angka ringkasan selalu mencakup seluruh sub-bidang, bukan hasil pencarian. Ini memudahkan Anda membandingkan kondisi keseluruhan.

### 12.4 Komponen yang dihitung

Kelengkapan dihitung dari **tujuh komponen dokumen**:

1. Identitas Pegawai.
2. Risk Register Spesifik Acuan.
3. SOP.
4. Formulir Pengendalian / Daftar Periksa.
5. Jadwal Rencana Pelaksanaan.
6. Bukti Pelaksanaan.
8. Evaluasi dan Rencana Perbaikan.

Komponen 7 tidak dihitung karena bukan dokumen.

Sebuah komponen dianggap terisi bila memiliki **minimal satu berkas** pada tahun tersebut. Tautan Google Drive tidak dihitung.

### 12.5 Memfilter daftar

- **Tahun** — mengganti tahun akan langsung memuat ulang halaman.
- **Cari Pengendalian Resiko** — mencari berdasarkan nama sub-bidang atau nama bidang.
- **Terapkan** — menjalankan pencarian.
- **Reset** — menghapus kata kunci.
- **Tampilkan yang sudah lengkap** — menampilkan juga sub-bidang yang sudah lengkap. Secara bawaan, halaman hanya menampilkan yang belum lengkap.

### 12.6 Membuka sub-bidang dari daftar

Setiap kartu sub-bidang menampilkan:

- Badge **Lengkap** atau jumlah komponen kosong.
- Nama sub-bidang dan bidangnya.
- Daftar komponen yang masih kosong.
- Ikon **Buka halaman 8 Komponen**.

Klik ikon tersebut untuk langsung membuka halaman sub-bidang pada tahun yang sedang dipilih. Di sana Anda dapat mengunggah dokumen yang kurang.

### 12.7 Menambah tahun manual

Lihat [Bab 9](#bab-9). Tahun manual yang ditambahkan di halaman ini juga muncul di halaman 8 Komponen dan halaman Penugasan Pegawai.

### 12.8 Menghapus seluruh dokumen satu tahun

1. Gulir ke kartu **Hapus Seluruh Dokumen Satu Tahun**.
2. Pilih **Tahun**.
3. Klik **Hapus Semua Dokumen Tahun Tersebut**.
4. Baca peringatan dengan saksama.
5. Setujui.

Yang terjadi:

- Seluruh berkas dokumen pada tahun tersebut dihapus dari **semua sub-bidang**.
- Berkas fisik di penyimpanan ikut dihapus.
- Record dokumen induk dihapus bila tidak ada berkas tersisa.
- Status residu dan ringkasan evaluasi pada record yang terhapus ikut hilang.
- Tautan Google Drive **tidak** ikut terhapus.
- Penugasan PIC **tidak** ikut terhapus.
- Tahun manual **tidak** ikut terhapus.

Bila tidak ada berkas sama sekali pada tahun tersebut, aplikasi menolak dan menampilkan pesan bahwa tidak ada yang dihapus.

> **Peringatan:** Tindakan ini tidak dapat dibatalkan. Pastikan tahun yang dipilih benar. Untuk menghapus hanya satu sub-bidang atau satu komponen, gunakan penghapusan dari halaman 8 Komponen.

### 12.9 Menghapus dokumen satu sub-bidang atau satu komponen

1. Buka halaman 8 Komponen sub-bidang yang dituju.
2. Gulir ke kartu **Manajemen Tahun & Penghapusan Dokumen**.
3. Pada bagian **Hapus Dokumen Pengendalian Resiko Ini per Tahun**, pilih **Tahun**.
4. Pilih **Cakupan Komponen**:
   - **Semua komponen dokumen (1,2,3,4,5,6,8)** — menghapus seluruh berkas tahun itu pada sub-bidang ini.
   - **Hanya Komponen X** — menghapus berkas satu komponen saja.
5. Klik **Hapus di Pengendalian Resiko Ini**.
6. Setujui konfirmasi.

Perilaku penting:

- Bila memilih satu komponen dan komponen lain masih memiliki berkas, record induk dipertahankan. Status residu dan ringkasan evaluasi tetap aman.
- Bila komponen yang dihapus adalah satu-satunya komponen yang memiliki berkas, record induk ikut terhapus dan status residu/evaluasi hilang.
- Bila memilih semua komponen dan tidak ada berkas tersisa, record induk dihapus. Status residu dan ringkasan evaluasi ikut hilang.
- Tautan Drive tidak ikut terhapus.
- Berkas fisik ikut terhapus.

### 12.10 Alur kerja pemantauan yang disarankan

1. Buka **Kelola Dokumen**.
2. Pilih tahun berjalan.
3. Catat sub-bidang dengan jumlah komponen kosong terbanyak.
4. Buka satu per satu melalui ikon panah.
5. Unggah dokumen yang kurang.
6. Kembali ke **Kelola Dokumen** dan periksa apakah jumlah belum lengkap berkurang.
7. Ulangi sampai seluruh sub-bidang lengkap atau sampai batas waktu yang ditetapkan.

---

<a id="bab-13"></a>

## Bab 13 — Memulihkan Dokumen dari Private Storage

### 13.1 Kapan fitur ini dipakai?

Fitur **Pemulihan Dokumen** dipakai bila:

- Berkas fisik masih ada di penyimpanan server, tetapi datanya tidak ada di basis data.
- Terjadi migrasi atau pemulihan basis data yang tidak disertai pemulihan tabel lampiran.
- Ada berkas yang dimasukkan ke folder penyimpanan secara manual.
- Terjadi kegagalan sebagian saat proses unggah.

Fitur ini **tidak** memulihkan berkas yang sudah terhapus dari penyimpanan. Fitur ini hanya mendaftarkan ulang berkas yang masih ada.

### 13.2 Membuka halaman

1. Buka **Panel Admin**.
2. Klik **Pemulihan Dokumen**.

Halaman akan memindai folder `crmc` pada private storage dan membandingkannya dengan data lampiran di basis data.

### 13.3 Memahami status

| Status | Arti | Dapat dipulihkan? |
|---|---|---|
| **Siap** | Struktur folder dikenali, tahun valid, sub-bidang ditemukan, kategori dikenal, dokumen induk ditemukan, dan ekstensi berkas ada. | Ya |
| **Perlu Periksa** | Salah satu syarat belum terpenuhi, misalnya sub-bidang tidak ditemukan, kategori tidak dikenal, atau dokumen induk belum ada. | Tidak, sampai penyebabnya diperbaiki |
| **Tidak Dapat** | Struktur folder tidak dikenali atau tahun tidak valid. | Tidak |
| **Terdaftar** | Berkas sudah memiliki record lampiran. | Tidak perlu dipulihkan; status ini tidak ditampilkan di daftar |

Alasan untuk status **Perlu Periksa** dan **Tidak Dapat** ditampilkan pada kolom **Keterangan**.

Penyebab umum:

- **Pengendalian resiko tidak ditemukan** — ID sub-bidang pada nama folder tidak ada di basis data. Kemungkinan sub-bidang sudah dihapus.
- **Kategori komponen tidak dikenali** — nama folder komponen tidak sesuai daftar yang dikenal.
- **Dokumen induk tidak ditemukan** — belum ada record dokumen untuk kombinasi sub-bidang dan tahun tersebut. Unggah satu berkas melalui aplikasi terlebih dahulu agar record induk terbentuk, lalu pindai ulang.
- **Ekstensi file tidak dikenali** — nama berkas tidak memiliki ekstensi.
- **Struktur folder tidak dikenali** — jumlah segmen folder bukan lima atau folder utama bukan `crmc`.
- **Tahun tidak valid** — nama folder tahun bukan empat digit angka.

### 13.4 Struktur folder yang dikenali

```text
crmc/{tahun}/{id_sub_bidang}/{kategori_komponen}/{nama_berkas}
```

Contoh:

```text
crmc/2026/12/risk_register/1730000000_risk-register-2026.pdf
```

Kategori yang dikenali:

- `identitas_pegawai`
- `risk_register`
- `sop`
- `formulir_pengendalian`
- `jadwal_pelaksanaan`
- `bukti_pelaksanaan`
- `evaluasi`

### 13.5 Memindai ulang

Hasil pemindaian disimpan sementara selama 10 menit. Bila Anda baru saja menambah, memindahkan, atau menghapus berkas di penyimpanan, klik **Pindai Ulang** di kanan atas. Tombol ini membuang cache dan membaca ulang isi penyimpanan.

### 13.6 Memulihkan satu berkas

1. Cari berkas melalui kotak **Cari Nama File**, filter **Tahun**, atau filter **Kategori**.
2. Klik **Terapkan Filter**.
3. Pada baris berstatus **Siap**, klik ikon **Pulihkan**.
4. Setujui konfirmasi.
5. Periksa pesan berhasil.

### 13.7 Memulihkan beberapa berkas sekaligus

1. Centang beberapa berkas berstatus **Siap**.
2. Centang kotak di kepala tabel untuk memilih semua berkas **Siap** pada halaman tersebut.
3. Klik **Pulihkan Terpilih**.
4. Setujui konfirmasi.
5. Periksa ringkasan berhasil dan gagal.

Hanya berkas berstatus **Siap** yang memiliki kotak centang.

### 13.8 Hal yang perlu diketahui

- Pemulihan tidak mengubah atau menghapus berkas fisik.
- Keterangan berkas hasil pemulihan kosong. Anda dapat mengisinya setelah pemulihan.
- Pemulihan tidak membuat dokumen induk baru. Dokumen induk harus sudah ada.
- Setiap pemulihan dicatat pada log aplikasi.
- Setelah pemulihan, halaman **Kelola Dokumen** akan menghitung berkas tersebut sebagai bagian dari kelengkapan.

---

<a id="bab-14"></a>

## Bab 14 — Mengelola Isi Halaman Beranda

### 14.1 Gambar struktur organisasi

Halaman Beranda menampilkan satu gambar bagan struktur organisasi. Hanya ada satu gambar aktif pada satu waktu.

**Mengunggah atau mengganti gambar**

1. Buka **Beranda**.
2. Gulir ke bagian **Struktur Organisasi**.
3. Klik **Unggah Gambar** bila belum ada gambar, atau **Ganti Gambar** bila sudah ada.
4. Pilih berkas JPG, PNG, atau WebP. Maksimal 4 MB.
5. Isi **Keterangan** bila perlu. Maksimal 300 karakter.
6. Klik **Unggah** atau **Ganti**.

Gambar lama otomatis dihapus setelah gambar baru berhasil disimpan.

**Menghapus gambar**

1. Klik **Hapus** pada bagian Struktur Organisasi.
2. Setujui konfirmasi.

Setelah dihapus, Beranda menampilkan keadaan kosong sampai Anda mengunggah gambar baru.

### 14.2 Galeri sarana dan prasarana

Galeri tampil sebagai carousel pada Beranda.

**Menambah gambar galeri**

1. Pada bagian galeri, klik tombol tambah gambar.
2. Pilih berkas JPG, PNG, atau WebP. Maksimal 4 MB.
3. Isi **Judul**. Wajib, maksimal 150 karakter.
4. Isi **Keterangan**. Opsional, maksimal 500 karakter.
5. Klik **Unggah**.

**Mengubah gambar galeri**

1. Klik ikon pensil pada gambar.
2. Ubah judul atau keterangan.
3. Kosongkan kolom gambar bila tidak ingin mengganti gambar lama.
4. Klik **Simpan Perubahan**.

**Menghapus gambar galeri**

1. Klik ikon tempat sampah pada gambar.
2. Setujui konfirmasi.

Berkas gambar ikut dihapus dari server.

### 14.3 Urutan galeri

Saat ini **belum ada antarmuka untuk mengubah urutan galeri**. Gambar diurutkan berdasarkan nilai urutan, lalu berdasarkan ID.

Gambar baru selalu memperoleh urutan terakhir. Bila Anda perlu mengubah urutan secara praktis:

1. Hapus gambar yang ingin dipindahkan.
2. Unggah ulang gambar tersebut.
3. Gambar akan muncul di urutan paling akhir.

Cara ini mengubah urutan, tetapi juga mengganti ID gambar. Lakukan hanya bila benar-benar diperlukan.

### 14.4 Aturan gambar Beranda

| Item | Format | Ukuran | Keterangan |
|---|---|---|---|
| Gambar struktur | JPG, PNG, WebP | 4 MB | Keterangan maksimal 300 karakter |
| Galeri | JPG, PNG, WebP | 4 MB | Judul wajib maksimal 150 karakter; keterangan maksimal 500 karakter |

Semua gambar diperkecil otomatis agar halaman tetap ringan.

---

<a id="bab-15"></a>

## Bab 15 — Mengelola Berkas Dasar Hukum

### 15.1 Daftar regulasi

Tab **Dasar Hukum** berisi empat regulasi utama yang sudah disediakan oleh sistem:

1. Surat Edaran Menteri Pekerjaan Umum dan Perumahan Rakyat Nomor 12/SE/M/2024.
2. Surat Edaran Kepala Badan Pengembangan Sumber Daya Manusia Nomor 03/SE/Km/2025.
3. Keputusan Kepala Pusat Pengembangan Kompetensi Sumber Daya Air, Cipta Karya dan Prasarana Strategis Nomor 07/KPTS/Ma/2026.
4. Keputusan Kepala Pusat Pengembangan Kompetensi Sumber Daya Air, Cipta Karya dan Prasarana Strategis Nomor 08/KPTS/Ma/2026.

> **Catatan:** Daftar regulasi tidak dapat ditambah atau dihapus melalui antarmuka. Administrator hanya dapat mengunggah, mengganti, dan menghapus berkas salinan untuk setiap regulasi.

### 15.2 Mengunggah berkas regulasi

1. Buka **Dashboard**.
2. Klik tab **Dasar Hukum**.
3. Pada kartu regulasi yang dituju, klik **Unggah Dokumen** atau **Ganti Dokumen**.
4. Pilih berkas.
5. Klik tombol unggah.
6. Tunggu halaman dimuat ulang. Pratinjau akan langsung tampil.

Aturan berkas:

- Format: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, atau PNG.
- Maksimal unggahan 50 MB.
- Berkas di atas 20 MB dikompres otomatis.
- Bila setelah kompresi masih di atas 20 MB, unggahan ditolak dan berkas lama tetap utuh.
- Unggahan baru otomatis menggantikan berkas lama.

### 15.3 Menghapus berkas regulasi

1. Pada kartu regulasi, klik **Hapus**.
2. Setujui konfirmasi.

Yang dihapus hanya berkas salinannya. Identitas regulasi tetap ada, sehingga Anda dapat mengunggah berkas baru kapan saja.

### 15.4 Akses tamu

Tamu dapat membaca tab Dasar Hukum tanpa login. Karena itu, pastikan berkas yang diunggah memang boleh dibaca publik. Jangan mengunggah dokumen bersifat rahasia ke tab ini.

---

<a id="bab-16"></a>

## Bab 16 — Menggunakan Halaman SOP

### 16.1 Fungsi halaman SOP

Halaman **SOP** menghimpun seluruh berkas pada Komponen 3 dari semua sub-bidang yang boleh Anda lihat. Halaman ini tidak menyimpan data baru. Semua perubahan tetap dilakukan dari halaman 8 Komponen.

### 16.2 Membuka halaman

Klik **SOP** pada navigasi. Pengguna yang belum login akan mendapat kode 403.

### 16.3 Filter yang tersedia

- **Kata Kunci** — mencari nama berkas, keterangan, atau nama sub-bidang.
- **Bidang** — membatasi ke satu bidang.
- **Tahun** — membatasi ke satu tahun.
- **Terapkan Filter** — menjalankan filter.
- **Reset** — menghapus semua filter.

Pegawai hanya melihat SOP dari bidang yang ditugaskan. Filter bidang di luar hak aksesnya akan diabaikan.

### 16.4 Tampilan grid dan daftar

Gunakan tombol **Grid** atau **Daftar** di kanan atas untuk mengubah tampilan. Pilihan ini hanya mengubah tampilan, bukan isi.

### 16.5 Memakai halaman SOP untuk audit

Administrator dapat memakai halaman ini untuk:

- Memastikan seluruh sub-bidang memiliki SOP.
- Membandingkan jumlah SOP antar bidang.
- Mencari SOP tertentu dengan kata kunci.
- Memeriksa SOP dari tahun tertentu.

Bila sebuah SOP tidak muncul di halaman ini, periksa apakah berkas benar-benar diunggah pada **Komponen 3** dan pada tahun yang benar.

---

<a id="bab-17"></a>

## Bab 17 — Daftar Periksa Rutin Administrator

### 17.1 Awal tahun anggaran

- [ ] Pastikan tahun berjalan sudah muncul di halaman 8 Komponen.
- [ ] Tambahkan tahun berikutnya bila perlu perencanaan lebih awal.
- [ ] Perbarui penugasan PIC melalui Komponen 1.
- [ ] Periksa bagan struktur pada Dashboard.
- [ ] Pastikan akun pegawai baru sudah dibuat dan bidang aksesnya benar.
- [ ] Nonaktifkan akun pegawai yang sudah tidak bertugas.
- [ ] Umumkan kepada pegawai bahwa tahun baru sudah dapat diisi.

### 17.2 Rutin bulanan

- [ ] Buka **Kelola Dokumen** dan pilih tahun berjalan.
- [ ] Catat sub-bidang yang belum lengkap.
- [ ] Tindak lanjuti sub-bidang dengan komponen kosong terbanyak.
- [ ] Periksa berkas yang gagal diunggah.
- [ ] Periksa tautan Drive yang sudah tidak valid.
- [ ] Periksa halaman SOP untuk memastikan SOP baru sudah muncul.

### 17.3 Rutin triwulanan

- [ ] Periksa kelengkapan komponen 1 sampai 6 dan 8.
- [ ] Perbarui status residu risiko bila kondisi pengendalian berubah.
- [ ] Perbarui ringkasan evaluasi.
- [ ] Periksa konsistensi status residu dengan bukti pelaksanaan.
- [ ] Tinjau akun pegawai yang tidak aktif.

### 17.4 Akhir tahun

- [ ] Pastikan seluruh dokumen tahun berjalan sudah lengkap.
- [ ] Perbarui status residu dan ringkasan evaluasi tahun berjalan.
- [ ] Siapkan penugasan tahun berikutnya.
- [ ] Jangan menghapus dokumen tahun berjalan sebelum periode audit selesai.
- [ ] Arsipkan laporan atau ekspor data bila diperlukan.
- [ ] Periksa daftar tahun manual dan rapikan tahun yang tidak lagi diperlukan.

### 17.5 Saat menambah pegawai baru

- [ ] Buat akun dengan role yang benar.
- [ ] Isi NIP dan email yang unik.
- [ ] Buat kata sandi sementara yang memenuhi aturan.
- [ ] Centang bidang akses yang sesuai.
- [ ] Tentukan apakah akun dihitung sebagai pegawai atau tamu.
- [ ] Tambahkan penugasan PIC bila pegawai tersebut menjadi PIC.
- [ ] Minta pegawai mengganti kata sandi setelah masuk pertama kali.

### 17.6 Saat menambah sub-bidang

- [ ] Tentukan bidang yang tepat.
- [ ] Gunakan nama yang jelas dan konsisten.
- [ ] Unggah gambar latar bila diperlukan.
- [ ] Tetapkan Pengendali Risiko melalui Komponen 1.
- [ ] Unggah dokumen komponen 1 sampai 6 dan 8.
- [ ] Isi status residu dan ringkasan evaluasi.

### 17.7 Sebelum menghapus data

- [ ] Pastikan tahun yang dipilih benar.
- [ ] Pastikan sub-bidang yang dipilih benar.
- [ ] Pastikan dokumen tersebut tidak sedang dibutuhkan untuk audit.
- [ ] Pertimbangkan untuk mengunduh salinan terlebih dahulu.
- [ ] Ingat bahwa penghapusan berkas bersifat permanen.
- [ ] Ingat bahwa menghapus sub-bidang menghapus seluruh dokumen dan penugasannya.

---

<a id="bab-18"></a>

## Bab 18 — Ringkasan Aturan Berkas dan Batas Ukuran

### 18.1 Dokumen CRMC

| Komponen | Format | Batas unggahan | Batas akhir | Kompresi otomatis |
|---|---|---|---|---|
| 1, 2, 3, 4, 5, 8 | PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, WEBP | 50 MB | 20 MB | Ya, untuk berkas di atas 20 MB |
| 6 | PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG, WEBP | 100 MB | 100 MB | Tidak |

### 18.2 Berkas pendukung

| Jenis | Format | Batas | Kompresi otomatis |
|---|---|---|---|
| Berkas Dasar Hukum | PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG | 50 MB unggahan; 20 MB akhir | Ya |
| Gambar struktur organisasi | JPG, PNG, WebP | 4 MB | Ya, diperkecil |
| Gambar galeri | JPG, PNG, WebP | 4 MB | Ya, diperkecil |
| Banner bidang | JPG, PNG, WebP | 4 MB | Ya, diperkecil |
| Gambar latar sub-bidang | JPG, PNG, WebP | 4 MB | Ya, diperkecil |
| Foto profil pegawai | JPG, PNG, WebP | 4 MB | Ya, diperkecil |

### 18.3 Batas panjang teks

| Teks | Batas |
|---|---|
| Keterangan berkas | 1.000 karakter |
| Label tombol Drive | 120 karakter |
| Tautan Drive | 500 karakter |
| Ringkasan evaluasi | 5.000 karakter |
| Keterangan gambar struktur | 300 karakter |
| Judul galeri | 150 karakter |
| Keterangan galeri | 500 karakter |
| Keterangan tahun manual | 255 karakter |
| Nama pegawai | 255 karakter |
| NIP | 30 karakter |
| Jabatan | 255 karakter |
| Nama sub-bidang | 150 karakter |
| Kata sandi | 8–64 karakter |

### 18.4 Batas frekuensi tindakan

Aplikasi membatasi beberapa tindakan untuk mencegah penyalahgunaan:

| Tindakan | Batas |
|---|---|
| Login | 5 percobaan per menit per email; 10 percobaan per menit per IP |
| Permintaan tautan reset kata sandi | 3 permintaan per menit per email; 10 permintaan per menit per IP |
| Unggah dokumen | 30 unggahan per menit per pengguna |
| Tindakan admin | 120 permintaan per menit per pengguna |

Bila terkena batas, tunggu satu menit lalu coba lagi.

---

<a id="bab-19"></a>

## Bab 19 — Penanganan Masalah Umum

### 19.1 Tidak dapat login

| Gejala | Kemungkinan penyebab | Solusi |
|---|---|---|
| Email atau kata sandi salah | Salah ketik atau kapitalisasi | Periksa ulang; gunakan fitur tampilkan kata sandi |
| Akun tidak aktif | Dinonaktifkan admin | Minta admin lain mengaktifkan |
| Verifikasi keamanan gagal | Turnstile belum selesai | Selesaikan verifikasi lalu kirim ulang |
| Terlalu banyak percobaan | Batas login tercapai | Tunggu satu menit |
| Email tidak terdaftar | Akun belum dibuat | Minta admin membuat akun |

### 19.2 Mendapat kode 403

| Halaman | Penyebab | Solusi |
|---|---|---|
| Halaman 8 Komponen | Bidang tidak ditugaskan ke akun Anda | Minta admin menambahkan bidang akses |
| Halaman SOP | Belum login | Masuk terlebih dahulu. Pegawai yang sudah masuk tetapi belum punya bidang akan melihat halaman kosong, bukan 403. |
| Panel Admin | Akun bukan admin | Gunakan akun admin |
| Dasar Hukum saat mengunggah | Akun bukan admin | Gunakan akun admin |

### 19.3 Unggahan gagal

| Pesan | Penyebab | Solusi |
|---|---|---|
| "Ukuran file maksimal 50MB per file" | Berkas melebihi 50 MB pada komponen 1–5 atau 8 | Kompres berkas secara manual |
| "Ukuran file maksimal 100MB per file" | Berkas melebihi 100 MB pada Komponen 6 | Kompres atau pecah berkas |
| "Ukuran berkas tetap melebihi 20MB setelah dikompres" | Kompresi otomatis tidak cukup | Kompres manual, turunkan resolusi gambar, atau gunakan Drive |
| "Format file harus ..." | Format tidak diizinkan | Konversi ke PDF, DOCX, XLSX, atau gambar yang didukung |
| "Tidak ada file yang berhasil diunggah" | Semua berkas gagal | Periksa pesan rinci dan coba satu berkas terlebih dahulu |
| "File gagal disimpan ke server" | Masalah izin folder atau ruang penyimpanan | Hubungi pengelola server |

### 19.4 Tautan Google Drive ditolak

| Pesan | Penyebab | Solusi |
|---|---|---|
| "Tautan harus menunjuk ke Google Drive" | Domain bukan Google | Gunakan tautan `drive.google.com` atau `docs.google.com` |
| "Tautan maksimal 500 karakter" | Tautan terlalu panjang | Gunakan tautan pendek dari Google Drive |
| "Teks tombol maksimal 120 karakter" | Label terlalu panjang | Pendekkan label |

### 19.5 Dokumen tidak muncul

1. Periksa tahun pada komponen. Mungkin dokumen tersimpan pada tahun lain.
2. Periksa apakah berkas benar-benar berhasil diunggah; lihat pesan setelah unggah.
3. Periksa apakah Anda membuka sub-bidang yang benar.
4. Periksa apakah berkas berada pada komponen yang benar.
5. Bila baru memulihkan berkas, klik **Pindai Ulang** di halaman Pemulihan Dokumen.

### 19.6 Kelengkapan tetap dianggap kosong

Penyebab paling umum:

- Komponen hanya memiliki tautan Google Drive. Tautan tidak dihitung.
- Berkas tersimpan pada tahun yang berbeda dari tahun yang sedang diperiksa.
- Berkas berada pada komponen yang berbeda.
- Sub-bidang yang diperiksa berbeda.

### 19.7 Tahun tidak muncul di halaman Penugasan Pegawai

Halaman tersebut hanya memakai daftar tahun manual. Tambahkan tahun yang diinginkan melalui **Kelola Dokumen → Tambah Tahun**.

### 19.8 Tahun manual dihapus tetapi dokumen masih ada

Ini perilaku yang benar. Menghapus tahun manual hanya menghilangkan pilihannya. Dokumen tidak ikut terhapus.

### 19.9 Pratinjau PDF kosong

- Gunakan tombol **Buka di tab baru** atau **Unduh**.
- Pastikan berkas PDF tidak rusak.
- Coba peramban lain.

### 19.10 Berkas DOC tidak dapat dipratinjau

Berkas DOC lama hanya dapat diunduh. Konversi ke DOCX atau PDF bila ingin pratinjau di halaman.

### 19.11 Halaman menampilkan "419 Page Expired"

Sesi Anda sudah kedaluwarsa. Muat ulang halaman, masuk kembali, lalu ulangi tindakan.

### 19.12 Halaman menampilkan "429 Too Many Requests"

Anda terkena batas frekuensi. Tunggu satu menit lalu coba lagi. Hindari menekan tombol simpan berulang kali.

### 19.13 Perubahan tidak terlihat

- Muat ulang halaman dengan `Ctrl + F5`.
- Pastikan Anda menyimpan perubahan, bukan hanya menutup modal.
- Periksa pesan kesalahan; mungkin penyimpanan gagal.

### 19.14 Pemulihan dokumen tidak tersedia

- Status berkas bukan **Siap**.
- Dokumen induk belum ada. Unggah satu berkas melalui aplikasi terlebih dahulu.
- Sub-bidang atau kategori tidak dikenal.
- Berkas sudah terdaftar.

Baca kolom **Keterangan** untuk mengetahui alasan pastinya.

---

<a id="bab-20"></a>

## Bab 20 — Keamanan dan Praktik Terbaik

### 20.1 Akun dan kata sandi

- Gunakan satu akun untuk satu orang. Jangan berbagi akun.
- Buat kata sandi sementara yang kuat dan minta pengguna menggantinya.
- Jangan mengirim kata sandi melalui kanal publik.
- Nonaktifkan akun segera setelah pegawai tidak bertugas.
- Tinjau daftar akun secara berkala.

### 20.2 Hak akses minimum

- Berikan role **Admin** hanya kepada orang yang benar-benar memerlukan.
- Berikan bidang akses sesuai kebutuhan pegawai.
- Jangan mencentang semua bidang hanya untuk kemudahan.
- Gunakan akun tamu untuk pengguna yang hanya perlu melihat.

### 20.3 Pengelolaan dokumen

- Simpan dokumen pada tahun yang benar.
- Isi keterangan untuk setiap berkas penting.
- Gunakan nama berkas yang konsisten.
- Hindari mengunggah dokumen rahasia ke tab Dasar Hukum karena tab tersebut dapat dibaca tamu.
- Unduh salinan sebelum menghapus data penting.
- Periksa kembali tahun dan sub-bidang sebelum menghapus.

### 20.4 Tautan Google Drive

- Pastikan izin folder Drive sesuai dengan pihak yang berhak.
- Jangan membagikan tautan folder pribadi.
- Periksa tautan secara berkala; tautan yang rusak akan mengecewakan pengguna.
- Ingat bahwa menghapus tautan di aplikasi tidak menghapus isi Drive.

### 20.5 Perangkat dan sesi

- Keluar setelah memakai perangkat bersama.
- Jangan mencentang **Ingat saya** pada perangkat umum.
- Gunakan peramban yang diperbarui.
- Hindari mengakses aplikasi melalui jaringan publik yang tidak tepercaya.

### 20.6 Jejak perubahan

Aplikasi tidak menyediakan halaman riwayat perubahan dokumen. Karena itu:

- Catat perubahan penting pada catatan internal unit Anda.
- Gunakan keterangan berkas untuk menuliskan konteks dan tanggal.
- Simpan salinan dokumen penting di luar aplikasi bila diperlukan.
- Hati-hati saat menghapus; tidak ada tombol urungkan.

---

<a id="lampiran-a"></a>

## Lampiran A — Peta Alamat Halaman

| Halaman | Alamat |
|---|---|
| Beranda | `/` atau `/home` |
| Login | `/login` |
| Lupa kata sandi | `/forgot-password` |
| Dashboard | `/dashboard` |
| Tab Dasar Hukum | `/dashboard?tab=dasar-hukum` |
| Tab bidang | `/dashboard?tab=bidang-{id}` |
| Halaman 8 Komponen | `/crmc/{slug}` |
| Halaman 8 Komponen tahun tertentu | `/crmc/{slug}?tahun=2026` |
| Halaman 8 Komponen tahun per komponen | `/crmc/{slug}?tahun=2026&t[risk_register]=2025` |
| Halaman SOP | `/sop` |
| Panel Admin | `/admin` |
| Kelola Pegawai | `/admin/pegawai` |
| Penugasan Pegawai | `/admin/penugasan` |
| Kelola Dokumen | `/admin/dokumen` |
| Pemulihan Dokumen | `/admin/pemulihan-dokumen` |

> **Catatan:** Alamat dengan `{slug}` dan `{id}` hanya contoh. Gunakan tautan yang tersedia di antarmuka agar tidak salah ketik.

---

<a id="lampiran-b"></a>

## Lampiran B — Glosarium

| Istilah | Penjelasan |
|---|---|
| **Admin** | Role akun dengan akses penuh. |
| **Bidang** | Unit organisasi tingkat pertama. |
| **Berkas fisik** | File yang tersimpan di server, bukan sekadar data di basis data. |
| **Dokumen induk** | Record yang menghubungkan satu sub-bidang dan satu tahun, tempat berkas dan status residu disimpan. |
| **Drive link** | Tautan ke Google Drive yang ditampilkan sebagai tombol. |
| **Kelengkapan** | Persentase atau jumlah komponen dokumen yang sudah memiliki minimal satu berkas. |
| **Komponen** | Delapan bagian isi halaman sub-bidang. |
| **PIC** | Person in Charge; Pemilik Risiko, Pengendali Mutu, atau Pengendali Risiko. |
| **Private storage** | Penyimpanan berkas yang tidak dapat diakses langsung lewat URL publik. |
| **Residu risiko** | Tingkat sisa risiko setelah pengendalian. |
| **Role** | Jenis akun: Admin atau Pegawai. |
| **Sub-bidang** | Proses kerja di bawah bidang; disebut juga pengendalian risiko. |
| **Tahun anggaran** | Tahun yang menempel pada dokumen dan penugasan. |
| **Tahun manual** | Tahun tambahan yang dibuat administrator. |
| **Tahun otomatis** | Tahun berjalan dan tahun berikutnya yang selalu tersedia. |

---

<a id="lampiran-c"></a>

## Lampiran C — Lembar Periksa Kelengkapan per Sub-Bidang

Gunakan lembar ini untuk memeriksa satu sub-bidang pada satu tahun.

**Identitas**

- Nama bidang: ______________________________
- Nama sub-bidang: ___________________________
- Tahun: ____________________________________
- Diperiksa oleh: ____________________________
- Tanggal: __________________________________

**Komponen dokumen**

| No | Komponen | Ada berkas? | Jumlah berkas | Keterangan |
|---|---|---|---|---|
| 1 | Identitas Pegawai | Ya / Tidak | ____ | |
| 2 | Risk Register | Ya / Tidak | ____ | |
| 3 | SOP | Ya / Tidak | ____ | |
| 4 | Formulir Pengendalian | Ya / Tidak | ____ | |
| 5 | Jadwal Pelaksanaan | Ya / Tidak | ____ | |
| 6 | Bukti Pelaksanaan | Ya / Tidak | ____ | |
| 8 | Evaluasi dan Rencana Perbaikan | Ya / Tidak | ____ | |

**Penugasan PIC**

| Peran | Nama | Sudah benar? |
|---|---|---|
| Pemilik Risiko | | Ya / Tidak |
| Pengendali Mutu | | Ya / Tidak |
| Pengendali Risiko | | Ya / Tidak |

**Status**

- Status residu: _____________________________
- Ringkasan evaluasi sudah diisi: Ya / Tidak
- Tautan Drive sudah diperiksa: Ya / Tidak
- Catatan: __________________________________

---

<a id="lampiran-d"></a>

## Lampiran D — Contoh Template Keterangan Berkas

### D.1 Identitas Pegawai

```text
SK Kepala Pusat Nomor 07/KPTS/Ma/2026 tentang Penetapan PIC CRMC Tahun 2026.
Berlaku sejak 1 Januari 2026.
```

### D.2 Risk Register

```text
Risk Register Spesifik Acuan Sub-Bidang {nama} Tahun {tahun}.
Reviu terakhir: {tanggal}. Disetujui pada rapat bidang {tanggal}.
```

### D.3 SOP

```text
SOP {nama prosedur} Nomor {nomor} Tahun {tahun}.
Revisi ke-{n}, berlaku sejak {tanggal}.
```

### D.4 Formulir Pengendalian

```text
Formulir pengendalian {nama} periode {bulan/triwulan} {tahun}.
Diisi oleh {nama}, diperiksa oleh {nama}.
```

### D.5 Jadwal Pelaksanaan

```text
Jadwal rencana pelaksanaan pengendalian risiko tahun {tahun}.
Mencakup {jumlah} kegiatan dari {bulan} sampai {bulan}.
```

### D.6 Bukti Pelaksanaan

```text
Bukti pelaksanaan {nama kegiatan} tanggal {tanggal}.
Berisi daftar hadir, notula, dan dokumentasi kegiatan.
```

### D.7 Evaluasi

```text
Ringkasan evaluasi tahun {tahun}.
Temuan: {uraian}.
Penyebab: {uraian}.
Tindakan perbaikan: {uraian}.
Target waktu: {tanggal}.
Penanggung jawab: {nama}.
```

### D.8 Tautan Drive

```text
Label: Dokumen Pendukung {nama komponen} {tahun}
URL: https://drive.google.com/drive/folders/...
```

---

<a id="lampiran-e"></a>

## Lampiran E — Riwayat Revisi

| Versi | Tanggal | Perubahan | Penyusun |
|---|---|---|---|
| 1.0 | 19 November 2026 | Penyusunan awal buku panduan administrator | Tim Pengelola CRMC |

---

<a id="penutup"></a>

## Penutup

Buku ini disusun agar administrator dapat bekerja secara mandiri, konsisten, dan aman. Bila menemukan perilaku aplikasi yang tidak sesuai dengan buku ini, catat langkah yang dilakukan, pesan yang muncul, dan waktu kejadian, lalu sampaikan kepada pengelola teknis aplikasi.

> **Ingat tiga hal utama:**
>
> 1. Periksa **tahun** sebelum mengunggah, menghapus, atau menugaskan.
> 2. Periksa **sub-bidang** sebelum menghapus data.
> 3. Gunakan **Komponen 1** untuk mengganti susunan PIC secara menyeluruh.
