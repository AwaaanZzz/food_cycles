@extends('layouts.app')

@section('title', 'Masuk - FoodCycle')

@section('content')
<x-auth-layout title="Selamat datang kembali" subtitle="Masuk untuk melanjutkan memasak bersama FoodCycle.">
    <form class="space-y-5" action="{{ route('login') }}" method="POST" x-data="{ show: false }">
        @csrf
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required autofocus value="{{ old('email') }}" placeholder="nama@email.com" class="input">
            @error('email') <p class="input-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="label">Kata sandi</label>
                <a href="{{ route('password.request') }}" class="mb-1.5 text-xs font-semibold text-brand-600 hover:text-brand-800 dark:text-brand-400">Lupa kata sandi?</a>
            </div>
            <div class="relative">
                <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="current-password" placeholder="Masukkan kata sandi" class="input pr-20">
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-3.5 text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white cursor-pointer"
                        :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'">
                    <span x-text="show ? 'Sembunyikan' : 'Tampilkan'"></span>
                </button>
            </div>
            @error('password') <p class="input-error">{{ $message }}</p> @enderror
        </div>

        <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600 dark:text-slate-400">
            <input type="checkbox" name="remember" class="checkbox"> Ingat saya
        </label>

        <button type="submit" class="btn btn-primary w-full py-3">Masuk</button>
    </form>

    <x-slot:footer>
        Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:text-brand-800 dark:text-brand-400">Daftar gratis</a>
    </x-slot:footer>
</x-auth-layout>
@endsection
