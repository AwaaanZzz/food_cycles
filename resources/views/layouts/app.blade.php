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

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/mascot.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Styles & Scripts via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full flex flex-col text-slate-800 dark:text-slate-100 antialiased bg-slate-50 dark:bg-slate-950 pb-16 md:pb-0 selection:bg-blue-100 selection:text-blue-900 dark:selection:bg-blue-900 dark:selection:text-blue-100 transition-colors duration-150"
      x-data
      @keydown.escape.window="if (window.Alpine && window.Alpine.store('app')) { $store.app.showPrefsModal = false; $store.app.openDemo = false; }">

    <!-- Navbar -->
    <header class="sticky top-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-b border-slate-200/90 dark:border-slate-800 shadow-2xs transition-colors duration-150">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Brand Logo (Mascot + Typography) -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                        <img src="{{ asset('images/mascot.png') }}" alt="FoodCycle" class="h-10 w-auto shrink-0 select-none group-hover:scale-105 transition-transform duration-200">
                        <div class="flex items-center gap-2">
                            <img src="{{ asset('images/brand-text.png') }}" alt="FoodCycle" class="h-7 w-auto select-none dark:brightness-0 dark:invert">
                            <span class="sr-only">FoodCycle</span>
                            <span class="hidden sm:inline-block text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-full shadow-2xs">
                                Assistant
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                @auth
                <nav class="hidden md:flex items-center gap-1.5 bg-slate-100/80 dark:bg-slate-800/80 p-1 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                    <a href="{{ route('dashboard') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-700/60' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        <span>Beranda</span>
                    </a>
                    <a href="{{ route('recipes.saved') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 {{ request()->routeIs('recipes.saved') ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-700/60' }}">
                        <svg class="w-4 h-4 {{ request()->routeIs('recipes.saved') ? 'text-white' : 'text-slate-500 dark:text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                        <span>Resep Tersimpan</span>
                    </a>
                    <button @click="$store.app.openDemo = true" 
                            onclick="window.openTutorial && window.openTutorial()" 
                            type="button" 
                            class="cursor-pointer px-3.5 py-1.5 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-700/60 transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Panduan</span>
                    </button>
                </nav>

                <!-- Desktop User Menu -->
                <div class="hidden md:flex items-center gap-2.5">
                    <!-- Dark Mode Toggle Button (Desktop) -->
                    <button @click="$store.app.toggleTheme()" 
                            type="button" 
                            class="cursor-pointer p-2 rounded-xl text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition shadow-2xs flex items-center justify-center" 
                            :title="$store.app.isDark ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'"
                            aria-label="Toggle Mode Malam">
                        <!-- Sun Icon (Active when dark) -->
                        <svg x-show="$store.app.isDark" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9h-1m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <!-- Moon Icon (Active when light) -->
                        <svg x-show="!$store.app.isDark" class="w-4 h-4 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <button @click="$store.app.showPrefsModal = true" 
                            onclick="window.openPreferences && window.openPreferences()" 
                            type="button" 
                            class="cursor-pointer px-3 py-1.5 text-xs font-semibold rounded-lg text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition flex items-center gap-1.5 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        <span>Preferensi</span>
                    </button>

                    <div class="h-4 w-px bg-slate-200 dark:bg-slate-700"></div>

                    <!-- Customer Profile Link -->
                    <a href="{{ route('profile') }}" 
                       class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100/80 dark:hover:bg-slate-800/80 transition cursor-pointer {{ request()->routeIs('profile') ? 'bg-blue-50 dark:bg-blue-950/60 ring-1 ring-blue-300 dark:ring-blue-700' : '' }}"
                       title="Buka Profil Customer">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-indigo-700 text-white flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="text-left">
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 leading-tight">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] text-blue-600 dark:text-blue-400 font-medium">{{ auth()->user()->dietary_preferences[0] ?? 'Umum' }}</p>
                        </div>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer" title="Keluar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
                @else
                <div class="flex items-center gap-2">
                    <!-- Dark Mode Toggle Button (Guest) -->
                    <button @click="$store.app.toggleTheme()" 
                            type="button" 
                            class="cursor-pointer p-2 rounded-xl text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 transition shadow-2xs flex items-center justify-center" 
                            :title="$store.app.isDark ? 'Beralih ke Mode Terang' : 'Beralih ke Mode Gelap'"
                            aria-label="Toggle Mode Malam">
                        <svg x-show="$store.app.isDark" x-cloak class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9h-1m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg x-show="!$store.app.isDark" class="w-4 h-4 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <button @click="$store.app.openDemo = true" 
                            onclick="window.openTutorial && window.openTutorial()" 
                            type="button" 
                            class="cursor-pointer px-3 py-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition">
                        Panduan
                    </button>
                    <a href="{{ route('login') }}" class="px-3.5 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white">Masuk</a>
                    <a href="{{ route('register') }}" class="px-4 py-2 text-xs font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-xl transition shadow-xs">Daftar</a>
                </div>
                @endauth

                <!-- Mobile Header Buttons -->
                <div class="md:hidden flex items-center gap-1">
                    <!-- Mobile Dark Mode Toggle -->
                    <button @click="$store.app.toggleTheme()" type="button" class="p-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg cursor-pointer" :title="$store.app.isDark ? 'Mode Terang' : 'Mode Gelap'" aria-label="Toggle Mode Malam">
                        <svg x-show="$store.app.isDark" x-cloak class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9h-1m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg x-show="!$store.app.isDark" class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                    <button @click="$store.app.openDemo = true" onclick="window.openTutorial && window.openTutorial()" class="p-2 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg cursor-pointer" title="Panduan">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </button>
                    <button @click="$store.app.mobileMenuOpen = ! $store.app.mobileMenuOpen" type="button" class="p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="! $store.app.mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                            <path x-show="$store.app.mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Drawer Menu -->
        @auth
        <div x-show="$store.app.mobileMenuOpen" x-cloak @click.away="$store.app.mobileMenuOpen = false" class="md:hidden border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 py-3 space-y-1 shadow-lg">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('dashboard') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white' : 'text-slate-600 dark:text-slate-300' }}">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Beranda</span>
            </a>
            <a href="{{ route('recipes.saved') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('recipes.saved') ? 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white' : 'text-slate-600 dark:text-slate-300' }}">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                <span>Resep Tersimpan</span>
            </a>
            <a href="{{ route('profile') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('profile') ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 font-bold' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800' }}">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span>Profil Customer</span>
            </a>
            <button @click="$store.app.showPrefsModal = true; $store.app.mobileMenuOpen = false" onclick="window.openPreferences && window.openPreferences()" class="w-full flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer">
                <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                <span>Preferensi Dapur</span>
            </button>
            <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ route('profile') }}" class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-200 leading-tight">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-slate-400 leading-tight">{{ auth()->user()->email }}</p>
                    </div>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-rose-600 dark:text-rose-400 px-3 py-1.5 rounded-lg hover:bg-rose-50 dark:hover:bg-rose-950/40 cursor-pointer">Keluar</button>
                </form>
            </div>
        </div>
        @endauth
    </header>

    <!-- Mobile Bottom Navigation Bar -->
    <nav class="fixed bottom-0 inset-x-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 shadow-md md:hidden py-1 px-4 flex items-center justify-around text-[10px] font-semibold transition-colors duration-150">
        <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-0.5 py-1 {{ request()->routeIs('dashboard') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            <span>Beranda</span>
        </a>
        <a href="{{ route('dashboard') }}#ai-workspace" class="flex flex-col items-center gap-0.5 py-1 text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <span>Buat Resep</span>
        </a>
        <a href="{{ route('recipes.saved') }}" class="flex flex-col items-center gap-0.5 py-1 {{ request()->routeIs('recipes.saved') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
            <span>Tersimpan</span>
        </a>
        @auth
        <a href="{{ route('profile') }}" class="flex flex-col items-center gap-0.5 py-1 {{ request()->routeIs('profile') ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span>Profil</span>
        </a>
        @endauth
        <button @click="$store.app.openDemo = true" onclick="window.openTutorial && window.openTutorial()" class="flex flex-col items-center gap-0.5 py-1 text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white cursor-pointer">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>Panduan</span>
        </button>
    </nav>

    <!-- Global Notification Banner Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 w-full">
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-cloak class="mb-4 rounded-xl bg-blue-50 dark:bg-blue-950/70 border border-blue-200 dark:border-blue-800/80 p-3.5 flex items-center justify-between text-blue-900 dark:text-blue-200 text-xs sm:text-sm shadow-2xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-blue-700 dark:text-blue-300 hover:text-blue-950 dark:hover:text-white p-1 cursor-pointer">&times;</button>
        </div>
        @endif

        @if(session('warning'))
        <div x-data="{ show: true }" x-show="show" x-cloak class="mb-4 rounded-xl bg-amber-50 dark:bg-amber-950/70 border border-amber-200 dark:border-amber-800/80 p-3.5 flex items-center justify-between text-amber-900 dark:text-amber-200 text-xs sm:text-sm">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span class="font-medium">{{ session('warning') }}</span>
            </div>
            <button @click="show = false" class="text-amber-700 dark:text-amber-300 hover:text-amber-950 dark:hover:text-white p-1 cursor-pointer">&times;</button>
        </div>
        @endif

        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-cloak class="mb-4 rounded-xl bg-rose-50 dark:bg-rose-950/70 border border-rose-200 dark:border-rose-800/80 p-3.5 flex items-center justify-between text-rose-900 dark:text-rose-200 text-xs sm:text-sm">
            <div class="flex items-center gap-2.5">
                <svg class="w-4 h-4 text-rose-600 dark:text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-rose-700 dark:text-rose-300 hover:text-rose-950 dark:hover:text-white p-1 cursor-pointer">&times;</button>
        </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-16 bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-6 transition-colors duration-150">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-2.5">
                    <img src="{{ asset('images/mascot.png') }}" alt="FoodCycle" class="h-6 w-auto">
                    <span class="font-bold text-slate-800 dark:text-slate-200">FoodCycle</span>
                    <span class="text-slate-300 dark:text-slate-700">|</span>
                    <span>Asisten Perencanaan Masak & Resep</span>
                </div>
                <div class="flex items-center gap-4">
                    <span>&copy; {{ date('Y') }} FoodCycle. Hak cipta dilindungi.</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- MODAL PANDUAN -->
    <div x-data="{ step: 1 }"
         x-show="$store.app.openDemo" 
         x-cloak 
         class="relative z-50" 
         role="dialog" 
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
                    
                    <div class="bg-gradient-to-r from-blue-50/80 via-amber-50/50 to-orange-50/40 dark:from-slate-850 dark:via-slate-800 dark:to-slate-850 px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <img src="{{ asset('images/mascot.png') }}" class="h-9 w-auto">
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight">Panduan Penggunaan FoodCycle</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Tiga langkah mudah membuat resep terencana</p>
                            </div>
                        </div>
                        <button type="button" @click="$store.app.openDemo = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1.5 rounded-xl hover:bg-white/80 dark:hover:bg-slate-800 transition cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="flex items-center justify-between px-6 py-3 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 text-xs font-semibold text-slate-500 dark:text-slate-400">
                        <span :class="step === 1 ? 'text-blue-700 dark:text-blue-400 bg-blue-100/90 dark:bg-blue-950/80 px-2.5 py-1 rounded-lg font-bold' : ''">1. Pilih Mode</span>
                        <span class="text-slate-300 dark:text-slate-600">&rarr;</span>
                        <span :class="step === 2 ? 'text-blue-700 dark:text-blue-400 bg-blue-100/90 dark:bg-blue-950/80 px-2.5 py-1 rounded-lg font-bold' : ''">2. Masukkan Data</span>
                        <span class="text-slate-300 dark:text-slate-600">&rarr;</span>
                        <span :class="step === 3 ? 'text-blue-700 dark:text-blue-400 bg-blue-100/90 dark:bg-blue-950/80 px-2.5 py-1 rounded-lg font-bold' : ''">3. Hasil Resep</span>
                    </div>

                    <div class="p-6 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        <div x-show="step === 1" class="space-y-3">
                            <h4 class="font-bold text-slate-900 dark:text-white">Langkah 1: Tentukan Metode Pembuatan Resep</h4>
                            <p>Pilih salah satu dari tiga metode yang tersedia di halaman utama:</p>
                            <div class="space-y-2 pt-1 text-xs">
                                <div class="p-3 rounded-xl bg-blue-50/60 dark:bg-blue-950/40 border border-blue-200/80 dark:border-blue-900/60">
                                    <strong class="text-blue-900 dark:text-blue-200 block mb-0.5 font-bold">Bahan & Peralatan (FCAI)</strong>
                                    Membuat resep berdasarkan ketersediaan bahan dapur dan peralatan masak yang Anda miliki.
                                </div>
                                <div class="p-3 rounded-xl bg-orange-50/60 dark:bg-orange-950/40 border border-orange-200/80 dark:border-orange-900/60">
                                    <strong class="text-orange-900 dark:text-orange-200 block mb-0.5 font-bold">Batas Anggaran (FCBB)</strong>
                                    Merancang menu makan dengan estimasi total belanja bahan tidak melebihi nominal anggaran tertentu.
                                </div>
                                <div class="p-3 rounded-xl bg-purple-50/60 dark:bg-purple-950/40 border border-purple-200/80 dark:border-purple-900/60">
                                    <strong class="text-purple-900 dark:text-purple-200 block mb-0.5 font-bold">Identifikasi Foto (FBPB)</strong>
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
                            <button x-show="step < 3" @click="step++" type="button" class="px-5 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold transition cursor-pointer shadow-sm shadow-blue-600/20">
                                Selanjutnya
                            </button>
                            <button x-show="step === 3" @click="$store.app.openDemo = false" type="button" class="px-5 py-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold transition cursor-pointer shadow-sm shadow-blue-600/20">
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
                        <div class="bg-gradient-to-r from-blue-50/80 via-indigo-50/40 to-white dark:from-slate-850 dark:via-slate-800 dark:to-slate-850 px-6 py-4 border-b border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-2xs">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight">Preferensi Dapur & Diet</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Sesuaikan profil untuk personalisasi resep otomatis</p>
                                </div>
                            </div>
                            <button type="button" @click="$store.app.showPrefsModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto text-xs sm:text-sm">
                            <div>
                                <label class="block font-bold text-slate-900 dark:text-white mb-2 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                    <span>Preferensi / Pantangan Diet</span>
                                </label>
                                @php
                                    $dietOptions = ['Halal', 'Vegetarian', 'Vegan', 'Rendah Gula', 'Rendah Garam', 'Bebas Gluten', 'Bebas Laktosa', 'Tinggi Protein'];
                                    $userDiets = auth()->user()->dietary_preferences ?? ['Halal'];
                                @endphp
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($dietOptions as $option)
                                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 hover:border-blue-300 dark:hover:border-blue-600 hover:bg-blue-50/40 dark:hover:bg-blue-950/40 cursor-pointer text-xs transition select-none">
                                        <input type="checkbox" name="dietary_preferences[]" value="{{ $option }}" {{ in_array($option, $userDiets) ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-500">
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
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs transition cursor-pointer shadow-sm shadow-blue-600/20">
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
