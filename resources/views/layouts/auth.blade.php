<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Masuk Sistem CRMC') | Kementerian Pekerjaan Umum</title>
    {{-- Favicon memakai logo Kementerian Pekerjaan Umum, bukan ikon
         bawaan Laravel. --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/Logo_PU.svg') }}">
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
    <style>
        {{-- Halaman autentikasi berdiri sendiri (tidak memakai layout utama),
             jadi token dan komponen dasarnya didefinisikan di sini.
             Aksen tunggal: biru. --}}
        :root {
            --ac: #2563eb;
            --ac-dark: #1d4ed8;
            --ink: #0f172a;
            --line: #e2e8f0;
            --mut: #64748b;
        }
        body { font-size: 14px; }

        .card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: .75rem;
        }
        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -.015em;
            color: var(--ink);
        }
        .page-sub { font-size: .8125rem; color: var(--mut); }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            padding: .125rem .5rem;
            border-radius: .375rem;
            background: #f1f5f9;
            color: #475569;
            font-size: .6875rem;
            font-weight: 600;
        }
        .badge-accent { background: #dbeafe; color: #1e40af; }
        .note {
            padding: .75rem 1rem;
            border: 1px solid var(--line);
            border-radius: .5rem;
            background: #f8fafc;
            font-size: .8125rem;
            color: #334155;
        }
        .label {
            display: block;
            margin-bottom: .375rem;
            font-size: .6875rem;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: var(--mut);
        }
        .input {
            width: 100%;
            padding: .5rem .75rem;
            border: 1px solid #cbd5e1;
            border-radius: .5rem;
            background: #fff;
            font-family: inherit;
            font-size: .8125rem;
            color: var(--ink);
        }
        .input:focus {
            outline: 0;
            border-color: var(--ac);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .2);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .375rem;
            padding: .5rem .875rem;
            border: 1px solid transparent;
            border-radius: .5rem;
            font-size: .8125rem;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
            cursor: pointer;
            transition: background .15s, border-color .15s, color .15s;
        }
        .btn-primary { background: var(--ac); color: #fff; width: 100%; padding: .625rem 1rem; }
        .btn-primary:hover { background: var(--ac-dark); }
        .btn-primary:disabled { opacity: .65; cursor: not-allowed; }
        .btn-outline { background: #fff; border-color: #cbd5e1; color: #334155; }
        .btn-outline:hover { background: #f8fafc; }

        /* Varian tombol di atas latar gelap */
        .btn-on-dark { background: rgba(255, 255, 255, .12); border-color: rgba(255, 255, 255, .3); color: #fff; }
        .btn-on-dark:hover { background: rgba(255, 255, 255, .22); }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-700 bg-blue-950 flex flex-col justify-between">

    {{-- Latar foto gedung PUSBANGKOM, treatment-nya sama dengan banner di
         halaman Beranda: gambar menutupi layar penuh, lalu dilapisi gradien
         biru tua supaya teks putih tetap terbaca.

         Lapis latar memakai `z-0`, bukan `-z-10`. <body> punya kelas
         `bg-blue-950` yang opaque, jadi elemen ber-z-index negatif dilukis
         di belakang warna body dan fotonya tidak terlihat sama sekali.
         Karena itu header, main, dan footer diberi `relative z-10`. --}}
    <div class="fixed inset-0 z-0 overflow-hidden">
        <img src="{{ asset('images/gedung_pusbangkom.jpg') }}"
             alt="Gedung PUSBANGKOM"
             class="absolute inset-0 h-full w-full object-cover object-center">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-950/95 via-blue-950/85 to-blue-900/50"></div>
    </div>

    <!-- TOP HEADER BAR -->
    <header class="relative z-10 w-full px-4 sm:px-6 py-3 flex items-center justify-between">
        <a href="{{ route('beranda') }}" class="flex items-center gap-2.5 min-w-0">
            <img src="{{ asset('images/Logo_PU.svg') }}" alt="Logo PUPR" class="w-8 h-8 object-contain shrink-0">
            <span class="min-w-0">
                <span class="block text-[10px] font-bold tracking-widest text-yellow-400 uppercase leading-tight">CRMC</span>
                <span class="block text-sm font-bold text-white leading-tight truncate">Pusbangkom SDA CKPS</span>
            </span>
        </a>
        <a href="{{ route('beranda') }}" class="btn btn-on-dark shrink-0">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Beranda</span>
        </a>
    </header>

    <!-- MAIN AUTH CONTAINER -->
    <main class="relative z-10 flex-1 flex items-center justify-center p-4 sm:p-6">
        <div class="w-full max-w-sm card shadow-2xl shadow-blue-950/40">
            @yield('content')
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="relative z-10 w-full text-center py-4 text-[11px] text-blue-200/80">
        &copy; {{ date('Y') }} Kementerian Pekerjaan Umum &bull; Badan Pengembangan Sumber Daya Manusia (BPSDM)
    </footer>

    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
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
    @stack('scripts')
</body>
</html>
