@extends('layouts.auth')

@section('title', 'Masuk Sistem CRMC')

@section('content')
    <!-- CARD HEADER -->
    <div class="px-5 py-5 border-b border-slate-200">
        <span class="badge badge-accent mb-2">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
            Autentikasi Pegawai
        </span>
        <h1 class="page-title">PUSBANGKOM SDA CKPS</h1>
        <p class="page-sub mt-1 leading-relaxed">
            Continuous Monitoring on Risk Control (CRMC)
        </p>
        <p class="page-sub mt-1 leading-relaxed">
            Pusat Pengembangan Kompetensi Sumber Daya Air, Cipta Karya dan Prasarana Strategis
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
                <p class="font-semibold">Gagal Masuk!</p>
                <p class="text-[11px] mt-0.5">{{ $errors->first() }}</p>
            </div>
        </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-3">
            @csrf

            <div>
                <label class="label" for="loginEmail">Alamat Email Dinas *</label>
                <div class="relative">
                    <input type="email" name="email" id="loginEmail" required autofocus
                           autocomplete="username"
                           value="{{ old('email') }}"
                           placeholder="nama@pu.go.id"
                           aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                           class="input pl-8">
                    <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5"></i>
                </div>
            </div>

            <div>
                <label class="label" for="loginPassword">Kata Sandi *</label>
                <div class="relative">
                    <input type="password" name="password" id="loginPassword" required
                           autocomplete="current-password"
                           placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;"
                           aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                           class="input pl-8 pr-9">
                    <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-2.5 top-2.5"></i>
                    <button type="button" onclick="togglePasswordVisibility('loginPassword', 'eyeIcon')"
                            title="Tampilkan kata sandi" aria-label="Tampilkan kata sandi"
                            class="absolute right-2 top-2 text-slate-400 hover:text-slate-700 transition">
                        <i data-lucide="eye" id="eyeIcon" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3">
                <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-600">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 accent-blue-600">
                    <span>Ingat saya</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-blue-700 hover:text-blue-800 hover:underline">
                    Lupa kata sandi?
                </a>
            </div>

            {{-- Tantangan Cloudflare Turnstile muncul hanya bila site key
                 terpasang, sehingga halaman tetap tampil normal di
                 lingkungan tanpa kunci. --}}
            @if(config('services.turnstile.site_key'))
            <div>
                <div class="cf-turnstile"
                     data-sitekey="{{ config('services.turnstile.site_key') }}"
                     data-theme="light"
                     data-appearance="always"
                     data-size="flexible"></div>
                @if($errors->has('cf-turnstile-response'))
                <p class="mt-1.5 text-[11px] text-rose-600">{{ $errors->first('cf-turnstile-response') }}</p>
                @endif
            </div>
            @endif

            <button type="submit" class="btn btn-primary">
                <span>Masuk ke Akun Saya</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>

        <p class="text-[11px] text-slate-500 text-center leading-relaxed">
            Akun dibuat oleh administrator. Jika akun Anda belum aktif atau terkendala,
            hubungi admin PUSBANGKOM.
        </p>
    </div>

    <!-- CARD FOOTER -->
    <div class="px-5 py-3 border-t border-slate-200 flex items-center justify-between text-[11px] text-slate-500">
        <span>Pusbangkom Kementerian PU</span>
        <span class="flex items-center gap-1"><i data-lucide="lock" class="w-3 h-3 text-emerald-600"></i> Terenkripsi Aman</span>
    </div>
@endsection

@push('scripts')
    @if(config('services.turnstile.site_key'))
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>
    @endif
@endpush
