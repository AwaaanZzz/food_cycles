@extends('layouts.app')

@section('title', 'Stok Dapur - FoodCycle')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8"
     x-data="{
        selected: @js($items->filter(fn ($i) => $i->days_left !== null && $i->days_left <= \App\Models\PantryItem::SOON_DAYS)->pluck('id')->values()),
        names: @js($items->pluck('name', 'id')),
        get ingredientText() { return this.selected.map(id => this.names[id]).join(', '); },
        toggle(id) { this.selected.includes(id) ? this.selected = this.selected.filter(x => x !== id) : this.selected.push(id); }
     }">

    <x-page-header eyebrow="Stok dapur" title="Isi Kulkas & Dapur"
        subtitle="Catat bahan beserta tanggal kedaluwarsanya. Bahan yang hampir kedaluwarsa dipilih otomatis agar segera dimasak, bukan dibuang."
        icon="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
        @if($expiringCount > 0)
            <x-slot:actions>
                <span class="badge bg-amber-50 px-3 py-1.5 text-xs text-amber-800 ring-1 ring-inset ring-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:ring-amber-800">
                    {{ $expiringCount }} bahan perlu dimasak dalam {{ \App\Models\PantryItem::SOON_DAYS }} hari
                </span>
            </x-slot:actions>
        @endif
    </x-page-header>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Add item -->
        <form method="POST" action="{{ route('pantry.store') }}" class="card h-fit space-y-4 p-6">
            @csrf
            <div>
                <h2 class="section-title">Tambah bahan</h2>
                <p class="section-subtitle">Isi tanggal agar FoodCycle bisa mengingatkan.</p>
            </div>
            <div>
                <label for="name" class="label">Nama bahan</label>
                <input id="name" name="name" required maxlength="100" value="{{ old('name') }}" placeholder="Contoh: Bayam" class="input">
                @error('name') <p class="input-error">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="quantity" class="label">Jumlah</label>
                    <input id="quantity" name="quantity" maxlength="50" value="{{ old('quantity') }}" placeholder="1 ikat" class="input">
                </div>
                <div>
                    <label for="expires_at" class="label">Kedaluwarsa</label>
                    <input id="expires_at" name="expires_at" type="date" value="{{ old('expires_at') }}" class="input px-3">
                    @error('expires_at') <p class="input-error">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex flex-wrap gap-1.5">
                @foreach(['Hari ini' => 0, '+2 hari' => 2, '+1 minggu' => 7] as $label => $days)
                    <button type="button" class="chip" @click="document.getElementById('expires_at').value = '{{ now()->addDays($days)->toDateString() }}'">{{ $label }}</button>
                @endforeach
            </div>
            <button type="submit" class="btn btn-primary w-full">Simpan ke Stok</button>
        </form>

        <!-- Item list -->
        <div class="space-y-4 lg:col-span-2">
            @if($items->isEmpty())
                <x-empty-state title="Stok dapur masih kosong" text="Tambahkan bahan pertama di form sebelah kiri." />
            @else
                <ul class="card divide-y divide-slate-100 dark:divide-slate-800">
                    @foreach($items as $item)
                    <li class="flex items-center gap-3 px-5 py-3.5">
                        <input type="checkbox" class="checkbox" :checked="selected.includes({{ $item->id }})" @change="toggle({{ $item->id }})"
                               aria-label="Pilih {{ $item->name }} untuk dimasak">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-slate-900 dark:text-white">{{ $item->name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $item->quantity ?: '—' }}@if($item->expires_at) · {{ $item->expires_at->translatedFormat('d M Y') }}@endif
                            </p>
                        </div>
                        <span class="badge shrink-0 {{ $item->expiry_badge_classes }}">{{ $item->expiry_label }}</span>
                        <form method="POST" action="{{ route('pantry.destroy', $item->id) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-icon h-8 w-8 hover:text-rose-600" aria-label="Hapus {{ $item->name }}" title="Sudah dipakai / dibuang">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </form>
                    </li>
                    @endforeach
                </ul>

                <!-- Cook with selected -->
                <div class="card-muted flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        <span class="font-semibold text-slate-900 dark:text-white" x-text="selected.length"></span> bahan dipilih<span x-show="selected.length">: <span x-text="ingredientText"></span></span>
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('pantry.destroy-many') }}" @submit="if (!confirm('Hapus bahan terpilih dari stok dapur?')) $event.preventDefault()">
                            @csrf @method('DELETE')
                            <template x-for="id in selected" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
                            <button type="submit" class="btn btn-secondary btn-sm" :disabled="selected.length === 0">Tandai sudah dipakai</button>
                        </form>
                        <form method="POST" action="{{ route('assistant.fcai') }}" x-data="{ busy: false }" @submit="busy = true">
                            @csrf
                            <input type="hidden" name="ingredients" :value="ingredientText">
                            <input type="hidden" name="notes" value="Prioritaskan bahan dari stok dapur yang hampir kedaluwarsa agar tidak terbuang.">
                            <button type="submit" class="btn btn-accent btn-sm" :disabled="selected.length === 0 || busy">
                                <span x-text="busy ? 'AI sedang meracik...' : 'Masak dari bahan terpilih'"></span>
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
