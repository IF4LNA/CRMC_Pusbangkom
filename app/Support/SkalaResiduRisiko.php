<?php

namespace App\Support;

/**
 * Skala status residu risiko untuk Komponen 7.
 *
 * Nilai disimpan di `dokumen_crmc.status_residu_risiko` memakai KUNCI
 * (slug), bukan labelnya. Ini disengaja: nama tampilan masih bisa diubah
 * tanpa harus menulis ulang data yang sudah tersimpan.
 *
 * Urutan array = urutan dari status paling parah ke paling terkendali,
 * sekaligus urutan skala dari merah ke hijau. Makin turun urutannya, makin
 * banyak dokumen pendukung yang harus sudah tersedia. Contohnya "Bahaya"
 * berarti belum ada dokumen sama sekali, sedangkan "Terkendali" berarti
 * Risk Register, form pengendalian, bukti pengendalian, sampai evaluasi
 * sudah lengkap.
 */
final class SkalaResiduRisiko
{
    /**
     * Definisi tiap tingkat skala.
     *
     * @var array<string, array{label: string, keterangan: string, warna: string}>
     */
    public const TINGKAT = [
        'bahaya' => [
            'label' => 'Bahaya',
            'keterangan' => 'Tanpa dokumen',
            'warna' => 'merah',
        ],
        'waspada_ii' => [
            'label' => 'Waspada II',
            'keterangan' => 'Tidak ada dokumen Risk Register',
            'warna' => 'orange',
        ],
        'waspada_i' => [
            'label' => 'Waspada I',
            'keterangan' => 'Belum ada form pengendalian',
            'warna' => 'kuning',
        ],
        'siaga' => [
            'label' => 'Siaga',
            'keterangan' => 'Sudah ada bukti pengendalian',
            'warna' => 'biru',
        ],
        'terkendali' => [
            'label' => 'Terkendali',
            'keterangan' => 'Tuntas',
            'warna' => 'hijau',
        ],
    ];

    /**
     * Palet warna tiap tingkat.
     *
     * Dipisah dari TINGKAT supaya definisi skala tetap murni data (dipakai
     * juga oleh validasi), sedangkan kelas Tailwind hanya ada di satu
     * tempat.
     *
     * Setiap warna menyediakan lima varian supaya view cukup memilih nama,
     * tanpa menulis ulang rangkaian kelas utilitas yang panjang:
     *
     *   kartu  - latar kartu status (bg + border)
     *   teks   - warna teks/heading di dalam kartu itu
     *   badge  - lencana dengan kontras tinggi
     *   bar    - segmen meter
     *   chip   - lencana ringan untuk label tingkat
     *   ikon   - kotak latar ikon
     *
     * Varian yang menyatu dengan komponen .badge/.card diberi "!" karena
     * kelas komponen itu memakai !important. Tanpa itu, urutan stylesheet
     * bisa membuat warna komponen mengalahkan warna tingkat.
     *
     * @var array<string, array<string, string>>
     */
    public const WARNA = [
        'merah' => [
            'kartu' => '!bg-rose-50 !border-rose-300',
            'teks' => '!text-rose-900',
            'badge' => '!bg-rose-600 !text-white',
            'bar' => 'bg-rose-500',
            'chip' => '!bg-rose-100 !text-rose-800 !border-rose-200',
            'ikon' => 'bg-rose-100 text-rose-700',
        ],
        'orange' => [
            'kartu' => '!bg-orange-50 !border-orange-300',
            'teks' => '!text-orange-900',
            'badge' => '!bg-orange-500 !text-white',
            'bar' => 'bg-orange-500',
            'chip' => '!bg-orange-100 !text-orange-900 !border-orange-200',
            'ikon' => 'bg-orange-100 text-orange-700',
        ],
        'kuning' => [
            'kartu' => '!bg-amber-50 !border-amber-300',
            'teks' => '!text-amber-900',
            'badge' => '!bg-amber-400 !text-amber-950',
            'bar' => 'bg-amber-400',
            'chip' => '!bg-amber-100 !text-amber-900 !border-amber-200',
            'ikon' => 'bg-amber-100 text-amber-800',
        ],
        'biru' => [
            'kartu' => '!bg-blue-50 !border-blue-300',
            'teks' => '!text-blue-900',
            'badge' => '!bg-blue-600 !text-white',
            'bar' => 'bg-blue-600',
            'chip' => '!bg-blue-100 !text-blue-900 !border-blue-200',
            'ikon' => 'bg-blue-100 text-blue-700',
        ],
        'hijau' => [
            'kartu' => '!bg-emerald-50 !border-emerald-300',
            'teks' => '!text-emerald-900',
            'badge' => '!bg-emerald-600 !text-white',
            'bar' => 'bg-emerald-500',
            'chip' => '!bg-emerald-100 !text-emerald-900 !border-emerald-200',
            'ikon' => 'bg-emerald-100 text-emerald-700',
        ],
    ];

    /**
     * Warna netral untuk status yang belum diisi.
     *
     * @var array<string, string>
     */
    public const WARNA_KOSONG = [
        'kartu' => '!bg-slate-50 !border-slate-200',
        'teks' => '!text-slate-500',
        'badge' => '!bg-slate-200 !text-slate-600',
        'bar' => 'bg-slate-300',
        'chip' => '!bg-slate-100 !text-slate-600 !border-slate-200',
        'ikon' => 'bg-slate-100 text-slate-500',
    ];

    /**
     * Nilai yang sah untuk disimpan (dipakai validasi request).
     *
     * @return array<int, string>
     */
    public static function nilaiValid(): array
    {
        return array_keys(self::TINGKAT);
    }

    /**
     * Bersihkan nilai dari database menjadi kunci skala yang sah.
     *
     * Nilai skala lama (Rendah/Sedang/Tinggi) sudah dikonversi permanen oleh
     * migration `normalisasi_skala_status_residu_risiko`, jadi di sini tidak
     * ada lagi pemetaan lama. Fungsi ini tetap ditulis sebagai penjaga:
     * nilai di luar daftar (termasuk null) selalu jadi null, bukan diteruskan
     * apa adanya supaya view tidak pernah menampilkan tingkat fiktif.
     */
    public static function normalisasi(?string $nilai): ?string
    {
        $kunci = strtolower(trim((string) $nilai));

        return array_key_exists($kunci, self::TINGKAT) ? $kunci : null;
    }

    /**
     * Pecah nilai tersimpan menjadi bagian yang sudah dinormalisasi.
     *
     * Nilai di luar daftar (termasuk null) menghasilkan `kunci` null plus
     * palet abu-abu, sehingga view tetap punya bentuk lengkap untuk
     * menampilkan status sebagai "Belum Diisi".
     *
     * @return array{kunci: ?string, label: string, keterangan: ?string, warna: array<string, string>}
     */
    public static function rincian(?string $nilai): array
    {
        $kunci = self::normalisasi($nilai);

        if ($kunci === null) {
            return [
                'kunci' => null,
                'label' => 'Belum Diisi',
                'keterangan' => null,
                'warna' => self::WARNA_KOSONG,
            ];
        }

        return [
            'kunci' => $kunci,
            'label' => self::TINGKAT[$kunci]['label'],
            'keterangan' => self::TINGKAT[$kunci]['keterangan'],
            'warna' => self::WARNA[self::TINGKAT[$kunci]['warna']],
        ];
    }
}
