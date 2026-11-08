@extends('layouts.app')

@section('title', 'Beranda - FoodCycle')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8" 
     x-data="dashboardUI()">

    <!-- Loading State Overlay -->
    <div x-show="isGenerating" x-cloak 
         class="fixed inset-0 z-50 flex flex-col items-center justify-center bg-slate-950/70 backdrop-blur-xs text-white transition-opacity duration-200">
        <div class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 rounded-2xl p-6 sm:p-8 shadow-xl max-w-sm w-full mx-4 text-center border border-slate-100 dark:border-slate-800">
            <div class="w-12 h-12 relative mx-auto mb-4">
                <div class="w-12 h-12 border-3 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                <img src="{{ asset('images/mascot.png') }}" class="w-6 h-6 absolute inset-0 m-auto" alt="FoodCycle">
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1" x-text="loadingTitle"></h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">FoodCycle AI sedang memproses data dan menyusun panduan memasak untuk Anda.</p>
        </div>
    </div>

    <!-- Hero Card: Branded Royal Blue with Warm Accents -->
    <div class="bg-gradient-to-br from-[#0B50B5] via-[#1055BE] to-[#083882] rounded-3xl p-6 sm:p-8 shadow-xl shadow-blue-900/15 text-white mb-8 overflow-hidden relative border border-blue-400/20">
        <!-- Ambient decorative background glows -->
        <div class="absolute -top-24 -right-24 w-80 h-80 bg-white/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-amber-400/15 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 relative z-10">
            <div class="max-w-2xl">
                <div class="flex items-center gap-2 mb-2.5">
                    <span class="text-[11px] font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-white border border-white/25 shadow-2xs">
                        Asisten Memasak Cerdas
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                    Selamat Datang, {{ $user->name }}!
                </h1>
                <p class="mt-2 text-xs sm:text-sm text-blue-100/90 leading-relaxed max-w-xl">
                    Pilih salah satu metode di bawah untuk merancang resep sehat dari ketersediaan bahan dapur, batasan anggaran belanja, atau foto hidangan masakan Anda.
                </p>

                <!-- Current User Preferences -->
                <div class="mt-5 flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-blue-200/80 font-medium">Preferensi Diet:</span>
                    @forelse($user->dietary_preferences ?? ['Umum'] as $diet)
                        <span class="px-2.5 py-0.5 rounded-lg bg-white/15 backdrop-blur-md text-white font-medium border border-white/20">{{ $diet }}</span>
                    @empty
                        <span class="px-2.5 py-0.5 rounded-lg bg-white/15 backdrop-blur-md text-white">Umum</span>
                    @endforelse

                    <span class="text-blue-300/40 mx-1">•</span>
                    <button @click="$store.app.showPrefsModal = true" onclick="window.openPreferences && window.openPreferences()" type="button" class="text-amber-300 hover:text-amber-200 font-bold underline decoration-amber-300/60 cursor-pointer transition">
                        Ubah Preferensi
                    </button>
                </div>
            </div>

            <!-- Stats & Brand Mascot Badge -->
            <div class="flex items-center gap-5 shrink-0">
                <div class="hidden sm:block shrink-0 relative">
                    <img src="{{ asset('images/mascot.png') }}" alt="FoodCycle Mascot" class="h-24 w-auto drop-shadow-[0_10px_20px_rgba(0,0,0,0.3)] hover:scale-105 transition-transform duration-300">
                </div>
                <div class="grid grid-cols-3 gap-2.5">
                    <div class="bg-white/15 backdrop-blur-md rounded-2xl p-3.5 border border-white/20 text-center min-w-[90px] shadow-sm">
                        <div class="text-xl sm:text-2xl font-black text-white">{{ $stats['total_generated'] }}</div>
                        <div class="text-[10px] text-blue-100 font-semibold mt-0.5">Total Resep</div>
                    </div>
                    <div class="bg-white/15 backdrop-blur-md rounded-2xl p-3.5 border border-white/20 text-center min-w-[90px] shadow-sm">
                        <div class="text-xl sm:text-2xl font-black text-white">{{ $stats['total_saved'] }}</div>
                        <div class="text-[10px] text-blue-100 font-semibold mt-0.5">Tersimpan</div>
                    </div>
                    <div class="bg-white/15 backdrop-blur-md rounded-2xl p-3.5 border border-white/20 text-center min-w-[90px] shadow-sm">
                        <div class="text-xl sm:text-2xl font-black text-white">{{ $stats['total_fcbb'] }}</div>
                        <div class="text-[10px] text-blue-100 font-semibold mt-0.5">Menu Hemat</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3 Action Cards (Mode Selector) -->
    <div id="ai-workspace" class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>3 Mode Memasak Cerdas</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pilih salah satu metode untuk mulai meracik resep masakan</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Mode 1: Bahan & Alat -->
            <div @click="activeTab = 'FCAI'"
                 :class="activeTab === 'FCAI' 
                    ? 'border-blue-600 dark:border-blue-500 ring-2 ring-blue-600/30 dark:ring-blue-500/30 bg-blue-50/20 dark:bg-slate-800/80 shadow-sm' 
                    : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-white dark:bg-slate-900 shadow-2xs'"
                 class="cursor-pointer rounded-2xl p-5 border transition-all duration-200 flex flex-col justify-between group">
                <div>
                    <div class="mb-3.5">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center transition shadow-2xs bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <span class="sr-only">FCAI</span>
                    </div>
                    <h3 class="text-base font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Bahan & Peralatan
                    </h3>
                    <p class="text-xs mt-1.5 leading-relaxed text-slate-500 dark:text-slate-400">
                        Rekomendasi resep berdasarkan bahan makanan yang tersedia dan alat dapur yang Anda miliki.
                    </p>
                </div>
                <div class="mt-5 pt-3.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-bold">
                    <span :class="activeTab === 'FCAI' ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300'">Pilih Mode</span>
                    <span x-show="activeTab === 'FCAI'" class="flex items-center gap-1.5 text-blue-600 dark:text-blue-400 font-bold">
                        <span class="w-2 h-2 rounded-full bg-blue-600 dark:bg-blue-400"></span>
                        Aktif
                    </span>
                </div>
            </div>

            <!-- Mode 2: Sesuai Anggaran -->
            <div @click="activeTab = 'FCBB'"
                 :class="activeTab === 'FCBB' 
                    ? 'border-blue-600 dark:border-blue-500 ring-2 ring-blue-600/30 dark:ring-blue-500/30 bg-blue-50/20 dark:bg-slate-800/80 shadow-sm' 
                    : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-white dark:bg-slate-900 shadow-2xs'"
                 class="cursor-pointer rounded-2xl p-5 border transition-all duration-200 flex flex-col justify-between group">
                <div>
                    <div class="mb-3.5">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center transition shadow-2xs bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <span class="sr-only">FCBB</span>
                    </div>
                    <h3 class="text-base font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Sesuai Anggaran
                    </h3>
                    <p class="text-xs mt-1.5 leading-relaxed text-slate-500 dark:text-slate-400">
                        Rancang menu makan lezat dengan batasan total biaya belanja dalam Rupiah (IDR).
                    </p>
                </div>
                <div class="mt-5 pt-3.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-bold">
                    <span :class="activeTab === 'FCBB' ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300'">Pilih Mode</span>
                    <span x-show="activeTab === 'FCBB'" class="flex items-center gap-1.5 text-blue-600 dark:text-blue-400 font-bold">
                        <span class="w-2 h-2 rounded-full bg-blue-600 dark:bg-blue-400"></span>
                        Aktif
                    </span>
                </div>
            </div>

            <!-- Mode 3: Identifikasi Foto -->
            <div @click="activeTab = 'FBPB'"
                 :class="activeTab === 'FBPB' 
                    ? 'border-blue-600 dark:border-blue-500 ring-2 ring-blue-600/30 dark:ring-blue-500/30 bg-blue-50/20 dark:bg-slate-800/80 shadow-sm' 
                    : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-white dark:bg-slate-900 shadow-2xs'"
                 class="cursor-pointer rounded-2xl p-5 border transition-all duration-200 flex flex-col justify-between group">
                <div>
                    <div class="mb-3.5">
                        <div class="w-11 h-11 rounded-xl flex items-center justify-center transition shadow-2xs bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <span class="sr-only">FBPB</span>
                    </div>
                    <h3 class="text-base font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Identifikasi Foto
                    </h3>
                    <p class="text-xs mt-1.5 leading-relaxed text-slate-500 dark:text-slate-400">
                        Unggah foto hidangan masakan untuk mengenali bahan dan langkah pembuatannya kembali.
                    </p>
                </div>
                <div class="mt-5 pt-3.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs font-bold">
                    <span :class="activeTab === 'FBPB' ? 'text-blue-600 dark:text-blue-400' : 'text-slate-400 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-300'">Pilih Mode</span>
                    <span x-show="activeTab === 'FBPB'" class="flex items-center gap-1.5 text-blue-600 dark:text-blue-400 font-bold">
                        <span class="w-2 h-2 rounded-full bg-blue-600 dark:bg-blue-400"></span>
                        Aktif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Form Panel -->
    <div class="mb-12">

        <!-- FORM 1: Bahan & Alat -->
        <div x-show="activeTab === 'FCAI'" 
             x-transition:enter="transition ease-out duration-150" 
             x-transition:enter-start="opacity-0 translate-y-1" 
             x-transition:enter-end="opacity-100 translate-y-0"
             class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xs overflow-hidden transition-colors duration-150">

            <div class="p-6 sm:p-8">
                <div class="pb-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Rekomendasi dari Bahan Dapur</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Masukkan bahan yang Anda miliki dan pilih peralatan masak yang tersedia.</p>
                    </div>
                </div>

                <form action="{{ route('assistant.fcai') }}" method="POST" @submit="submitWithLoading('Menyusun Resep...')">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-6">
                        
                        <!-- Left Columns: Ingredients -->
                        <div class="lg:col-span-2 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Bahan yang Tersedia <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="ingredients" x-model="fcaiIngredients" required rows="3"
                                    placeholder="Contoh: 2 butir telur, tempe, wortel, bawang putih, kecap manis"
                                    class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 p-3.5 text-xs sm:text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition bg-slate-50/50 dark:bg-slate-800/80 focus:bg-white dark:focus:bg-slate-800"></textarea>
                                
                                <!-- Quick-add Chips -->
                                <div class="mt-2.5 flex flex-wrap items-center gap-1.5 text-xs">
                                    <span class="text-slate-400 dark:text-slate-500 font-medium">Pilih cepat:</span>
                                    <template x-for="chip in ['Telur', 'Tahu', 'Tempe', 'Ayam', 'Kangkung', 'Bawang', 'Wortel', 'Nasi']">
                                        <button type="button" @click="addIngredient(chip)" 
                                            class="px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-blue-600 hover:text-white text-slate-700 dark:text-slate-200 font-bold border border-slate-200 dark:border-slate-700 transition-all cursor-pointer shadow-2xs">
                                            + <span x-text="chip"></span>
                                        </button>
                                    </template>
                                </div>
                                @error('ingredients')
                                    <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Cooking Tools Selector -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-xs font-bold text-slate-800 dark:text-slate-200">
                                        Peralatan Masak yang Digunakan
                                    </label>
                                    <button type="button" @click="selectAllTools()" class="text-xs text-blue-600 dark:text-blue-400 font-bold hover:underline cursor-pointer">
                                        Pilih Semua
                                    </button>
                                </div>
                                
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    @php
                                        $defaultTools = ['Kompor Gas/Listrik', 'Wajan / Kuali', 'Teflon Anti Lengket', 'Panci Rebus', 'Rice Cooker', 'Air Fryer'];
                                        $userTools = $user->available_tools ?? ['Kompor Gas/Listrik', 'Wajan / Kuali'];
                                    @endphp
                                    @foreach($defaultTools as $tool)
                                    <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/60 hover:bg-slate-100/60 dark:hover:bg-slate-700/60 cursor-pointer text-xs transition select-none">
                                        <input type="checkbox" name="tools[]" value="{{ $tool }}" 
                                            {{ in_array($tool, $userTools) ? 'checked' : '' }}
                                            class="fcai-tool-check rounded-md text-blue-600 focus:ring-blue-500">
                                        <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $tool }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Settings -->
                        <div class="space-y-4 lg:border-l lg:border-slate-100 dark:lg:border-slate-800 lg:pl-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">Jumlah Porsi</label>
                                <select name="servings" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 p-2.5 text-xs sm:text-sm text-slate-900 dark:text-white bg-slate-50/50 dark:bg-slate-800 focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:outline-none">
                                    <option value="1 porsi">1 porsi</option>
                                    <option value="2 porsi" selected>2 porsi</option>
                                    <option value="3-4 porsi">3 - 4 porsi</option>
                                    <option value="5+ porsi">5+ porsi</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">Tingkat Kesulitan</label>
                                <select name="difficulty" class="w-full rounded-xl border border-slate-200 dark:border-slate-700 p-2.5 text-xs sm:text-sm text-slate-900 dark:text-white bg-slate-50/50 dark:bg-slate-800 focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:outline-none">
                                    <option value="Sangat Mudah & Cepat" selected>Mudah & Cepat</option>
                                    <option value="Sedang">Sedang</option>
                                    <option value="Kreatif">Kreatif</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">Catatan Khusus (Opsional)</label>
                                <input type="text" name="notes" placeholder="Contoh: Tanpa santan, rasa pedas manis" 
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 p-2.5 text-xs sm:text-sm text-slate-900 dark:text-white bg-slate-50/50 dark:bg-slate-800 focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:outline-none">
                            </div>

                            <div class="pt-2">
                                <button type="submit" 
                                    class="w-full py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
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
             class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xs overflow-hidden transition-colors duration-150">

            <div class="p-6 sm:p-8">
                <div class="pb-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Perencanaan Menu Sesuai Anggaran</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Tentukan batas nominal anggaran belanja dalam Rupiah (IDR).</p>
                    </div>
                </div>

                <form action="{{ route('assistant.fcbb') }}" method="POST" @submit="submitWithLoading('Menyusun Menu...')">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-6">
                        
                        <!-- Left 2 Cols: Budget & Nominal Chips -->
                        <div class="lg:col-span-2 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                    Batas Anggaran Belanja <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center font-bold text-slate-500 dark:text-slate-400 text-base">Rp</span>
                                    <input type="number" name="budget" x-model.number="fcbbBudget" required min="5000" max="1000000" step="1000"
                                        class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 pl-12 pr-4 py-3.5 text-base font-bold text-slate-900 dark:text-white bg-slate-50/50 dark:bg-slate-800 focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition">
                                </div>
                                
                                <!-- Quick Amount Buttons -->
                                <div class="mt-3 flex flex-wrap items-center gap-1.5 text-xs">
                                    <span class="text-slate-400 dark:text-slate-500 font-medium">Pilih nominal:</span>
                                    <template x-for="nominal in [15000, 25000, 50000, 100000]">
                                        <button type="button" @click="fcbbBudget = nominal" 
                                            :class="fcbbBudget === nominal ? 'bg-blue-600 text-white font-bold border-blue-600 shadow-xs' : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 font-semibold'"
                                            class="px-3 py-1.5 rounded-lg border transition-all cursor-pointer shadow-2xs">
                                            Rp <span x-text="nominal.toLocaleString('id-ID')"></span>
                                        </button>
                                    </template>
                                </div>
                                @error('budget')
                                    <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Meal Type Radio Selector -->
                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-2">Waktu Makan</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    @foreach(['Sarapan', 'Makan Siang', 'Makan Malam', 'Menu Harian'] as $meal)
                                    <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/60 hover:bg-slate-100/60 dark:hover:bg-slate-700/60 cursor-pointer text-xs transition select-none">
                                        <input type="radio" name="meal_type" value="{{ $meal }}" {{ $loop->index === 1 ? 'checked' : '' }} class="text-blue-600 focus:ring-blue-500">
                                        <span class="text-slate-800 dark:text-slate-200 font-semibold">{{ $meal }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Right Col: Portion Stepper & Action -->
                        <div class="space-y-4 lg:border-l lg:border-slate-100 dark:lg:border-slate-800 lg:pl-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">Jumlah Porsi</label>
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="if (portionCount > 1) portionCount--" 
                                        class="w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-black flex items-center justify-center transition cursor-pointer shadow-2xs">-</button>
                                    <span class="font-extrabold text-sm text-slate-900 dark:text-white w-16 text-center" x-text="portionCount + ' Porsi'"></span>
                                    <input type="hidden" name="servings" :value="portionCount + ' porsi'">
                                    <button type="button" @click="if (portionCount < 10) portionCount++" 
                                        class="w-10 h-10 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 font-black flex items-center justify-center transition cursor-pointer shadow-2xs">+</button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">Catatan Khusus (Opsional)</label>
                                <input type="text" name="notes" placeholder="Contoh: Menu tinggi protein, praktis" 
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 p-2.5 text-xs sm:text-sm text-slate-900 dark:text-white bg-slate-50/50 dark:bg-slate-800 focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:outline-none">
                            </div>

                            <div class="pt-2">
                                <button type="submit" 
                                    class="w-full py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
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
             class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xs overflow-hidden transition-colors duration-150">

            <div class="p-6 sm:p-8">
                <div class="pb-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Identifikasi Resep dari Foto</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Unggah foto hidangan masakan untuk dianalisis dan disusun panduan resepnya.</p>
                    </div>
                </div>

                <form action="{{ route('assistant.fbpb') }}" method="POST" enctype="multipart/form-data" @submit="submitWithLoading('Menganalisis Foto...')">
                    @csrf
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 pt-6">
                        
                        <!-- Upload Dropzone -->
                        <div class="lg:col-span-2 space-y-4">
                            <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">
                                Foto Hidangan Masakan <span class="text-rose-500">*</span>
                            </label>
                            
                            <!-- Dropzone Area -->
                            <div class="relative rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700 hover:border-blue-400 dark:hover:border-blue-500 p-8 text-center transition bg-slate-50/50 dark:bg-slate-800/40 hover:bg-slate-50 dark:hover:bg-slate-800/70">
                                <input type="file" name="photo" x-ref="fileInput" @change="previewImage($event)" required accept="image/jpeg,image/png,image/jpg,image/webp"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                
                                <!-- State: No Image -->
                                <div x-show="!photoPreview" class="space-y-2">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center mx-auto shadow-2xs">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div class="text-xs text-slate-700 dark:text-slate-300 font-semibold">
                                        <span class="text-blue-600 dark:text-blue-400 font-bold hover:underline">Klik untuk mengunggah</span> atau seret foto ke sini
                                    </div>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500">Format file: JPG, PNG, atau WEBP (maks. 8MB)</p>
                                </div>

                                <!-- State: Image Preview -->
                                <div x-show="photoPreview" x-cloak class="relative inline-block">
                                    <img :src="photoPreview" alt="Pratinjau" class="max-h-56 rounded-xl object-contain shadow-md mx-auto border border-slate-200 dark:border-slate-700">
                                    <button type="button" @click.stop="removePhoto()" 
                                        class="mt-3 px-3.5 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 hover:text-rose-600 text-slate-700 dark:text-slate-200 text-xs font-bold transition shadow-xs cursor-pointer">
                                        Ganti Foto
                                    </button>
                                </div>
                            </div>

                            @error('photo')
                                <p class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Right Col: Notes & Action -->
                        <div class="space-y-4 lg:border-l lg:border-slate-100 dark:lg:border-slate-800 lg:pl-6">
                            <div>
                                <label class="block text-xs font-bold text-slate-800 dark:text-slate-200 mb-1.5">Catatan Tambahan (Opsional)</label>
                                <textarea name="notes" rows="4" placeholder="Contoh: Tolong buat versi hemat untuk 2 porsi rumahan" 
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 p-2.5 text-xs sm:text-sm text-slate-900 dark:text-white bg-slate-50/50 dark:bg-slate-800 focus:bg-white dark:focus:bg-slate-800 focus:border-blue-500 focus:outline-none"></textarea>
                            </div>

                            <div class="pt-2">
                                <button type="submit" 
                                    class="w-full py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-xs transition-all flex items-center justify-center gap-2 cursor-pointer">
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

    <!-- 4. Recent Recipes Section -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base sm:text-lg font-black text-slate-900 dark:text-white">Riwayat Resep Terakhir</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">Resep yang baru saja Anda hasilkan</p>
            </div>
            <a href="{{ route('recipes.saved') }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">
                Lihat Resep Tersimpan &rarr;
            </a>
        </div>

        @if($recentRecipes->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($recentRecipes as $recipe)
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-5 shadow-xs hover:border-blue-300 dark:hover:border-blue-500 hover:shadow-md transition-all flex flex-col justify-between group">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="text-[10px] font-extrabold uppercase tracking-wider px-2 py-0.5 rounded-md 
                                {{ $recipe->type === 'fcai' ? 'bg-blue-100 dark:bg-blue-950/80 text-blue-800 dark:text-blue-300' : ($recipe->type === 'fcbb' ? 'bg-orange-100 dark:bg-orange-950/80 text-orange-800 dark:text-orange-300' : 'bg-violet-100 dark:bg-violet-950/80 text-violet-800 dark:text-violet-300') }}">
                                {{ $recipe->type_label }}
                            </span>
                            <span class="text-[11px] text-slate-400 dark:text-slate-500">
                                {{ $recipe->created_at->translatedFormat('d M Y') }}
                            </span>
                        </div>

                        <h4 class="font-extrabold text-slate-900 dark:text-white text-sm line-clamp-1 mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                            {{ $recipe->title }}
                        </h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 leading-relaxed">
                            {{ $recipe->description }}
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-xs font-extrabold text-slate-900 dark:text-white bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-lg">
                            {{ $recipe->formatted_cost }}
                        </span>
                        <a href="{{ route('recipes.show', $recipe->id) }}" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
                            <span>Lihat Detail</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-8 text-center max-w-md mx-auto shadow-2xs">
                <img src="{{ asset('images/mascot.png') }}" class="w-12 h-auto mx-auto mb-2 opacity-80" alt="FoodCycle">
                <p class="text-xs text-slate-500 dark:text-slate-400">Belum ada resep yang dibuat. Pilih salah satu metode di atas untuk memulai.</p>
            </div>
        @endif
    </div>
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

            previewImage(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.photoPreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            },

            removePhoto() {
                this.photoPreview = null;
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
