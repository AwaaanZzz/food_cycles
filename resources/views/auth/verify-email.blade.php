@extends('layouts.app')

@section('title', 'Verifikasi Email - FoodCycle')

@section('content')
<x-auth-layout title="Verifikasi email Anda">
    <div class="card p-6">
        <p class="text-sm leading-relaxed text-slate-600 dark:text-slate-300">
            Kami sudah mengirim tautan verifikasi ke <span class="font-semibold text-slate-900 dark:text-white">{{ auth()->user()->email }}</span>.
            Klik tautan tersebut agar akun Anda bisa dipulihkan jika lupa kata sandi.
        </p>
        <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
            @csrf
            <button type="submit" class="btn btn-primary w-full">Kirim Ulang Tautan</button>
        </form>
    </div>

    <x-slot:footer>
        <a href="{{ route('dashboard') }}" class="font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white">Nanti saja, lanjut ke Beranda &rarr;</a>
    </x-slot:footer>
</x-auth-layout>
@endsection
