@extends('layouts.app')

@section('title', 'Profil - ' . $user->name . ' - FoodCycle')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

    <!-- Top Breadcrumb Navigation -->
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-400 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Beranda AI</span>
        </a>

        <div class="flex items-center gap-2">
            <a href="{{ route('recipes.saved') }}" class="btn btn-secondary btn-sm">
                <svg class="w-3.5 h-3.5 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
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

    <!-- 1. Profile summary -->
    <div class="card p-6 sm:p-8 mb-8 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <!-- Large Avatar Box with Initial -->
                <div class="relative shrink-0">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-br from-brand-700 via-brand-600 to-brand-900 text-white flex items-center justify-center font-extrabold text-3xl sm:text-4xl shadow-md ring-4 ring-brand-100 dark:ring-brand-950">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full bg-emerald-500 border-2 border-white dark:border-slate-900 flex items-center justify-center text-white" title="Akun Aktif">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>

                <!-- Account details -->
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">
                            {{ $user->name }}
                        </h1>
                        <span class="badge bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-200 dark:bg-brand-950/60 dark:text-brand-300 dark:ring-brand-800"> Anggota FoodCycle </span>
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
                        <span class="px-3 py-1 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-800 dark:text-brand-300 font-bold text-xs border border-brand-200/70 dark:border-brand-800 shadow-2xs">
                            {{ $diet }}
                        </span>
                    @empty
                        <span class="text-xs text-slate-400 dark:text-slate-500">Umum / Tanpa pantangan</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Activity stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        
        <!-- Total Resep Dibuat -->
        <div class="card flex items-center gap-4 p-5">
            <div class="w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Resep AI</div>
                <div class="mt-0.5 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $stats['total_recipes'] }}</div>
            </div>
        </div>

        <!-- Resep Tersimpan -->
        <div class="card flex items-center gap-4 p-5">
            <div class="w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
            </div>
            <div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Koleksi Tersimpan</div>
                <div class="mt-0.5 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $stats['saved_recipes'] }}</div>
            </div>
        </div>

        <!-- Mode Memasak Favorit -->
        <div class="card flex items-center gap-4 p-5">
            <div class="w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Mode favorit</div>
                <div class="mt-1 text-sm font-semibold leading-snug text-slate-900 line-clamp-2 dark:text-white">{{ $stats['favorite_mode'] }}</div>
            </div>
        </div>

        <!-- Alat Dapur Terdaftar -->
        <div class="card flex items-center gap-4 p-5">
            <div class="w-12 h-12 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <div>
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Peralatan Dapur</div>
                <div class="mt-0.5 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $stats['tools_count'] }} <span class="text-xs font-semibold text-slate-400">Alat</span></div>
            </div>
        </div>
    </div>

    <!-- 3. Main Two-Column Layout: Profile Info vs Dietary & Kitchen Settings -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-10">

        <!-- Left Column: Personal Data, Security & Theme (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Pengaturan Mode Tampilan (Tema Gelap / Terang) -->
            <div class="card p-6 sm:p-7">
                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center shadow-2xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Tema & Mode Tampilan</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Pilih kenyamanan visual untuk aplikasi Anda</p>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2.5">
                    <!-- Option Light -->
                    <button type="button" @click="$store.app.setTheme('light')" 
                        :class="$store.app.theme === 'light' ? 'border-brand-600 bg-brand-50/60 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 ring-2 ring-brand-500/20' : 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                        class="p-3 rounded-2xl border text-center transition cursor-pointer flex flex-col items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9h-1m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                        <span class="text-xs font-bold">Terang</span>
                    </button>

                    <!-- Option Dark -->
                    <button type="button" @click="$store.app.setTheme('dark')" 
                        :class="$store.app.theme === 'dark' ? 'border-brand-600 bg-brand-50/60 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 ring-2 ring-brand-500/20' : 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                        class="p-3 rounded-2xl border text-center transition cursor-pointer flex flex-col items-center gap-2">
                        <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                        <span class="text-xs font-bold">Malam</span>
                    </button>

                    <!-- Option System -->
                    <button type="button" @click="$store.app.setTheme('system')" 
                        :class="$store.app.theme === 'system' ? 'border-brand-600 bg-brand-50/60 dark:bg-brand-950/60 text-brand-700 dark:text-brand-300 ring-2 ring-brand-500/20' : 'border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'"
                        class="p-3 rounded-2xl border text-center transition cursor-pointer flex flex-col items-center gap-2">
                        <svg class="w-5 h-5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span class="text-xs font-bold">Sistem</span>
                    </button>
                </div>
            </div>

            <!-- Edit Data Diri Card -->
            <div class="card p-6 sm:p-7">
                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center shadow-2xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Data diri</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Perbarui nama dan alamat email akun Anda</p>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="name" class="label">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input id="name" name="name" type="text" required value="{{ old('name', $user->name) }}"
                            placeholder="Nama lengkap Anda"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none transition bg-slate-50/50 dark:bg-slate-800 focus:bg-white dark:focus:bg-slate-800">
                        @error('name')
                            <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="label">
                            Alamat Email <span class="text-rose-500">*</span>
                        </label>
                        <input id="email" name="email" type="email" required value="{{ old('email', $user->email) }}"
                            placeholder="nama@email.com"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 focus:outline-none transition bg-slate-50/50 dark:bg-slate-800 focus:bg-white dark:focus:bg-slate-800">
                        @error('email')
                            <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                            class="btn btn-primary w-full">
                            Simpan Perubahan Profil
                        </button>
                    </div>
                </form>
            </div>

            <!-- Ganti Kata Sandi Card -->
            <div class="card p-6 sm:p-7" x-data="{ showPasswords: false }">
                <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center shadow-2xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Keamanan & Sandi</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Ubah kata sandi akun untuk menjaga keamanan</p>
                    </div>
                </div>

                <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="current_password" class="label">
                                Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                            </label>
                            <button type="button" @click="showPasswords = !showPasswords" class="text-xs text-brand-600 dark:text-brand-400 hover:text-brand-800 font-semibold cursor-pointer">
                                <span x-text="showPasswords ? 'Sembunyikan' : 'Tampilkan'"></span>
                            </button>
                        </div>
                        <input id="current_password" name="current_password" :type="showPasswords ? 'text' : 'password'" required
                            placeholder="Kata sandi saat ini"
                            class="input">
                        @error('current_password')
                            <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="label">
                            Kata Sandi Baru <span class="text-rose-500">*</span>
                        </label>
                        <input id="password" name="password" :type="showPasswords ? 'text' : 'password'" required
                            placeholder="Minimal 6 karakter"
                            class="input">
                        @error('password')
                            <p class="input-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="label">
                            Ulangi Kata Sandi Baru <span class="text-rose-500">*</span>
                        </label>
                        <input id="password_confirmation" name="password_confirmation" :type="showPasswords ? 'text' : 'password'" required
                            placeholder="Ulangi kata sandi baru"
                            class="input">
                    </div>

                    <div class="pt-2">
                        <button type="submit" 
                            class="btn btn-primary w-full">
                            Perbarui Kata Sandi
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danger zone: delete account -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-rose-200 dark:border-rose-900/60 p-6 shadow-2xs"
                 x-data="{ open: {{ $errors->has('delete_password') || $errors->has('delete_confirm') ? 'true' : 'false' }} }">
                <h2 class="text-base font-bold text-rose-700 dark:text-rose-400">Hapus Akun</h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Menghapus akun akan menghapus <b>semua</b> resep, foto, stok dapur, dan menu mingguan Anda secara permanen. Tindakan ini tidak bisa dibatalkan.
                </p>
                <button type="button" x-show="!open" @click="open = true"
                        class="mt-4 px-4 py-2 rounded-xl border border-rose-300 dark:border-rose-800 text-rose-700 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-xs font-bold transition cursor-pointer">
                    Saya ingin menghapus akun
                </button>
                <form x-show="open" x-cloak method="POST" action="{{ route('profile.destroy') }}" class="mt-4 space-y-3">
                    @csrf
                    @method('DELETE')
                    <div>
                        <label for="delete_password" class="label">Kata sandi Anda</label>
                        <input id="delete_password" name="delete_password" type="password" required autocomplete="current-password"
                               class="input">
                        @error('delete_password') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="delete_confirm" class="label">Ketik <span class="font-bold text-rose-600">HAPUS</span> untuk konfirmasi</label>
                        <input id="delete_confirm" name="delete_confirm" type="text" required autocomplete="off"
                               class="input">
                        @error('delete_confirm') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn btn-danger btn-sm flex-1">Hapus Akun Permanen</button>
                        <button type="button" @click="open = false" class="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold cursor-pointer">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Column: Kitchen Tools & Dietary Preferences (7 cols) -->
        <div class="lg:col-span-7">
            <div class="card p-6 sm:p-8 h-full flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-5 pb-4 border-b border-slate-100 dark:border-slate-800">
                        <div class="w-9 h-9 rounded-xl bg-brand-50 dark:bg-brand-950/60 text-brand-600 dark:text-brand-400 flex items-center justify-center shadow-2xs">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900 dark:text-white">Dapur & preferensi diet</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Preferensi ini menjadi acuan utama AI dalam meracik resep otomatis</p>
                        </div>
                    </div>

                    <form action="{{ route('preferences.update') }}" method="POST" id="preferences-form">
                        @csrf

                        <!-- Dietary Preferences Section -->
                        <div class="mb-6">
                            <label class="mb-3 block text-sm font-semibold text-slate-900 dark:text-white">
                                <span>Preferensi / pantangan makanan</span>
                            </label>
                            
                            @php
                                $dietOptions = ['Halal', 'Vegetarian', 'Vegan', 'Rendah Gula', 'Rendah Garam', 'Bebas Gluten', 'Bebas Laktosa', 'Tinggi Protein'];
                                $userDiets = $user->dietary_preferences ?? ['Halal'];
                            @endphp

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                                @foreach($dietOptions as $option)
                                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 hover:border-brand-300 dark:hover:border-brand-600 hover:bg-brand-50/40 dark:hover:bg-brand-950/40 cursor-pointer text-xs transition select-none">
                                    <input type="checkbox" name="dietary_preferences[]" value="{{ $option }}" 
                                        {{ in_array($option, $userDiets) ? 'checked' : '' }} 
                                        class="rounded text-brand-600 focus:ring-brand-500">
                                    <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $option }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Cooking Tools Section -->
                        <div class="mb-6">
                            <label class="mb-3 block text-sm font-semibold text-slate-900 dark:text-white">
                                <span>Peralatan masak yang anda miliki</span>
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
                                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 hover:border-brand-300 dark:hover:border-brand-600 hover:bg-brand-50/40 dark:hover:bg-brand-950/40 cursor-pointer text-xs transition select-none">
                                    <input type="checkbox" name="available_tools[]" value="{{ $tool }}" 
                                        {{ in_array($tool, $userTools) ? 'checked' : '' }} 
                                        class="rounded text-brand-600 focus:ring-brand-500">
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
                        class="btn btn-primary btn-sm">
                        Simpan Setelan Dapur
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Recent recipes -->
    <section>
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">Resep terakhir Anda</h2>
                <p class="section-subtitle">Ide masakan yang baru saja Anda buat bersama FoodCycle AI.</p>
            </div>
            <a href="{{ route('recipes.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-800 dark:text-brand-400">Lihat semua &rarr;</a>
        </div>
        @if($recentRecipes->count() > 0)
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($recentRecipes as $recipe)
                    <x-recipe-card :recipe="$recipe" />
                @endforeach
            </div>
        @else
            <x-empty-state title="Belum ada resep" text="Buat resep pertama Anda dari halaman Beranda.">
                <a href="{{ route('dashboard') }}#ai-workspace" class="btn btn-primary">Buat Resep</a>
            </x-empty-state>
        @endif
    </section>
</div>
@endsection
