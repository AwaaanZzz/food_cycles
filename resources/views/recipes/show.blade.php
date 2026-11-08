@extends('layouts.app')

@section('title', $recipe->title . ' - FoodCycle')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8" 
     x-data="{ 
        checkedIngredients: [], 
        checkedSteps: [],
        isSaved: {{ $recipe->is_saved ? 'true' : 'false' }},
        copySuccess: false,
        async toggleSave() {
            try {
                const response = await fetch('{{ route('recipes.toggle-save', $recipe->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    this.isSaved = data.is_saved;
                }
            } catch (e) {
                document.getElementById('fallback-save-form').submit();
            }
        },
        shareRecipe() {
            navigator.clipboard.writeText(window.location.href);
            this.copySuccess = true;
            setTimeout(() => this.copySuccess = false, 3000);
        }
     }">

    <!-- Top Navigation & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-white transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Beranda</span>
        </a>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2">
            <!-- Share -->
            <button @click="shareRecipe()" type="button" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                <span x-text="copySuccess ? 'Tautan Disalin' : 'Bagikan'"></span>
            </button>

            <!-- Print -->
            <button onclick="window.print()" type="button" class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak</span>
            </button>

            <!-- Save / Favorite -->
            <button @click="toggleSave()" type="button" 
                :class="isSaved ? 'bg-rose-50 dark:bg-rose-950/60 border-rose-200 dark:border-rose-900/60 text-rose-600 dark:text-rose-400' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800'"
                class="px-3.5 py-1.5 rounded-lg border text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer">
                <svg class="w-3.5 h-3.5" :class="isSaved ? 'text-rose-500 fill-current' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <span x-text="isSaved ? 'Tersimpan' : 'Simpan'"></span>
            </button>

            <form id="fallback-save-form" method="POST" action="{{ route('recipes.toggle-save', $recipe->id) }}" class="hidden">
                @csrf
            </form>

            <!-- Delete -->
            <form method="POST" action="{{ route('recipes.destroy', $recipe->id) }}" onsubmit="return confirm('Hapus resep ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition cursor-pointer" title="Hapus Resep" aria-label="Hapus resep">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Recipe Header Card -->
    <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/90 dark:border-slate-800 overflow-hidden mb-6 shadow-sm transition-colors duration-150">
        
        <!-- Recipe Photo (if present) -->
        @if($recipe->image_path)
        <div class="w-full h-72 sm:h-80 bg-slate-100 dark:bg-slate-800 overflow-hidden relative">
            <img src="{{ $recipe->image_url }}" 
                 alt="{{ $recipe->title }}" 
                 class="w-full h-full object-cover object-center"
                 loading="eager"
                 onerror="if (!this.dataset.failed) { this.dataset.failed = '1'; this.src = '{{ $recipe->fallback_image_url }}'; }">
        </div>
        @endif

        <div class="p-6 sm:p-8">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-lg 
                    @if($recipe->type === 'fcai') bg-blue-50 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800
                    @elseif($recipe->type === 'fcbb') bg-orange-50 dark:bg-orange-950/70 text-orange-700 dark:text-orange-300 border border-orange-200 dark:border-orange-800
                    @else bg-purple-50 dark:bg-purple-950/70 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 @endif">
                    {{ $recipe->type_label }}
                </span>
                <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">
                    {{ $recipe->created_at->translatedFormat('d F Y, H:i') }}
                </span>
            </div>

            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white tracking-tight leading-snug">
                {{ $recipe->title }}
            </h1>
            <p class="mt-2 text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                {{ $recipe->description }}
            </p>

            <!-- 3 Key Metric Cards -->
            <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-800 grid grid-cols-3 gap-3 text-center">
                <div class="p-3.5 bg-blue-50/80 dark:bg-blue-950/40 rounded-2xl border border-blue-200/70 dark:border-blue-900/60 shadow-2xs">
                    <div class="flex items-center justify-center text-blue-600 dark:text-blue-400 mb-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="text-[11px] text-blue-700 dark:text-blue-300 font-bold uppercase tracking-wider">Waktu</div>
                    <div class="text-xs sm:text-sm font-black text-slate-900 dark:text-white mt-0.5">{{ $recipe->cooking_time_minutes ?? $recipe->cooking_time }}</div>
                </div>
                <div class="p-3.5 bg-amber-50/80 dark:bg-amber-950/40 rounded-2xl border border-amber-200/70 dark:border-amber-900/60 shadow-2xs">
                    <div class="flex items-center justify-center text-amber-600 dark:text-amber-400 mb-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div class="text-[11px] text-amber-700 dark:text-amber-300 font-bold uppercase tracking-wider">Porsi</div>
                    <div class="text-xs sm:text-sm font-black text-slate-900 dark:text-white mt-0.5">{{ $recipe->servings }}</div>
                </div>
                <div class="p-3.5 bg-emerald-50/80 dark:bg-emerald-950/40 rounded-2xl border border-emerald-200/70 dark:border-emerald-900/60 shadow-2xs">
                    <div class="flex items-center justify-center text-emerald-600 dark:text-emerald-400 mb-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="text-[11px] text-emerald-700 dark:text-emerald-300 font-bold uppercase tracking-wider">Estimasi Biaya</div>
                    <div class="text-xs sm:text-sm font-black text-slate-900 dark:text-white mt-0.5">{{ $recipe->formatted_cost }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Nutrition Information -->
    @php
        $nutrition = $recipe->nutrition_info ?? $recipe->nutrition ?? [];
    @endphp
    @if(!empty($nutrition))
    <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-xs mb-6 transition-colors duration-150">
        <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-3 flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
            <span>Informasi Nilai Gizi per Porsi</span>
        </h3>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
            <div class="p-3.5 bg-gradient-to-b from-orange-50 to-amber-50/30 dark:from-orange-950/40 dark:to-amber-950/20 border border-orange-200/70 dark:border-orange-900/60 rounded-2xl shadow-2xs">
                <div class="text-[11px] text-orange-700 dark:text-orange-300 font-bold uppercase tracking-wider">Kalori</div>
                <div class="text-base font-black text-slate-900 dark:text-white mt-0.5">{{ $nutrition['calories'] ?? '-' }}</div>
            </div>
            <div class="p-3.5 bg-gradient-to-b from-blue-50 to-indigo-50/30 dark:from-blue-950/40 dark:to-indigo-950/20 border border-blue-200/70 dark:border-blue-900/60 rounded-2xl shadow-2xs">
                <div class="text-[11px] text-blue-700 dark:text-blue-300 font-bold uppercase tracking-wider">Protein</div>
                <div class="text-base font-black text-slate-900 dark:text-white mt-0.5">{{ $nutrition['protein'] ?? '-' }}</div>
            </div>
            <div class="p-3.5 bg-gradient-to-b from-emerald-50 to-teal-50/30 dark:from-emerald-950/40 dark:to-teal-950/20 border border-emerald-200/70 dark:border-emerald-900/60 rounded-2xl shadow-2xs">
                <div class="text-[11px] text-emerald-700 dark:text-emerald-300 font-bold uppercase tracking-wider">Karbohidrat</div>
                <div class="text-base font-black text-slate-900 dark:text-white mt-0.5">{{ $nutrition['carbs'] ?? '-' }}</div>
            </div>
            <div class="p-3.5 bg-gradient-to-b from-rose-50 to-pink-50/30 dark:from-rose-950/40 dark:to-pink-950/20 border border-rose-200/70 dark:border-rose-900/60 rounded-2xl shadow-2xs">
                <div class="text-[11px] text-rose-700 dark:text-rose-300 font-bold uppercase tracking-wider">Lemak</div>
                <div class="text-base font-black text-slate-900 dark:text-white mt-0.5">{{ $nutrition['fat'] ?? '-' }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Two-column Layout: Ingredients & Tools vs Steps -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left: Tools & Ingredients (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Cooking Tools -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 sm:p-6 border border-slate-200/90 dark:border-slate-800 shadow-2xs transition-colors duration-150">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-3 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    <span>Peralatan yang Digunakan</span>
                </h3>
                <div class="flex flex-wrap gap-2">
                    @forelse($recipe->tools ?? $recipe->cooking_tools ?? [] as $tool)
                        <span class="px-3 py-1.5 bg-blue-50/70 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300 rounded-xl text-xs font-semibold border border-blue-200/60 dark:border-blue-800 shadow-2xs">
                            {{ $tool }}
                        </span>
                    @empty
                        <span class="text-xs text-slate-400 dark:text-slate-500">Peralatan umum dapur</span>
                    @endforelse
                </div>
            </div>

            <!-- Ingredients & Estimated Cost -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 sm:p-6 border border-slate-200/90 dark:border-slate-800 shadow-2xs transition-colors duration-150">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                            <span>Bahan Masakan</span>
                        </h3>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Tandai bahan yang sudah tersedia</p>
                    </div>
                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/80 dark:border-emerald-800 px-2.5 py-1 rounded-lg">
                        {{ count($recipe->ingredients ?? []) }} Item
                    </span>
                </div>

                <div class="space-y-2">
                    @foreach($recipe->ingredients ?? [] as $index => $item)
                    @php
                        $itemName = is_array($item) ? ($item['name'] ?? 'Bahan') : $item;
                        $amount = is_array($item) ? ($item['amount'] ?? '') : '';
                        $price = is_array($item) && isset($item['estimated_price']) ? (int) $item['estimated_price'] : 0;
                    @endphp
                    <label class="flex items-center justify-between p-2.5 rounded-xl border border-slate-100 dark:border-slate-800/80 hover:border-slate-200 dark:hover:border-slate-700 hover:bg-slate-50/70 dark:hover:bg-slate-800/50 cursor-pointer transition select-none">
                        <div class="flex items-center gap-2.5">
                            <input type="checkbox" value="{{ $index }}" x-model="checkedIngredients"
                                class="h-4 w-4 rounded border-slate-300 dark:border-slate-600 text-blue-600 focus:ring-blue-500">
                            <div>
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200" :class="checkedIngredients.includes('{{ $index }}') ? 'line-through text-slate-400 dark:text-slate-600' : ''">
                                    {{ $itemName }}
                                </span>
                                @if($amount)
                                    <span class="text-[11px] text-slate-400 dark:text-slate-500 block">{{ $amount }}</span>
                                @endif
                            </div>
                        </div>

                        @if($price > 0)
                            <span class="text-xs font-semibold text-slate-700 dark:text-slate-300 shrink-0">
                                Rp {{ number_format($price, 0, ',', '.') }}
                            </span>
                        @endif
                    </label>
                    @endforeach
                </div>

                <!-- Total Cost Summary -->
                <div class="mt-4 p-3 rounded-xl bg-gradient-to-r from-emerald-50/80 via-teal-50/50 to-blue-50/30 dark:from-emerald-950/40 dark:via-teal-950/30 dark:to-blue-950/30 border border-emerald-200/80 dark:border-emerald-900/60 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Total Estimasi Belanja:</span>
                    <span class="text-xs font-black text-emerald-700 dark:text-emerald-400">{{ $recipe->formatted_cost }}</span>
                </div>
            </div>
        </div>

        <!-- Right: Cooking Steps & Tips (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Interactive Cooking Steps -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 sm:p-6 border border-slate-200/90 dark:border-slate-800 shadow-2xs transition-colors duration-150">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#E65419]"></span>
                            <span>Langkah Memasak</span>
                        </h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Klik langkah untuk menandai selesai</p>
                    </div>
                    <span class="text-xs font-bold text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-950/60 border border-blue-200/80 dark:border-blue-800 px-2.5 py-1 rounded-lg">
                        <span x-text="checkedSteps.length"></span>/{{ count($recipe->steps ?? []) }} Selesai
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($recipe->steps ?? [] as $index => $step)
                    <div class="flex items-start gap-3 p-3.5 rounded-2xl border transition cursor-pointer select-none"
                         :class="checkedSteps.includes('{{ $index }}') ? 'bg-slate-50/80 dark:bg-slate-800/40 border-slate-200 dark:border-slate-800 opacity-60' : 'bg-white dark:bg-slate-800/60 border-slate-200/80 dark:border-slate-700/70 hover:border-blue-300 dark:hover:border-blue-500 hover:shadow-2xs'"
                         @click="checkedSteps.includes('{{ $index }}') ? checkedSteps = checkedSteps.filter(i => i !== '{{ $index }}') : checkedSteps.push('{{ $index }}')">
                        
                        <!-- Step Indicator -->
                        <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0 transition"
                             :class="checkedSteps.includes('{{ $index }}') ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-2xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200'">
                            <template x-if="checkedSteps.includes('{{ $index }}')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="!checkedSteps.includes('{{ $index }}')">
                                <span>{{ $index + 1 }}</span>
                            </template>
                        </div>

                        <!-- Step Text -->
                        <div class="flex-grow pt-0.5">
                            <p class="text-xs sm:text-sm text-slate-800 dark:text-slate-200 leading-relaxed" :class="checkedSteps.includes('{{ $index }}') ? 'line-through text-slate-400 dark:text-slate-600' : ''">
                                {{ $step }}
                            </p>
                        </div>
                    </div>
                    @empty
                        <p class="text-xs text-slate-400 dark:text-slate-500">Tidak ada instruksi langkah memasak.</p>
                    @endforelse
                </div>
            </div>

            <!-- Tips Card -->
            @if(!empty($recipe->tips))
            <div class="bg-gradient-to-br from-amber-50 via-orange-50/50 to-amber-100/40 dark:from-amber-950/40 dark:via-orange-950/30 dark:to-amber-900/20 rounded-3xl p-5 sm:p-6 border border-amber-200/90 dark:border-amber-900/60 shadow-2xs text-amber-950 dark:text-amber-200 transition-colors duration-150">
                <h4 class="text-xs font-black uppercase tracking-wider text-amber-900 dark:text-amber-300 mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    <span>Tips Memasak Chef</span>
                </h4>
                <p class="text-xs sm:text-sm text-amber-900 dark:text-amber-200 leading-relaxed font-medium">
                    {{ $recipe->tips }}
                </p>
            </div>
            @endif

            <!-- Disclaimer Card -->
            <div class="p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 text-slate-500 dark:text-slate-400 text-[11px] leading-relaxed bg-slate-50/70 dark:bg-slate-900 transition-colors duration-150">
                <strong class="text-slate-700 dark:text-slate-300">Disclaimer FoodCycle:</strong>
                {{ $recipe->disclaimer ?? 'Informasi estimasi harga dan nilai gizi merupakan perkiraan rata-rata dan dapat bervariasi tergantung lokasi pasar serta ketersediaan bahan.' }}
            </div>
        </div>
    </div>
</div>
@endsection
