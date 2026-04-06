<div class="px-6 py-6 pb-20" wire:poll.60s>

    {{-- ═══════════════════════════════════════════════════════════════
         HEADER & FILTER BAR
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="mb-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6 bg-white dark:bg-slate-900 rounded-[40px] p-8 border border-gray-100 dark:border-gray-800 shadow-xl shadow-slate-200/40 dark:shadow-none">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-3xl bg-gradient-to-tr from-teal-600 to-emerald-500 flex items-center justify-center text-white shadow-lg shadow-emerald-500/30 group">
                <svg class="w-8 h-8 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight uppercase leading-none">CX Upsell</h1>
                    <span class="px-2 py-1 rounded-lg bg-emerald-500 text-white text-[10px] font-black tracking-[0.2em] animate-pulse">LIVE</span>
                </div>
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest mt-2 flex items-center gap-2">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Pantau Pendapatan Tambah Jasa & Penawaran Langsung (OTO)
                </p>
            </div>
        </div>

        <div class="flex flex-col items-end gap-4">
            {{-- Quick Preset Buttons --}}
            <div class="flex flex-wrap items-center gap-2">
                @php $presets = [
                    ['key' => 'hari_ini', 'label' => 'Hari Ini', 'icon' => '📌'],
                    ['key' => 'kemarin', 'label' => 'Kemarin', 'icon' => '◀'],
                    ['key' => '7_hari', 'label' => '7 Hari', 'icon' => '📅'],
                    ['key' => '30_hari', 'label' => '30 Hari', 'icon' => '📆'],
                    ['key' => 'bulan_ini', 'label' => 'Bulan Ini', 'icon' => '🗓'],
                    ['key' => 'bulan_lalu', 'label' => 'Bulan Lalu', 'icon' => '⏪'],
                    ['key' => 'kuartal_ini', 'label' => 'Kuartal', 'icon' => '📊'],
                    ['key' => 'tahun_ini', 'label' => 'Tahun Ini', 'icon' => '🏢'],
                ]; @endphp

                @foreach($presets as $p)
                    <button wire:click="setPreset('{{ $p['key'] }}')"
                        class="px-3.5 py-2 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all duration-300
                        {{ $activePreset === $p['key']
                            ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/25 scale-105'
                            : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-600 dark:hover:text-emerald-400 border border-transparent hover:border-emerald-500/20' }}">
                        <span class="mr-1">{{ $p['icon'] }}</span> {{ $p['label'] }}
                    </button>
                @endforeach
            </div>

            {{-- Custom Date Range --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-800/50 p-2.5 rounded-[32px] border border-gray-100 dark:border-gray-800">
                    <div class="flex items-center px-4 gap-3">
                        <input type="date" wire:model="startDate" class="bg-transparent border-none text-xs font-black text-slate-800 dark:text-white focus:ring-0 p-0 w-32 cursor-pointer">
                        <span class="text-slate-300 dark:text-slate-700 font-black">—</span>
                        <input type="date" wire:model="endDate" class="bg-transparent border-none text-xs font-black text-slate-800 dark:text-white focus:ring-0 p-0 w-32 cursor-pointer">
                    </div>
                    <button wire:click="applyFilter" wire:loading.attr="disabled"
                        class="px-8 py-3 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-black uppercase tracking-widest transition-all duration-300 flex items-center gap-3 shadow-lg shadow-emerald-500/25">
                        <span wire:loading.remove wire:target="applyFilter, setPreset">TERAPKAN</span>
                        <span wire:loading wire:target="applyFilter, setPreset" class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            MEMUAT...
                        </span>
                    </button>
                </div>
                <button wire:click="refreshData"
                    class="w-14 h-14 rounded-[24px] bg-white dark:bg-slate-800 border border-gray-100 dark:border-gray-700 text-slate-500 hover:text-emerald-600 hover:border-emerald-200 transition-all shadow-sm flex items-center justify-center group"
                    title="Muat ulang data dari server">
                    <svg class="w-6 h-6 group-hover:rotate-180 transition-transform duration-700 {{ $isLoading ? 'animate-spin' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Error Banner --}}
    @if($errorMessage)
        <div class="mb-10 p-6 rounded-[32px] bg-rose-500/10 border border-rose-500/20 flex items-center gap-5">
            <div class="w-12 h-12 rounded-2xl bg-rose-500 flex items-center justify-center text-white shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <p class="text-sm font-black text-rose-600 uppercase tracking-widest">{{ $errorMessage }}</p>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
         SECTION 1: STATUS ISU PELANGGAN
         Menampilkan jumlah keluhan/isu dari pelanggan dan seberapa
         cepat tim CX menanganinya.
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="mb-12">
        <div class="flex items-end gap-4 mb-8 px-2">
            <div>
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.4em] flex items-center gap-4">
                    <span class="h-1 w-12 bg-emerald-500 rounded-full"></span>
                    Status Isu Pelanggan
                </h2>
                <p class="text-[11px] text-slate-400/70 mt-1.5 ml-16">Berapa banyak keluhan yang masuk dan sudah ditangani oleh tim CX</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
            {{-- Total Isu --}}
            <div class="bg-gradient-to-tr from-slate-800 to-slate-700 dark:from-slate-800 dark:to-slate-700 rounded-[32px] p-7 text-white relative overflow-hidden group shadow-xl shadow-slate-800/20">
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                <p class="text-[11px] font-black uppercase tracking-widest text-slate-400 mb-1 leading-none">Semua Isu</p>
                <p class="text-[10px] text-slate-500 mb-3">Total keluhan yang masuk</p>
                <h3 class="text-4xl font-black tracking-tighter leading-none">{{ $kpi['total'] ?? 0 }}</h3>
            </div>

            {{-- Isu Terbuka --}}
            <div class="bg-white dark:bg-slate-900 rounded-[32px] p-7 border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden group">
                <div class="absolute top-4 right-4 w-3 h-3 rounded-full bg-rose-500 animate-pulse"></div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1 leading-none">Belum Ditangani</p>
                <p class="text-[10px] text-slate-400/60 mb-3">Perlu segera diproses</p>
                <h3 class="text-3xl font-black text-rose-500 tracking-tighter leading-none">{{ $kpi['open'] ?? 0 }}</h3>
            </div>

            {{-- Terselesaikan --}}
            <div class="bg-white dark:bg-slate-900 rounded-[32px] p-7 border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden group">
                <div class="absolute top-4 right-4 w-3 h-3 rounded-full bg-emerald-500"></div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1 leading-none">Selesai</p>
                <p class="text-[10px] text-slate-400/60 mb-3">Sudah berhasil ditangani</p>
                <h3 class="text-3xl font-black text-emerald-500 tracking-tighter leading-none">{{ $kpi['resolved'] ?? 0 }}</h3>
            </div>

            {{-- Dibatalkan --}}
            <div class="bg-white dark:bg-slate-900 rounded-[32px] p-7 border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden group">
                <div class="absolute top-4 right-4 w-3 h-3 rounded-full bg-slate-400"></div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1 leading-none">Dibatalkan</p>
                <p class="text-[10px] text-slate-400/60 mb-3">Isu yang tidak dilanjutkan</p>
                <h3 class="text-3xl font-black text-slate-500 tracking-tighter leading-none">{{ $kpi['cancelled'] ?? 0 }}</h3>
            </div>

            {{-- Tingkat Penyelesaian --}}
            <div class="bg-emerald-600 rounded-[32px] p-7 text-white relative overflow-hidden group shadow-xl shadow-emerald-500/20">
                <div class="absolute -right-4 -bottom-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                <p class="text-[11px] font-black uppercase tracking-widest text-emerald-100/60 mb-1 leading-none">Penyelesaian</p>
                <p class="text-[10px] text-emerald-100/40 mb-3">Persentase isu yang selesai</p>
                <h3 class="text-4xl font-black tracking-tighter leading-none">{{ $kpi['resolution_rate'] ?? 0 }}<span class="text-lg">%</span></h3>
                <div class="mt-3 h-1.5 w-full bg-white/20 rounded-full overflow-hidden">
                    <div class="h-full bg-white rounded-full transition-all duration-1000" style="width: {{ min($kpi['resolution_rate'] ?? 0, 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         SECTION 2: PENDAPATAN TAMBAH JASA
         Pendapatan dari jasa tambahan yang ditawarkan tim CX
         kepada pelanggan saat proses servis berlangsung.
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="mb-12">
        <div class="flex items-end gap-4 mb-8 px-2">
            <div>
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.4em] flex items-center gap-4">
                    <span class="h-1 w-12 bg-indigo-500 rounded-full"></span>
                    Pendapatan Tambah Jasa
                </h2>
                <p class="text-[11px] text-slate-400/70 mt-1.5 ml-16">Pendapatan dari jasa tambahan yang ditawarkan CS saat proses servis berjalan</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Total Pendapatan Jasa --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-16 h-16 text-indigo-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Pendapatan</p>
                <p class="text-[10px] text-slate-400/60 mb-3">Seluruh pemasukan dari layanan tambahan</p>
                <h3 class="text-2xl font-black text-indigo-600 dark:text-indigo-400 leading-none">Rp {{ number_format($summary['total_nominal'] ?? 0, 0, ',', '.') }}</h3>
                <div class="mt-4 flex items-center gap-2">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400 font-mono tracking-tight">{{ $summary['total_volume'] ?? 0 }} SPK terlayani</span>
                </div>
            </div>

            {{-- Rata-rata Per SPK --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-16 h-16 text-violet-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" /><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9z" clip-rule="evenodd" /></svg>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-0.5">Rata-rata per SPK</p>
                <p class="text-[10px] text-slate-400/60 mb-3">Pendapatan rata-rata setiap order (ARPU)</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white leading-none">Rp {{ number_format($summary['combined_arpu'] ?? 0, 0, ',', '.') }}</h3>
                <div class="mt-4">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-violet-100 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400 font-mono tracking-tight">Per Order Rata-Rata</span>
                </div>
            </div>

            {{-- Jumlah Kategori --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-16 h-16 text-teal-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-0.5">Variasi Jasa</p>
                <p class="text-[10px] text-slate-400/60 mb-3">Jumlah jenis layanan yang ditawarkan</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white leading-none">{{ count($categories) }} <span class="text-sm font-bold text-slate-400">Kategori</span></h3>
                <div class="mt-4 flex -space-x-1.5">
                    @php $catColors = ['bg-indigo-500','bg-emerald-500','bg-amber-500','bg-rose-500','bg-violet-500']; @endphp
                    @foreach(array_slice(array_keys($categories), 0, 5) as $idx => $cat)
                        <div class="w-5 h-5 rounded-full {{ $catColors[$idx % count($catColors)] }} border-2 border-white dark:border-slate-900" title="{{ $cat }}"></div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         SECTION 3: PENDAPATAN OTO (ONE-TIME OFFER)
         Pendapatan dari penawaran langsung / kampanye yang diberikan
         kepada pelanggan di luar jasa servis standar.
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="mb-12">
        <div class="flex items-end gap-4 mb-8 px-2">
            <div>
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.4em] flex items-center gap-4">
                    <span class="h-1 w-12 bg-orange-500 rounded-full"></span>
                    Pendapatan OTO (Penawaran Langsung)
                </h2>
                <p class="text-[11px] text-slate-400/70 mt-1.5 ml-16">Pendapatan dari penawaran langsung / promo khusus yang diterima pelanggan</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {{-- Total Pendapatan OTO --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-16 h-16 text-orange-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-0.5">Total Pendapatan</p>
                <p class="text-[10px] text-slate-400/60 mb-3">Seluruh pemasukan dari penawaran langsung</p>
                <h3 class="text-2xl font-black text-orange-600 dark:text-orange-400 leading-none">Rp {{ number_format($otoSummary['total_nominal'] ?? 0, 0, ',', '.') }}</h3>
                <div class="mt-4 flex items-center gap-2">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-orange-100 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400 font-mono tracking-tight">{{ $otoSummary['total_volume'] ?? 0 }} SPK terlayani</span>
                </div>
            </div>

            {{-- Rata-rata Per SPK OTO --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-16 h-16 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" /><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9z" clip-rule="evenodd" /></svg>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-0.5">Rata-rata per SPK</p>
                <p class="text-[10px] text-slate-400/60 mb-3">Pendapatan rata-rata setiap order OTO</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white leading-none">Rp {{ number_format($otoSummary['combined_arpu'] ?? 0, 0, ',', '.') }}</h3>
                <div class="mt-4">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400 font-mono tracking-tight">Per Order Rata-Rata</span>
                </div>
            </div>

            {{-- Jumlah Kategori OTO --}}
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group">
                <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                    <svg class="w-16 h-16 text-orange-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                </div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-0.5">Variasi OTO</p>
                <p class="text-[10px] text-slate-400/60 mb-3">Jumlah jenis penawaran yang tersedia</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white leading-none">{{ count($otoCategories) }} <span class="text-sm font-bold text-slate-400">Kategori</span></h3>
                @if(($otoSummary['total_nominal'] ?? 0) == 0)
                    <p class="mt-3 text-[10px] text-slate-400/50 italic">Belum ada transaksi OTO di periode ini</p>
                @endif
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         SECTION 4: GRAFIK PERBANDINGAN
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="mb-12">
        <div class="flex items-end gap-4 mb-8 px-2">
            <div>
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.4em] flex items-center gap-4">
                    <span class="h-1 w-12 bg-violet-500 rounded-full"></span>
                    Grafik & Visualisasi
                </h2>
                <p class="text-[11px] text-slate-400/70 mt-1.5 ml-16">Perbandingan visual antara sumber pendapatan dan sebaran jenis layanan</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            {{-- Perbandingan Pendapatan: Bar Chart --}}
            <div class="lg:col-span-3 bg-white dark:bg-slate-900 rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                <div class="p-8 pb-2">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight">Perbandingan Pendapatan</h3>
                            <p class="text-[10px] text-slate-400/70 mt-1">Kontribusi pendapatan dari masing-masing sumber upsell</p>
                        </div>
                        <span class="text-[10px] font-bold px-3 py-1.5 rounded-full bg-violet-100 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400 uppercase tracking-tighter">Grafik Batang</span>
                    </div>
                </div>
                <div class="px-8 pb-8" style="height: 320px;">
                    <canvas id="revenueBarChart"></canvas>
                </div>
            </div>

            {{-- Komposisi Kategori: Donut --}}
            <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden flex flex-col">
                <div class="p-8 pb-2">
                    <h3 class="text-base font-black text-slate-800 dark:text-white uppercase tracking-tight text-center">Sebaran Jenis Layanan</h3>
                    <p class="text-[10px] text-slate-400/70 mt-1 text-center">Proporsi layanan yang paling sering ditawarkan</p>
                </div>
                <div class="flex-1 flex items-center justify-center px-8 pb-4 relative" style="min-height: 240px;">
                    <canvas id="categoryDonut"></canvas>
                    @php $totalAllItems = count($upsellItems) + count($otoItems); @endphp
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-4xl font-black text-slate-800 dark:text-white leading-none">{{ $totalAllItems }}</span>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter mt-1">TOTAL ITEM</span>
                    </div>
                </div>
                {{-- Legend --}}
                <div class="px-8 pb-8 flex flex-wrap justify-center gap-x-5 gap-y-2">
                    @php
                        $chartColors = ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899'];
                        $allCats = collect($categories)->union(collect($otoCategories))->toArray();
                    @endphp
                    @foreach($allCats as $cat => $count)
                        <div class="flex items-center gap-1.5">
                            <div class="w-2.5 h-2.5 rounded-full" style="background: {{ $chartColors[$loop->index % count($chartColors)] }}"></div>
                            <span class="text-[10px] font-bold text-slate-500 tracking-tighter">{{ $cat ?: 'Lainnya' }} ({{ $count }})</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         SECTION 5: TABEL RINCIAN — TAMBAH JASA
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="mb-12">
        <div class="flex items-end gap-4 mb-8 px-2">
            <div>
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.4em] flex items-center gap-4">
                    <span class="h-1 w-12 bg-indigo-500 rounded-full"></span>
                    Rincian Transaksi — Tambah Jasa
                </h2>
                <p class="text-[11px] text-slate-400/70 mt-1.5 ml-16">Daftar lengkap setiap transaksi jasa tambahan beserta nominal pendapatan</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-2 h-8 rounded-full bg-indigo-500"></span>
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Tambah Jasa</h3>
                </div>
                <span class="text-xs font-bold text-slate-400">{{ count($upsellItems) }} transaksi</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">No. SPK</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama Jasa</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Kategori</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        @forelse($upsellItems as $item)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-black text-slate-900 dark:text-white group-hover:text-indigo-500 transition-colors tracking-tight">{{ data_get($item, 'work_order.spk_number') ?? data_get($item, 'spk_number', '-') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-slate-500 uppercase tracking-tighter">
                                    {{ data_get($item, 'work_order.customer_name', '-') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-tight">
                                    {{ data_get($item, 'custom_service_name', '-') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter border bg-indigo-500/10 text-indigo-500 border-indigo-500/20">
                                        {{ data_get($item, 'category_name', '-') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-emerald-600 dark:text-emerald-400 tabular-nums">
                                    Rp {{ number_format(data_get($item, 'total_revenue', 0), 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="w-12 h-12 text-slate-200 dark:text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                        <p class="text-slate-400 text-sm italic">Tidak ada transaksi Tambah Jasa di periode ini</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         SECTION 6: TABEL RINCIAN — OTO
    ═══════════════════════════════════════════════════════════════ --}}
    <div class="mb-12">
        <div class="flex items-end gap-4 mb-8 px-2">
            <div>
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-[0.4em] flex items-center gap-4">
                    <span class="h-1 w-12 bg-orange-500 rounded-full"></span>
                    Rincian Transaksi — OTO (Penawaran Langsung)
                </h2>
                <p class="text-[11px] text-slate-400/70 mt-1.5 ml-16">Daftar lengkap penawaran langsung / promo khusus beserta nominal pendapatan</p>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-[32px] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="w-2 h-8 rounded-full bg-orange-500"></span>
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">OTO (Penawaran Langsung)</h3>
                </div>
                <span class="text-xs font-bold text-slate-400">{{ count($otoItems) }} transaksi</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-separate border-spacing-0">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-800/50">
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">No. SPK</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama Penawaran</th>
                            <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Kategori</th>
                            <th class="px-6 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        @forelse($otoItems as $item)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-black text-slate-900 dark:text-white group-hover:text-orange-500 transition-colors tracking-tight">{{ data_get($item, 'work_order.spk_number') ?? data_get($item, 'spk_number', '-') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-slate-500 uppercase tracking-tighter">
                                    {{ data_get($item, 'work_order.customer_name', '-') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-tight">
                                    {{ data_get($item, 'custom_service_name', '-') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-tighter border bg-orange-500/10 text-orange-500 border-orange-500/20">
                                        {{ data_get($item, 'category_name', '-') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-black text-emerald-600 dark:text-emerald-400 tabular-nums">
                                    Rp {{ number_format(data_get($item, 'total_revenue', 0), 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="w-12 h-12 text-slate-200 dark:text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                        <p class="text-slate-400 text-sm italic">Tidak ada transaksi OTO di periode ini</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         CHART.JS ENGINE
    ═══════════════════════════════════════════════════════════════ --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Render pertama kali
            renderAllCharts();

            // Render ulang saat data berubah (event dari backend)
            Livewire.on('analytics-updated', (eventData) => {
                const data = Array.isArray(eventData) ? eventData[0] : eventData;
                renderAllCharts(data.barChartData, data.donutChartData);
            });
        });

        function renderAllCharts(barData = null, donutData = null) {
            const palette = ['#6366f1','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899'];
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(148,163,184,0.08)' : 'rgba(148,163,184,0.15)';
            const textColor = isDark ? '#94a3b8' : '#64748b';

            // ── Perbandingan Pendapatan (Horizontal Bar) ──
            const barCtx = document.getElementById('revenueBarChart');
            if (barCtx) {
                if (window.__barChart) window.__barChart.destroy();

                let labels, jasaRev, otoRev;

                if (barData) {
                    labels = barData.labels;
                    // PHP maps arrays as associative, JS handles them as objects. Map to array based on labels.
                    jasaRev = labels.map(l => barData.jasaRev[l] || 0);
                    otoRev = labels.map(l => barData.otoRev[l] || 0);
                } else {
                    // Initial load fallback
                    const initialJasa = @json($upsellItems);
                    const initialOto = @json($otoItems);
                    labels = [...new Set([...initialJasa.map(i=>i.category_name), ...initialOto.map(i=>i.category_name)])].filter(Boolean);
                    jasaRev = labels.map(l => initialJasa.filter(i=>i.category_name===l).reduce((a,b)=>a+parseFloat(b.total_revenue||0),0));
                    otoRev = labels.map(l => initialOto.filter(i=>i.category_name===l).reduce((a,b)=>a+parseFloat(b.total_revenue||0),0));
                }

                window.__barChart = new Chart(barCtx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Tambah Jasa',
                                data: jasaRev,
                                backgroundColor: 'rgba(99, 102, 241, 0.7)',
                                borderColor: '#6366f1',
                                borderWidth: 2,
                                borderRadius: 8,
                                borderSkipped: false,
                                barPercentage: 0.6,
                                categoryPercentage: 0.7,
                            },
                            {
                                label: 'OTO',
                                data: otoRev,
                                backgroundColor: 'rgba(249, 115, 22, 0.7)',
                                borderColor: '#f97316',
                                borderWidth: 2,
                                borderRadius: 8,
                                borderSkipped: false,
                                barPercentage: 0.6,
                                categoryPercentage: 0.7,
                            }
                        ]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                align: 'end',
                                labels: { color: textColor, font: { weight: 'bold', size: 11 }, padding: 16 }
                            },
                            tooltip: {
                                backgroundColor: isDark ? '#1e293b' : '#ffffff',
                                titleColor: isDark ? '#f8fafc' : '#0f172a',
                                bodyColor: isDark ? '#94a3b8' : '#64748b',
                                borderColor: isDark ? '#334155' : '#e2e8f0',
                                borderWidth: 1,
                                cornerRadius: 12,
                                padding: 14,
                                callbacks: { label: ctx => ctx.dataset.label + ': Rp ' + ctx.parsed.x.toLocaleString('id-ID') }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: gridColor, drawBorder: false },
                                ticks: {
                                    color: textColor,
                                    font: { weight: 'bold', size: 10 },
                                    callback: v => v >= 1000000 ? 'Rp' + (v/1000000).toFixed(1) + 'jt' : v >= 1000 ? 'Rp' + (v/1000) + 'rb' : 'Rp' + v
                                }
                            },
                            y: { grid: { display: false }, ticks: { color: textColor, font: { weight: '800', size: 11 } } }
                        }
                    }
                });
            }

            // ── Donut: Sebaran Jenis Layanan ──
            const donutCtx = document.getElementById('categoryDonut');
            if (donutCtx) {
                if (window.__donutChart) window.__donutChart.destroy();

                let labels, counts;
                if (donutData) {
                    labels = donutData.labels;
                    counts = donutData.counts;
                } else {
                    const jasaCats = @json($categories);
                    const otoCats = @json($otoCategories);
                    const merged = {...jasaCats};
                    Object.entries(otoCats).forEach(([k, v]) => { merged[k] = (merged[k] || 0) + v; });
                    labels = Object.keys(merged);
                    counts = Object.values(merged);
                }

                window.__donutChart = new Chart(donutCtx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: counts,
                            backgroundColor: labels.map((_, i) => palette[i % palette.length]),
                            borderWidth: 0,
                            cutout: '78%',
                            borderRadius: 6,
                            spacing: 3,
                            hoverOffset: 12,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: isDark ? '#1e293b' : '#ffffff',
                                titleColor: isDark ? '#f8fafc' : '#0f172a',
                                bodyColor: isDark ? '#94a3b8' : '#64748b',
                                borderColor: isDark ? '#334155' : '#e2e8f0',
                                borderWidth: 1,
                                cornerRadius: 12,
                                padding: 14,
                                callbacks: {
                                    label: ctx => ctx.label + ': ' + ctx.parsed + ' item (' + Math.round(ctx.parsed / counts.reduce((a,b)=>a+b,0) * 100) + '%)'
                                }
                            }
                        }
                    }
                });
            }
        }
    </script>

    {{-- Loading Overlay --}}
    <div wire:loading wire:target="applyFilter, refreshData" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/30 backdrop-blur-sm transition-all duration-300">
        <div class="bg-white dark:bg-slate-900 rounded-[32px] p-8 border border-gray-100 dark:border-gray-800 shadow-2xl flex items-center gap-5">
            <svg class="animate-spin w-8 h-8 text-emerald-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <div>
                <p class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-widest">Memuat Data...</p>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Sinkronisasi dengan API Master</p>
            </div>
        </div>
    </div>
</div>
