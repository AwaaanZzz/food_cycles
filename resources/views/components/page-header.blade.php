@props(['eyebrow' => null, 'title', 'subtitle' => null, 'icon' => null])

{{-- One header style for every inner page: eyebrow, title, description, optional actions on the right --}}
<header {{ $attributes->merge(['class' => 'mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="flex items-start gap-4 min-w-0">
        @if($icon)
            <div class="hidden sm:flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand-50 text-brand-700 ring-1 ring-brand-100 dark:bg-brand-950/60 dark:text-brand-300 dark:ring-brand-900">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
            </div>
        @endif
        <div class="min-w-0">
            @if($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endif
            <h1 class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h1>
            @if($subtitle)<p class="mt-1.5 max-w-2xl text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ $subtitle }}</p>@endif
        </div>
    </div>
    @if(isset($actions) && $actions->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2 shrink-0">{{ $actions }}</div>
    @endif
</header>
