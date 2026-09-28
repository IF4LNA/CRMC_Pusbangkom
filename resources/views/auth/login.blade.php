<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sistem CRMC | Kementerian Pekerjaan Umum</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pupr: { navy: '#0f172a', blue: '#1e3a8a', yellow: '#f59e0b', accent: '#2563eb' }
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-950 flex flex-col justify-between relative overflow-x-hidden selection:bg-amber-500 selection:text-slate-950">

    <!-- Background Decorative Glows -->
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-blue-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed bottom-0 right-1/4 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <!-- TOP HEADER BAR -->
    <header class="w-full px-6 py-4 flex items-center justify-between z-10">
        <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
            <div class="w-10 h-10 flex items-center justify-center">
                <img src="{{ asset('images/Logo_PU.svg') }}" alt="Logo PUPR" class="w-full h-full object-contain">
            </div>
            <div>
                <span class="text-[10px] font-bold tracking-widest text-amber-400 uppercase block">Kementerian Pekerjaan Umum</span>
                <span class="text-sm font-extrabold text-white group-hover:text-amber-400 transition">CRMC Pusbangkom</span>
            </div>
        </a>
        <a href="{{ url('/') }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl border border-slate-700 bg-slate-800/80 hover:bg-slate-700 text-slate-300 text-xs font-semibold transition">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Beranda</span>
        </a>
    </header>

    <!-- MAIN LOGIN CONTAINER -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 z-10">
        <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-100/10 overflow-hidden">
            
            <!-- CARD HEADER -->
            <div class="bg-gradient-to-r from-slate-900 via-blue-950 to-slate-900 p-6 sm:p-8 text-white border-b border-slate-800 relative">
                <div class="inline-flex items-center space-x-2 px-3 py-1 bg-amber-500/20 text-amber-400 rounded-full text-xs font-bold mb-3 border border-amber-500/30">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    <span>Autentikasi Pegawai & Administrator</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-white leading-tight">
                    Masuk ke Sistem CRMC
                </h1>
                <p class="text-xs text-slate-300 mt-1.5 leading-relaxed">
                    Akses pemantauan kepatuhan pengendalian risiko dan penugasan instrumen 8 komponen.
                </p>
            </div>

            <!-- FORM BODY -->
            <div class="p-6 sm:p-8 space-y-5">
                
                @if($errors->any())
                <div class="p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-start space-x-2.5">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0 mt-0.5"></i>
                    <div>
                        <p class="font-bold">Gagal Masuk!</p>
                        <p class="text-[11px] mt-0.5">{{ $errors->first() }}</p>
                    </div>
                </div>
                @endif

                <form action="{{ route('login.post') }}" method="POST" class="space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-bold text-slate-800 mb-1.5">Alamat Email Dinas *</label>
                        <div class="relative">
                            <input type="email" name="email" id="loginEmail" required value="{{ old('email', 'admin@pu.go.id') }}" 
                                   placeholder="nama@pu.go.id"
                                   class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none transition">
                            <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-800 mb-1.5">Kata Sandi *</label>
                        <div class="relative">
                            <input type="password" name="password" id="loginPassword" required value="password123"
                                   placeholder="••••••••"
                                   class="w-full pl-9 pr-10 py-2.5 rounded-xl border border-slate-300 text-slate-900 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none transition">
                            <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600">
                                <i data-lucide="eye" id="eyeIcon" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1">
                        <label class="flex items-center space-x-2 cursor-pointer text-slate-600">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-amber-500 focus:ring-amber-400 border-slate-300">
                            <span>Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full py-3 px-4 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold rounded-2xl text-xs flex items-center justify-center space-x-2 transition shadow-md hover:shadow-lg">
                        <span>Masuk ke Akun Saya</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </button>
                </form>

                <!-- DEMO QUICK LOGIN SWITCHER -->
                <div class="pt-4 border-t border-slate-100">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-2 text-center">Akun Uji Coba Cepat (Klik untuk Isi Otomatis)</span>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" onclick="fillCredentials('admin@pu.go.id', 'password123')" 
                                class="p-2.5 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-200 text-left transition group">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-extrabold text-amber-800 uppercase">Mode Admin</span>
                                <i data-lucide="user-check" class="w-3.5 h-3.5 text-amber-600"></i>
                            </div>
                            <p class="text-[11px] font-bold text-slate-800 mt-0.5 truncate">Fajar Syaffiqri</p>
                            <p class="text-[9px] text-slate-500">Akses Penuh & Penugasan</p>
                        </button>

                        <button type="button" onclick="fillCredentials('rina.staf@pu.go.id', 'password123')" 
                                class="p-2.5 rounded-xl bg-blue-50 hover:bg-blue-100 border border-blue-200 text-left transition group">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-extrabold text-blue-800 uppercase">Mode Pegawai</span>
                                <i data-lucide="user" class="w-3.5 h-3.5 text-blue-600"></i>
                            </div>
                            <p class="text-[11px] font-bold text-slate-800 mt-0.5 truncate">Rina Melati</p>
                            <p class="text-[9px] text-slate-500">Pengendali Risiko</p>
                        </button>
                    </div>
                </div>

            </div>

            <!-- CARD FOOTER -->
            <div class="bg-slate-50 px-6 py-3.5 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                <span>Pusbangkom Kementerian PU</span>
                <span class="flex items-center gap-1"><i data-lucide="lock" class="w-3 h-3 text-emerald-600"></i> Terenkripsi Aman</span>
            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="w-full text-center py-4 text-[11px] text-slate-500 z-10">
        &copy; 2026 Kementerian Pekerjaan Umum &bull; Badan Pengembangan Sumber Daya Manusia (BPSDM)
    </footer>

    <script>
        function fillCredentials(email, password) {
            document.getElementById('loginEmail').value = email;
            document.getElementById('loginPassword').value = password;
        }

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('loginPassword');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passwordInput.type = 'password';
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }

        window.onload = function() {
            lucide.createIcons();
        };
    </script>
</body>
</html>
