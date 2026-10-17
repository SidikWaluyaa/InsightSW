<div>
    <div class="space-y-4 mb-8">
        <x-ui.page-header title="Dashboard Finance" subtitle="Tagihan, pembayaran, dan kas masuk langsung dari sistem pusat." badge="Live API">
            <x-ui.range-toolbar :start="$startDate" :end="$endDate" :max="now()->toDateString()" :presets="$this->datePresets()" refresh="refreshData" />
        </x-ui.page-header>

        <x-ui.range-info :start="$startDate" :end="$endDate"
            :updated="$metadata && isset($metadata['last_updated']) ? \Carbon\Carbon::parse($metadata['last_updated'])->diffForHumans() : null" />
    </div>

    <div class="space-y-8" wire:init="loadData">
        @if (!$isLoaded)
            {{-- Skeleton Loaders for entire page --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 animate-pulse">
                <div class="h-32 bg-slate-200/50 dark:bg-slate-700/30 rounded-3xl border border-slate-100 dark:border-slate-800"></div>
                <div class="h-32 bg-slate-200/50 dark:bg-slate-700/30 rounded-3xl border border-slate-100 dark:border-slate-800"></div>
                <div class="h-32 bg-slate-200/50 dark:bg-slate-700/30 rounded-3xl border border-slate-100 dark:border-slate-800"></div>
                <div class="h-32 bg-slate-200/50 dark:bg-slate-700/30 rounded-3xl border border-slate-100 dark:border-slate-800"></div>
            </div>
            <div class="h-[250px] bg-slate-200/50 dark:bg-slate-700/30 rounded-3xl border border-slate-100 dark:border-slate-800 animate-pulse"></div>
            <div class="h-[400px] bg-slate-200/50 dark:bg-slate-700/30 rounded-3xl border border-slate-100 dark:border-slate-800 animate-pulse"></div>
        @elseif ($errorMessage)
            {{-- Alert error --}}
            <div class="p-6 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900 rounded-3xl flex items-start gap-4">
                <div class="p-3 bg-rose-500/10 text-rose-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-rose-800 dark:text-rose-400">Gagal Memuat Data</h3>
                    <p class="text-sm text-rose-600 dark:text-rose-500 mt-1">Sistem gagal berkomunikasi dengan API Keuangan: {{ $errorMessage }}</p>
                    <button wire:click="refreshData" class="mt-3 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl transition-all">
                        Coba Lagi
                    </button>
                </div>
            </div>
        @else
            {{-- ================= FINANCE & DATA HEALTH (PRD halaman 6) ================= --}}
            @php
                $rp = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
                $alertCount = $redAlerts === null ? null : count($redAlerts);
                $alertTypes = [
                    'siap_kirim_belum_dp' => ['label' => 'Siap Kirim Belum DP', 'aksi' => 'Tahan kirim', 'tone' => 'text-rose-600 dark:text-rose-400'],
                    'biodata_tanpa_spk' => ['label' => 'Biodata Tanpa SPK', 'aksi' => 'Buat SPK', 'tone' => 'text-amber-600 dark:text-amber-400'],
                ];
            @endphp
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Finance & Data Reconciliation</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400">Status kesehatan kas, peringatan dini kebocoran kas, dan audit verifikasi data.</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-500 dark:text-gray-400">Jadwal audit: tanggal 10, 20, dan akhir bulan</span>
                    <x-ui.btn variant="secondary" icon="refresh" disabled title="Menyusul: sumber mutasi bank & proses audit belum tersedia">Audit Sekarang</x-ui.btn>
                </div>
            </div>

            {{-- Alur 1: status kesehatan kas & akurasi --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                <x-ui.stat-card label="Total Kas Masuk" :value="$rp($metrics['total_cash_received'] ?? 0)" icon="cash" tone="blue" compact
                    note="Pembayaran tervalidasi Finance (transfer bank manual BCA/Mandiri/kasir) pada periode." />
                <x-ui.stat-card label="Total Piutang / Unpaid" :value="$rp($metrics['total_outstanding_receivables'] ?? 0)" icon="clock" tone="rose" compact
                    note="Sisa tagihan yang menunggu pelunasan." />
                <x-ui.stat-card label="Matching Rate Data" value="Belum dihitung" icon="check-circle" compact badge="Toleransi ≥ 98%" badge-tone="slate"
                    note="Mutasi bank vs SPK: (1 − Selisih ÷ Total Transaksi) × 100%. Datanya belum tersedia (Tim Data)." />
                <x-ui.stat-card label="Red Alerts (Anomali)" :value="$alertCount === null ? 'Menyusul' : number_format($alertCount, 0, ',', '.') . ' kasus'" icon="warning"
                    :tone="$alertCount ? 'rose' : 'slate'" compact
                    :badge="$alertCount ? 'Butuh tindakan segera' : null" badge-tone="rose"
                    :note="$alertCount === null ? 'Menunggu data anomali dari sistem pusat.' : 'Siap kirim belum DP dan biodata tanpa SPK.'" />
                <x-ui.stat-card label="Total Nilai Tagihan" :value="$rp($metrics['total_invoiced_value'] ?? 0)" icon="clipboard-list" tone="emerald" compact
                    :note="'Nilai tagihan periode ' . \Carbon\Carbon::parse($period['start'] ?? now())->translatedFormat('d M') . ' – ' . \Carbon\Carbon::parse($period['end'] ?? now())->translatedFormat('d M Y') . '.'" />
                <x-ui.stat-card label="Rasio Penagihan (Collection)" :value="number_format((float) ($metrics['collection_rate'] ?? 0), 2, ',', '.') . '%'" icon="chart" tone="amber"
                    note="Kas masuk ÷ nilai tagihan periode. Bisa di atas 100% karena pelunasan tagihan periode sebelumnya ikut masuk." />
            </div>

            {{-- Alur 2: panel peringatan dini kebocoran kas (red alerts) --}}
            <section class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Panel Peringatan Dini Kebocoran Kas</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400">SPK siap kirim yang tagihannya belum dibayar, dan biodata customer yang belum dibuatkan SPK.</p>
                </div>
                @if($redAlerts === null)
                    <p class="px-6 py-10 text-sm text-center text-amber-600 dark:text-amber-400">Menunggu data anomali dari sistem pusat (field baru).</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase tracking-widest font-bold text-slate-400 dark:text-gray-500">
                                    <th class="px-4 py-3 whitespace-nowrap">Tipe Anomali</th>
                                    <th class="px-4 py-3 whitespace-nowrap">No SPK / Invoice</th>
                                    <th class="px-4 py-3">Customer</th>
                                    <th class="px-4 py-3 whitespace-nowrap">CS Handler</th>
                                    <th class="px-4 py-3">Masalah & Risiko</th>
                                    <th class="px-4 py-3 whitespace-nowrap">Status Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($redAlerts as $i => $alert)
                                    @php $type = $alertTypes[$alert['tipe'] ?? ''] ?? ['label' => $alert['tipe'] ?? '-', 'aksi' => '-', 'tone' => 'text-slate-600']; @endphp
                                    <tr wire:key="alert-{{ $i }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                        <td class="px-4 py-3 font-bold whitespace-nowrap {{ $type['tone'] }}">{{ $type['label'] }}</td>
                                        <td class="px-4 py-3 font-mono text-xs text-slate-700 dark:text-gray-200 whitespace-nowrap">{{ $alert['nomor'] ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-700 dark:text-gray-200">{{ $alert['customer_name'] ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-gray-300 whitespace-nowrap">{{ $alert['cs_name'] ?? '-' }}</td>
                                        <td class="px-4 py-3 text-slate-600 dark:text-gray-300">{{ $alert['masalah'] ?? '-' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap"><span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase bg-gray-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300">{{ $type['aksi'] }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="px-6 py-10 text-center text-sm text-emerald-600 dark:text-emerald-400">Tidak ada anomali pada periode ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            {{-- Alur 3: log audit verifikasi 3 kali sebulan --}}
            <section class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Log Audit Verifikasi 3 Kali Sebulan</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400">Pencocokan transaksi sistem dengan mutasi bank tiap tanggal 10, 20, dan akhir bulan ({{ \Carbon\Carbon::parse($auditSchedule[0]['tanggal'])->translatedFormat('F Y') }}). Hasil audit belum bisa dihitung: sumber mutasi bank & proses audit belum tersedia.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase tracking-widest font-bold text-slate-400 dark:text-gray-500">
                                <th class="px-4 py-3 whitespace-nowrap">Periode Audit</th>
                                <th class="px-4 py-3 whitespace-nowrap">Tanggal Eksekusi</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap">Total Transaksi</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap">Data Cocok</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap">Selisih / Typo</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap">Akurasi (%)</th>
                                <th class="px-4 py-3 whitespace-nowrap">Disetujui Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($auditSchedule as $audit)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                    <td class="px-4 py-3 font-bold text-slate-800 dark:text-white whitespace-nowrap">{{ $audit['label'] }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap {{ $audit['lewat'] ? 'text-amber-600 dark:text-amber-400' : 'text-slate-500 dark:text-gray-400' }}">
                                        {{ $audit['lewat'] ? $audit['tanggal']->translatedFormat('d M Y') . ' · belum diaudit' : 'Standby (' . $audit['tanggal']->translatedFormat('d M') . ')' }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-slate-400">–</td>
                                    <td class="px-4 py-3 text-right text-slate-400">–</td>
                                    <td class="px-4 py-3 text-right text-slate-400">–</td>
                                    <td class="px-4 py-3 text-right text-slate-400">–</td>
                                    <td class="px-4 py-3 text-slate-400">–</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Distribusi Status Tagihan --}}
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 border border-gray-100 dark:border-gray-800 shadow-sm">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white uppercase tracking-tight mb-6">
                    Distribusi Status Tagihan
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Belum Bayar -->
                    <div class="p-6 bg-slate-50 dark:bg-gray-800/40 rounded-2xl border border-gray-100 dark:border-gray-800/60 relative overflow-hidden group">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-2.5 h-2.5 bg-slate-400 dark:bg-slate-500 rounded-full"></div>
                            <div>
                                <h4 class="text-xs font-black text-slate-500 uppercase tracking-widest">Belum Bayar</h4>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                    {{ $statusBreakdown['belum_bayar']['count'] ?? 0 }} Transaksi
                                </span>
                            </div>
                        </div>
                        <div class="flex justify-between items-end mb-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase">Total Nominal</span>
                            <span class="text-sm font-black text-slate-700 dark:text-slate-200">
                                Rp {{ number_format($statusBreakdown['belum_bayar']['total_amount'] ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="h-2 w-full bg-slate-200/50 dark:bg-slate-800 rounded-full overflow-hidden">
                            @php
                                $totalAll = ($statusBreakdown['belum_bayar']['total_amount'] ?? 0) + 
                                            ($statusBreakdown['dp_cicil']['total_amount'] ?? 0) + 
                                            ($statusBreakdown['lunas']['total_amount'] ?? 0);
                                $bbPercentage = $totalAll > 0 ? (($statusBreakdown['belum_bayar']['total_amount'] ?? 0) / $totalAll) * 100 : 0;
                            @endphp
                            <div class="h-full bg-slate-400 dark:bg-slate-500 transition-all duration-1000" style="width: {{ $bbPercentage }}%"></div>
                        </div>
                    </div>

                    <!-- DP/Cicil -->
                    <div class="p-6 bg-amber-50/25 dark:bg-amber-950/10 rounded-2xl border border-amber-100/30 dark:border-amber-900/10 relative overflow-hidden group">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-2.5 h-2.5 bg-amber-500 rounded-full"></div>
                            <div>
                                <h4 class="text-xs font-black text-amber-500 uppercase tracking-widest">DP / Cicil</h4>
                                <span class="text-[10px] font-bold text-amber-400/80 uppercase tracking-wider">
                                    {{ $statusBreakdown['dp_cicil']['count'] ?? 0 }} Transaksi
                                </span>
                            </div>
                        </div>
                        <div class="flex justify-between items-end mb-2">
                            <span class="text-[10px] font-bold text-amber-400 uppercase">Total Nominal</span>
                            <span class="text-sm font-black text-slate-700 dark:text-slate-200">
                                Rp {{ number_format($statusBreakdown['dp_cicil']['total_amount'] ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="h-2 w-full bg-slate-200/50 dark:bg-slate-800 rounded-full overflow-hidden">
                            @php
                                $dpPercentage = $totalAll > 0 ? (($statusBreakdown['dp_cicil']['total_amount'] ?? 0) / $totalAll) * 100 : 0;
                            @endphp
                            <div class="h-full bg-amber-500 transition-all duration-1000" style="width: {{ $dpPercentage }}%"></div>
                        </div>
                    </div>

                    <!-- Lunas -->
                    <div class="p-6 bg-emerald-50/25 dark:bg-emerald-950/10 rounded-2xl border border-emerald-100/30 dark:border-emerald-900/10 relative overflow-hidden group">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></div>
                            <div>
                                <h4 class="text-xs font-black text-emerald-500 uppercase tracking-widest">Lunas</h4>
                                <span class="text-[10px] font-bold text-emerald-400/80 uppercase tracking-wider">
                                    {{ $statusBreakdown['lunas']['count'] ?? 0 }} Transaksi
                                </span>
                            </div>
                        </div>
                        <div class="flex justify-between items-end mb-2">
                            <span class="text-[10px] font-bold text-emerald-400 uppercase">Total Nominal</span>
                            <span class="text-sm font-black text-emerald-600 dark:text-emerald-400">
                                Rp {{ number_format($statusBreakdown['lunas']['total_amount'] ?? 0, 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="h-2 w-full bg-slate-200/50 dark:bg-slate-800 rounded-full overflow-hidden">
                            @php
                                $lunasPercentage = $totalAll > 0 ? (($statusBreakdown['lunas']['total_amount'] ?? 0) / $totalAll) * 100 : 0;
                            @endphp
                            <div class="h-full bg-emerald-500 transition-all duration-1000" style="width: {{ $lunasPercentage }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Distribusi Type Pembayaran --}}
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 border border-gray-100 dark:border-gray-800 shadow-sm">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white uppercase tracking-tight mb-6">
                    Distribusi Tipe Pembayaran
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                    @foreach($paymentTypeDistribution as $key => $dist)
                        <div class="relative overflow-hidden bg-slate-50 dark:bg-gray-800/40 rounded-2xl border border-gray-100 dark:border-gray-800/60 p-5 group flex flex-col justify-between min-h-[160px] transition-all hover:scale-[1.02] duration-300">
                            <!-- Top Info & Badge -->
                            <div class="flex justify-between items-start w-full">
                                <div class="p-2.5 rounded-xl {{ $dist['icon_class'] }}">
                                    @if($dist['icon'] === 'clock')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    @elseif($dist['icon'] === 'check-circle')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    @elseif($dist['icon'] === 'plus')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    @elseif($dist['icon'] === 'lightning-bolt')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                    @elseif($dist['icon'] === 'gift')
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                    @endif
                                </div>
                                <span class="text-[9px] font-black tracking-widest px-2.5 py-0.5 rounded-full {{ $dist['badge'] }}">
                                    {{ $dist['badge_text'] }}
                                </span>
                            </div>

                            <!-- Content -->
                            <div class="mt-4">
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest">{{ $dist['label'] }}</p>
                                <h4 class="text-lg font-black text-slate-800 dark:text-white mt-0.5 tracking-tight">
                                    Rp {{ number_format($dist['total'], 0, ',', '.') }}
                                </h4>
                                <span class="text-[9px] font-semibold text-slate-500 uppercase mt-1 block">
                                    {{ $dist['count'] }} Transaksi
                                </span>
                            </div>

                            <!-- Bottom colored indicator line -->
                            <div class="absolute bottom-0 left-0 right-0 h-1 {{ $dist['color'] }}"></div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Data List Section --}}
            <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex flex-col xl:flex-row xl:items-center justify-between gap-6 bg-slate-50/40 dark:bg-gray-800/10">
                    {{-- Tabs Toggle --}}
                    <div class="flex p-1 bg-slate-100 dark:bg-gray-800 rounded-2xl border border-gray-200/50 dark:border-gray-700 w-fit shrink-0">
                        <button 
                            wire:click="$set('activeTab', 'invoices')"
                            class="flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-black tracking-wider transition-all {{ $activeTab === 'invoices' ? 'bg-white dark:bg-gray-900 text-indigo-600 dark:text-indigo-400 shadow-md' : 'text-slate-500 hover:text-slate-800 dark:hover:text-gray-200' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>DATA INVOICES ({{ $totalInvoices }})</span>
                        </button>
                        <button 
                            wire:click="$set('activeTab', 'payments')"
                            class="flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-black tracking-wider transition-all {{ $activeTab === 'payments' ? 'bg-white dark:bg-gray-900 text-indigo-600 dark:text-indigo-400 shadow-md' : 'text-slate-500 hover:text-slate-800 dark:hover:text-gray-200' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>DATA PEMBAYARAN ({{ $totalPayments }})</span>
                        </button>
                    </div>

                    {{-- Filters & Search --}}
                    <div class="flex flex-col md:flex-row items-stretch md:items-center gap-4 w-full xl:w-auto">
                        @if($activeTab === 'invoices')
                            <div class="flex items-center gap-2 bg-white dark:bg-gray-800 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Filter Status:</span>
                                <select wire:model.live="statusFilter" class="bg-transparent border-none focus:ring-0 text-xs font-bold text-indigo-600 dark:text-indigo-400 cursor-pointer outline-none w-[120px] appearance-auto p-0 dark:bg-gray-800">
                                    <option value="all">Semua Status</option>
                                    <option value="BB">Belum Bayar</option>
                                    <option value="BL">DP / Cicil</option>
                                    <option value="L">Lunas</option>
                                </select>
                            </div>
                        @endif

                        @if($activeTab === 'payments')
                            <div class="flex items-center gap-2 bg-white dark:bg-gray-800 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest whitespace-nowrap">Tipe Bayar:</span>
                                <select wire:model.live="paymentTypeFilter" class="bg-transparent border-none focus:ring-0 text-xs font-bold text-indigo-600 dark:text-indigo-400 cursor-pointer outline-none w-[130px] appearance-auto p-0 dark:bg-gray-800">
                                    <option value="all">Semua Tipe</option>
                                    <option value="BEFORE">DP Awal</option>
                                    <option value="AFTER">Pelunasan</option>
                                    <option value="TAMBAH_JASA">Tambah Jasa</option>
                                    <option value="LUNAS_AWAL">Lunas Awal</option>
                                    <option value="ONGKIR">Ongkir</option>
                                </select>
                            </div>
                        @endif

                        <div class="relative flex-1 md:min-w-[320px]">
                            <input wire:model.live.debounce.300ms="search" type="text" 
                                placeholder="Cari nomor invoice / nama customer..."
                                class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-gray-200 transition-all outline-none">
                            <div class="absolute left-3 top-3.5 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <a href="{{ route('finance-live.export-pdf', ['type' => $activeTab, 'startDate' => $startDate, 'endDate' => $endDate, 'search' => $search, 'statusFilter' => $statusFilter, 'paymentTypeFilter' => $paymentTypeFilter]) }}"
                           target="_blank"
                           class="flex items-center justify-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-xs font-black tracking-wider transition-all shadow-md active:scale-95 whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>CETAK PDF</span>
                        </a>
                    </div>
                </div>

                {{-- Table view --}}
                <div class="overflow-x-auto">
                    @if ($activeTab === 'invoices')
                        <!-- Table: Invoices -->
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase tracking-widest font-bold text-slate-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-800">
                                    <th class="px-6 py-4 text-center">#</th>
                                    <th class="px-6 py-4">Nomor Invoice</th>
                                    <th class="px-6 py-4">Customer</th>
                                    <th class="px-6 py-4 text-right">Total</th>
                                    <th class="px-6 py-4 text-right">Ongkir</th>
                                    <th class="px-6 py-4 text-right">Diskon</th>
                                    <th class="px-6 py-4 text-right">Terbayar</th>
                                    <th class="px-6 py-4 text-right">Sisa</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-center">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 uppercase text-[11px] font-bold">
                                @forelse($invoices as $index => $invoice)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/30 transition-colors">
                                        <td class="px-6 py-5 text-center text-slate-400 font-mono">
                                            {{ ($invoicePage - 1) * $perPage + $index + 1 }}
                                        </td>
                                        <td class="px-6 py-5">
                                            <span class="text-slate-800 dark:text-white font-extrabold text-xs tracking-tight">
                                                {{ $invoice['spk_number'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-slate-600 dark:text-gray-300">
                                            {{ $invoice['customer_name'] }}
                                        </td>
                                        <td class="px-6 py-5 text-right text-slate-800 dark:text-slate-200 font-mono">
                                            Rp {{ number_format($invoice['total_bill'] ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-5 text-right text-slate-500 font-mono">
                                            Rp {{ number_format($invoice['shipping_cost'] ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-5 text-right text-rose-400 font-mono">
                                            Rp {{ number_format($invoice['discount'] ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-5 text-right text-emerald-500 font-mono">
                                            Rp {{ number_format($invoice['amount_paid'] ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-5 text-right text-rose-500 font-extrabold text-xs font-mono">
                                            Rp {{ number_format($invoice['remaining_balance'] ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            @php
                                                $status = $invoice['status_pembayaran'] ?? 'BB';
                                                $badgeClass = match($status) {
                                                    'L' => 'bg-emerald-100 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20',
                                                    'BL', 'DP', 'C' => 'bg-amber-100 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20',
                                                    default => 'bg-rose-100 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20'
                                                };
                                                $statusText = match($status) {
                                                    'L' => 'Lunas',
                                                    'BL', 'DP', 'C' => 'DP/Cicil',
                                                    default => 'Belum Bayar'
                                                };
                                            @endphp
                                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-extrabold uppercase {{ $badgeClass }}">
                                                {{ $statusText }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-center text-slate-500 font-mono">
                                            {{ $invoice['created_at'] ? \Carbon\Carbon::parse($invoice['created_at'])->translatedFormat('d M Y') : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-8 py-20 text-center">
                                            <div class="flex flex-col items-center gap-4 opacity-30">
                                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="text-sm">Tidak ada data invoice ditemukan.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @else
                        <!-- Table: Payments -->
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase tracking-widest font-bold text-slate-400 dark:text-gray-500 border-b border-gray-100 dark:border-gray-800">
                                    <th class="px-6 py-4 text-center">#</th>
                                    <th class="px-6 py-4">Nomor Invoice</th>
                                    <th class="px-6 py-4">Customer</th>
                                    <th class="px-6 py-4 text-right">Jumlah Bayar</th>
                                    <th class="px-6 py-4 text-center">Tipe Pembayaran</th>
                                    <th class="px-6 py-4">Catatan</th>
                                    <th class="px-6 py-4 text-center">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 uppercase text-[11px] font-bold">
                                @forelse($payments as $index => $payment)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/30 transition-colors">
                                        <td class="px-6 py-5 text-center text-slate-400 font-mono">
                                            {{ ($paymentPage - 1) * $perPage + $index + 1 }}
                                        </td>
                                        <td class="px-6 py-5">
                                            <span class="text-slate-800 dark:text-white font-extrabold text-xs tracking-tight">
                                                {{ $payment['invoice_number'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-slate-600 dark:text-gray-300">
                                            {{ $payment['customer_name'] }}
                                        </td>
                                        <td class="px-6 py-5 text-right text-emerald-600 dark:text-emerald-400 font-extrabold text-xs font-mono">
                                            Rp {{ number_format($payment['amount_paid'] ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-5 text-center">
                                            @php
                                                $type = strtoupper($payment['payment_type'] ?? 'BEFORE');
                                                $typeLabel = match($type) {
                                                    'BEFORE' => 'DP AWAL',
                                                    'AFTER' => 'PELUNASAN',
                                                    'TAMBAH_JASA' => 'TAMBAH JASA',
                                                    'LUNAS_AWAL' => 'LUNAS AWAL',
                                                    'ONGKIR' => 'ONGKIR',
                                                    default => $type
                                                };
                                                $badgeClass = match($type) {
                                                    'BEFORE' => 'bg-blue-500/10 text-blue-500 border border-blue-500/20',
                                                    'AFTER' => 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20',
                                                    'TAMBAH_JASA' => 'bg-indigo-500/10 text-indigo-500 border border-indigo-500/20',
                                                    'LUNAS_AWAL' => 'bg-amber-500/10 text-amber-500 border border-amber-500/20',
                                                    'ONGKIR' => 'bg-rose-500/10 text-rose-500 border border-rose-500/20',
                                                    default => 'bg-slate-500/10 text-slate-500 border border-slate-500/20'
                                                };
                                            @endphp
                                            <span class="px-2.5 py-0.5 rounded-full text-[9px] font-extrabold uppercase {{ $badgeClass }}">
                                                {{ $typeLabel }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-slate-500 normal-case italic font-medium">
                                            {{ $payment['notes'] ?: ($payment['pic_name'] ? 'By ' . $payment['pic_name'] : '-') }}
                                        </td>
                                        <td class="px-6 py-5 text-center text-slate-500 font-mono">
                                            {{ $payment['paid_at'] ? \Carbon\Carbon::parse($payment['paid_at'])->translatedFormat('d M Y') : '-' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-8 py-20 text-center">
                                            <div class="flex flex-col items-center gap-4 opacity-30">
                                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="text-sm">Tidak ada data pembayaran ditemukan.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    @endif
                </div>

                {{-- Pagination Controls --}}
                @if ($activeTab === 'invoices')
                    @if ($totalInvoicePages > 1)
                        <div class="px-8 py-6 bg-slate-50/40 dark:bg-gray-800/10 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">
                                Menampilkan halaman <span class="font-black text-indigo-600 dark:text-indigo-400 font-mono">{{ $invoicePage }}</span> dari <span class="font-black text-slate-700 dark:text-slate-200 font-mono">{{ $totalInvoicePages }}</span> (Total {{ $totalInvoices }} data)
                            </span>
                            <div class="flex items-center gap-2">
                                <button 
                                    wire:click="prevInvoicePage" 
                                    @if($invoicePage <= 1) disabled @endif
                                    class="px-4 py-2 bg-white dark:bg-gray-800 text-xs font-black tracking-wider uppercase border border-gray-200 dark:border-gray-700 rounded-xl text-slate-700 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">
                                    Sebelumnya
                                </button>
                                <button 
                                    wire:click="nextInvoicePage" 
                                    @if($invoicePage >= $totalInvoicePages) disabled @endif
                                    class="px-4 py-2 bg-white dark:bg-gray-800 text-xs font-black tracking-wider uppercase border border-gray-200 dark:border-gray-700 rounded-xl text-slate-700 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">
                                    Selanjutnya
                                </button>
                            </div>
                        </div>
                    @endif
                @else
                    @if ($totalPaymentPages > 1)
                        <div class="px-8 py-6 bg-slate-50/40 dark:bg-gray-800/10 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                            <span class="text-xs text-slate-500 dark:text-slate-400 font-bold">
                                Menampilkan halaman <span class="font-black text-indigo-600 dark:text-indigo-400 font-mono">{{ $paymentPage }}</span> dari <span class="font-black text-slate-700 dark:text-slate-200 font-mono">{{ $totalPaymentPages }}</span> (Total {{ $totalPayments }} data)
                            </span>
                            <div class="flex items-center gap-2">
                                <button 
                                    wire:click="prevPaymentPage" 
                                    @if($paymentPage <= 1) disabled @endif
                                    class="px-4 py-2 bg-white dark:bg-gray-800 text-xs font-black tracking-wider uppercase border border-gray-200 dark:border-gray-700 rounded-xl text-slate-700 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">
                                    Sebelumnya
                                </button>
                                <button 
                                    wire:click="nextPaymentPage" 
                                    @if($paymentPage >= $totalPaymentPages) disabled @endif
                                    class="px-4 py-2 bg-white dark:bg-gray-800 text-xs font-black tracking-wider uppercase border border-gray-200 dark:border-gray-700 rounded-xl text-slate-700 dark:text-gray-300 hover:bg-slate-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">
                                    Selanjutnya
                                </button>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        @endif
    </div>

</div>
