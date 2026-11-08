<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light dark">

    <!-- Anti-FOUC Dark Mode Immediate Initializer -->
    <script>
        (function() {
            try {
                const storedTheme = localStorage.getItem('theme');
                const isSystemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                const shouldBeDark = storedTheme === 'dark' || (storedTheme !== 'light' && isSystemDark);
                if (shouldBeDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                const meta = document.querySelector('meta[name="color-scheme"]');
                if (meta) {
                    meta.content = (storedTheme === 'dark' || storedTheme === 'light') ? storedTheme : 'light dark';
                }
            } catch (e) {}
        })();
    </script>

    <title>@yield('title', 'FoodCycle')</title>

    <!-- SEO & social preview (WhatsApp, Facebook, X, Telegram) -->
    @php
        $metaTitle = trim($__env->yieldContent('meta_title')) ?: trim($__env->yieldContent('title', 'FoodCycle AI'));
        $metaDesc = trim($__env->yieldContent('meta_description')) ?: 'FoodCycle AI: asisten memasak cerdas. Ubah bahan sisa di dapur, anggaran belanja, atau foto makanan menjadi resep lezat, hemat, dan tanpa sisa.';
        $metaImage = trim($__env->yieldContent('meta_image')) ?: asset('images/brand-full.jpg');
    @endphp
    <meta name="description" content="{{ $metaDesc }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:site_name" content="FoodCycle AI">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="id_ID">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    <meta name="twitter:image" content="{{ $metaImage }}">
    <meta name="theme-color" content="#0B50B5">
    @stack('head')

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/mascot.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Font is set once via --font-sans in resources/css/app.css */
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full flex flex-col bg-slate-50 text-slate-800 antialiased dark:bg-slate-950 dark:text-slate-100 pb-20 lg:pb-0 selection:bg-brand-100 selection:text-brand-900 dark:selection:bg-brand-900 dark:selection:text-brand-100"
      x-data
      @keydown.escape.window="if (window.Alpine && window.Alpine.store('app')) { $store.app.showPrefsModal = false; $store.app.openDemo = false; $store.app.mobileMenuOpen = false; }">

    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[60] btn btn-primary btn-sm">Lewati ke konten</a>

    @php
        // Main sections: [route name or pattern (".*" = whole section), label, icon path]
        $mainNav = [
            ['dashboard', 'Beranda', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['recipes.index', 'Riwayat', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['recipes.saved', 'Tersimpan', 'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z'],
            ['pantry.*', 'Stok Dapur', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
            ['meal-plans.*', 'Menu Mingguan', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ];
        $navUrl = fn ($r) => route(str_replace('.*', '.index', $r));
        $sun = 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z';
        $moon = 'M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z';
        $info = 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
        $sliders = 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4';
    @endphp

    <!-- Top bar -->
    <header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/80 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/80">
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ auth()->check() ? route('dashboard') : route('home') }}" class="shrink-0 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" aria-label="FoodCycle beranda">
                <x-brand :badge="true" />
            </a>

            @auth
            <!-- Desktop navigation -->
            <nav class="hidden lg:flex items-center gap-0.5 rounded-xl bg-slate-100/80 p-1 dark:bg-slate-900" aria-label="Navigasi utama">
                @foreach($mainNav as [$navRoute, $navLabel, $navIcon])
                    @php $active = request()->routeIs($navRoute); @endphp
                    <a href="{{ $navUrl($navRoute) }}" @if($active) aria-current="page" @endif
                       class="flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-1.5 text-[13px] font-semibold transition {{ $active ? 'bg-white text-slate-900 shadow-sm ring-1 ring-slate-200/70 dark:bg-slate-800 dark:text-white dark:ring-slate-700' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}">
                        <svg class="hidden xl:block h-4 w-4 {{ $active ? 'text-brand-600 dark:text-brand-400' : '' }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $navIcon }}"/></svg>
                        {{ $navLabel }}
                    </a>
                @endforeach
            </nav>

            <!-- Desktop actions -->
            <div class="hidden lg:flex items-center gap-1">
                <button type="button" class="btn-icon" @click="$store.app.openDemo = true" onclick="window.openTutorial && window.openTutorial()" title="Panduan" aria-label="Buka panduan penggunaan">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $info }}"/></svg>
                </button>
                <button type="button" class="btn-icon" @click="$store.app.toggleTheme()" :title="$store.app.isDark ? 'Mode terang' : 'Mode gelap'" :aria-label="$store.app.isDark ? 'Beralih ke mode terang' : 'Beralih ke mode gelap'">
                    <svg x-show="$store.app.isDark" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $sun }}"/></svg>
                    <svg x-show="!$store.app.isDark" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $moon }}"/></svg>
                </button>
                <button type="button" class="btn-icon" @click="$store.app.showPrefsModal = true" onclick="window.openPreferences && window.openPreferences()" title="Preferensi dapur & diet" aria-label="Preferensi dapur dan diet">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $sliders }}"/></svg>
                </button>
                <span class="mx-2 h-6 w-px bg-slate-200 dark:bg-slate-800" aria-hidden="true"></span>
                <a href="{{ route('profile') }}" title="Profil" aria-label="Profil {{ auth()->user()->name }}"
                   class="flex items-center gap-2.5 rounded-xl p-1 pr-2 transition hover:bg-slate-100 dark:hover:bg-slate-800 {{ request()->routeIs('profile') ? 'bg-slate-100 dark:bg-slate-800' : '' }}">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-xs font-bold uppercase text-white ring-2 ring-white dark:ring-slate-900">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                    <span class="hidden 2xl:block max-w-[8rem] truncate text-sm font-semibold text-slate-700 dark:text-slate-200">{{ auth()->user()->name }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-icon hover:!text-rose-600" title="Keluar" aria-label="Keluar dari akun">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>

            <!-- Mobile actions -->
            <div class="flex items-center gap-0.5 lg:hidden">
                <button type="button" class="btn-icon" @click="$store.app.toggleTheme()" :aria-label="$store.app.isDark ? 'Beralih ke mode terang' : 'Beralih ke mode gelap'">
                    <svg x-show="$store.app.isDark" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $sun }}"/></svg>
                    <svg x-show="!$store.app.isDark" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $moon }}"/></svg>
                </button>
                <button type="button" class="btn-icon" @click="$store.app.openDemo = true" onclick="window.openTutorial && window.openTutorial()" aria-label="Buka panduan penggunaan">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $info }}"/></svg>
                </button>
                <button type="button" class="btn-icon" @click="$store.app.mobileMenuOpen = !$store.app.mobileMenuOpen" aria-label="Buka menu navigasi" :aria-expanded="$store.app.mobileMenuOpen">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                        <path x-show="!$store.app.mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        <path x-show="$store.app.mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            @else
            <!-- Guest actions -->
            <div class="flex items-center gap-1 sm:gap-2">
                <button type="button" class="btn-icon" @click="$store.app.toggleTheme()" :aria-label="$store.app.isDark ? 'Beralih ke mode terang' : 'Beralih ke mode gelap'">
                    <svg x-show="$store.app.isDark" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $sun }}"/></svg>
                    <svg x-show="!$store.app.isDark" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $moon }}"/></svg>
                </button>
                <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Daftar gratis</a>
            </div>
            @endauth
        </div>

        @auth
        <!-- Mobile drawer -->
        <div x-show="$store.app.mobileMenuOpen" x-cloak x-transition.opacity @click.away="$store.app.mobileMenuOpen = false"
             class="border-t border-slate-200 bg-white px-4 pb-4 pt-2 shadow-raised lg:hidden dark:border-slate-800 dark:bg-slate-950">
            <nav class="grid gap-0.5" aria-label="Menu">
                @foreach($mainNav as [$navRoute, $navLabel, $navIcon])
                    @php $active = request()->routeIs($navRoute); @endphp
                    <a href="{{ $navUrl($navRoute) }}" @if($active) aria-current="page" @endif
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ $active ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/60 dark:text-brand-300' : 'text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-900' }}">
                        <svg class="h-5 w-5 opacity-80" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $navIcon }}"/></svg>
                        {{ $navLabel }}
                    </a>
                @endforeach
                <button type="button" @click="$store.app.showPrefsModal = true; $store.app.mobileMenuOpen = false" onclick="window.openPreferences && window.openPreferences()"
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-900 cursor-pointer">
                    <svg class="h-5 w-5 opacity-80" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $sliders }}"/></svg>
                    Preferensi dapur & diet
                </button>
            </nav>
            <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800">
                <a href="{{ route('profile') }}" class="flex min-w-0 items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-600 text-sm font-bold uppercase text-white">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-semibold text-slate-900 dark:text-white">{{ auth()->user()->name }}</span>
                        <span class="block truncate text-xs text-slate-500">{{ auth()->user()->email }}</span>
                    </span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-ghost btn-sm !text-rose-600">Keluar</button>
                </form>
            </div>
        </div>
        @endauth
    </header>

    @auth
    <!-- Mobile bottom navigation -->
    @php
        $bottomNav = [
            ['dashboard', 'Beranda', $mainNav[0][2]],
            ['recipes.index', 'Riwayat', $mainNav[1][2]],
            null, // centre "create" button
            ['pantry.*', 'Stok', $mainNav[3][2]],
            ['profile', 'Profil', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        ];
    @endphp
    <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200/80 bg-white/90 pb-[env(safe-area-inset-bottom)] backdrop-blur-xl lg:hidden dark:border-slate-800 dark:bg-slate-950/90" aria-label="Navigasi bawah">
        <div class="mx-auto grid h-16 max-w-md grid-cols-5 items-center px-2">
            @foreach($bottomNav as $item)
                @if($item === null)
                    <a href="{{ route('dashboard') }}#ai-workspace" class="mx-auto -mt-6 flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-600 text-white shadow-raised ring-4 ring-white transition hover:bg-brand-700 dark:ring-slate-950" aria-label="Buat resep">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    </a>
                @else
                    @php [$r, $label, $icon] = $item; $active = request()->routeIs($r); @endphp
                    <a href="{{ $navUrl($r) }}" @if($active) aria-current="page" @endif
                       class="flex flex-col items-center gap-1 text-[11px] font-medium {{ $active ? 'text-brand-600 dark:text-brand-400' : 'text-slate-500 dark:text-slate-400' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="{{ $active ? 2.2 : 1.8 }}" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                        {{ $label }}
                    </a>
                @endif
            @endforeach
        </div>
    </nav>
    @endauth

    <!-- Notifications -->
    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        @auth
        @if(!auth()->user()->hasVerifiedEmail() && !request()->routeIs('verification.*'))
        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900 ring-1 ring-inset ring-amber-200 dark:bg-amber-950/40 dark:text-amber-200 dark:ring-amber-900">
            <span>Email Anda belum diverifikasi. Verifikasi agar akun bisa dipulihkan jika lupa kata sandi.</span>
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="font-semibold underline underline-offset-2 hover:no-underline cursor-pointer">Kirim tautan verifikasi</button>
            </form>
        </div>
        @endif
        @endauth

        @php
            $flashStyles = [
                'success' => ['bg-emerald-50 text-emerald-900 ring-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-200 dark:ring-emerald-900', 'text-emerald-600 dark:text-emerald-400', 'M5 13l4 4L19 7'],
                'warning' => ['bg-amber-50 text-amber-900 ring-amber-200 dark:bg-amber-950/40 dark:text-amber-200 dark:ring-amber-900', 'text-amber-600 dark:text-amber-400', 'M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z'],
                'error' => ['bg-rose-50 text-rose-900 ring-rose-200 dark:bg-rose-950/40 dark:text-rose-200 dark:ring-rose-900', 'text-rose-600 dark:text-rose-400', 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ];
        @endphp
        @foreach($flashStyles as $key => [$box, $iconColor, $iconPath])
            @if(session($key))
            <div x-data="{ show: true }" x-show="show" x-transition.opacity role="{{ $key === 'error' ? 'alert' : 'status' }}"
                 class="mt-4 flex items-start gap-3 rounded-xl px-4 py-3 text-sm ring-1 ring-inset {{ $box }}">
                <svg class="mt-0.5 h-5 w-5 shrink-0 {{ $iconColor }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/></svg>
                <p class="flex-1 font-medium">{{ session($key) }}</p>
                <button type="button" @click="show = false" class="-m-1 rounded-md p-1 opacity-60 hover:opacity-100 cursor-pointer" aria-label="Tutup notifikasi">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            @endif
        @endforeach
    </div>

    <main id="main" class="flex-1">
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-slate-200/80 dark:border-slate-800">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-8 text-sm text-slate-500 sm:flex-row sm:px-6 lg:px-8 dark:text-slate-400">
            <div class="flex items-center gap-3">
                <x-brand size="sm" />
                <span class="hidden sm:inline text-slate-300 dark:text-slate-700">·</span>
                <span class="hidden sm:inline">Masak cerdas, tanpa sisa.</span>
            </div>
            <p>&copy; {{ date('Y') }} FoodCycle. Estimasi harga & gizi dihitung oleh AI.</p>
        </div>
    </footer>

    <!-- MODAL PANDUAN -->
    <div x-data="{ step: 1 }"
         x-show="$store.app.openDemo" 
         x-cloak 
         class="relative z-50" 
         role="dialog" aria-labelledby="guide-title"
         aria-modal="true">
        
        <div x-show="$store.app.openDemo" 
             x-transition:enter="ease-out duration-150" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-100" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" 
             @click="$store.app.openDemo = false"></div>

        <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="$store.app.openDemo" 
                     x-transition:enter="ease-out duration-150" 
                     x-transition:enter-start="opacity-0 scale-95" 
                     x-transition:enter-end="opacity-100 scale-100" 
                     x-transition:leave="ease-in duration-100" 
                     x-transition:leave-start="opacity-100 scale-100" 
                     x-transition:leave-end="opacity-0 scale-95" 
                     class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 dark:border-slate-800"
                     @click.stop>
                    
                    <div class="bg-gradient-to-r from-brand-50/80 via-amber-50/50 to-orange-50/40 dark:from-slate-850 dark:via-slate-800 dark:to-slate-850 px-4 sm:px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="{{ asset('images/mascot.png') }}" alt="" class="h-8 sm:h-9 w-auto shrink-0">
                            <div>
                                <h3 id="guide-title" class="text-sm sm:text-base font-bold leading-snug text-slate-900 dark:text-white tracking-tight">Panduan Penggunaan FoodCycle</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Tiga langkah mudah membuat resep terencana</p>
                            </div>
                        </div>
                        <button type="button" @click="$store.app.openDemo = false" aria-label="Tutup panduan" class="shrink-0 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1.5 rounded-xl hover:bg-white/80 dark:hover:bg-slate-800 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="flex items-center justify-between gap-1 px-3 sm:px-6 py-3 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-[11px] sm:text-xs font-semibold whitespace-nowrap text-slate-500 dark:text-slate-400">
                        <span :class="step === 1 ? 'text-brand-700 dark:text-brand-400 bg-brand-100/90 dark:bg-brand-950/80 px-2 sm:px-2.5 py-1 rounded-lg font-bold' : ''">1. Pilih Mode</span>
                        <span class="text-slate-300 dark:text-slate-600">&rarr;</span>
                        <span :class="step === 2 ? 'text-brand-700 dark:text-brand-400 bg-brand-100/90 dark:bg-brand-950/80 px-2 sm:px-2.5 py-1 rounded-lg font-bold' : ''">2. Masukkan Data</span>
                        <span class="text-slate-300 dark:text-slate-600">&rarr;</span>
                        <span :class="step === 3 ? 'text-brand-700 dark:text-brand-400 bg-brand-100/90 dark:bg-brand-950/80 px-2 sm:px-2.5 py-1 rounded-lg font-bold' : ''">3. Hasil Resep</span>
                    </div>

                    <div class="p-4 sm:p-6 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        <div x-show="step === 1" class="space-y-3">
                            <h4 class="font-bold text-slate-900 dark:text-white">Langkah 1: Tentukan Metode Pembuatan Resep</h4>
                            <p>Pilih salah satu dari tiga metode yang tersedia di halaman utama:</p>
                            <div class="space-y-2 pt-1 text-xs">
                                <div class="p-3 rounded-xl bg-brand-50/60 dark:bg-brand-950/40 border border-brand-200/80 dark:border-brand-900/60">
                                    <strong class="text-brand-900 dark:text-brand-200 block mb-0.5 font-bold">Bahan & Peralatan (FCAI)</strong>
                                    Membuat resep berdasarkan ketersediaan bahan dapur dan peralatan masak yang Anda miliki.
                                </div>
                                <div class="p-3 rounded-xl bg-orange-50/60 dark:bg-orange-950/40 border border-orange-200/80 dark:border-orange-900/60">
                                    <strong class="text-orange-900 dark:text-orange-200 block mb-0.5 font-bold">Batas Anggaran (FCBB)</strong>
                                    Merancang menu makan dengan estimasi total belanja bahan tidak melebihi nominal anggaran tertentu.
                                </div>
                                <div class="p-3 rounded-xl bg-violet-50/60 dark:bg-violet-950/40 border border-violet-200/80 dark:border-violet-900/60">
                                    <strong class="text-violet-900 dark:text-violet-200 block mb-0.5 font-bold">Identifikasi Foto (FBPB)</strong>
                                    Mengenali jenis hidangan dari foto yang diunggah dan menyusun panduan memasaknya kembali.
                                </div>
                            </div>
                        </div>

                        <div x-show="step === 2" class="space-y-3">
                            <h4 class="font-bold text-slate-900 dark:text-white">Langkah 2: Isi Formulir</h4>
                            <p>Lengkapi formulir sesuai kebutuhan:</p>
                            <ul class="space-y-2 list-disc list-inside text-xs">
                                <li>Ketik bahan yang tersedia atau gunakan tombol tambah cepat.</li>
                                <li>Pilih peralatan masak utama seperti wajan, kompor, atau rice cooker.</li>
                                <li>Tentukan jumlah porsi dan catatan khusus jika ada.</li>
                            </ul>
                        </div>

                        <div x-show="step === 3" class="space-y-3">
                            <h4 class="font-bold text-slate-900 dark:text-white">Langkah 3: Dapatkan Resep & Simpan</h4>
                            <p>Sistem akan menampilkan panduan resep lengkap yang memuat:</p>
                            <ul class="space-y-2 list-disc list-inside text-xs">
                                <li>Daftar takaran bahan beserta estimasi harga per item.</li>
                                <li>Langkah memasak yang dapat ditandai selesai secara interaktif.</li>
                                <li>Ringkasan nilai gizi per porsi.</li>
                                <li>Opsi simpan ke koleksi untuk diakses kembali kapan saja.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="bg-slate-50/90 dark:bg-slate-800/80 px-6 py-3.5 flex items-center justify-between border-t border-slate-100 dark:border-slate-800">
                        <div>
                            <button x-show="step > 1" @click="step--" type="button" class="px-3.5 py-1.5 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition cursor-pointer">
                                Kembali
                            </button>
                        </div>
                        <div>
                            <button x-show="step < 3" @click="step++" type="button" class="btn btn-primary btn-sm">
                                Selanjutnya
                            </button>
                            <button x-show="step === 3" @click="$store.app.openDemo = false" type="button" class="btn btn-primary btn-sm">
                                Mengerti
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PREFERENSI DAPUR & DIET -->
    @auth
    <div x-data
         x-show="$store.app.showPrefsModal" 
         x-cloak 
         class="relative z-50" 
         role="dialog" 
         aria-modal="true">
        
        <div x-show="$store.app.showPrefsModal" 
             x-transition:enter="ease-out duration-150" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-100" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity" 
             @click="$store.app.showPrefsModal = false"></div>

        <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div x-show="$store.app.showPrefsModal" 
                     x-transition:enter="ease-out duration-150" 
                     x-transition:enter-start="opacity-0 scale-95" 
                     x-transition:enter-end="opacity-100 scale-100" 
                     x-transition:leave="ease-in duration-100" 
                     x-transition:leave-start="opacity-100 scale-100" 
                     x-transition:leave-end="opacity-0 scale-95" 
                     class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200 dark:border-slate-800"
                     @click.stop>
                    
                    <form action="{{ route('preferences.update') }}" method="POST">
                        @csrf
                        <div class="bg-gradient-to-r from-brand-50/80 via-brand-50/40 to-white dark:from-slate-850 dark:via-slate-800 dark:to-slate-850 px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-3 shrink-0">
                                <div class="w-9 h-9 rounded-xl bg-brand-600 text-white flex items-center justify-center shadow-2xs">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-bold text-slate-900 dark:text-white tracking-tight">Preferensi Dapur & Diet</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Sesuaikan profil untuk personalisasi resep otomatis</p>
                                </div>
                            </div>
                            <button type="button" @click="$store.app.showPrefsModal = false" aria-label="Tutup preferensi" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto text-xs sm:text-sm">
                            <div>
                                <label class="block font-bold text-slate-900 dark:text-white mb-2 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-brand-600"></span>
                                    <span>Preferensi / Pantangan Diet</span>
                                </label>
                                @php
                                    $dietOptions = ['Halal', 'Vegetarian', 'Vegan', 'Rendah Gula', 'Rendah Garam', 'Bebas Gluten', 'Bebas Laktosa', 'Tinggi Protein'];
                                    $userDiets = auth()->user()->dietary_preferences ?? ['Halal'];
                                @endphp
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($dietOptions as $option)
                                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 hover:border-brand-300 dark:hover:border-brand-600 hover:bg-brand-50/40 dark:hover:bg-brand-950/40 cursor-pointer text-xs transition select-none">
                                        <input type="checkbox" name="dietary_preferences[]" value="{{ $option }}" {{ in_array($option, $userDiets) ? 'checked' : '' }} class="rounded text-brand-600 focus:ring-brand-500">
                                        <span class="text-slate-700 dark:text-slate-200 font-medium">{{ $option }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-900 dark:text-white mb-2 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <span>Peralatan Masak yang Tersedia</span>
                                </label>
                                @php
                                    $toolOptions = ['Kompor Gas/Listrik', 'Wajan / Kuali', 'Teflon Anti Lengket', 'Panci Rebus', 'Rice Cooker', 'Air Fryer', 'Blender / Chopper', 'Oven / Microwave', 'Pisau & Talenan'];
                                    $userTools = auth()->user()->available_tools ?? ['Kompor Gas/Listrik', 'Wajan / Kuali', 'Rice Cooker'];
                                @endphp
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($toolOptions as $tool)
                                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 hover:border-amber-300 dark:hover:border-amber-600 hover:bg-amber-50/40 dark:hover:bg-amber-950/40 cursor-pointer text-xs transition select-none">
                                        <input type="checkbox" name="available_tools[]" value="{{ $tool }}" {{ in_array($tool, $userTools) ? 'checked' : '' }} class="rounded text-amber-600 focus:ring-amber-500">
                                        <span class="text-slate-700 dark:text-slate-200 font-medium">{{ $tool }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-50/90 dark:bg-slate-800/80 px-6 py-3.5 flex items-center justify-between border-t border-slate-100 dark:border-slate-800">
                            <button type="button" @click="$store.app.showPrefsModal = false" class="text-xs text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white font-semibold cursor-pointer">Batal</button>
                            <button type="submit" class="btn btn-primary btn-sm">
                                Simpan Preferensi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endauth

    @stack('scripts')
</body>
</html>
