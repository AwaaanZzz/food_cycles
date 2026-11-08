@props(['title', 'subtitle' => null, 'wide' => false])

{{-- Shared shell for login / register / password / verification pages: brand panel + form card --}}
<div class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-7xl grid-cols-1 lg:grid-cols-2 lg:gap-12 lg:px-8">
    <!-- Brand panel (desktop) -->
    <aside class="relative my-8 hidden overflow-hidden rounded-3xl bg-brand-700 p-10 text-white lg:flex lg:flex-col lg:justify-between">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.18),transparent_55%)]"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-16 h-72 w-72 rounded-full bg-tomato-500/30 blur-3xl"></div>

        <div class="relative">
            <p class="text-sm font-semibold text-brand-100">FoodCycle AI</p>
            <h2 class="mt-3 max-w-md text-3xl font-bold leading-tight tracking-tight">Masak enak, hemat, dan tanpa sisa.</h2>
            <ul class="mt-8 space-y-4 text-sm text-brand-50/90">
                @foreach([
                    'Resep dari bahan sisa di kulkas dan alat masak yang Anda punya',
                    'Menu sesuai anggaran, lengkap dengan harga tiap bahan',
                    'Pengingat bahan hampir kedaluwarsa dan menu 7 hari',
                ] as $point)
                    <li class="flex gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ $point }}
                    </li>
                @endforeach
            </ul>
        </div>
        <img src="{{ asset('images/mascot.png') }}" alt="" class="relative w-44 self-end drop-shadow-2xl">
    </aside>

    <!-- Form -->
    <section class="flex items-center justify-center px-4 py-10 sm:px-6">
        <div class="w-full {{ $wide ? 'max-w-lg' : 'max-w-md' }}">
            <div class="mb-8">
                <img src="{{ asset('images/mascot.png') }}" alt="" class="mb-5 h-12 w-auto lg:hidden">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h1>
                @if($subtitle)<p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>@endif
            </div>

            @if(session('status'))
                <div class="mb-5 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 ring-1 ring-inset ring-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:ring-emerald-900" role="status">
                    {{ session('status') }}
                </div>
            @endif

            {{ $slot }}

            @isset($footer)
                <p class="mt-8 text-center text-sm text-slate-500 dark:text-slate-400">{{ $footer }}</p>
            @endisset
        </div>
    </section>
</div>
