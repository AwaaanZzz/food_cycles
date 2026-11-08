@extends('layouts.app')

@section('title', $plan->title . ' - Menu Mingguan FoodCycle')

@push('head')<meta name="robots" content="noindex">@endpush

@section('content')
@php
    $overBudget = $plan->estimated_cost > $plan->budget;
    $shoppingText = "🛒 Belanja mingguan: {$plan->title}\n\n"
        . collect($plan->shopping_list)->map(fn ($s) => '☐ ' . $s['name'] . ($s['amount'] ? " ({$s['amount']})" : '') . ($s['estimated_price'] ? ' - ' . \App\Models\MealPlan::rupiah($s['estimated_price']) : ''))->join("\n")
        . "\n\nPerkiraan total: " . \App\Models\MealPlan::rupiah($plan->estimated_cost) . "\n\n— dari FoodCycle AI";
@endphp
<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
    <a href="{{ route('meal-plans.index') }}" class="mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Semua menu mingguan
    </a>

    <x-page-header eyebrow="Menu mingguan · {{ $plan->servings }}" :title="$plan->title"
        subtitle="Dibuat {{ $plan->created_at->translatedFormat('d F Y') }}">
        <x-slot:actions>
            <button onclick="window.print()" type="button" class="btn btn-secondary btn-sm">Cetak</button>
            <form method="POST" action="{{ route('meal-plans.destroy', $plan->id) }}" onsubmit="return confirm('Hapus rencana menu ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-ghost btn-sm hover:!text-rose-600">Hapus</button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <!-- Budget summary -->
    <dl class="mb-6 grid grid-cols-3 divide-x divide-slate-100 card dark:divide-slate-800">
        <div class="p-4 sm:p-5">
            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Anggaran</dt>
            <dd class="mt-1 text-lg font-bold tracking-tight text-slate-900 sm:text-xl dark:text-white">{{ \App\Models\MealPlan::rupiah($plan->budget) }}</dd>
        </div>
        <div class="p-4 sm:p-5">
            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Total belanja</dt>
            <dd class="mt-1 text-lg font-bold tracking-tight sm:text-xl {{ $overBudget ? 'text-rose-600' : 'text-emerald-600 dark:text-emerald-400' }}">{{ \App\Models\MealPlan::rupiah($plan->estimated_cost) }}</dd>
        </div>
        <div class="p-4 sm:p-5">
            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $overBudget ? 'Kelebihan' : 'Sisa anggaran' }}</dt>
            <dd class="mt-1 text-lg font-bold tracking-tight text-slate-900 sm:text-xl dark:text-white">{{ \App\Models\MealPlan::rupiah(abs($plan->budget - $plan->estimated_cost)) }}</dd>
        </div>
    </dl>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <!-- 7 days -->
        <ol class="space-y-3 lg:col-span-3">
            @foreach($plan->days as $i => $day)
            <li class="card flex gap-4 p-5">
                <div class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-xl bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-100 dark:bg-brand-950/60 dark:text-brand-300 dark:ring-brand-900">
                    <span class="text-[10px] font-semibold uppercase leading-none">{{ \Illuminate\Support\Str::limit($day['day'], 3, '') }}</span>
                    <span class="mt-0.5 text-base font-bold leading-none">{{ $i + 1 }}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="font-semibold text-slate-900 dark:text-white">{{ $day['menu'] }}</h3>
                        @if($day['estimated_cost'])
                            <span class="shrink-0 text-xs font-semibold text-slate-500 dark:text-slate-400">{{ \App\Models\MealPlan::rupiah($day['estimated_cost']) }}</span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $day['description'] }}</p>
                    @if($day['main_ingredients'])
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach($day['main_ingredients'] as $ing)
                            <span class="badge bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300">{{ $ing }}</span>
                        @endforeach
                    </div>
                    @endif
                    <form method="POST" action="{{ route('assistant.fcai') }}" class="mt-3" x-data="{ busy: false }" @submit="busy = true">
                        @csrf
                        <input type="hidden" name="ingredients" value="{{ implode(', ', $day['main_ingredients']) ?: $day['menu'] }}">
                        <input type="hidden" name="notes" value="{{ \Illuminate\Support\Str::limit('Buat resep lengkap untuk menu: ' . $day['menu'], 290) }}">
                        <input type="hidden" name="servings" value="{{ $plan->servings }}">
                        <button type="submit" :disabled="busy" class="text-sm font-semibold text-brand-600 hover:text-brand-800 disabled:opacity-60 dark:text-brand-400 cursor-pointer">
                            <span x-text="busy ? 'Menyusun resep...' : 'Lihat resep lengkap →'"></span>
                        </button>
                    </form>
                </div>
            </li>
            @endforeach
        </ol>

        <!-- Shopping list -->
        <aside class="space-y-4 lg:col-span-2" x-data="{ copied: false, text: @js($shoppingText) }">
            <div class="card p-6 lg:sticky lg:top-24">
                <h2 class="section-title">Daftar belanja seminggu</h2>
                <p class="section-subtitle">Centang yang sudah dibeli.</p>
                <ul class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($plan->shopping_list as $item)
                    <li class="flex items-center justify-between gap-3 py-2.5 text-sm">
                        <label class="flex cursor-pointer items-center gap-2.5">
                            <input type="checkbox" class="checkbox peer">
                            <span class="peer-checked:text-slate-400 peer-checked:line-through">
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ $item['name'] }}</span>
                                @if($item['amount'])<span class="text-slate-400"> · {{ $item['amount'] }}</span>@endif
                            </span>
                        </label>
                        @if($item['estimated_price'])<span class="shrink-0 text-slate-600 dark:text-slate-300">{{ \App\Models\MealPlan::rupiah($item['estimated_price']) }}</span>@endif
                    </li>
                    @endforeach
                </ul>
                <div class="mt-3 flex justify-between border-t border-slate-100 pt-3 text-sm font-semibold text-slate-900 dark:border-slate-800 dark:text-white">
                    <span>Total</span><span>{{ \App\Models\MealPlan::rupiah($plan->estimated_cost) }}</span>
                </div>
                <div class="mt-5 grid grid-cols-2 gap-2">
                    <button type="button" class="btn btn-secondary btn-sm"
                            @click="navigator.clipboard.writeText(text).then(() => { copied = true; setTimeout(() => copied = false, 2500) }).catch(() => window.prompt('Salin daftar belanja ini:', text))">
                        <span x-text="copied ? 'Tersalin ✓' : 'Salin daftar'"></span>
                    </button>
                    <a :href="'https://wa.me/?text=' + encodeURIComponent(text)" target="_blank" rel="noopener" class="btn btn-success btn-sm">Kirim ke WhatsApp</a>
                </div>
                @if($plan->tips)
                    <div class="mt-5 rounded-xl bg-amber-50 p-4 text-sm leading-relaxed text-amber-900 ring-1 ring-inset ring-amber-200 dark:bg-amber-950/30 dark:text-amber-200 dark:ring-amber-900/60">
                        <p class="font-semibold">Tips hemat</p>
                        <p class="mt-1">{{ $plan->tips }}</p>
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection
