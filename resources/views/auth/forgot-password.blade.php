@extends('layouts.app')

@section('title', 'Lupa Kata Sandi - FoodCycle')

@section('content')
<x-auth-layout title="Lupa kata sandi?" subtitle="Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.">
    <form class="space-y-5" action="{{ route('password.email') }}" method="POST">
        @csrf
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" name="email" type="email" autocomplete="email" required autofocus value="{{ old('email') }}" placeholder="nama@email.com" class="input">
            @error('email') <p class="input-error">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="btn btn-primary w-full py-3">Kirim Tautan Reset</button>
    </form>

    <x-slot:footer>
        Sudah ingat? <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-800 dark:text-brand-400">Kembali masuk</a>
    </x-slot:footer>
</x-auth-layout>
@endsection
