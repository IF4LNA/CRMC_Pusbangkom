@extends('admin.layout')

@section('title', 'Kelola Akun Pegawai - Admin CRMC')

@section('admin-content')

    {{-- ===================== KEPALA HALAMAN (BANNER) ===================== --}}
    <div class="relative overflow-hidden rounded-xl border border-blue-900 min-h-[8rem] p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <img src="{{ asset('images/banner_bidang.png') }}" alt="Banner Admin" loading="lazy" decoding="async"
             class="absolute inset-0 h-full w-full object-cover object-center">
        <div class="relative min-w-0 drop-shadow-lg">
            <span class="eyebrow text-blue-200">Panel Administrator</span>
            <h1 class="page-title mt-0.5 text-white">Kelola Akun Pegawai</h1>
            <p class="page-sub mt-1 leading-relaxed text-blue-100">
                Buat, ubah, dan hapus akun pegawai untuk penugasan di seluruh
                sub-bidang CRMC.
            </p>
        </div>

        <div class="relative z-10 flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('admin.index') }}" class="btn btn-outline">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Panel Admin</span>
            </a>
            <button type="button" onclick="openTambahModal()" class="btn btn-primary">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Tambah Pegawai Baru</span>
            </button>
        </div>
    </div>

    <!-- STATS -->
    {{-- Angka di sini selalu untuk seluruh pegawai, bukan hasil pencarian,
         supaya tetap bisa dipakai sebagai pembanding. --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="stat">
            <p class="stat-label">Total Pegawai</p>
            <p class="stat-value">{{ $totalPegawai }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Administrator</p>
            <p class="stat-value">{{ $totalAdmin }}</p>
        </div>
        <div class="stat">
            <p class="stat-label">Pegawai</p>
            <p class="stat-value">{{ $totalPegawaiRole }}</p>
        </div>
    </div>

    <!-- TABLE PEGAWAI -->
    <div class="card overflow-hidden">
        <div class="card-head flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h2 class="card-title flex items-center gap-1.5">
                <i data-lucide="list" class="w-4 h-4 text-slate-400"></i>
                Daftar Seluruh Akun Pegawai
                <span class="badge">{{ $users->count() }} dari {{ $totalPegawai }}</span>
            </h2>

            {{-- Pencarian dikirim lewat GET supaya bisa ditandai, dibagikan,
                 dan tetap bekerja tanpa JavaScript. --}}
            <form method="GET" action="{{ route('admin.pegawai.index') }}"
                  class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:w-64">
                    <input type="search" name="q" value="{{ $kataKunci }}"
                           placeholder="Cari nama, NIP, jabatan, email..."
                           aria-label="Cari pegawai"
                           class="input pl-8">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5"></i>
                </div>

                <select name="peran" aria-label="Saring berdasarkan role" class="input sm:w-40">
                    <option value="">Semua Role</option>
                    <option value="pegawai" {{ $peran === 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                    <option value="admin" {{ $peran === 'admin' ? 'selected' : '' }}>Administrator</option>
                </select>

                <button type="submit" class="btn btn-primary shrink-0">
                    <i data-lucide="search" class="w-3.5 h-3.5"></i>
                    Cari
                </button>

                @if ($kataKunci !== '' || $peran !== '')
                    <a href="{{ route('admin.pegawai.index') }}" class="btn btn-quiet shrink-0" title="Reset pencarian">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        Reset
                    </a>
                @endif
            </form>
        </div>

        @if ($users->isEmpty())
            <div class="empty">
                <i data-lucide="search-x" class="w-7 h-7 text-slate-300 mx-auto mb-2"></i>
                <p class="font-semibold text-slate-600">Tidak ada pegawai yang cocok</p>
                <p class="text-[11px] text-slate-400 mt-1 max-w-md mx-auto leading-relaxed">
                    Coba kata kunci lain, atau tekan Reset untuk melihat seluruh pegawai.
                </p>
            </div>
        @endif

        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama Lengkap</th>
                        <th>NIP</th>
                        <th>Jabatan</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Bidang Akses</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                    <tr>
                        <td>
                            <img src="{{ $u->foto_url }}" alt="{{ $u->name }}" class="w-10 h-10 rounded-md object-cover border border-slate-200">
                        </td>
                        <td class="font-semibold text-slate-900">{{ $u->name }}</td>
                        <td class="font-mono">{{ $u->nip ?? '-' }}</td>
                        <td>{{ $u->jabatan ?? '-' }}</td>
                        <td>{{ $u->email }}</td>
                        <td>
                            @if($u->isAdmin())
                                <span class="badge badge-accent">Admin</span>
                            @else
                                <span class="badge badge-info">Pegawai</span>
                            @endif
                        </td>
                        <td>
                            @if($u->is_active)
                                <span class="badge badge-ok">Aktif</span>
                            @else
                                <span class="badge badge-bad">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            @if($u->isAdmin())
                                <span class="badge badge-accent">Semua bidang</span>
                            @elseif($u->bidang->isEmpty())
                                <span class="badge" title="Pegawai ini belum ditugaskan ke bidang mana pun">Belum ada akses</span>
                            @else
                                <div class="flex flex-wrap gap-1 max-w-[16rem]">
                                    @foreach($u->bidang as $bidangUser)
                                        <span class="badge badge-info">{{ $bidangUser->nama_bidang }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button onclick="openEditModal({{ json_encode([
                                    'id' => $u->id,
                                    'name' => $u->name,
                                    'nip' => $u->nip,
                                    'jabatan' => $u->jabatan,
                                    'email' => $u->email,
                                    'role' => $u->role,
                                    'is_active' => (bool) $u->is_active,
                                    'bidang_ids' => $u->bidang->pluck('id')->values(),
                                ]) }})" class="icon-btn" title="Edit">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </button>
                                @if($u->id !== Auth::id())
                                <form action="{{ route('admin.pegawai.toggle-status', $u->id) }}" method="POST"
                                      data-konfirmasi="Yakin ingin {{ $u->is_active ? 'menonaktifkan' : 'mengaktifkan' }} akun &quot;{{ $u->name }}&quot;?">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="icon-btn {{ $u->is_active ? 'icon-btn-danger' : '' }}" title="{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <i data-lucide="{{ $u->is_active ? 'power-off' : 'power' }}" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.pegawai.delete', $u->id) }}" method="POST"
                                      data-konfirmasi="Yakin ingin menghapus akun &quot;{{ $u->name }}&quot;? Akun ini tidak bisa dipulihkan lagi.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="icon-btn icon-btn-danger" title="Hapus">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('admin-modals')
<!-- MODAL TAMBAH PEGAWAI -->
<div id="tambahModal" class="modal hidden">
    <div class="modal-card !max-w-xl">
        <div class="modal-head">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-50 text-blue-700 rounded-md flex items-center justify-center shrink-0">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="eyebrow">Administrator</span>
                    <h3 class="modal-title mt-0.5">Tambah Akun Pegawai Baru</h3>
                </div>
            </div>
            <button onclick="closeTambahModal()" class="icon-btn" title="Tutup">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="{{ route('admin.pegawai.store') }}" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col min-h-0">
            <div class="modal-body space-y-3">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="label">Nama Lengkap *</label>
                        <input type="text" name="name" required class="input">
                    </div>
                    <div>
                        <label class="label">NIP *</label>
                        <input type="text" name="nip" required class="input">
                    </div>
                </div>
                <div>
                    <label class="label">Jabatan *</label>
                    <input type="text" name="jabatan" required class="input">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="label">Email Dinas *</label>
                        <input type="email" name="email" required class="input">
                    </div>
                    <div>
                        <label class="label">Password *</label>
                        <div class="relative">
                            <input type="password" name="password" id="tambahPassword" required minlength="8" maxlength="12" pattern="(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,12}" class="input pr-10">
                            <button type="button" onclick="togglePassword('tambahPassword', this)" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" title="Tampilkan/sembunyikan password">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <p class="hint">8-12 karakter, wajib ada angka dan simbol.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="label">Role *</label>
                        <select name="role" required class="input">
                            <option value="pegawai">Pegawai</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 pt-6">
                        <input type="checkbox" name="is_active" id="tambahIsActive" value="1" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <label for="tambahIsActive" class="text-sm text-slate-700 cursor-pointer">Akun aktif (boleh login)</label>
                    </div>
                    <div>
                        <label class="label">Foto Profil (Opsional)</label>
                        <input type="file" name="foto_profil" accept="image/jpeg,image/png,image/webp" class="input !py-1.5">
                        <p class="hint">JPG, PNG, atau WebP. Maksimal 4 MB.</p>
                    </div>
                </div>

                <div>
                    <label class="label">Bidang yang Dapat Diakses</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 rounded-lg border border-slate-200 bg-slate-50/60 p-3">
                        @foreach($daftarBidang as $bidangAkses)
                            <label class="flex items-start gap-2 text-xs text-slate-700 cursor-pointer">
                                <input type="checkbox" name="bidang_ids[]" value="{{ $bidangAkses->id }}"
                                       class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span>{{ $bidangAkses->nama_bidang }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="hint">
                        Pegawai hanya dapat membuka bidang yang dicentang beserta sub-bidangnya.
                        Kosongkan semua centang bila pegawai belum boleh mengakses bidang apa pun.
                        Admin selalu memiliki akses penuh.
                    </p>
                </div>

            </div>

            <div class="modal-foot">
                <button type="button" onclick="closeTambahModal()" class="btn btn-quiet">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Akun</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT PEGAWAI -->
<div id="editModal" class="modal hidden">
    <div class="modal-card !max-w-xl">
        <div class="modal-head">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-50 text-blue-700 rounded-md flex items-center justify-center shrink-0">
                    <i data-lucide="pencil" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="eyebrow">Administrator</span>
                    <h3 class="modal-title mt-0.5">Edit Data Pegawai</h3>
                </div>
            </div>
            <button onclick="closeEditModal()" class="icon-btn" title="Tutup">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="editForm" method="POST" enctype="multipart/form-data" class="flex-1 flex flex-col min-h-0">
            <div class="modal-body space-y-3">
                @csrf
                @method('PUT')
                <input type="hidden" id="editUserId" name="user_id" value="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="label">Nama Lengkap *</label>
                        <input type="text" name="name" id="editName" required class="input">
                    </div>
                    <div>
                        <label class="label">NIP *</label>
                        <input type="text" name="nip" id="editNip" required class="input">
                    </div>
                </div>
                <div>
                    <label class="label">Jabatan *</label>
                    <input type="text" name="jabatan" id="editJabatan" required class="input">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="label">Email Dinas *</label>
                        <input type="email" name="email" id="editEmail" required class="input">
                    </div>
                    <div>
                        <label class="label">Password Baru (kosongkan jika tidak diubah)</label>
                        <div class="relative">
                            <input type="password" name="password" id="editPassword" minlength="8" maxlength="12" pattern="(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,12}" class="input pr-10" placeholder="&#8226;&#8226;&#8226;&#8226;">
                            <button type="button" onclick="togglePassword('editPassword', this)" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" title="Tampilkan/sembunyikan password">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                        <p class="hint">Kosongkan jika tidak diubah. Jika diisi: 8-12 karakter, wajib ada angka dan simbol.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="label">Role *</label>
                        <select name="role" id="editRole" required class="input">
                            <option value="pegawai">Pegawai</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2 pt-6">
                        <input type="checkbox" name="is_active" id="editIsActive" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <label for="editIsActive" class="text-sm text-slate-700 cursor-pointer">Akun aktif (boleh login)</label>
                    </div>
                    <div>
                        <label class="label">Foto Profil Baru (Opsional)</label>
                        <input type="file" name="foto_profil" accept="image/jpeg,image/png,image/webp" class="input !py-1.5">
                        <p class="hint">JPG, PNG, atau WebP. Maksimal 4 MB. Kosongkan bila tidak ingin mengganti foto.</p>
                    </div>
                </div>

                <div>
                    <label class="label">Bidang yang Dapat Diakses</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 rounded-lg border border-slate-200 bg-slate-50/60 p-3">
                        @foreach($daftarBidang as $bidangAkses)
                            <label class="flex items-start gap-2 text-xs text-slate-700 cursor-pointer">
                                <input type="checkbox" name="bidang_ids[]" value="{{ $bidangAkses->id }}"
                                       class="checkbox-bidang-edit mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span>{{ $bidangAkses->nama_bidang }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="hint">
                        Pegawai hanya dapat membuka bidang yang dicentang beserta sub-bidangnya.
                        Kosongkan semua centang bila pegawai belum boleh mengakses bidang apa pun.
                        Admin selalu memiliki akses penuh.
                    </p>
                </div>

            </div>

            <div class="modal-foot">
                <button type="button" onclick="closeEditModal()" class="btn btn-quiet">Batal</button>
                <button type="submit" class="btn btn-dark">Perbarui Data</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('admin-scripts')
<script>
    // Konfirmasi hapus memakai atribut data-konfirmasi, bukan confirm('...')
    // yang disisipkan langsung ke HTML. Nama akun bisa mengandung tanda
    // kutip, dan satu tanda kutip saja sudah cukup untuk menutup string JS
    // pada pola lama. Dengan data-konfirmasi, teksnya dibaca sebagai data
    // murni lewat dataset sehingga tidak pernah dieksekusi.
    document.addEventListener('submit', function (e) {
        const pesan = e.target.dataset && e.target.dataset.konfirmasi;
        if (pesan && !window.confirm(pesan)) {
            e.preventDefault();
        }
    }, true);

    function openTambahModal() {
        document.getElementById('tambahModal').classList.remove('hidden');
    }
    function closeTambahModal() {
        document.getElementById('tambahModal').classList.add('hidden');
    }
    function openEditModal(user) {
        document.getElementById('editForm').action = '/admin/pegawai/' + user.id;
        document.getElementById('editUserId').value = user.id;
        document.getElementById('editName').value = user.name;
        document.getElementById('editNip').value = user.nip || '';
        document.getElementById('editJabatan').value = user.jabatan || '';
        document.getElementById('editEmail').value = user.email;
        document.getElementById('editRole').value = user.role;
        document.getElementById('editIsActive').checked = !!user.is_active;

        // Centang bidang yang memang sudah ditugaskan. Nilai dari server
        // berupa array id, jadi dibandingkan sebagai angka.
        const bidangIds = (user.bidang_ids || []).map(Number);
        document.querySelectorAll('.checkbox-bidang-edit').forEach(function (checkbox) {
            checkbox.checked = bidangIds.includes(Number(checkbox.value));
        });

        document.getElementById('editModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (!input) return;
        if (input.type === 'password') {
            input.type = 'text';
            if (icon) icon.setAttribute('data-lucide', 'eye-off');
        } else {
            input.type = 'password';
            if (icon) icon.setAttribute('data-lucide', 'eye');
        }
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    // Kalau validasi gagal, buka kembali modal yang tadi dipakai beserta
    // isiannya supaya user tidak perlu mengetik ulang.
    @if($errors->any() && old('_method') === 'PUT' && old('user_id'))
        (function () {
            var form = document.getElementById('editForm');
            form.action = '/admin/pegawai/' + '{{ old('user_id') }}';
            document.getElementById('editName').value = @json(old('name'));
            document.getElementById('editNip').value = @json(old('nip'));
            document.getElementById('editJabatan').value = @json(old('jabatan'));
            document.getElementById('editEmail').value = @json(old('email'));
            document.getElementById('editRole').value = @json(old('role'));
            document.getElementById('editIsActive').checked = {{ old('is_active') ? 'true' : 'false' }};
            var bidangLama = @json(array_map('intval', old('bidang_ids', [])));
            document.querySelectorAll('.checkbox-bidang-edit').forEach(function (checkbox) {
                checkbox.checked = bidangLama.includes(Number(checkbox.value));
            });
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editModal').scrollIntoView({ behavior: 'smooth', block: 'center' });
        })();
    @elseif($errors->any())
        openTambahModal();
    @endif
</script>
@endpush
