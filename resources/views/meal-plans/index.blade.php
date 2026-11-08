@extends('layouts.app')

@section('title', 'Menu Mingguan - FoodCycle')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">

    <x-page-header eyebrow="Menu mingguan" title="Rencana Makan 7 Hari"
        subtitle="Tentukan anggaran belanja seminggu. AI menyusun menu Senin–Minggu yang saling memakai bahan yang sama, lengkap dengan satu daftar belanja."
        icon="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('meal-plans.store') }}" class="card h-fit space-y-5 p-6"
              x-data="{ busy: false, budget: {{ (int) old('budget', 150000) }} }" @submit="busy = true">
            @csrf
            <div>
                <h2 class="section-title">Buat menu baru</h2>
                <p class="section-subtitle">Proses AI sekitar 10–20 detik.</p>
            </div>
            <div>
                <label for="budget" class="label">Anggaran seminggu (Rp)</label>
                <input id="budget" name="budget" type="number" min="50000" max="5000000" step="5000" required x-model.number="budget" class="input">
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach([100000, 150000, 250000, 400000] as $nominal)
                        <button type="button" class="chip" :class="budget === {{ $nominal }} && '!border-brand-300 !bg-brand-50 !text-brand-700 dark:!bg-brand-950/60 dark:!text-brand-300'" @click="budget = {{ $nominal }}">
                            {{ number_format($nominal / 1000, 0) }} rb
                        </button>
                    @endforeach
                </div>
                @error('budget') <p class="input-error">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="servings" class="label">Porsi</label>
                    <select id="servings" name="servings" class="input">
                        @foreach(['1 porsi', '2 porsi', '3-4 porsi', '5-6 porsi'] as $s)
                            <option @selected(old('servings', '2 porsi') === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="meals" class="label">Waktu makan</label>
                    <select id="meals" name="meals" class="input">
                        @foreach(['Makan malam saja', 'Makan siang dan makan malam', 'Sarapan, siang, dan malam'] as $m)
                            <option @selected(old('meals', 'Makan malam saja') === $m)>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label for="notes" class="label">Catatan <span class="font-normal text-slate-400">(opsional)</span></label>
                <input id="notes" name="notes" maxlength="300" value="{{ old('notes') }}" placeholder="Contoh: tidak pedas, ada anak kecil" class="input">
            </div>
            <button type="submit" class="btn btn-primary w-full" :disabled="busy">
                <svg x-show="busy" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                <span x-text="busy ? 'Menyusun menu 7 hari...' : 'Susun Menu Seminggu'"></span>
            </button>
        </form>

        <section class="lg:col-span-2" aria-labelledby="prev-plans">
            <h2 id="prev-plans" class="section-title mb-3">Rencana sebelumnya</h2>
            @forelse($plans as $plan)
                <a href="{{ route('meal-plans.show', $plan->id) }}" class="card mb-3 block p-5 transition hover:border-brand-200 hover:shadow-raised dark:hover:border-brand-800">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $plan->title }}</p>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                {{ $plan->created_at->translatedFormat('d M Y') }} · {{ $plan->servings }} · anggaran {{ \App\Models\MealPlan::rupiah($plan->budget) }}
                            </p>
                        </div>
                        <span class="shrink-0 text-sm font-semibold text-slate-900 dark:text-white">{{ \App\Models\MealPlan::rupiah($plan->estimated_cost) }}</span>
                    </div>
                    <p class="mt-3 text-xs text-slate-500 line-clamp-1 dark:text-slate-400">{{ collect($plan->days)->pluck('menu')->join(' · ') }}</p>
                </a>
            @empty
                <x-empty-state title="Belum ada rencana menu" text="Isi form di samping untuk membuat menu seminggu pertama Anda." />
            @endforelse
            {{ $plans->links() }}
        </section>
    </div>
</div>
@endsection
