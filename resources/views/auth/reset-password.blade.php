@extends('layouts.app')

@section('title', 'Atur Ulang Kata Sandi - FoodCycle')

@section('content')
<x-auth-layout title="Buat kata sandi baru" subtitle="Minimal 8 karakter. Gunakan kombinasi yang mudah Anda ingat.">
    <form class="space-y-5" action="{{ route('password.update') }}" method="POST" x-data="{ show: false }">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email', $email) }}" class="input">
            @error('email') <p class="input-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="label">Kata sandi baru</label>
            <input id="password" name="password" :type="show ? 'text' : 'password'" required autocomplete="new-password" autofocus placeholder="Minimal 8 karakter" class="input">
            @error('password') <p class="input-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="label">Ulangi kata sandi baru</label>
            <input id="password_confirmation" name="password_confirmation" :type="show ? 'text' : 'password'" required autocomplete="new-password" placeholder="Ulangi kata sandi" class="input">
        </div>
        <label class="flex cursor-pointer items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
            <input type="checkbox" class="checkbox" x-model="show"> Tampilkan kata sandi
        </label>

        <button type="submit" class="btn btn-primary w-full py-3">Simpan Kata Sandi Baru</button>
    </form>
</x-auth-layout>
@endsection
