@extends('layouts.app')

@section('title', 'Kelola Akun Pegawai - Admin CRMC')

@section('content')
<main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- BREADCRUMB -->
    <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
        <nav class="flex items-center space-x-2 text-slate-500">
            <a href="{{ url('/') }}" class="hover:text-blue-900 flex items-center gap-1 font-medium transition">
                <i data-lucide="home" class="w-3.5 h-3.5"></i>
                <span>Beranda CRMC</span>
            </a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
            <span class="text-amber-600 font-bold">Kelola Akun Pegawai</span>
        </nav>

        <a href="{{ url('/') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold transition shadow-sm text-xs">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5 text-slate-500"></i>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

    <!-- FLASH MESSAGE -->
    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center space-x-3 text-xs text-emerald-900 shadow-sm">
        <div class="w-8 h-8 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
            <i data-lucide="check" class="w-4 h-4"></i>
        </div>
        <div class="flex-1">
            <p class="font-bold">Berhasil!</p>
            <p class="text-emerald-700">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center space-x-3 text-xs text-rose-900 shadow-sm">
        <div class="w-8 h-8 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0">
            <i data-lucide="alert-triangle" class="w-4 h-4"></i>
        </div>
        <div class="flex-1">
            <p class="font-bold">Gagal!</p>
            <p class="text-rose-700">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-900 shadow-sm">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-rose-500 text-white rounded-xl flex items-center justify-center shrink-0">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
            </div>
            <p class="font-bold">Perubahan tidak tersimpan!</p>
        </div>
        <ul class="mt-2 ml-11 space-y-1 list-disc">
            @foreach($errors->all() as $pesan)
                <li>{{ $pesan }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- HERO HEADER -->
    <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white border border-slate-800 shadow-xl relative overflow-hidden">
        <div class="absolute -right-20 -top-20 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="space-y-2">
                <div class="inline-flex items-center space-x-2 px-3 py-1 bg-amber-500/20 text-amber-400 rounded-full text-xs font-bold border border-amber-500/30">
                    <i data-lucide="users" class="w-3.5 h-3.5"></i>
                    <span class="uppercase tracking-wider">Panel Administrator</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Kelola Akun Pegawai</h1>
                <p class="text-slate-300 text-xs sm:text-sm">Buat, edit, dan hapus akun pegawai untuk penugasan di seluruh sub-bidang CRMC.</p>
            </div>
            <button onclick="openTambahModal()" class="inline-flex items-center space-x-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-xl text-sm transition shadow-md shrink-0">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Tambah Pegawai Baru</span>
            </button>
        </div>
    </div>

    <!-- STATS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Pegawai</p>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ $users->count() }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Administrator</p>
                <p class="text-2xl font-black text-amber-600 mt-1">{{ $users->where('role', 'admin')->count() }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <i data-lucide="shield" class="w-6 h-6"></i>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pegawai</p>
                <p class="text-2xl font-black text-blue-900 mt-1">{{ $users->where('role', 'pegawai')->count() }}</p>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <i data-lucide="user-check" class="w-6 h-6"></i>
            </div>
        </div>
    </div>

    <!-- TABLE PEGAWAI -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="list" class="w-4 h-4 text-amber-500"></i>
                Daftar Seluruh Akun Pegawai
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-bold text-slate-500 uppercase tracking-wider">Foto</th>
                        <th class="text-left px-5 py-3 font-bold text-slate-500 uppercase tracking-wider">Nama Lengkap</th>
                        <th class="text-left px-5 py-3 font-bold text-slate-500 uppercase tracking-wider">NIP</th>
                        <th class="text-left px-5 py-3 font-bold text-slate-500 uppercase tracking-wider">Jabatan</th>
                        <th class="text-left px-5 py-3 font-bold text-slate-500 uppercase tracking-wider">Email</th>
                        <th class="text-left px-5 py-3 font-bold text-slate-500 uppercase tracking-wider">Role</th>
                        <th class="text-center px-5 py-3 font-bold text-slate-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($users as $u)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-5 py-3">
                            <img src="{{ $u->foto_url }}" alt="{{ $u->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-200 shadow-xs">
                        </td>
                        <td class="px-5 py-3 font-bold text-slate-900">{{ $u->name }}</td>
                        <td class="px-5 py-3 text-slate-600 font-mono">{{ $u->nip ?? '-' }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $u->jabatan ?? '-' }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $u->email }}</td>
                        <td class="px-5 py-3">
                            @if($u->role === 'admin')
                                <span class="px-2 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-800 rounded-full">Admin</span>
                            @else
                                <span class="px-2 py-0.5 text-[10px] font-semibold bg-blue-50 text-blue-800 rounded-full">Pegawai</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <button onclick="openEditModal({{ json_encode($u) }})" class="p-1.5 bg-blue-50 hover:bg-blue-100 text-blue-900 rounded-lg transition" title="Edit">
                                    <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
                                </button>
                                @if($u->id !== Auth::id())
                                <form action="{{ route('admin.pegawai.delete', $u->id) }}" method="POST"
                                      data-konfirmasi="Yakin ingin menghapus akun &quot;{{ $u->name }}&quot;? Akun ini tidak bisa dipulihkan lagi.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg transition" title="Hapus">
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

</main>
@endsection

@section('modals')
<!-- MODAL TAMBAH PEGAWAI -->
<div id="tambahModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
    <div class="bg-white w-full max-w-xl max-h-[92vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-slate-100">
        <div class="bg-slate-900 text-white p-5 sm:p-6 flex items-start justify-between border-b border-slate-800">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center text-slate-950 font-bold shrink-0">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest">Administrator</span>
                    <h3 class="text-lg font-extrabold text-white">Tambah Akun Pegawai Baru</h3>
                </div>
            </div>
            <button onclick="closeTambahModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-5 sm:p-6">
            <form action="{{ route('admin.pegawai.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" required class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">NIP *</label>
                        <input type="text" name="nip" required class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-800 mb-1">Jabatan *</label>
                    <input type="text" name="jabatan" required class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Email Dinas *</label>
                        <input type="email" name="email" required class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Password *</label>
                        <input type="password" name="password" required minlength="6" class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Role *</label>
                        <select name="role" required class="w-full p-2.5 rounded-xl border border-slate-300 bg-white font-semibold focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <option value="pegawai">Pegawai</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Foto Profil (Opsional)</label>
                        <input type="file" name="foto_profil" accept="image/jpeg,image/png,image/webp" class="w-full p-2 bg-slate-50 rounded-xl border border-slate-300 text-slate-600">
                        <p class="mt-1 text-[10px] text-slate-400">JPG, PNG, atau WebP. Maksimal 4 MB.</p>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-end space-x-2">
                    <button type="button" onclick="closeTambahModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold shadow-sm transition">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT PEGAWAI -->
<div id="editModal" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm z-50 flex items-center justify-center p-3 sm:p-4 hidden">
    <div class="bg-white w-full max-w-xl max-h-[92vh] rounded-3xl shadow-2xl flex flex-col overflow-hidden border border-slate-100">
        <div class="bg-slate-900 text-white p-5 sm:p-6 flex items-start justify-between border-b border-slate-800">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold shrink-0">
                    <i data-lucide="pencil" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-amber-400 uppercase tracking-widest">Administrator</span>
                    <h3 class="text-lg font-extrabold text-white">Edit Data Pegawai</h3>
                </div>
            </div>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-5 sm:p-6">
            <form id="editForm" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                @method('PUT')
                <input type="hidden" id="editUserId" name="user_id" value="">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" id="editName" required class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">NIP *</label>
                        <input type="text" name="nip" id="editNip" required class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block font-bold text-slate-800 mb-1">Jabatan *</label>
                    <input type="text" name="jabatan" id="editJabatan" required class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Email Dinas *</label>
                        <input type="email" name="email" id="editEmail" required class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Password Baru (kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" minlength="6" class="w-full p-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none" placeholder="••••••">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Role *</label>
                        <select name="role" id="editRole" required class="w-full p-2.5 rounded-xl border border-slate-300 bg-white font-semibold focus:ring-2 focus:ring-amber-500 focus:outline-none">
                            <option value="pegawai">Pegawai</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">Foto Profil Baru (Opsional)</label>
                        <input type="file" name="foto_profil" accept="image/jpeg,image/png,image/webp" class="w-full p-2 bg-slate-50 rounded-xl border border-slate-300 text-slate-600">
                        <p class="mt-1 text-[10px] text-slate-400">JPG, PNG, atau WebP. Maksimal 4 MB. Kosongkan bila tidak ingin mengganti foto.</p>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-200 flex justify-end space-x-2">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-900 hover:bg-blue-950 text-white font-bold shadow-sm transition">Perbarui Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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
        document.getElementById('editModal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
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
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editModal').scrollIntoView({ behavior: 'smooth', block: 'center' });
        })();
    @elseif($errors->any())
        openTambahModal();
    @endif
</script>
@endpush
