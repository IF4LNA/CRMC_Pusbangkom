{{-- Footer bersama untuk seluruh halaman CRMC. --}}
<footer class="mt-auto bg-blue-950 text-slate-300 border-t border-blue-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">

            {{-- Identitas instansi --}}
            <div class="lg:col-span-2">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-10 h-10 flex items-center justify-center bg-blue-900 rounded-lg shrink-0">
                        <img src="{{ asset('images/Logo_PU.svg') }}" alt="Logo Kementerian Pekerjaan Umum" class="w-full h-full object-contain p-1">
                    </div>
                    <div>
                        <p class="eyebrow !text-blue-300">Kementerian Pekerjaan Umum</p>
                        <h3 class="text-sm font-bold text-white leading-tight">CRMC PUSBANGKOM ACP</h3>
                        <p class="page-sub !text-blue-200/70">Continuous Monitoring on Risk Control</p>
                    </div>
                </div>
                <p class="page-sub leading-relaxed max-w-md">
                    Aplikasi pemantauan berkelanjutan pengendalian risiko sebagai
                    mendukung penerapan Enterprise Risk Management (ERM) di
                    lingkungan Pusat Sumber Daya Air dan Prasarana Wilayah
                    Wilayah III Bandung.
                </p>
            </div>

            {{-- Tautan cepat --}}
            <div>
                <h4 class="eyebrow !text-white mb-3">Navigasi</h4>
                <ul class="space-y-2 text-xs">
                    <li>
                        <a href="{{ route('beranda') }}" class="text-blue-200/80 hover:text-white transition">
                            Beranda
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('crmc.dashboard') }}" class="text-blue-200/80 hover:text-white transition">
                            Dashboard CRMC
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('crmc.dashboard', ['tab' => 'dasar-hukum']) }}" class="text-blue-200/80 hover:text-white transition">
                            Dasar Hukum
                        </a>
                    </li>
                    @auth
                        <li>
                            <a href="{{ route('admin.pegawai.index') }}" class="text-blue-200/80 hover:text-white transition">
                                Kelola Pegawai
                            </a>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('login') }}" class="text-blue-200/80 hover:text-white transition">
                                Masuk / Login
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>

            {{-- Kontak --}}
            <div>
                <h4 class="eyebrow !text-white mb-3">Kontak</h4>
                <ul class="space-y-2 text-xs">
                    <li class="flex items-start gap-2">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-blue-400 shrink-0 mt-0.5"></i>
                        <span class="text-blue-200/80">Jl. Padjajaran, Bandung</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="crosshair" class="w-3.5 h-3.5 text-blue-400 shrink-0 mt-0.5"></i>
                        <span class="text-blue-200/80">6&deg;53&apos;58.0&quot;S 107&deg;39&apos;46.5&quot;E</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="phone" class="w-3.5 h-3.5 text-blue-400 shrink-0 mt-0.5"></i>
                        <span class="text-blue-200/80">(022) 4203113</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i data-lucide="mail" class="w-3.5 h-3.5 text-blue-400 shrink-0 mt-0.5"></i>
                        <a href="mailto:pusbangkom@pu.go.id" class="text-blue-200/80 hover:text-white transition break-all">pusbangkom@pu.go.id</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-6 pt-4 border-t border-blue-900 flex flex-col sm:flex-row items-center justify-between gap-2">
            <p class="text-[11px] text-blue-200/50">
                &copy; {{ date('Y') }} Kementerian Pekerjaan Umum. Hak cipta dilindungi undang-undang.
            </p>
            <p class="text-[11px] text-blue-200/50">
                Continuous Monitoring on Risk Control
            </p>
        </div>
    </div>
</footer>
