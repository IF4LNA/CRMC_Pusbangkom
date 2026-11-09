@extends('layouts.auth')

@section('title', 'Lupa Kata Sandi')

@section('content')
    <!-- CARD HEADER -->
    <div class="px-5 py-5 border-b border-slate-200">
        <span class="badge badge-accent mb-2">
            <i data-lucide="key-round" class="w-3.5 h-3.5"></i>
            Pemulihan Akun
        </span>
        <h1 class="page-title">Lupa Kata Sandi?</h1>
        <p class="page-sub mt-1 leading-relaxed">
            Masukkan alamat email dinas Anda. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
        </p>
    </div>

    <!-- FORM BODY -->
    <div class="p-5 space-y-4">

        @if(session('status'))
        <div class="note !border-emerald-200 !bg-emerald-50 !text-emerald-800 flex items-start gap-2" role="status">
            <i data-lucide="check-circle" class="w-4 h-4 shrink-0 mt-0.5"></i>
            <p class="text-[11px] mt-0.5">{{ session('status') }}</p>
        </div>
        @endif

        @if($errors->any())
        <div class="note !border-rose-200 !bg-rose-50 !text-rose-800 flex items-start gap-2" role="alert" aria-live="assertive">
            <i data-lucide="alert-circle" class="w-4 h-4 shrink-0 mt-0.5"></i>
            <div>
                <p class="font-semibold">Permintaan Gagal</p>
                <p class="text-[11px] mt-0.5">{{ $errors->first() }}</p>
            </div>
        </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" class="space-y-3">
            @csrf

            <div>
                <label class="label" for="forgotEmail">Alamat Email Dinas *</label>
                <div class="relative">
                    <input type="email" name="email" id="forgotEmail" required autofocus
                           autocomplete="username"
                           value="{{ old('email') }}"
                           placeholder="nama@pu.go.id"
                           aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                           class="input pl-8">
                    <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5"></i>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <span>Kirim Tautan Atur Ulang</span>
                <i data-lucide="send" class="w-4 h-4"></i>
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
