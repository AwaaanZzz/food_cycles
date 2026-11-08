@extends('layouts.app')

@section('title', $recipe->title . ' - FoodCycle')
@section('meta_title', $recipe->title)
@section('meta_description', \Illuminate\Support\Str::limit(trim($recipe->description . ' Estimasi biaya ' . $recipe->formatted_cost . ', ' . $recipe->cooking_time . '.'), 180))
@section('og_type', 'article')
@if(($public ?? false) && $recipe->image_path)
    @section('meta_image', route('recipes.public-photo', $recipe->share_token))
@endif
@unless($public ?? false)
    @push('head')<meta name="robots" content="noindex">@endpush
@endunless

@section('content')
<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8" 
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
        shareUrl: @js($recipe->share_token ? route('recipes.public', $recipe->share_token) : null),
        isPublicView: @js($public ?? false),
        async shareRecipe() {
            let url = this.shareUrl;
            if (!this.isPublicView) {
                const res = await fetch('{{ route('recipes.share', $recipe->id) }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ action: 'enable' })
                });
                url = this.shareUrl = (await res.json()).url;
            } else {
                url = window.location.href;
            }
            // Native share sheet on phones (WhatsApp etc.), otherwise copy the link
            if (navigator.share) {
                try { await navigator.share({ title: @js($recipe->title), text: 'Coba resep ini dari FoodCycle!', url }); return; } catch (e) { if (e.name === 'AbortError') return; }
            }
            try { await navigator.clipboard.writeText(url); } catch (e) { window.prompt('Salin tautan ini:', url); }
            this.copySuccess = true;
            setTimeout(() => this.copySuccess = false, 3000);
        },
        async stopSharing() {
            if (!confirm('Nonaktifkan tautan publik? Orang yang sudah menerima tautan tidak bisa membukanya lagi.')) return;
            await fetch('{{ route('recipes.share', $recipe->id) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ action: 'disable' })
            });
            this.shareUrl = null;
        }
     }">

    <!-- Top Navigation & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        @if($public ?? false)
        <div class="text-xs text-slate-500 dark:text-slate-400">
            Resep ini dibagikan dari <span class="font-bold text-slate-700 dark:text-slate-200">FoodCycle AI</span>.
            <a href="{{ route('register') }}" class="font-bold text-brand-600 dark:text-brand-400 hover:underline">Buat resepmu sendiri &rarr;</a>
        </div>
        @else
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            <span>Kembali ke Beranda</span>
        </a>
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <!-- Share -->
            <button @click="shareRecipe()" type="button" class="btn btn-secondary btn-sm">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                <span x-text="copySuccess ? 'Tautan Disalin' : 'Bagikan'"></span>
            </button>

            @unless($public ?? false)
            <!-- Stop sharing (only while a public link exists) -->
            <button x-show="shareUrl" x-cloak @click="stopSharing()" type="button"
                    class="btn btn-ghost btn-sm hover:!text-rose-600"
                    title="Matikan tautan publik resep ini">
                Nonaktifkan link
            </button>

            <!-- Regenerate: same input, a different recipe -->
            @if(!empty($recipe->source_input) && in_array($recipe->type, ['fcai', 'fcbb', 'fbpb']))
            <form method="POST" action="{{ route('recipes.regenerate', $recipe->id) }}" x-data="{ busy: false }" @submit="busy = true">
                @csrf
                <button type="submit" :disabled="busy"
                        class="btn btn-secondary btn-sm"
                        title="Buat resep lain dengan bahan/anggaran/foto yang sama">
                    <svg class="w-3.5 h-3.5" :class="busy && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span x-text="busy ? 'Memasak ulang...' : 'Buat Ulang'"></span>
                </button>
            </form>
            @endif
            @endunless

            <!-- Print -->
            <button onclick="window.print()" type="button" class="btn btn-secondary btn-sm">
                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak</span>
            </button>

            @unless($public ?? false)
            <!-- Save / Favorite -->
            <button @click="toggleSave()" type="button"
                :class="isSaved ? '!border-tomato-200 !bg-tomato-50 !text-tomato-700 dark:!border-tomato-900 dark:!bg-tomato-950/40 dark:!text-tomato-300' : ''"
                class="btn btn-secondary btn-sm">
                <svg class="w-3.5 h-3.5" :class="isSaved ? 'fill-current' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                <button type="submit" class="btn-icon h-8 w-8 hover:!bg-rose-50 hover:!text-rose-600 dark:hover:!bg-rose-950/40" title="Hapus Resep" aria-label="Hapus resep">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </form>
            @endunless
        </div>
    </div>

    <!-- Recipe summary -->
    @php
        $nutrition = $recipe->nutrition_info ?? $recipe->nutrition ?? [];
        $facts = [
            ['Waktu', $recipe->cooking_time, 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Porsi', $recipe->servings, 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['Kesulitan', $recipe->difficulty, 'M13 10V3L4 14h7v7l9-11h-7z'],
            ['Estimasi biaya', $recipe->formatted_cost, 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ];
        $nutriLabels = ['calories' => 'Kalori', 'protein' => 'Protein', 'carbs' => 'Karbohidrat', 'fat' => 'Lemak'];
    @endphp
    <article class="card mb-6 overflow-hidden">
        @if($recipe->image_path)
            <div class="aspect-[21/9] max-h-96 w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
                <img src="{{ ($public ?? false) ? route('recipes.public-photo', $recipe->share_token) : $recipe->image_url }}"
                     alt="{{ $recipe->title }}" class="h-full w-full object-cover" loading="eager">
            </div>
        @endif

        <div class="p-6 sm:p-8">
            <div class="flex flex-wrap items-center gap-2.5">
                <x-type-badge :type="$recipe->type" />
                <time class="text-xs text-slate-400 dark:text-slate-500" datetime="{{ $recipe->created_at->toIso8601String() }}">{{ $recipe->created_at->translatedFormat('d F Y, H:i') }}</time>
                @if(!($public ?? false) && $recipe->rating)
                    <span class="text-xs font-semibold text-amber-500">★ {{ $recipe->rating }}/5</span>
                @endif
            </div>
            <h1 class="mt-3 text-2xl font-bold leading-tight tracking-tight text-slate-900 sm:text-3xl dark:text-white">{{ $recipe->title }}</h1>
            @if($recipe->description)
                <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600 sm:text-base dark:text-slate-300">{{ $recipe->description }}</p>
            @endif

            <dl class="mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-xl bg-slate-200/70 ring-1 ring-slate-200/70 sm:grid-cols-4 dark:bg-slate-800 dark:ring-slate-800">
                @foreach($facts as [$label, $value, $icon])
                    <div class="bg-white p-4 dark:bg-slate-900">
                        <dt class="flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                            {{ $label }}
                        </dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900 sm:text-base dark:text-white">{{ $value ?: '-' }}</dd>
                    </div>
                @endforeach
            </dl>

            @if(!empty($nutrition))
                <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 rounded-xl bg-slate-50 px-4 py-3 text-sm dark:bg-slate-800/50">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">Gizi per porsi</span>
                    @foreach($nutriLabels as $key => $label)
                        <span class="text-slate-500 dark:text-slate-400">{{ $label }} <span class="font-semibold text-slate-900 dark:text-white">{{ $nutrition[$key] ?? '-' }}</span></span>
                    @endforeach
                </div>
            @endif
        </div>
    </article>

    <!-- Two-column Layout: Ingredients & Tools vs Steps -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left: Tools & Ingredients (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Cooking Tools -->
            <div class="card p-6">
                <h2 class="section-title">Peralatan yang Digunakan</h2>
                <div class="flex flex-wrap gap-2">
                    @forelse($recipe->tools ?? $recipe->cooking_tools ?? [] as $tool)
                        <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            {{ $tool }}
                        </span>
                    @empty
                        <span class="text-xs text-slate-400 dark:text-slate-500">Peralatan umum dapur</span>
                    @endforelse
                </div>
            </div>

            <!-- Ingredients & Estimated Cost -->
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="section-title">Bahan Masakan</h2>
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
                                class="checkbox">
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
                <div class="mt-4 flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3 dark:bg-slate-800/50">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Total Estimasi Belanja:</span>
                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">{{ $recipe->formatted_cost }}</span>
                </div>

                <!-- Shopping list: every ingredient NOT ticked as already available -->
                @php
                    $shoppingItems = collect($recipe->ingredients ?? [])->map(fn ($i) => [
                        'name' => is_array($i) ? ($i['name'] ?? '') : (string) $i,
                        'amount' => is_array($i) ? ($i['amount'] ?? '') : '',
                        'price' => is_array($i) ? (int) ($i['estimated_price'] ?? 0) : 0,
                    ])->values();
                @endphp
                <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-800"
                     x-data="{
                        items: @js($shoppingItems),
                        copied: false,
                        get toBuy() { return this.items.filter((it, i) => !checkedIngredients.includes(String(i))); },
                        get text() {
                            const total = this.toBuy.reduce((s, it) => s + it.price, 0);
                            const lines = this.toBuy.map(it => '☐ ' + it.name + (it.amount ? ' (' + it.amount + ')' : '') + (it.price ? ' - Rp ' + it.price.toLocaleString('id-ID') : ''));
                            return '🛒 Daftar belanja: ' + @js($recipe->title) + '\n\n' + lines.join('\n') + (total ? '\n\nPerkiraan total: Rp ' + total.toLocaleString('id-ID') : '') + '\n\n— dari FoodCycle AI';
                        },
                        async copy() {
                            try { await navigator.clipboard.writeText(this.text); this.copied = true; setTimeout(() => this.copied = false, 2500); }
                            catch (e) { window.prompt('Salin daftar belanja ini:', this.text); }
                        }
                     }">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Daftar belanja</h3>
                        <span class="text-[11px] text-slate-400 dark:text-slate-500" x-text="toBuy.length + ' bahan perlu dibeli'"></span>
                    </div>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Bahan yang sudah dicentang di atas tidak ikut masuk daftar.</p>
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <button type="button" @click="copy()" :disabled="toBuy.length === 0"
                                class="py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-text="copied ? 'Tersalin ✓' : 'Salin Daftar'"></span>
                        </button>
                        <a :href="'https://wa.me/?text=' + encodeURIComponent(text)" target="_blank" rel="noopener"
                           :class="toBuy.length === 0 && 'pointer-events-none opacity-50'"
                           class="py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold text-center transition">
                            Kirim ke WhatsApp
                        </a>
                    </div>
                </div>
            </div>

            @unless($public ?? false)
            <!-- Personal rating & notes (private, only the owner sees them) -->
            <form method="POST" action="{{ route('recipes.review', $recipe->id) }}"
                  class="card p-6"
                  x-data="{ rating: {{ (int) $recipe->rating }}, hover: 0 }">
                @csrf
                <h2 class="section-title">Catatan pribadi</h2>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Hanya terlihat oleh Anda. Contoh: "kurangi garam", "anak-anak suka".</p>

                <div class="mt-3 flex items-center gap-1" role="radiogroup" aria-label="Rating resep">
                    <input type="hidden" name="rating" :value="rating">
                    @for($star = 1; $star <= 5; $star++)
                    <button type="button" @click="rating = (rating === {{ $star }} ? 0 : {{ $star }})" @mouseenter="hover = {{ $star }}" @mouseleave="hover = 0"
                            role="radio" :aria-checked="rating === {{ $star }}" aria-label="{{ $star }} bintang"
                            class="text-2xl leading-none cursor-pointer transition-transform hover:scale-110"
                            :class="(hover || rating) >= {{ $star }} ? 'text-amber-400' : 'text-slate-300 dark:text-slate-700'">★</button>
                    @endfor
                    <span class="ml-2 text-xs text-slate-500 dark:text-slate-400" x-text="rating ? rating + '/5' : 'Belum dinilai'"></span>
                </div>

                <textarea name="user_notes" rows="3" maxlength="1000" placeholder="Tulis catatan Anda untuk resep ini..."
                          class="input mt-3">{{ old('user_notes', $recipe->user_notes) }}</textarea>
                <button type="submit" class="btn btn-primary mt-2 w-full">
                    Simpan Catatan
                </button>
            </form>
            @endunless
        </div>

        <!-- Right: Cooking Steps & Tips (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Interactive Cooking Steps -->
            <div class="card p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="section-title">Langkah memasak</h2>
                        <p class="section-subtitle">Ketuk langkah untuk menandai selesai.</p>
                    </div>
                    <span class="badge bg-brand-50 px-2.5 py-1 text-xs text-brand-700 ring-1 ring-inset ring-brand-200 dark:bg-brand-950/60 dark:text-brand-300 dark:ring-brand-800">
                        <span x-text="checkedSteps.length"></span>/{{ count($recipe->steps ?? []) }} Selesai
                    </span>
                </div>

                <ol class="space-y-2.5">
                    @forelse($recipe->steps ?? [] as $index => $step)
                    <li class="flex cursor-pointer select-none items-start gap-3.5 rounded-xl border p-4 transition"
                         :class="checkedSteps.includes('{{ $index }}') ? 'border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/40' : 'border-slate-200 hover:border-brand-300 dark:border-slate-800 dark:hover:border-brand-700'"
                         @click="checkedSteps.includes('{{ $index }}') ? checkedSteps = checkedSteps.filter(i => i !== '{{ $index }}') : checkedSteps.push('{{ $index }}')">
                        
                        <!-- Step Indicator -->
                        <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs shrink-0 transition"
                             :class="checkedSteps.includes('{{ $index }}') ? 'bg-brand-600 text-white shadow-2xs' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200'">
                            <template x-if="checkedSteps.includes('{{ $index }}')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="!checkedSteps.includes('{{ $index }}')">
                                <span>{{ $index + 1 }}</span>
                            </template>
                        </div>

                        <!-- Step Text -->
                        <div class="flex-grow pt-0.5">
                            <p class="text-sm leading-relaxed text-slate-700 dark:text-slate-200" :class="checkedSteps.includes('{{ $index }}') && '!text-slate-400 line-through dark:!text-slate-600'">
                                {{ $step }}
                            </p>
                        </div>
                    </li>
                    @empty
                        <li class="text-sm text-slate-400 dark:text-slate-500">Tidak ada instruksi langkah memasak.</li>
                    @endforelse
                </ol>
            </div>

            <!-- Tips Card -->
            @if(!empty($recipe->tips))
            <div class="rounded-2xl bg-amber-50 p-6 ring-1 ring-inset ring-amber-200 dark:bg-amber-950/30 dark:ring-amber-900/60">
                <h2 class="mb-2 flex items-center gap-2 text-sm font-semibold text-amber-900 dark:text-amber-200">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    Tips dari chef
                </h2>
                <p class="text-sm leading-relaxed text-amber-900/90 dark:text-amber-100/90">
                    {{ $recipe->tips }}
                </p>
            </div>
            @endif

            <!-- Disclaimer Card -->
            <div class="px-1 text-xs leading-relaxed text-slate-400 dark:text-slate-500">
                                {{ $recipe->disclaimer ?? 'Informasi estimasi harga dan nilai gizi merupakan perkiraan rata-rata dan dapat bervariasi tergantung lokasi pasar serta ketersediaan bahan.' }}
            </div>
        </div>
    </div>
</div>
@endsection
