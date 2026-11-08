@extends('layouts.app')

@section('title', 'Daftar - FoodCycle')

@section('content')
<x-auth-layout title="Buat akun FoodCycle" subtitle="Gratis. Mulai susun resep dari bahan yang Anda punya." :wide="true">
    <form class="space-y-5" action="{{ route('register') }}" method="POST" x-data="{ show: false }">
        @csrf
        <div>
            <label for="name" class="label">Nama lengkap</label>
            <input id="name" name="name" type="text" autocomplete="name" required autofocus value="{{ old('name') }}" placeholder="Nama Anda" class="input">
            @error('name') <p class="input-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}" placeholder="nama@email.com" class="input">
            @error('email') <p class="input-error">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="password" class="label">Kata sandi</label>
                <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="new-password" placeholder="Minimal 6 karakter" class="input">
                @error('password') <p class="input-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="password_confirmation" class="label">Ulangi kata sandi</label>
                <input id="password_confirmation" name="password_confirmation" :type="show ? 'text' : 'password'" required autocomplete="new-password" placeholder="Ulangi kata sandi" class="input">
            </div>
        </div>
        <label class="-mt-2 flex cursor-pointer items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            <input type="checkbox" class="checkbox" x-model="show"> Tampilkan kata sandi
        </label>

        <fieldset>
            <legend class="label">Preferensi diet <span class="font-normal text-slate-400">(bisa diubah nanti)</span></legend>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                @foreach(['Halal', 'Vegetarian', 'Rendah Gula', 'Tinggi Protein'] as $diet)
                    <label class="flex cursor-pointer select-none items-center gap-2 rounded-xl border border-slate-200 px-3 py-2.5 text-sm transition hover:border-slate-300 has-[:checked]:border-brand-400 has-[:checked]:bg-brand-50/60 dark:border-slate-700 dark:has-[:checked]:border-brand-600 dark:has-[:checked]:bg-brand-950/40">
                        <input type="checkbox" name="dietary_preferences[]" value="{{ $diet }}" @checked(in_array($diet, old('dietary_preferences', ['Halal']))) class="checkbox">
                        <span class="font-medium text-slate-700 dark:text-slate-300">{{ $diet }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <button type="submit" class="btn btn-primary w-full py-3">Buat Akun</button>
    </form>

    <x-slot:footer>
        Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-800 dark:text-brand-400">Masuk</a>
    </x-slot:footer>
</x-auth-layout>
@endsection
