<?php

namespace App\Support;

use App\Models\Bidang;
use App\Models\DokumenCrmc;
use App\Models\LampiranCrmc;
use App\Models\SubMenu;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

/**
 * Satu tempat untuk aturan akses per bidang.
 *
 * Aturan akses:
 *   - Pengunjung yang belum login HANYA boleh membaca Beranda dan Dasar
 *     Hukum. Dokumen dan bidang CRMC lain memerlukan akun yang berwenang.
 *   - Admin boleh membaca dan mengelola semua bidang.
 *   - Pegawai hanya boleh membuka bidang yang ditugaskan kepadanya
 *     (tabel pivot `bidang_user`). Tanpa penugasan berarti tidak ada bidang
 *     yang boleh dibuka.
 *
 * Penulisan (unggah, ubah keterangan, hapus, tautan Drive, penugasan)
 * selalu menuntut login. Pegawai hanya boleh menulis pada bidang yang
 * ditugaskan; pengunjung tidak pernah boleh menulis.
 *
 * Jangan pernah memakai `bidang_id`, `sub_menu_id`, atau id dokumen yang
 * dikirim browser sebagai dasar izin tanpa mencocokkannya ke baris database
 * lebih dulu. Method di kelas ini menerima id hasil pembacaan database,
 * bukan input mentah.
 */
final class AksesBidang
{
    /** Apakah pengguna yang sedang login adalah admin. */
    public static function admin(): bool
    {
        return Auth::check() && Auth::user()->isAdmin();
    }

    /** Apakah permintaan datang dari pengunjung yang belum login. */
    public static function tamu(): bool
    {
        return ! Auth::check();
    }

    /**
     * Batas bidang yang boleh dilihat pengguna saat ini.
     *
     * null  = tidak dibatasi (admin)
     * []    = tidak punya akses ke bidang mana pun (tamu dan pegawai tanpa
     *         penugasan; tamu hanya boleh membaca Beranda dan Dasar Hukum)
     * array = daftar id bidang yang boleh dibuka
     *
     * @return array<int, int>|null
     */
    public static function batasBidangIds(): ?array
    {
        if (self::admin()) {
            return null;
        }

        if (self::tamu()) {
            return [];
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->isPegawai()) {
            return [];
        }

        return $user->bidangIds();
    }

    /**
     * Batas sub-bidang yang boleh dilihat, atau null bila tidak dibatasi.
     *
     * @return array<int, int>|null
     */
    public static function batasSubMenuIds(): ?array
    {
        $bidangIds = self::batasBidangIds();

        if ($bidangIds === null) {
            return null;
        }

        if ($bidangIds === []) {
            return [];
        }

        return SubMenu::query()
            ->whereIn('bidang_id', $bidangIds)
            ->pluck('id')
            ->all();
    }

    /** Apakah pengguna boleh membaca bidang tertentu. */
    public static function bolehLihatBidang(?int $bidangId): bool
    {
        if (self::admin()) {
            return true;
        }

        if (self::tamu()) {
            return false;
        }

        if ($bidangId === null) {
            return false;
        }

        return in_array((int) $bidangId, self::batasBidangIds() ?? [], true);
    }

    /** Apakah pengguna boleh membaca sub-bidang tertentu. */
    public static function bolehLihatSubMenu(?SubMenu $subMenu): bool
    {
        return $subMenu !== null && self::bolehLihatBidang($subMenu->bidang_id);
    }

    /** Apakah pengguna boleh membaca dokumen tertentu. */
    public static function bolehLihatDokumen(?DokumenCrmc $dokumen): bool
    {
        return $dokumen !== null && self::bolehLihatSubMenu($dokumen->subMenu);
    }

    /** Apakah pengguna boleh membaca berkas lampiran tertentu. */
    public static function bolehLihatLampiran(?LampiranCrmc $lampiran): bool
    {
        return $lampiran !== null && self::bolehLihatDokumen($lampiran->dokumenCrmc);
    }

    /**
     * Apakah pengguna boleh MENGELOLA (unggah/ubah/hapus) bidang tertentu.
     *
     * Tamu selalu ditolak. Admin selalu boleh. Pegawai hanya bidang yang
     * ditugaskan kepadanya.
     */
    public static function bolehKelolaBidang(?int $bidangId): bool
    {
        if (self::tamu()) {
            return false;
        }

        if (self::admin()) {
            return true;
        }

        if ($bidangId === null) {
            return false;
        }

        return in_array((int) $bidangId, self::batasBidangIds() ?? [], true);
    }

    /** Apakah pengguna boleh mengelola sub-bidang tertentu. */
    public static function bolehKelolaSubMenu(?SubMenu $subMenu): bool
    {
        return $subMenu !== null && self::bolehKelolaBidang($subMenu->bidang_id);
    }

    /** Tolak dengan 403 bila bidang tidak boleh dibaca. */
    public static function wajibLihatBidang(?int $bidangId, string $pesan = 'Anda tidak memiliki akses ke bidang ini.'): void
    {
        abort_unless(self::bolehLihatBidang($bidangId), 403, $pesan);
    }

    /** Tolak dengan 403 bila sub-bidang tidak boleh dibaca. */
    public static function wajibLihatSubMenu(?SubMenu $subMenu, string $pesan = 'Anda tidak memiliki akses ke sub-bidang ini.'): void
    {
        abort_unless(self::bolehLihatSubMenu($subMenu), 403, $pesan);
    }

    /** Tolak dengan 403 bila sub-bidang tidak boleh dikelola. */
    public static function wajibKelolaSubMenu(?SubMenu $subMenu, string $pesan = 'Anda tidak memiliki akses untuk mengubah data pada bidang ini.'): void
    {
        abort_unless(self::bolehKelolaSubMenu($subMenu), 403, $pesan);
    }

    /** Tolak dengan 403 bila lampiran tidak boleh dibaca. */
    public static function wajibLihatLampiran(?LampiranCrmc $lampiran, string $pesan = 'Anda tidak memiliki akses ke berkas ini.'): void
    {
        abort_unless(self::bolehLihatLampiran($lampiran), 403, $pesan);
    }
}
