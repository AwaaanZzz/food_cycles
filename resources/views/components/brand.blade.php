@props(['size' => 'md', 'badge' => false])

{{-- Mascot + wordmark. The wordmark is cream with a navy outline; it is inverted to white in dark mode. --}}
@php $h = ['sm' => ['h-8', 'h-6'], 'md' => ['h-10', 'h-7'], 'lg' => ['h-14', 'h-9']][$size]; @endphp
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
    <img src="{{ asset('images/mascot.png') }}" alt="" class="{{ $h[0] }} w-auto shrink-0 select-none">
    <img src="{{ asset('images/brand-text.png') }}" alt="FoodCycle" class="{{ $h[1] }} w-auto select-none dark:brightness-0 dark:invert">
    @if($badge)
        <span class="hidden sm:inline-flex lg:hidden 2xl:inline-flex badge bg-brand-50 text-brand-700 ring-1 ring-inset ring-brand-200 dark:bg-brand-950/60 dark:text-brand-300 dark:ring-brand-800">AI Assistant</span>
    @endif
</span>
