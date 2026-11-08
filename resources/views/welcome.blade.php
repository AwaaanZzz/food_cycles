@extends('layouts.app')

@section('title', 'FoodCycle AI - Masak Cerdas dari Bahan Sisa, Anggaran & Foto')
@section('meta_description', 'Asisten memasak AI gratis: ubah bahan sisa di kulkas, batas anggaran belanja, atau foto makanan menjadi resep lengkap dengan estimasi harga dan gizi. Kurangi makanan terbuang.')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Hero -->
    <section class="relative mt-6 overflow-hidden rounded-3xl bg-brand-700 px-6 py-14 text-white shadow-raised sm:px-12 sm:py-20">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.2),transparent_55%)]"></div>
        <div class="absolute -bottom-24 -left-16 w-72 h-72 bg-tomato-500/30 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-brand-50 ring-1 ring-inset ring-white/20">
                    Asisten Memasak Berbasis AI
                </span>
                <h1 class="mt-5 text-4xl font-bold leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">
                    Masak enak, hemat,<br class="hidden sm:block"> dan <span class="text-tomato-300">tanpa sisa</span>.
                </h1>
                <p class="mt-4 text-sm sm:text-base text-brand-100/90 max-w-xl leading-relaxed">
                    Punya bahan sisa di kulkas, uang belanja terbatas, atau penasaran resep dari foto makanan?
                    FoodCycle menyusun resep lengkap untukmu: bahan, langkah, estimasi harga, dan nilai gizi.
                </p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn btn-lg bg-white text-brand-700 shadow-sm hover:bg-brand-50">
                        Mulai Gratis
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-lg bg-white/10 text-white ring-1 ring-inset ring-white/25 hover:bg-white/20">
                        Saya Sudah Punya Akun
                    </a>
                </div>
            </div>
            <div class="flex justify-center">
                <img src="{{ asset('images/brand-full.jpg') }}" alt="Maskot tomat FoodCycle"
                     class="w-64 rounded-3xl shadow-2xl ring-1 ring-white/20 sm:w-80">
            </div>
        </div>
    </section>

    <!-- Three modes -->
    <section class="py-14">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Tiga cara membuat resep</h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Pilih yang paling cocok dengan kondisi dapurmu hari ini.</p>
        </div>
        <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach([
                ['Bahan & Peralatan', 'Tulis bahan yang ada dan alat masak yang kamu punya. Cuma ada rice cooker? Resepnya pakai rice cooker.', 'bg-brand-50 dark:bg-brand-950/50 text-brand-600 dark:text-brand-400', 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                ['Sesuai Anggaran', 'Tentukan batas belanja, misalnya Rp 15.000. Setiap bahan diberi harga pasar dan totalnya dijaga tetap di bawah anggaran.', 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                ['Dari Foto', 'Foto makanan di restoran atau media sosial, lalu dapatkan resep untuk membuatnya sendiri di rumah.', 'bg-violet-50 dark:bg-violet-950/50 text-violet-600 dark:text-violet-400', 'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z M15 13a3 3 0 11-6 0 3 3 0 016 0z'],
            ] as [$title, $text, $iconClass, $path])
            <div class="card p-6">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center {{ $iconClass }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}"/></svg>
                </div>
                <h3 class="mt-4 font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
                <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400 leading-relaxed">{{ $text }}</p>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Extra features -->
    <section class="pb-14">
        <div class="card p-6 sm:p-10">
            <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Lebih dari sekadar resep</h2>
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-sm">
                @foreach([
                    ['Stok Dapur', 'Catat isi kulkas beserta tanggal kedaluwarsanya. FoodCycle mengingatkan bahan yang harus segera dimasak.'],
                    ['Menu Mingguan', 'Susun menu 7 hari sekaligus dalam satu anggaran, lengkap dengan daftar belanja gabungan.'],
                    ['Daftar Belanja', 'Ubah bahan resep menjadi daftar belanja dan kirim langsung ke WhatsApp.'],
                    ['Jejak Hemat', 'Lihat berapa resep yang kamu masak dan berapa bahan sisa yang berhasil diselamatkan.'],
                ] as [$title, $text])
                <div>
                    <h3 class="font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
                    <p class="mt-1 text-slate-500 dark:text-slate-400 leading-relaxed">{{ $text }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- How it works -->
    <section class="pb-16">
        <h2 class="text-center text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 dark:text-white">Cara kerjanya</h2>
        <ol class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach(['Daftar gratis dan atur preferensi diet serta alat masakmu.', 'Masukkan bahan, anggaran, atau unggah foto makanan.', 'Dapatkan resep lengkap, simpan, bagikan, atau masak ulang dengan variasi lain.'] as $i => $step)
            <li class="card flex items-start gap-4 p-5">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-50 font-bold text-brand-700 ring-1 ring-inset ring-brand-200 dark:bg-brand-950/60 dark:text-brand-300 dark:ring-brand-800">{{ $i + 1 }}</span>
                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed pt-1.5">{{ $step }}</p>
            </li>
            @endforeach
        </ol>
        <div class="mt-10 text-center">
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg inline-flex">
                Buat Akun FoodCycle
            </a>
        </div>
    </section>
</div>
@endsection
