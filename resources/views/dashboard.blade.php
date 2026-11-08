@extends('layouts.app')

@section('title', 'Beranda - FoodCycle')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8" x-data="dashboardUI()">

    <!-- Loading State Overlay -->
    <div x-show="isGenerating" x-cloak 
         class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-slate-950/70 backdrop-blur-xs text-white transition-opacity duration-200">
        <div class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-2xl p-6 sm:p-8 shadow-xl max-w-sm w-full mx-4 text-center border border-slate-100 dark:border-slate-800">
            <div class="w-12 h-12 relative mx-auto mb-4">
                <div class="w-12 h-12 border-3 border-brand-600 border-t-transparent rounded-full animate-spin"></div>
                <img src="{{ asset('images/mascot.png') }}" class="w-6 h-6 absolute inset-0 m-auto" alt="FoodCycle">
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1" x-text="loadingTitle"></h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">FoodCycle AI sedang memproses data dan menyusun panduan memasak untuk Anda.</p>
        </div>
    </div>

    @php
        $hour = (int) now()->format('G');
        $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
        $kpis = [
            ['Total resep', $stats['total_generated'], 'semua waktu', 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
            ['Resep bulan ini', $monthStats['recipes'], $monthStats['month'], 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['Bahan dimanfaatkan', $monthStats['ingredients_used'], 'dari resep bahan sisa', 'M5 13l4 4L19 7'],
            ['Rata-rata biaya', 'Rp ' . number_format($monthStats['avg_cost'], 0, ',', '.'), 'per resep bulan ini', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ];
        $modes = [
            'FCAI' => ['Bahan & Peralatan', 'Masak dari bahan yang ada di dapur.', 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
            'FCBB' => ['Sesuai Anggaran', 'Menu lezat dengan batas belanja.', 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
            'FBPB' => ['Dari Foto', 'Resep recook dari foto makanan.', 'M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z M15 13a3 3 0 11-6 0 3 3 0 016 0z'],
        ];
    @endphp

    <!-- Greeting -->
    <section class="relative mb-6 overflow-hidden rounded-2xl bg-brand-700 px-6 py-7 text-white shadow-raised sm:px-8">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(255,255,255,0.18),transparent_55%)]"></div>
        <div class="pointer-events-none absolute -bottom-20 -left-10 h-56 w-56 rounded-full bg-tomato-500/25 blur-3xl"></div>
        <div class="relative flex items-center justify-between gap-6">
            <div class="min-w-0">
                <p class="text-sm font-medium text-brand-100">{{ $greeting }},</p>
                <h1 class="mt-0.5 truncate text-2xl font-bold tracking-tight sm:text-3xl">{{ $user->name }}</h1>
                <p class="mt-2 max-w-xl text-sm leading-relaxed text-brand-100/90">Mau masak apa hari ini? Pilih cara membuat resep di bawah, atau masak dari bahan yang hampir kedaluwarsa.</p>
                <div class="mt-4 flex flex-wrap items-center gap-2 text-xs">
                    @foreach($user->dietary_preferences ?: ['Umum'] as $diet)
                        <span class="rounded-md bg-white/15 px-2 py-0.5 font-medium ring-1 ring-inset ring-white/20">{{ $diet }}</span>
                    @endforeach
                    <button type="button" @click="$store.app.showPrefsModal = true" onclick="window.openPreferences && window.openPreferences()"
                            class="rounded-md px-2 py-0.5 font-semibold text-white/90 underline decoration-white/40 underline-offset-2 hover:decoration-white cursor-pointer">
                        Ubah preferensi
                    </button>
                </div>
            </div>
            <img src="{{ asset('images/mascot.png') }}" alt="" class="hidden h-24 w-auto shrink-0 drop-shadow-xl sm:block">
        </div>
    </section>

    <!-- KPIs -->
    <dl class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach($kpis as [$label, $value, $hint, $icon])
            <div class="card p-4 sm:p-5">
                <div class="flex items-center justify-between">
                    <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $label }}</dt>
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </div>
                <dd class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl dark:text-white">{{ $value }}</dd>
                <p class="mt-0.5 text-[11px] text-slate-400 dark:text-slate-500">{{ $hint }}</p>
            </div>
        @endforeach
    </dl>

    <!-- Expiry reminder -->
    @if($expiringItems->isNotEmpty())
        <section class="mb-8 flex flex-col gap-4 rounded-2xl bg-amber-50 p-5 ring-1 ring-inset ring-amber-200 sm:flex-row sm:items-center sm:justify-between dark:bg-amber-950/30 dark:ring-amber-900/70">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div>
                    <h2 class="text-sm font-semibold text-amber-950 dark:text-amber-100">Segera masak sebelum terbuang</h2>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach($expiringItems as $item)
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-white px-2 py-1 text-xs font-medium text-slate-800 ring-1 ring-inset ring-amber-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-amber-900">
                                {{ $item->name }}
                                <span class="badge px-1.5 py-0 {{ $item->expiry_badge_classes }}">{{ $item->expiry_label }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="flex shrink-0 gap-2">
                <a href="{{ route('pantry.index') }}" class="btn btn-secondary btn-sm">Kelola stok</a>
                <form method="POST" action="{{ route('assistant.fcai') }}" x-data="{ busy: false }" @submit="busy = true">
                    @csrf
                    <input type="hidden" name="ingredients" value="{{ $expiringItems->pluck('name')->join(', ') }}">
                    <input type="hidden" name="notes" value="Prioritaskan bahan dari stok dapur yang hampir kedaluwarsa agar tidak terbuang.">
                    <button type="submit" class="btn btn-accent btn-sm" :disabled="busy">
                        <span x-text="busy ? 'Meracik resep...' : 'Masak sekarang'"></span>
                    </button>
                </form>
            </div>
        </section>
    @endif

    <!-- AI workspace: mode picker -->
    <section id="ai-workspace" class="scroll-mt-20">
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">Buat resep baru</h2>
                <p class="section-subtitle">Pilih cara AI menyusun resep untuk Anda.</p>
            </div>
            @if($expiringItems->isEmpty())
                <a href="{{ route('pantry.index') }}" class="hidden text-sm font-semibold text-brand-600 hover:text-brand-800 sm:inline dark:text-brand-400">Catat stok dapur →</a>
            @endif
        </div>

        <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-3" role="tablist" aria-label="Mode pembuatan resep">
            @foreach($modes as $key => [$title, $desc, $icon])
                <button type="button" role="tab" :aria-selected="activeTab === '{{ $key }}'" @click="selectTab('{{ $key }}')"
                        :class="activeTab === '{{ $key }}' ? 'border-brand-500 ring-4 ring-brand-500/10 bg-white dark:bg-slate-900 dark:border-brand-500' : 'border-slate-200 bg-white/60 hover:border-slate-300 hover:bg-white dark:border-slate-800 dark:bg-slate-900/50 dark:hover:border-slate-700'"
                        class="group flex items-center gap-3.5 rounded-2xl border p-4 text-left transition cursor-pointer focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-brand-500/30">
                    <span :class="activeTab === '{{ $key }}' ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-500 group-hover:text-slate-700 dark:bg-slate-800 dark:text-slate-400'"
                          class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block text-sm font-semibold text-slate-900 dark:text-white">{{ $title }}</span>
                        <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $desc }}</span>
                    </span>
                </button>
            @endforeach
        </div>
    </section>

    <!-- Dynamic Form Panel -->
    <div id="mode-form" class="mb-12 scroll-mt-20">

        <!-- FORM 1: Bahan & Alat -->
        <div x-show="activeTab === 'FCAI'" 
             x-transition:enter="transition ease-out duration-150" 
             x-transition:enter-start="opacity-0 translate-y-1" 
             x-transition:enter-end="opacity-100 translate-y-0"
             class="card overflow-hidden">

            <div class="p-6 sm:p-8">
                <div class="pb-5 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">Rekomendasi dari Bahan Dapur</h3>
                        <p class="section-subtitle">Masukkan bahan yang Anda miliki dan pilih peralatan masak yang tersedia.</p>
                    </div>
                </div>

                <form action="{{ route('assistant.fcai') }}" method="POST" @submit="submitWithLoading('Menyusun Resep...')">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-6">
                        
                        <!-- Left Columns: Ingredients -->
                        <div class="lg:col-span-2 space-y-4">
                            <div>
                                <label class="label">
                                    Bahan yang Tersedia <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="ingredients" x-model="fcaiIngredients" required rows="3"
                                    placeholder="Contoh: 2 butir telur, tempe, wortel, bawang putih, kecap manis"
                                    class="input"></textarea>
                                
                                <!-- Quick-add Chips -->
                                <div class="mt-2.5 flex flex-wrap items-center gap-1.5 text-xs">
                                    <span class="mr-1 text-slate-400 dark:text-slate-500">Pilih cepat:</span>
                                    <template x-for="chip in ['Telur', 'Tahu', 'Tempe', 'Ayam', 'Kangkung', 'Bawang', 'Wortel', 'Nasi']">
                                        <button type="button" @click="addIngredient(chip)" 
                                            class="chip">
                                            + <span x-text="chip"></span>
                                        </button>
                                    </template>
                                </div>
                                @error('ingredients')
                                    <p class="input-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Cooking Tools Selector -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="label !mb-0">
                                        Peralatan Masak yang Digunakan
                                    </label>
                                    <button type="button" @click="selectAllTools()" class="text-xs text-brand-600 dark:text-brand-400 font-bold hover:underline cursor-pointer">
                                        Pilih Semua
                                    </button>
                                </div>
                                
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    @php
                                        $defaultTools = ['Kompor Gas/Listrik', 'Wajan / Kuali', 'Teflon Anti Lengket', 'Panci Rebus', 'Rice Cooker', 'Air Fryer'];
                                        $userTools = $user->available_tools ?? ['Kompor Gas/Listrik', 'Wajan / Kuali'];
                                    @endphp
                                    @foreach($defaultTools as $tool)
                                    <label class="flex cursor-pointer select-none items-center gap-2.5 rounded-xl border border-slate-200 p-3 text-sm transition hover:border-slate-300 has-[:checked]:border-brand-400 has-[:checked]:bg-brand-50/60 dark:border-slate-800 dark:hover:border-slate-700 dark:has-[:checked]:border-brand-600 dark:has-[:checked]:bg-brand-950/40">
                                        <input type="checkbox" name="tools[]" value="{{ $tool }}" 
                                            {{ in_array($tool, $userTools) ? 'checked' : '' }}
                                            class="fcai-tool-check checkbox">
                                        <span class="font-medium text-slate-700 dark:text-slate-200">{{ $tool }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Settings -->
                        <div class="space-y-4 lg:border-l lg:border-slate-100 dark:lg:border-slate-800 lg:pl-6">
                            <div>
                                <label class="label">Jumlah Porsi</label>
                                <select name="servings" class="input">
                                    <option value="1 porsi">1 porsi</option>
                                    <option value="2 porsi" selected>2 porsi</option>
                                    <option value="3-4 porsi">3 - 4 porsi</option>
                                    <option value="5+ porsi">5+ porsi</option>
                                </select>
                            </div>

                            <div>
                                <label class="label">Tingkat Kesulitan</label>
                                <select name="difficulty" class="input">
                                    <option value="Sangat Mudah & Cepat" selected>Mudah & Cepat</option>
                                    <option value="Sedang">Sedang</option>
                                    <option value="Kreatif">Kreatif</option>
                                </select>
                            </div>

                            <div>
                                <label class="label">Catatan Khusus (Opsional)</label>
                                <input type="text" name="notes" placeholder="Contoh: Tanpa santan, rasa pedas manis" 
                                    class="input">
                            </div>

                            <div class="pt-2">
                                <button type="submit" 
                                    class="btn btn-primary btn-lg w-full">
                                    <span>Buat Resep dengan AI</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- FORM 2: Sesuai Anggaran -->
        <div x-show="activeTab === 'FCBB'" 
             x-transition:enter="transition ease-out duration-150" 
             x-transition:enter-start="opacity-0 translate-y-1" 
             x-transition:enter-end="opacity-100 translate-y-0"
             class="card overflow-hidden">

            <div class="p-6 sm:p-8">
                <div class="pb-5 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">Perencanaan Menu Sesuai Anggaran</h3>
                        <p class="section-subtitle">Tentukan batas nominal anggaran belanja dalam Rupiah (IDR).</p>
                    </div>
                </div>

                <form action="{{ route('assistant.fcbb') }}" method="POST" @submit="submitWithLoading('Menyusun Menu...')">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-6">
                        
                        <!-- Left 2 Cols: Budget & Nominal Chips -->
                        <div class="lg:col-span-2 space-y-4">
                            <div>
                                <label class="label">
                                    Batas Anggaran Belanja <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm font-semibold text-slate-400">Rp</span>
                                    <input type="number" name="budget" x-model.number="fcbbBudget" required min="5000" max="1000000" step="1000"
                                        class="input pl-12 py-3 text-base font-semibold">
                                </div>
                                
                                <!-- Quick Amount Buttons -->
                                <div class="mt-3 flex flex-wrap items-center gap-1.5 text-xs">
                                    <span class="mr-1 text-slate-400 dark:text-slate-500">Pilih cepat:</span>
                                    <template x-for="nominal in [15000, 25000, 50000, 100000]">
                                        <button type="button" @click="fcbbBudget = nominal" 
                                            :class="fcbbBudget === nominal && '!border-brand-300 !bg-brand-50 !text-brand-700 dark:!bg-brand-950/60 dark:!text-brand-300'"
                                            class="chip">
                                            Rp <span x-text="nominal.toLocaleString('id-ID')"></span>
                                        </button>
                                    </template>
                                </div>
                                @error('budget')
                                    <p class="input-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Meal Type Radio Selector -->
                            <div>
                                <label class="label">Waktu Makan</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    @foreach(['Sarapan', 'Makan Siang', 'Makan Malam', 'Menu Harian'] as $meal)
                                    <label class="flex cursor-pointer select-none items-center gap-2.5 rounded-xl border border-slate-200 p-3 text-sm transition hover:border-slate-300 has-[:checked]:border-brand-400 has-[:checked]:bg-brand-50/60 dark:border-slate-800 dark:hover:border-slate-700 dark:has-[:checked]:border-brand-600 dark:has-[:checked]:bg-brand-950/40">
                                        <input type="radio" name="meal_type" value="{{ $meal }}" {{ $loop->index === 1 ? 'checked' : '' }} class="h-4 w-4 border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-800">
                                        <span class="font-medium text-slate-700 dark:text-slate-200">{{ $meal }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Right Col: Portion Stepper & Action -->
                        <div class="space-y-4 lg:border-l lg:border-slate-100 dark:lg:border-slate-800 lg:pl-6">
                            <div>
                                <label class="label">Jumlah Porsi</label>
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="if (portionCount > 1) portionCount--" aria-label="Kurangi porsi"
                                        class="btn btn-secondary h-10 w-10 !p-0 text-lg">-</button>
                                    <span class="w-20 text-center text-sm font-semibold text-slate-900 dark:text-white" x-text="portionCount + ' Porsi'" aria-live="polite"></span>
                                    <input type="hidden" name="servings" :value="portionCount + ' porsi'">
                                    <button type="button" @click="if (portionCount < 10) portionCount++" aria-label="Tambah porsi"
                                        class="btn btn-secondary h-10 w-10 !p-0 text-lg">+</button>
                                </div>
                            </div>

                            <div>
                                <label class="label">Catatan Khusus (Opsional)</label>
                                <input type="text" name="notes" placeholder="Contoh: Menu tinggi protein, praktis" 
                                    class="input">
                            </div>

                            <div class="pt-2">
                                <button type="submit" 
                                    class="btn btn-primary btn-lg w-full">
                                    <span>Rancang Menu Hemat AI</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- FORM 3: Identifikasi Foto -->
        <div x-show="activeTab === 'FBPB'" 
             x-transition:enter="transition ease-out duration-150" 
             x-transition:enter-start="opacity-0 translate-y-1" 
             x-transition:enter-end="opacity-100 translate-y-0"
             class="card overflow-hidden">

            <div class="p-6 sm:p-8">
                <div class="pb-5 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">Identifikasi Resep dari Foto</h3>
                        <p class="section-subtitle">Unggah foto hidangan masakan untuk dianalisis dan disusun panduan resepnya.</p>
                    </div>
                </div>

                <form action="{{ route('assistant.fbpb') }}" method="POST" enctype="multipart/form-data" @submit="submitWithLoading('Menganalisis Foto...')">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-6">
                        
                        <!-- Upload Dropzone -->
                        <div class="lg:col-span-2 space-y-4">
                            <label class="label">
                                Foto Hidangan Masakan <span class="text-rose-500">*</span>
                            </label>
                            
                            <!-- Dropzone Area -->
                            <div class="relative rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/60 p-10 text-center transition hover:border-brand-400 hover:bg-brand-50/40 dark:border-slate-700 dark:bg-slate-800/40 dark:hover:border-brand-500 dark:hover:bg-brand-950/30">
                                <input type="file" name="photo" x-ref="fileInput" @change="previewImage($event)" required accept="image/jpeg,image/png,image/jpg,image/webp"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                
                                <!-- State: No Image -->
                                <div x-show="!photoPreview" class="space-y-2">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center mx-auto shadow-2xs">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div class="text-xs text-slate-700 dark:text-slate-300 font-semibold">
                                        <span class="text-brand-600 dark:text-brand-400 font-bold hover:underline">Klik untuk mengunggah</span> atau seret foto ke sini
                                    </div>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Format file: JPG, PNG, atau WEBP (maks. 8MB)</p>
                                </div>

                                <!-- State: Image Preview -->
                                <div x-show="photoPreview" x-cloak class="relative inline-block">
                                    <img :src="photoPreview" alt="Pratinjau" class="max-h-56 rounded-xl object-contain shadow-md mx-auto border border-slate-200 dark:border-slate-700">
                                    <p x-show="photoInfo" x-text="photoInfo" class="mt-2 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400"></p>
                                    <button type="button" @click.stop="removePhoto()" 
                                        class="btn btn-secondary btn-sm relative z-10 mt-3">
                                        Ganti Foto
                                    </button>
                                </div>
                            </div>

                            @error('photo')
                                <p class="input-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Right Col: Notes & Action -->
                        <div class="space-y-4 lg:border-l lg:border-slate-100 dark:lg:border-slate-800 lg:pl-6">
                            <div>
                                <label class="label">Catatan Tambahan (Opsional)</label>
                                <textarea name="notes" rows="4" placeholder="Contoh: Tolong buat versi hemat untuk 2 porsi rumahan" 
                                    class="input"></textarea>
                            </div>

                            <div class="pt-2">
                                <button type="submit" 
                                    class="btn btn-primary btn-lg w-full">
                                    <span>Analisis & Buat Resep</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Recent recipes -->
    <section>
        <div class="mb-4 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">Resep terakhir</h2>
                <p class="section-subtitle">Resep yang baru saja Anda buat.</p>
            </div>
            <a href="{{ route('recipes.index') }}" class="text-sm font-semibold text-brand-600 hover:text-brand-800 dark:text-brand-400">Lihat semua &rarr;</a>
        </div>
        @if($recentRecipes->count() > 0)
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($recentRecipes as $recipe)
                    <x-recipe-card :recipe="$recipe" />
                @endforeach
            </div>
        @else
            <x-empty-state title="Belum ada resep" text="Pilih salah satu cara di atas untuk membuat resep pertama Anda." />
        @endif
    </section>
</div>

@push('scripts')
<script>
    function dashboardUI() {
        return {
            activeTab: 'FCAI',
            fcaiIngredients: '',
            fcbbBudget: 20000,
            portionCount: 2,
            photoPreview: null,
            isGenerating: false,
            loadingTitle: 'Menyiapkan Resep...',

            selectTab(tab) {
                this.activeTab = tab;
                // On narrow screens the form sits below the mode cards: scroll it into view
                if (window.matchMedia('(max-width: 767px)').matches) {
                    this.$nextTick(() => {
                        document.getElementById('mode-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    });
                }
            },

            addIngredient(name) {
                if (!this.fcaiIngredients.trim()) {
                    this.fcaiIngredients = name;
                } else {
                    this.fcaiIngredients += ', ' + name;
                }
            },

            selectAllTools() {
                const checkboxes = document.querySelectorAll('.fcai-tool-check');
                checkboxes.forEach(cb => cb.checked = true);
            },

            photoInfo: '',

            async previewImage(event) {
                const input = event.target;
                let file = input.files[0];
                if (!file) return;

                // Shrink big phone photos before upload: faster upload, faster AI, less quota.
                // Falls back to the original file if the browser cannot decode it.
                try {
                    const smaller = await this.compressImage(file, 1600, 0.85);
                    if (smaller && smaller.size < file.size) {
                        const dt = new DataTransfer();
                        dt.items.add(smaller);
                        input.files = dt.files;
                        this.photoInfo = 'Foto dikompres: ' + (file.size / 1048576).toFixed(1) + ' MB → ' + (smaller.size / 1048576).toFixed(1) + ' MB';
                        file = smaller;
                    } else {
                        this.photoInfo = '';
                    }
                } catch (e) {
                    this.photoInfo = '';
                }

                const reader = new FileReader();
                reader.onload = (e) => { this.photoPreview = e.target.result; };
                reader.readAsDataURL(file);
            },

            compressImage(file, maxSide, quality) {
                return new Promise((resolve, reject) => {
                    const img = new Image();
                    const url = URL.createObjectURL(file);
                    img.onload = () => {
                        URL.revokeObjectURL(url);
                        const scale = Math.min(1, maxSide / Math.max(img.width, img.height));
                        const canvas = document.createElement('canvas');
                        canvas.width = Math.round(img.width * scale);
                        canvas.height = Math.round(img.height * scale);
                        canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
                        canvas.toBlob(blob => blob
                            ? resolve(new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }))
                            : reject(new Error('toBlob failed')), 'image/jpeg', quality);
                    };
                    img.onerror = () => { URL.revokeObjectURL(url); reject(new Error('decode failed')); };
                    img.src = url;
                });
            },

            removePhoto() {
                this.photoPreview = null;
                this.photoInfo = '';
                this.$refs.fileInput.value = '';
            },

            submitWithLoading(title) {
                this.loadingTitle = title;
                this.isGenerating = true;
            }
        };
    }
</script>
@endpush
@endsection
