@props(['type'])

@php
    // One colour per AI mode, used everywhere a recipe type is shown
    [$label, $classes] = match ($type) {
        'fcai' => ['Bahan & Peralatan', 'bg-brand-50 text-brand-700 ring-brand-200 dark:bg-brand-950/60 dark:text-brand-300 dark:ring-brand-800'],
        'fcbb' => ['Sesuai Anggaran', 'bg-amber-50 text-amber-800 ring-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:ring-amber-800'],
        'fbpb' => ['Dari Foto', 'bg-violet-50 text-violet-700 ring-violet-200 dark:bg-violet-950/50 dark:text-violet-300 dark:ring-violet-800'],
        default => [strtoupper((string) $type), 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700'],
    };
@endphp
<span {{ $attributes->merge(['class' => "badge ring-1 ring-inset $classes"]) }}>{{ $label }}</span>
