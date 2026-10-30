<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use App\Models\DokumenCrmc;
use App\Models\LampiranCrmc;
use App\Models\PenugasanCrmc;
use App\Models\SubMenu;
use App\Models\TahunAnggaran;
use App\Models\TautanDriveCrmc;
use App\Models\User;
use App\Support\PenyimpananGambar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Halaman utama panel Administrator.
     *
     * Hanya admin yang boleh mengakses. Halaman ini menjadi pintu masuk
     * dua menu: Kelola Pegawai dan Kelola Dokumen. Navigasi panel sendiri
     * ada di navbar utama, bukan sidebar.
     */
    public function index()
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator.');
        }

        return view('admin.index', [
            'totalPegawai' => User::count(),
            'totalAdmin' => User::where('role', 'admin')->count(),
            'totalPegawaiRole' => User::where('role', 'pegawai')->count(),
        ]);
    }

    // ============================================================
    // ADMIN: Kelola Akun Pegawai
    // ============================================================

    public function pegawai(Request $request)
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator.');
        }

        $kataKunci = trim((string) $request->input('q'));
        $peran = (string) $request->input('peran');

        $users = User::query()
            ->with('bidang')
            ->when($kataKunci !== '', function ($q) use ($kataKunci) {
                $saya = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $kataKunci).'%';
                $q->where(function ($dalam) use ($saya) {
                    $dalam->where('name', 'like', $saya)
                        ->orWhere('nip', 'like', $saya)
                        ->orWhere('jabatan', 'like', $saya)
                        ->orWhere('email', 'like', $saya);
                });
            })
            ->when(in_array($peran, ['admin', 'pegawai'], true), fn ($q) => $q->where('role', $peran))
            ->orderBy('name')
            ->get();

        return view('admin.pegawai', [
            'users' => $users,
            'daftarBidang' => Bidang::orderBy('id')->get(),
            'kataKunci' => $kataKunci,
            'peran' => $peran,
            'totalPegawai' => User::count(),
            'totalAdmin' => User::where('role', 'admin')->count(),
            'totalPegawaiRole' => User::where('role', 'pegawai')->count(),
        ]);
    }

    public function simpanAkun(Request $request)
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:30|unique:users,nip',
            'jabatan' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|max:12|regex:/[0-9]/|regex:/[^A-Za-z0-9]/',
            'role' => 'required|in:admin,pegawai',
            'bidang_ids' => 'nullable|array',
            'bidang_ids.*' => 'integer|distinct|exists:bidang,id',
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], self::pesanValidasiAkun());

        $data = $request->only(['name', 'nip', 'jabatan', 'email', 'role']);
        $data['password'] = Hash::make($request->input('password'));
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('foto_profil')) {
            $data['foto_profil'] = PenyimpananGambar::simpan($request->file('foto_profil'), 'foto-pegawai');
        }

        $bidangIds = $request->input('bidang_ids', []);

        DB::transaction(function () use ($data, $bidangIds) {
            $user = User::create($data);
            $user->bidang()->sync($bidangIds);
        });

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Akun pegawai "'.$data['name'].'" berhasil dibuat.');
    }

    public function updateAkun(Request $request, $id)
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator.');
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:30|unique:users,nip,'.$user->id,
            'jabatan' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|max:12|regex:/[0-9]/|regex:/[^A-Za-z0-9]/',
            'role' => 'required|in:admin,pegawai',
            'bidang_ids' => 'nullable|array',
            'bidang_ids.*' => 'integer|distinct|exists:bidang,id',
            'foto_profil' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ], self::pesanValidasiAkun());

        $data = $request->only(['name', 'nip', 'jabatan', 'email', 'role']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $fotoLama = $user->getRawOriginal('foto_profil');

        if ($request->hasFile('foto_profil')) {
            $data['foto_profil'] = PenyimpananGambar::simpan($request->file('foto_profil'), 'foto-pegawai');
        }

        $user->update($data);

        $user->bidang()->sync($request->input('bidang_ids', []));
        $user->segarkanBidang();

        if ($request->hasFile('foto_profil') && ! empty($fotoLama) && $fotoLama !== $data['foto_profil']) {
            $this->hapusFotoPegawai($user, $fotoLama);
        }

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Data pegawai "'.$data['name'].'" berhasil diperbarui.');
    }

    public function hapusAkun($id)
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator.');
        }

        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;

        $this->hapusFotoPegawai($user);

        $user->delete();

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Akun pegawai "'.$name.'" berhasil dihapus.');
    }

    public function toggleStatusAkun($id)
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator.');
        }

        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Akun pegawai "'.$user->name.'" berhasil '.$status.'.');
    }

    private static function pesanValidasiAkun(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nip.required' => 'NIP wajib diisi.',
            'nip.unique' => 'NIP ini sudah dipakai pegawai lain.',
            'nip.max' => 'NIP maksimal 30 karakter.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'email.required' => 'Email dinas wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah dipakai pegawai lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.max' => 'Password maksimal 12 karakter.',
            'password.regex' => 'Password harus mengandung angka dan simbol.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role harus Admin atau Pegawai.',
            'bidang_ids.array' => 'Pilihan bidang tidak valid.',
            'bidang_ids.*.integer' => 'Pilihan bidang tidak valid.',
            'bidang_ids.*.distinct' => 'Bidang yang sama tidak boleh dipilih dua kali.',
            'bidang_ids.*.exists' => 'Bidang yang dipilih tidak ditemukan.',
            'foto_profil.image' => 'Foto profil harus berupa file gambar (JPG, PNG, atau WebP).',
            'foto_profil.mimes' => 'Format foto harus JPG, PNG, atau WebP. File HEIC perlu dikonversi dulu.',
            'foto_profil.max' => 'Ukuran foto maksimal 4 MB. Foto tidak tersimpan karena melebihi batas.',
        ];
    }

    private function hapusFotoPegawai(User $user, ?string $foto = null): void
    {
        $foto ??= $user->getRawOriginal('foto_profil');

        if (empty($foto) || str_starts_with($foto, 'http')) {
            return;
        }

        $candidates = [$foto];
        if (str_starts_with($foto, 'public/')) {
            $candidates[] = substr($foto, strlen('public/'));
        } else {
            $candidates[] = 'public/'.$foto;
        }

        foreach ($candidates as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    // ============================================================
    // ADMIN: Kelola Dokumen
    // ============================================================

    public function dokumen(Request $request)
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator.');
        }

        $tahunSekarang = (int) date('Y');

        $daftarTahun = collect([$tahunSekarang, $tahunSekarang + 1])
            ->merge(DokumenCrmc::distinct()->pluck('tahun_pelaksanaan')->all())
            ->merge(TahunAnggaran::pluck('tahun')->all())
            ->merge(PenugasanCrmc::distinct()->pluck('tahun_pelaksanaan')->all())
            ->merge(TautanDriveCrmc::distinct()->pluck('tahun_pelaksanaan')->all())
            ->map(fn ($t) => (int) $t)
            ->filter(fn ($t) => $t >= 2000 && $t <= 2100)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $selectedTahun = (int) $request->input('tahun', $tahunSekarang);
        if (! in_array($selectedTahun, $daftarTahun, true)) {
            $selectedTahun = $daftarTahun[count($daftarTahun) - 1] ?? $tahunSekarang;
        }

        // Daftar semua kategori dokumen (Komponen 1,2,3,4,5,6,8) — Komponen 7 (residu) tidak termasuk.
        $kategoriDokumen = [
            'identitas_pegawai' => ['nomor' => 1, 'judul' => 'Identitas Pegawai'],
            'risk_register' => ['nomor' => 2, 'judul' => 'Risk Register Spesifik Acuan'],
            'sop' => ['nomor' => 3, 'judul' => 'Standar Operasional Prosedur (SOP)'],
            'formulir_pengendalian' => ['nomor' => 4, 'judul' => 'Formulir Pengendalian / Daftar Periksa'],
            'jadwal_pelaksanaan' => ['nomor' => 5, 'judul' => 'Jadwal Rencana Pelaksanaan'],
            'bukti_pelaksanaan' => ['nomor' => 6, 'judul' => 'Bukti Pelaksanaan'],
            'evaluasi' => ['nomor' => 8, 'judul' => 'Evaluasi & Rencana Perbaikan'],
        ];

        // Semua lampiran tahun terpilih dimuat sekali, lalu dikelompokkan per
        // sub-bidang. Dengan cara ini tidak ada query di dalam loop kategori
        // (7 kategori x jumlah sub-bidang).
        $kategoriTerisiPerSubBidang = DokumenCrmc::query()
            ->where('tahun_pelaksanaan', $selectedTahun)
            ->with(['lampiran' => fn ($q) => $q->select('id', 'dokumen_crmc_id', 'kategori_komponen')])
            ->get(['id', 'sub_menu_id'])
            ->groupBy('sub_menu_id')
            ->map(fn ($daftarDokumen) => $daftarDokumen
                ->flatMap(fn ($dokumen) => $dokumen->lampiran->pluck('kategori_komponen'))
                ->unique()
                ->values()
                ->all());

        // Untuk setiap sub-bidang, tentukan komponen mana yang BELUM ada
        // dokumennya untuk tahun yang dipilih.
        $subBidang = SubMenu::with('bidang')
            ->orderBy('bidang_id')
            ->orderBy('id')
            ->get()
            ->map(function ($sm) use ($kategoriTerisiPerSubBidang, $kategoriDokumen) {
                $kategoriTerisi = $kategoriTerisiPerSubBidang->get($sm->id, []);

                $belumDiisi = collect($kategoriDokumen)
                    ->reject(fn ($meta, $kategori) => in_array($kategori, $kategoriTerisi, true))
                    ->map(fn ($meta, $kategori) => [
                        'kategori' => $kategori,
                        'nomor' => $meta['nomor'],
                        'judul' => $meta['judul'],
                    ])
                    ->values();

                return [
                    'id' => $sm->id,
                    'nama_sub_menu' => $sm->nama_sub_menu,
                    'slug' => $sm->slug,
                    'bidang' => $sm->bidang?->nama_bidang ?? '-',
                    'belum_diisi' => $belumDiisi,
                    'jumlah_belum' => $belumDiisi->count(),
                    'selesai' => $belumDiisi->isEmpty(),
                ];
            });

        // Angka ringkasan selalu menggambarkan seluruh sub-bidang, bukan hasil
        // pencarian/penyaringan, supaya tetap bisa dipakai sebagai pembanding.
        $jumlahLengkap = $subBidang->where('selesai', true)->count();
        $jumlahBelumLengkap = $subBidang->where('selesai', false)->count();

        $kataKunci = trim((string) $request->input('q'));
        if ($kataKunci !== '') {
            $subBidang = $subBidang->filter(fn ($sb) => Str::contains($sb['nama_sub_menu'], $kataKunci, true)
                || Str::contains($sb['bidang'], $kataKunci, true));
        }

        // Halaman ini fokus pada sub-bidang yang dokumennya belum lengkap.
        // Sub-bidang yang sudah lengkap hanya muncul bila diminta lewat ?semua=1.
        $tampilkanSemua = $request->boolean('semua');
        if (! $tampilkanSemua) {
            $subBidang = $subBidang->where('selesai', false);
        }

        // Urutkan: sub-bidang dengan komponen belum diisi terbanyak lebih dulu.
        $subBidang = $subBidang->sortByDesc('jumlah_belum')->values();

        $tahunManual = TahunAnggaran::orderByDesc('tahun')->get();

        return view('admin.dokumen', [
            'daftarTahun' => $daftarTahun,
            'selectedTahun' => $selectedTahun,
            'subBidang' => $subBidang,
            'tahunManual' => $tahunManual,
            'kataKunci' => $kataKunci,
            'tampilkanSemua' => $tampilkanSemua,
            'jumlahLengkap' => $jumlahLengkap,
            'jumlahBelumLengkap' => $jumlahBelumLengkap,
            'totalSubBidang' => $jumlahLengkap + $jumlahBelumLengkap,
            'totalKomponen' => count($kategoriDokumen),
        ]);
    }

    public function simpanTahun(Request $request)
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator.');
        }

        $request->validate([
            'tahun' => 'required|numeric|min:2000|max:2100',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'tahun.required' => 'Tahun wajib diisi.',
            'tahun.numeric' => 'Tahun harus berupa angka.',
            'tahun.min' => 'Tahun minimal 2000.',
            'tahun.max' => 'Tahun maksimal 2100.',
            'tahun.unique' => 'Tahun ini sudah ada di daftar tahun tambahan.',
        ]);

        $tahun = (int) $request->input('tahun');
        $keterangan = trim((string) $request->input('keterangan'));

        if (TahunAnggaran::where('tahun', $tahun)->exists()) {
            return back()->with('error', "Tahun {$tahun} sudah ada di daftar tahun tambahan.");
        }

        TahunAnggaran::create([
            'tahun' => $tahun,
            'keterangan' => $keterangan !== '' ? $keterangan : null,
        ]);

        return back()->with('success', "Tahun {$tahun} berhasil ditambahkan ke daftar pilihan tahun.");
    }

    public function hapusTahunDestroy($id)
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator.');
        }

        $tahunAnggaran = TahunAnggaran::findOrFail($id);
        $tahun = $tahunAnggaran->tahun;

        $jumlahDokumen = DokumenCrmc::where('tahun_pelaksanaan', $tahun)->count();

        $tahunAnggaran->delete();

        $pesan = "Tahun {$tahun} dihapus dari daftar tahun tambahan.";
        if ($jumlahDokumen > 0) {
            $pesan .= " Catatan: {$jumlahDokumen} dokumen tahun {$tahun} tidak ikut terhapus.";
        }

        return back()->with('success', $pesan);
    }

    public function hapusDokumenTahunSemua(Request $request)
    {
        if (! Auth::check() || ! Auth::user()->isAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Administrator yang dapat menghapus dokumen per tahun.');
        }

        $request->validate([
            'tahun' => 'required|numeric|min:2000|max:2100',
        ], [
            'tahun.required' => 'Tahun wajib dipilih.',
        ]);

        $tahun = (int) $request->input('tahun');
        $hasil = $this->hapusDokumen(['tahun_pelaksanaan' => $tahun]);

        if ($hasil['file'] === 0) {
            return back()->with('error', "Tidak ada dokumen tahun {$tahun} di seluruh sub-bidang, jadi tidak ada yang dihapus.");
        }

        return back()->with(
            'success',
            "{$hasil['file']} berkas tahun {$tahun} dari {$hasil['sub_bidang']} sub-bidang berhasil dihapus (termasuk file fisik di storage)."
        );
    }

    private function hapusDokumen(array $filter, ?string $kategori = null): array
    {
        $dokumenQuery = DokumenCrmc::query()->where($filter);
        $dokumenIds = (clone $dokumenQuery)->pluck('id');

        $lampiranQuery = LampiranCrmc::query()
            ->whereIn('dokumen_crmc_id', $dokumenIds)
            ->when($kategori, fn ($q) => $q->where('kategori_komponen', $kategori));

        $lampiranDihapus = (clone $lampiranQuery)->get();

        $jumlahLampiran = $lampiranDihapus->count();
        $jumlahSubMenu = (clone $dokumenQuery)->distinct()->count('sub_menu_id');
        $jumlahDokumen = $dokumenIds->count();

        foreach ($lampiranDihapus as $lampiran) {
            $lampiran->hapusBerkas();
        }

        (clone $lampiranQuery)->delete();

        foreach ($dokumenIds as $id) {
            if (! LampiranCrmc::where('dokumen_crmc_id', $id)->exists()) {
                DokumenCrmc::where('id', $id)->delete();
            }
        }

        return [
            'file' => $jumlahLampiran,
            'dokumen' => $jumlahDokumen,
            'sub_bidang' => $jumlahSubMenu,
            'lampiran' => $jumlahLampiran,
        ];
    }
}
