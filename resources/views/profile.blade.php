@extends('layouts.app')

@section('title', 'Profil Customer - ' . $user->name . ' - FoodCycle')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

    <!-- Top Breadcrumb Navigation -->
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Beranda AI</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('recipes.saved') }}" class="px-3.5 py-1.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-bold flex items-center gap-1.5 transition shadow-2xs">
                <svg class="w-3.5 h-3.5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                <span>Resep Tersimpan ({{ $stats['saved_recipes'] }})</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="px-3 py-1.5 rounded-xl border border-rose-200 dark:border-rose-900/60 bg-rose-50/70 dark:bg-rose-950/40 hover:bg-rose-100 dark:hover:bg-rose-900/60 text-rose-600 dark:text-rose-400 text-xs font-bold flex items-center gap-1.5 transition cursor-pointer" title="Keluar dari akun">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>

    <!-- 1. Customer Profile Hero Card -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 shadow-sm p-6 sm:p-8 mb-8 relative overflow-hidden transition-colors duration-150">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <!-- Large Avatar Box with Initial -->
                <div class="relative shrink-0">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-br from-[#0B50B5] via-blue-600 to-indigo-700 text-white flex items-center justify-center font-black text-3xl sm:text-4xl shadow-md ring-4 ring-blue-100 dark:ring-blue-950">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-900 flex items-center justify-center text-white" title="Customer Aktif">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>

                <!-- Customer Credentials -->
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ $user->name }}
                        </h1>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                            Customer FoodCycle
                        </span>
                    </div>

                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 font-medium">
                        {{ $user->email }}
                    </p>

                    <div class="flex flex-wrap items-center gap-2 mt-3 text-xs text-slate-500 dark:text-slate-400">
                        <span class="inline-flex items-center gap-1 font-medium">
                            <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Bergabung sejak {{ $user->created_at->translatedFormat('d F Y') }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Action Badges -->
            <div class="flex flex-wrap sm:flex-col sm:items-end gap-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100 dark:border-slate-800">
                <span class="text-xs font-bold text-slate-400 dark:text-slate-500">Diet Utama:</span>
                <div class="flex flex-wrap gap-1.5">
                    @forelse($user->dietary_preferences ?? ['Umum'] as $diet)
                        <span class="px-3 py-1 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 font-bold text-xs border border-blue-200/70 dark:border-blue-800 shadow-2xs">
                            {{ $diet }}
                        </span>
                    @empty
                        <span class="text-xs text-slate-400 dark:text-slate-500">Umum / Tanpa pantangan</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Customer Activity & Cooking Stats (4 Metrics) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        <!-- Total Resep Dibuat -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-2xs flex items-center gap-4 transition-colors duration-150">
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div class="text-xs font-bold text-slate-400 dark:text-slate-500">Total Resep AI</div>
                <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">{{ $stats['total_recipes'] }}</div>
            </div>
        </div>

        <!-- Resep Tersimpan -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-2xs flex items-center gap-4 transition-colors duration-150">
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
            </div>
            <div>
                <div class="text-xs font-bold text-slate-400 dark:text-slate-500">Koleksi Tersimpan</div>
                <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">{{ $stats['saved_recipes'] }}</div>
            </div>
        </div>

        <!-- Mode Memasak Favorit -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-2xs flex items-center gap-4 transition-colors duration-150">
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <div class="text-xs font-bold text-slate-400 dark:text-slate-500">Metode Favorit</div>
                <div class="text-sm font-black text-slate-900 dark:text-white mt-1 line-clamp-1">{{ $stats['favorite_mode'] }}</div>
            </div>
        </div>

        <!-- Alat Dapur Terdaftar -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-2xs flex items-center gap-4 transition-colors duration-150">
            <div class="w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <div>
                <div class="text-xs font-bold text-slate-400 dark:text-slate-500">Peralatan Dapur</div>
                <div class="text-2xl font-black text-slate-900 dark:text-white mt-0.5">{{ $stats['tools_count'] }} <span class="text-xs font-semibold text-slate-400">Alat</span></div>
            </div>
        </div>
    </div>

    <!-- 3. Main Two-Column Layout: Profile Info vs Dietary & Kitchen Settings -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-10">

        <!-- Left Column: Personal Data, Security & Theme (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Pengaturan Mode Tampilan (Tema Gelap / Terang) -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 border border-slate-200/90 dark:border-slate-800 shadow-2xs transition-colors duration-150">
                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-2xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">Tema & Mode Tampilan</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pilih kenyamanan visual untuk aplikasi Anda</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2.5">
                    <!-- Option Light -->
                    <button type="button" @click="$store.app.setTheme('light')" 
                        :class="$store.app.theme === 'light' ? 'border-blue-600 bg-blue-50/60 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 ring-2 ring-blue-500/20' : 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                        class="p-3 rounded-2xl border text-center transition cursor-pointer flex flex-col items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9h-1m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        <span class="text-xs font-bold">Terang</span>
                    </button>

                    <!-- Option Dark -->
                    <button type="button" @click="$store.app.setTheme('dark')" 
                        :class="$store.app.theme === 'dark' ? 'border-blue-600 bg-blue-50/60 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 ring-2 ring-blue-500/20' : 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                        class="p-3 rounded-2xl border text-center transition cursor-pointer flex flex-col items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                        <span class="text-xs font-bold">Malam</span>
                    </button>

                    <!-- Option System -->
                    <button type="button" @click="$store.app.setTheme('system')" 
                        :class="$store.app.theme === 'system' ? 'border-blue-600 bg-blue-50/60 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 ring-2 ring-blue-500/20' : 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                        class="p-3 rounded-2xl border text-center transition cursor-pointer flex flex-col items-center gap-2">
                        <svg class="w-5 h-5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span class="text-xs font-bold">Sistem</span>
                    </button>
                </div>
            </div>

            <!-- Edit Data Diri Card -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 border border-slate-200/90 dark:border-slate-800 shadow-2xs transition-colors duration-150">
                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-2xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">Data Diri Customer</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Perbarui nama dan alamat email akun Anda</p>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input id="name" name="name" type="text" required value="{{ old('name', $user->name) }}"
                            placeholder="Nama lengkap Anda"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition bg-slate-50/50 dark:bg-slate-800 focus:bg-white dark:focus:bg-slate-800">
                        @error('name')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Alamat Email <span class="text-rose-500">*</span>
                        </label>
                        <input id="email" name="email" type="email" required value="{{ old('email', $user->email) }}"
                            placeholder="nama@email.com"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition bg-slate-50/50 dark:bg-slate-800 focus:bg-white dark:focus:bg-slate-800">
                        @error('email')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                            class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-xs transition cursor-pointer">
                            Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>

            <!-- Ganti Kata Sandi Card -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-7 border border-slate-200/90 dark:border-slate-800 shadow-2xs transition-colors duration-150" x-data="{ showPasswords: false }">
                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-2xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">Keamanan & Sandi</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Ubah kata sandi akun untuk menjaga keamanan</p>
                    </div>
                </div>

                <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="current_password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                            </label>
                            <button type="button" @click="showPasswords = !showPasswords" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 font-semibold cursor-pointer">
                                <span x-text="showPasswords ? 'Sembunyikan' : 'Tampilkan'"></span>
                            </button>
                        </div>
                        <input id="current_password" name="current_password" :type="showPasswords ? 'text' : 'password'" required
                            placeholder="Kata sandi saat ini"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition bg-slate-50/50 dark:bg-slate-800 focus:bg-white dark:focus:bg-slate-800">
                        @error('current_password')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Kata Sandi Baru <span class="text-rose-500">*</span>
                        </label>
                        <input id="password" name="password" :type="showPasswords ? 'text' : 'password'" required
                            placeholder="Minimal 6 karakter"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition bg-slate-50/50 dark:bg-slate-800 focus:bg-white dark:focus:bg-slate-800">
                        @error('password')
                            <p class="mt-1 text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                            Ulangi Kata Sandi Baru <span class="text-rose-500">*</span>
                        </label>
                        <input id="password_confirmation" name="password_confirmation" :type="showPasswords ? 'text' : 'password'" required
                            placeholder="Ulangi kata sandi baru"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition bg-slate-50/50 dark:bg-slate-800 focus:bg-white dark:focus:bg-slate-800">
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                            class="w-full py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs sm:text-sm shadow-xs transition cursor-pointer">
                            Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Kitchen Tools & Dietary Preferences (7 cols) -->
        <div class="lg:col-span-7">
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/90 dark:border-slate-800 shadow-2xs h-full flex flex-col justify-between transition-colors duration-150">
                <div>
                    <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-9 h-9 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-black text-slate-900 dark:text-white">Setelan Dapur & Diet Customer</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Preferensi ini menjadi acuan utama AI dalam meracik resep otomatis</p>
                        </div>
                    </div>

                    <form action="{{ route('preferences.update') }}" method="POST" id="preferences-form">
                        @csrf

                        <!-- Dietary Preferences Section -->
                        <div class="mb-6">
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-3 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                <span>Preferensi / Pantangan Makanan</span>
                            </label>
                            
                            @php
                                $dietOptions = ['Halal', 'Vegetarian', 'Vegan', 'Rendah Gula', 'Rendah Garam', 'Bebas Gluten', 'Bebas Laktosa', 'Tinggi Protein'];
                                $userDiets = $user->dietary_preferences ?? ['Halal'];
                            @endphp

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                @foreach($dietOptions as $option)
                                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 hover:border-blue-300 dark:hover:border-blue-600 hover:bg-blue-50/40 dark:hover:bg-blue-950/40 cursor-pointer text-xs transition select-none">
                                    <input type="checkbox" name="dietary_preferences[]" value="{{ $option }}" 
                                        {{ in_array($option, $userDiets) ? 'checked' : '' }} 
                                        class="rounded text-blue-600 focus:ring-blue-500">
                                    <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $option }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Cooking Tools Section -->
                        <div class="mb-6">
                            <label class="block text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-3 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                <span>Peralatan Masak yang Anda Miliki</span>
                            </label>

                            @php
                                $toolOptions = [
                                    'Kompor Gas/Listrik', 
                                    'Wajan / Kuali', 
                                    'Teflon Anti Lengket', 
                                    'Panci Rebus', 
                                    'Rice Cooker', 
                                    'Air Fryer', 
                                    'Blender / Chopper', 
                                    'Oven / Microwave', 
                                    'Pisau & Talenan'
                                ];
                                $userTools = $user->available_tools ?? ['Kompor Gas/Listrik', 'Wajan / Kuali', 'Rice Cooker'];
                            @endphp

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                @foreach($toolOptions as $tool)
                                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 hover:border-blue-300 dark:hover:border-blue-600 hover:bg-blue-50/40 dark:hover:bg-blue-950/40 cursor-pointer text-xs transition select-none">
                                    <input type="checkbox" name="available_tools[]" value="{{ $tool }}" 
                                        {{ in_array($tool, $userTools) ? 'checked' : '' }} 
                                        class="rounded text-blue-600 focus:ring-blue-500">
                                    <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $tool }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </form>
                </div>

                <div class="pt-5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Pilihan ini tersimpan otomatis pada akun Anda.</p>
                    <button type="submit" form="preferences-form" 
                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition cursor-pointer shadow-xs">
                        Simpan Setelan Dapur
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Riwayat Resep Terakhir Customer -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 sm:p-8 border border-slate-200/90 dark:border-slate-800 shadow-2xs transition-colors duration-150">
        <div class="flex items-center justify-between mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">Riwayat Resep Terakhir Anda</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Daftar ide masakan yang baru saja Anda racik bersama FoodCycle AI</p>
            </div>
            <a href="{{ route('recipes.saved') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                Lihat Resep Tersimpan &rarr;
            </a>
        </div>

        @if($recentRecipes->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($recentRecipes as $recipe)
                <div class="p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 hover:border-blue-300 dark:hover:border-blue-500 hover:shadow-2xs transition bg-slate-50/40 dark:bg-slate-800/50 hover:bg-white dark:hover:bg-slate-800 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200/70 dark:border-blue-800">
                                {{ $recipe->type_label }}
                            </span>
                            <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                                {{ $recipe->created_at->translatedFormat('d M Y') }}
                            </span>
                        </div>

                        <h3 class="font-bold text-slate-900 dark:text-white text-sm line-clamp-1 mb-1">
                            <a href="{{ route('recipes.show', $recipe->id) }}" class="hover:text-blue-600 dark:hover:text-blue-400 transition">
                                {{ $recipe->title }}
                            </a>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
                            {{ $recipe->description }}
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-200/60 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-900 dark:text-white">
                            {{ $recipe->formatted_cost }}
                        </span>
                        <a href="{{ route('recipes.show', $recipe->id) }}" class="font-bold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 flex items-center gap-1">
                            <span>Buka Resep</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <img src="{{ asset('images/mascot.png') }}" class="w-14 h-auto mx-auto mb-2 opacity-80" alt="FoodCycle Mascot">
                <h4 class="font-bold text-slate-800 dark:text-slate-200 text-xs">Belum ada resep yang dibuat</h4>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Gunakan salah satu dari 3 mode memasak di beranda untuk memulai.</p>
                <div class="mt-4">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition shadow-xs">
                        Mulai Meracik Resep
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
