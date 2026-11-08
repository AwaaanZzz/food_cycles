@props(['recipe', 'showSave' => true, 'savedMode' => false])

{{-- Recipe summary card used by Riwayat, Tersimpan, dashboard and profile --}}
<article class="card group flex flex-col overflow-hidden transition hover:-translate-y-0.5 hover:shadow-raised hover:border-brand-200 dark:hover:border-brand-800">
    <a href="{{ route('recipes.show', $recipe->id) }}" class="flex flex-1 flex-col focus-visible:outline-none">
        @if($recipe->image_path)
            <div class="relative aspect-[16/9] overflow-hidden bg-slate-100 dark:bg-slate-800">
                <img src="{{ $recipe->image_url }}" alt="{{ $recipe->title }}" loading="lazy"
                     class="h-full w-full object-cover transition duration-300 group-hover:scale-[1.03]">
            </div>
        @endif
        <div class="flex flex-1 flex-col p-5">
            <div class="flex items-center justify-between gap-2">
                <x-type-badge :type="$recipe->type" />
                <time class="text-[11px] text-slate-400 dark:text-slate-500" datetime="{{ $recipe->created_at->toDateString() }}">
                    {{ $recipe->created_at->translatedFormat('d M Y') }}
                </time>
            </div>
            <h3 class="mt-3 font-semibold leading-snug text-slate-900 line-clamp-2 group-hover:text-brand-700 dark:text-white dark:group-hover:text-brand-300">
                {{ $recipe->title }}
            </h3>
            @if($recipe->description)
                <p class="mt-1.5 text-xs leading-relaxed text-slate-500 line-clamp-2 dark:text-slate-400">{{ $recipe->description }}</p>
            @endif
            <div class="mt-auto flex items-center justify-between pt-4 text-xs">
                <span class="inline-flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $recipe->cooking_time }}
                </span>
                <span class="font-semibold text-slate-900 dark:text-white">{{ $recipe->formatted_cost }}</span>
            </div>
        </div>
    </a>
    @if($showSave)
        <div class="flex items-center justify-between border-t border-slate-100 px-5 py-2.5 dark:border-slate-800">
            <form method="POST" action="{{ route('recipes.toggle-save', $recipe->id) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-semibold transition cursor-pointer
                    {{ $recipe->is_saved ? 'text-tomato-600 hover:text-tomato-700 dark:text-tomato-400' : 'text-slate-500 hover:text-tomato-600 dark:text-slate-400' }}">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" fill="{{ $recipe->is_saved ? 'currentColor' : 'none' }}" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    {{ $recipe->is_saved ? ($savedMode ? 'Hapus dari favorit' : 'Tersimpan') : 'Simpan' }}
                </button>
            </form>
            @if($recipe->rating)
                <span class="text-xs font-semibold text-amber-500" aria-label="Rating {{ $recipe->rating }} dari 5">★ {{ $recipe->rating }}/5</span>
            @endif
        </div>
    @endif
</article>
