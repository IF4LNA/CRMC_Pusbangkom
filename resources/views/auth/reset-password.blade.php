@extends('layouts.auth')

@section('title', 'Atur Ulang Kata Sandi')

@section('content')
    <!-- CARD HEADER -->
    <div class="px-5 py-5 border-b border-slate-200">
        <span class="badge badge-accent mb-2">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
            Keamanan Akun
        </span>
        <h1 class="page-title">Atur Ulang Kata Sandi</h1>
        <p class="page-sub mt-1 leading-relaxed">
            Buat kata sandi baru untuk akun CRMC Anda. Gunakan kombinasi huruf, angka, dan simbol.
        </p>
    </div>

    <!-- FORM BODY -->
    <div class="p-5 space-y-4">

        @if($errors->any())
        <div class="note !border-rose-200 !bg-rose-50 !text-rose-800 flex items-start gap-2" role="alert" aria-live="assertive">
            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 mt-0.5"></i>
            <div>
                <p class="font-semibold">Gagal Mengubah Kata Sandi</p>
                <p class="text-[11px] mt-0.5">{{ $errors->first() }}</p>
            </div>
        </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST" class="space-y-3">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label class="label" for="resetEmail">Alamat Email Dinas *</label>
                <div class="relative">
                    <input type="email" name="email" id="resetEmail" required
                           autocomplete="username"
                           value="{{ old('email', $email) }}"
                           placeholder="nama@pu.go.id"
                           aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                           class="input pl-8">
                    <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5"></i>
                </div>
            </div>

            <div>
                <label class="label" for="resetPassword">Kata Sandi Baru *</label>
                <div class="relative">
                    <input type="password" name="password" id="resetPassword" required autofocus
                           autocomplete="new-password"
                           placeholder="Minimal 8 karakter"
                           aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                           class="input pl-8 pr-9">
                    <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5"></i>
                    <button type="button" onclick="togglePasswordVisibility('resetPassword', 'resetEyeIcon')"
                            title="Tampilkan kata sandi" aria-label="Tampilkan kata sandi baru"
                            class="absolute right-2 top-2 text-slate-400 hover:text-slate-700 transition">
                        <i data-lucide="eye" id="resetEyeIcon" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <div>
                <label class="label" for="resetPasswordConfirmation">Ulangi Kata Sandi Baru *</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="resetPasswordConfirmation" required
                           autocomplete="new-password"
                           placeholder="Ulangi kata sandi baru"
                           class="input pl-8 pr-9">
                    <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5"></i>
                    <button type="button" onclick="togglePasswordVisibility('resetPasswordConfirmation', 'confirmEyeIcon')"
                            title="Tampilkan kata sandi" aria-label="Tampilkan ulangi kata sandi"
                            class="absolute right-2 top-2 text-slate-400 hover:text-slate-700 transition">
                        <i data-lucide="eye" id="confirmEyeIcon" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <p class="text-[11px] text-slate-500 leading-relaxed">
                Kata sandi minimal 8 karakter dan harus mengandung angka serta simbol.
            </p>

            <button type="submit" class="btn btn-primary">
                <span>Simpan Kata Sandi Baru</span>
                <i data-lucide="check" class="w-4 h-4"></i>
            </button>
        </form>

        <div class="pt-3 border-t border-slate-100 text-center">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-700 hover:text-blue-800 hover:underline">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Kembali ke halaman masuk
            </a>
        </div>
    </div>

    <!-- CARD FOOTER -->
    <div class="px-5 py-3 border-t border-slate-200 flex items-center justify-between text-[11px] text-slate-500">
        <span>Pusbangkom Kementerian PU</span>
        <span class="flex items-center gap-1"><i data-lucide="lock" class="w-3 h-3 text-emerald-600"></i> Terenkripsi Aman</span>
    </div>
@endsection
