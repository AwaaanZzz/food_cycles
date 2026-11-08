@extends('layouts.app')

@section('title', 'Resep Tersimpan - FoodCycle')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
    
    <!-- Header with Search & Filter -->
    <div class="bg-gradient-to-br from-[#0B50B5] via-[#1055BE] to-[#083882] rounded-3xl p-6 sm:p-8 text-white mb-6 shadow-lg shadow-blue-900/15 relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div class="relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-blue-100 text-[11px] font-bold uppercase tracking-wider mb-2">
                <svg class="w-3.5 h-3.5 text-amber-300" fill="currentColor" viewBox="0 0 20 20"><path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/></svg>
                <span>Koleksi Favorit</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                Resep Tersimpan
            </h1>
            <p class="text-xs sm:text-sm text-blue-100/90 mt-1 max-w-lg leading-relaxed">
                Koleksi ide hidangan dan resep lezat yang telah Anda tandai untuk dimasak kembali.
            </p>
        </div>

        <!-- Search Bar -->
        <form method="GET" action="{{ route('recipes.saved') }}" class="flex items-center gap-2 relative z-10 w-full md:w-auto">
            <div class="relative w-full sm:w-72">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari judul resep atau bahan..."
                    class="rounded-xl border border-white/20 bg-white/95 dark:bg-slate-900/95 dark:border-slate-700 backdrop-blur-md pl-10 pr-3 py-2.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-400 w-full shadow-xs">
            </div>

            @if(request('q') || request('type'))
                <a href="{{ route('recipes.saved') }}" class="px-3 py-2 text-xs text-white/80 hover:text-white font-bold rounded-xl bg-white/10 hover:bg-white/20 transition whitespace-nowrap">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Filter Pills -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
        <a href="{{ route('recipes.saved') }}" 
           class="px-4 py-2 rounded-xl border transition whitespace-nowrap {{ !request('type') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white border-blue-600 font-bold shadow-sm shadow-blue-500/25' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold' }}">
            Semua ({{ $recipes->total() }})
        </a>
        <a href="{{ route('recipes.saved', ['type' => 'fcai']) }}" 
           class="px-4 py-2 rounded-xl border transition whitespace-nowrap {{ request('type') === 'fcai' ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white border-blue-600 font-bold shadow-sm shadow-blue-500/25' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold' }}">
            Bahan & Peralatan
        </a>
        <a href="{{ route('recipes.saved', ['type' => 'fcbb']) }}" 
           class="px-4 py-2 rounded-xl border transition whitespace-nowrap {{ request('type') === 'fcbb' ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-white border-orange-500 font-bold shadow-sm shadow-orange-500/25' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold' }}">
            Sesuai Anggaran
        </a>
        <a href="{{ route('recipes.saved', ['type' => 'fbpb']) }}" 
           class="px-4 py-2 rounded-xl border transition whitespace-nowrap {{ request('type') === 'fbpb' ? 'bg-gradient-to-r from-violet-600 to-indigo-600 text-white border-violet-600 font-bold shadow-sm shadow-violet-500/25' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-200/80 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 font-semibold' }}">
            Dari Foto
        </a>
    </div>

    <!-- Recipes Grid -->
    <div class="mt-6">
        @if($recipes->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($recipes as $recipe)
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs hover:shadow-md hover:border-blue-300 dark:hover:border-blue-500 transition-all duration-200 overflow-hidden flex flex-col justify-between">
                    <div>
                        @if($recipe->image_path)
                            <div class="h-44 w-full bg-slate-100 dark:bg-slate-800 overflow-hidden relative">
                                <img src="{{ $recipe->image_url }}" 
                                     alt="{{ $recipe->title }}" 
                                     class="w-full h-full object-cover"
                                     loading="lazy"
                                     onerror="if (!this.dataset.failed) { this.dataset.failed = '1'; this.src = '{{ $recipe->fallback_image_url }}'; }">
                                <span class="absolute top-2.5 left-2.5 text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md backdrop-blur-xs shadow-2xs border
                                    @if($recipe->type === 'fcai') bg-blue-50/95 dark:bg-blue-950/80 text-blue-800 dark:text-blue-300 border-blue-200 dark:border-blue-800
                                    @elseif($recipe->type === 'fcbb') bg-orange-50/95 dark:bg-orange-950/80 text-orange-800 dark:text-orange-300 border-orange-200 dark:border-orange-800
                                    @else bg-purple-50/95 dark:bg-purple-950/80 text-purple-800 dark:text-purple-300 border-purple-200 dark:border-purple-800 @endif">
                                    {{ $recipe->type_label }}
                                </span>
                            </div>
                        @else
                            <div class="p-5 pb-0 flex items-center justify-between">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-md
                                    @if($recipe->type === 'fcai') bg-blue-50 dark:bg-blue-950/80 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800
                                    @elseif($recipe->type === 'fcbb') bg-orange-50 dark:bg-orange-950/80 text-orange-700 dark:text-orange-300 border border-orange-200 dark:border-orange-800
                                    @else bg-purple-50 dark:bg-purple-950/80 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 @endif">
                                    {{ $recipe->type_label }}
                                </span>
                                <span class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                                    {{ $recipe->created_at->translatedFormat('d M Y') }}
                                </span>
                            </div>
                        @endif

                        <div class="p-5">
                            <h3 class="font-bold text-slate-900 dark:text-white text-base line-clamp-1">
                                <a href="{{ route('recipes.show', $recipe->id) }}" class="hover:text-blue-700 dark:hover:text-blue-400 transition">
                                    {{ $recipe->title }}
                                </a>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-2 leading-relaxed">
                                {{ $recipe->description }}
                            </p>

                            <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                                <span class="text-slate-500 dark:text-slate-400 flex items-center gap-1 font-medium">
                                    <svg class="w-3.5 h-3.5 text-blue-500 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ $recipe->cooking_time_minutes ?? $recipe->cooking_time }}</span>
                                </span>
                                <span class="font-black text-slate-900 dark:text-white">
                                    {{ $recipe->formatted_cost }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="px-5 py-3 border-t border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-850/60 flex items-center justify-between">
                        <form method="POST" action="{{ route('recipes.toggle-save', $recipe->id) }}">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 flex items-center gap-1 cursor-pointer transition">
                                <span>Hapus</span>
                            </button>
                        </form>

                        <a href="{{ route('recipes.show', $recipe->id) }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 flex items-center gap-1">
                            <span>Lihat Detail</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $recipes->links() }}
            </div>
        @else
            <!-- Clean Empty State with Brand Mascot -->
            <div class="bg-gradient-to-b from-blue-50/70 via-white to-slate-50 dark:from-slate-900 dark:via-slate-850 dark:to-slate-900 rounded-3xl border border-blue-200/70 dark:border-slate-800 p-10 text-center max-w-md mx-auto shadow-sm">
                <img src="{{ asset('images/mascot.png') }}" class="w-20 h-auto mx-auto mb-3" alt="FoodCycle Mascot">
                <h3 class="font-black text-slate-900 dark:text-white text-base">Belum Ada Resep Tersimpan</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Resep yang Anda simpan dari halaman utama akan ditampilkan di sini.
                </p>
                <div class="mt-5">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs transition shadow-md shadow-blue-600/20">
                        Kembali ke Beranda
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
