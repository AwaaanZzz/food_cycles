@props(['title', 'text' => null])

<div {{ $attributes->merge(['class' => 'card flex flex-col items-center px-6 py-14 text-center']) }}>
    <img src="{{ asset('images/mascot.png') }}" alt="" class="h-16 w-auto opacity-90">
    <h3 class="mt-4 font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
    @if($text)<p class="mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">{{ $text }}</p>@endif
    @if($slot->isNotEmpty())<div class="mt-5">{{ $slot }}</div>@endif
</div>
