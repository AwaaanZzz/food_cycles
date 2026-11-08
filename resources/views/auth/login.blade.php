@extends('layouts.app')

@section('title', 'Masuk - FoodCycle')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center py-10 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
    <!-- Ambient glowing accents -->
    <div class="absolute -top-24 -left-24 w-80 h-80 bg-blue-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-orange-500/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full bg-white/95 dark:bg-slate-900/95 backdrop-blur-md rounded-3xl shadow-xl shadow-slate-200/80 dark:shadow-none border border-slate-200/90 dark:border-slate-800 p-8 sm:p-10 relative z-10 transition-colors" x-data="{ showPassword: false }">
        <div class="mb-6 text-center">
            <a href="{{ url('/') }}" class="inline-block group mb-3">
                <img src="{{ asset('images/mascot.png') }}" class="h-16 w-auto mx-auto group-hover:scale-105 transition-transform duration-200" alt="FoodCycle Mascot">
                <div class="flex items-center justify-center gap-1.5 mt-2">
                    <img src="{{ asset('images/brand-text.png') }}" class="h-7 w-auto dark:brightness-0 dark:invert" alt="FoodCycle">
                </div>
            </a>
            <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Masuk ke FoodCycle</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Masukkan email dan kata sandi Anda untuk melanjutkan</p>
        </div>

        <form class="space-y-4" action="{{ route('login') }}" method="POST">
            @csrf

            <!-- Email -->
            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Alamat Email
                </label>
                <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                    placeholder="nama@email.com"
                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition">
                @error('email')
                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label for="password" class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                        Kata Sandi
                    </label>
                    <button type="button" @click="showPassword = !showPassword" class="text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-semibold cursor-pointer">
                        <span x-text="showPassword ? 'Sembunyikan' : 'Tampilkan'"></span>
                    </button>
                </div>
                <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required
                    placeholder="Masukkan kata sandi"
                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition">
                @error('password')
                    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember me -->
            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" class="rounded text-blue-600 focus:ring-blue-500 dark:bg-slate-800 dark:border-slate-700">
                    <span class="text-slate-600 dark:text-slate-400 font-medium">Ingat saya</span>
                </label>
            </div>

            <button type="submit" 
                class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-blue-600 via-[#0B50B5] to-indigo-700 hover:from-blue-700 hover:to-indigo-800 text-white font-bold text-xs sm:text-sm shadow-md shadow-blue-600/25 transition mt-2 cursor-pointer">
                Masuk
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-100 dark:border-slate-800 text-center text-xs text-slate-500 dark:text-slate-400">
            Belum memiliki akun? 
            <a href="{{ route('register') }}" class="font-bold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">Daftar sekarang</a>
        </div>
    </div>
</div>
@endsection
