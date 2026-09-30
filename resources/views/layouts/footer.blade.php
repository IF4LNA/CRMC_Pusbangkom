{{-- Footer bersama untuk seluruh halaman CRMC. --}}
<footer class="bg-slate-900 text-slate-300 mt-auto border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

            {{-- Identitas instansi --}}
            <div class="lg:col-span-2">
                <div class="flex items-start gap-3 mb-4">
                    <div class="w-12 h-12 flex items-center justify-center bg-slate-800 rounded-xl shrink-0 border border-slate-700">
                        <img src="{{ asset('images/Logo_PU.svg') }}" alt="Logo Kementerian Pekerjaan Umum" class="w-full h-full object-contain p-1">
                    </div>
                    <div>
                        <p class="text-[11px] font-bold tracking-widest text-amber-400 uppercase">Kementerian Pekerjaan Umum</p>
                        <h3 class="text-base font-extrabold text-white leading-tight">CRMC PUSBANGKOM ACP</h3>
                        <p class="text-[11px] text-slate-400">Continuous Monitoring on Risk Control</p>
                    </div>
                </div>
                <p class="text-xs leading-relaxed text-slate-400 max-w-md">
                    Aplikasi pemantauan berkelanjutan pengendalian risiko sebagai
                    mendukung penerapan Enterprise Risk Management (ERM) di
                    lingkungan Pusat Sumber Daya Air dan Prasarana Wilayah
                   Wilayah III Bandung.
                </p>
            </div>

            {{-- Tautan cepat --}}
            <div>
                <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Navigasi</h4>
                <ul class="space-y-2.5 text-xs">
                    <li>
                        <a href="{{ route('beranda') }}" class="flex items-center gap-2 text-slate-400 hover:text-amber-400 transition">
                            <i data-lucide="chevron-right" class="w-3 h-3"></i> Beranda
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('home') }}" class="flex items-center gap-2 text-slate-400 hover:text-amber-400 transition">
                            <i data-lucide="chevron-right" class="w-3 h-3"></i> Dashboard CRMC
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('home') }}?tab=dasar-hukum" class="flex items-center gap-2 text-slate-400 hover:text-amber-400 transition">
                            <i data-lucide="chevron-right" class="w-3 h-3"></i> Dasar Hukum
                        </a>
                    </li>
                    @auth
                        <li>
                            <a href="{{ route('admin.pegawai.index') }}" class="flex items-center gap-2 text-slate-400 hover:text-amber-400 transition">
                                <i data-lucide="chevron-right" class="w-3 h-3"></i> Kelola Pegawai
                            </a>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('login') }}" class="flex items-center gap-2 text-slate-400 hover:text-amber-400 transition">
                                <i data-lucide="chevron-right" class="w-3 h-3"></i> Masuk / Login
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>

            {{-- Kontak --}}
            <div>
                <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Kontak</h4>
                <ul class="space-y-3 text-xs">
                    <li class="flex items-start gap-2.5">
                        <i data-lucide="map-pin" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5"></i>
                        <span class="text-slate-400 leading-relaxed">
                            Jl. Padjajaran, Bandung
                        </span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i data-lucide="crosshair" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5"></i>
                        <span class="text-slate-400">6&deg;53&apos;58.0&quot;S 107&deg;39&apos;46.5&quot;E</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i data-lucide="phone" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5"></i>
                        <span class="text-slate-400">(022) 4203113</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <i data-lucide="mail" class="w-4 h-4 text-amber-500 shrink-0 mt-0.5"></i>
                        <a href="mailto:pusbangkom@pu.go.id" class="text-slate-400 hover:text-amber-400 transition break-all">pusbangkom@pu.go.id</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-10 pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-[11px] text-slate-500 text-center sm:text-left">
                &copy; {{ date('Y') Kementerian Pekerjaan Umum. Hak cipta dilindungi undang-undang.
            </p>
            <p class="text-[11px] text-slate-500 flex items-center gap-1.5">
                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-500"></i>
                Continuous Monitoring on Risk Control
            </p>
        </div>
    </div>
</footer>
