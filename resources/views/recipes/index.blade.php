@extends('layouts.app')

@php
    // Shared list view: $mode is 'saved' (favorites) or 'all' (full history)
    $isSaved = $mode === 'saved';
    $listRoute = $isSaved ? 'recipes.saved' : 'recipes.index';
    $filters = [null => 'Semua', 'fcai' => 'Bahan & Peralatan', 'fcbb' => 'Sesuai Anggaran', 'fbpb' => 'Dari Foto'];
    $activeType = in_array(request('type'), ['fcai', 'fcbb', 'fbpb'], true) ? request('type') : null;
@endphp

@section('title', ($isSaved ? 'Resep Tersimpan' : 'Riwayat Resep') . ' - FoodCycle')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

    <x-page-header
        :eyebrow="$isSaved ? 'Koleksi favorit' : 'Semua resep'"
        :title="$isSaved ? 'Resep Tersimpan' : 'Riwayat Resep'"
        :subtitle="$isSaved ? 'Resep yang Anda tandai untuk dimasak lagi.' : 'Semua resep yang pernah dibuat asisten AI untuk Anda, dari yang terbaru.'"
        :icon="$isSaved ? 'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z' : 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'">
        <x-slot:actions>
            <a href="{{ route('dashboard') }}#ai-workspace" class="btn btn-primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Buat Resep
            </a>
        </x-slot:actions>
    </x-page-header>

    <!-- Toolbar: type filter + search -->
    <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <nav class="inline-flex w-full overflow-x-auto rounded-xl bg-slate-100 p-1 dark:bg-slate-800/70 md:w-auto" aria-label="Filter jenis resep">
            @foreach($filters as $type => $label)
                @php $active = $activeType === $type; @endphp
                <a href="{{ route($listRoute, array_filter(['type' => $type, 'q' => request('q')])) }}" @if($active) aria-current="page" @endif
                   class="whitespace-nowrap rounded-lg px-3.5 py-1.5 text-xs font-semibold transition {{ $active ? 'bg-white text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white' : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white' }}">
                    {{ $label }}@if($type === null) <span class="ml-1 text-slate-400">{{ $recipes->total() }}</span>@endif
                </a>
            @endforeach
        </nav>

        <form method="GET" action="{{ route($listRoute) }}" class="flex w-full items-center gap-2 md:w-80" role="search">
            @if($activeType)<input type="hidden" name="type" value="{{ $activeType }}">@endif
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari judul atau bahan..." aria-label="Cari resep" class="input pl-10">
            </div>
            @if(request('q') || $activeType)
                <a href="{{ route($listRoute) }}" class="btn btn-ghost btn-sm">Reset</a>
            @endif
        </form>
    </div>

    @if($recipes->count() > 0)
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($recipes as $recipe)
                <x-recipe-card :recipe="$recipe" :saved-mode="$isSaved" />
            @endforeach
        </div>
        <div class="mt-8">{{ $recipes->links() }}</div>
    @else
        @if(request('q') || $activeType)
            <x-empty-state title="Tidak ada resep yang cocok" text="Coba kata kunci atau filter lain." />
        @else
            <x-empty-state
                :title="$isSaved ? 'Belum ada resep tersimpan' : 'Belum ada riwayat resep'"
                :text="$isSaved ? 'Tekan Simpan pada resep yang Anda suka agar muncul di sini.' : 'Buat resep pertama Anda dari bahan, anggaran, atau foto.'">
                <a href="{{ route('dashboard') }}#ai-workspace" class="btn btn-primary">Buat Resep Pertama</a>
            </x-empty-state>
        @endif
    @endif
</div>
@endsection
