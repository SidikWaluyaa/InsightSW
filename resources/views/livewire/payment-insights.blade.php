<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl md:text-2xl text-slate-800 dark:text-white tracking-tight">
                Pantauan Arus Kas & Omzet
            </h2>
            <div class="flex items-center gap-3">
                @if($lastSyncTime)
                    <div class="flex flex-col items-end hidden md:flex">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-gray-500 uppercase tracking-widest">Update Terakhir</span>
                        <span class="text-xs font-semibold text-slate-600 dark:text-gray-300">{{ \Carbon\Carbon::createFromTimestamp($lastSyncTime)->diffForHumans() }}</span>
                    </div>
                @endif
                <button 
                    x-data="{ 
                        sync() { 
                            Swal.fire({
                                title: 'Menarik Data...',
                                html: 'Mohon tunggu sebentar, sedang sinkronisasi dengan server pusat.',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                didOpen: () => { Swal.showLoading(); }
                            });
                            $wire.syncData()
                        } 
                    }"
                    @click="sync"
                    wire:loading.attr="disabled"
                    class="relative overflow-hidden group px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white rounded-xl shadow-md transition-all active:scale-95 disabled:opacity-70">
                    <div class="relative z-10 flex items-center gap-2">
                        <div wire:loading wire:target="syncData" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                        <svg wire:loading.remove wire:target="syncData" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                        <span class="text-sm font-bold tracking-tight uppercase">Perbarui Data Keuangan</span>
                    </div>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="space-y-8" wire:poll.30s>
        {{-- Analytics Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Daily Revenue Chart --}}
            <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800 dark:text-white uppercase tracking-tight">Omzet Tunai Harian</h3>
                        <p class="text-[10px] text-indigo-500 font-bold uppercase tracking-widest mt-1">
                            Rentang Waktu: {{ \Carbon\Carbon::parse($this->analyticsStartDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($this->analyticsEndDate)->format('d/m/Y') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2 bg-slate-50 dark:bg-gray-800/50 p-2 rounded-2xl border border-gray-100 dark:border-gray-800">
                            <input type="date" wire:model.live="analyticsStartDate" class="bg-transparent border-none focus:ring-0 text-[11px] font-black text-indigo-600 dark:text-indigo-400 w-[120px] cursor-pointer">
                            <span class="text-slate-400 text-xs">—</span>
                            <input type="date" wire:model.live="analyticsEndDate" class="bg-transparent border-none focus:ring-0 text-[11px] font-black text-indigo-600 dark:text-indigo-400 w-[120px] cursor-pointer">
                        </div>
                        <div class="flex items-center gap-2 px-3 hidden xl:flex">
                            <span class="w-3 h-3 bg-indigo-500 rounded-full"></span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Jumlah Terbayar</span>
                        </div>
                    </div>
                </div>
                <div id="revenueChart" wire:ignore style="min-height: 350px;" class="flex items-center justify-center">
                    <div class="w-8 h-8 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                </div>
            </div>

            {{-- Tableau-style Widget --}}
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-800 flex flex-col">
                <div class="mb-6">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white uppercase tracking-tight">Performa Omzet</h3>
                    <p class="text-[10px] text-slate-400 mt-1 uppercase tracking-widest font-bold">Ringkasan harian uang masuk — Gaya Tableau</p>
                </div>
                
                <div class="flex-1 overflow-y-auto space-y-4 pr-2 custom-scrollbar" style="max-height: 350px;">
                    @foreach($this->dailyRevenue->reverse() as $day)
                        @php 
                            $percentage = ($day->total / $this->maxDailyRevenue) * 100;
                            $isHighest = $day->total == $this->maxDailyRevenue;
                        @endphp
                        <div class="group cursor-default">
                            <div class="flex justify-between items-end mb-1.5">
                                <span class="text-[11px] font-black text-slate-500 dark:text-gray-400 uppercase">{{ \Carbon\Carbon::parse($day->date)->translatedFormat('d M Y') }}</span>
                                <span class="text-xs font-bold {{ $isHighest ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-800 dark:text-white' }}">
                                    {{ $this->formatCurrency($day->total) }}
                                </span>
                            </div>
                            <div class="h-2 w-full bg-slate-100 dark:bg-gray-800 rounded-full overflow-hidden relative">
                                <div class="absolute inset-y-0 left-0 bg-gradient-to-r {{ $isHighest ? 'from-indigo-600 to-blue-500' : 'from-slate-400 to-slate-500 dark:from-gray-600 dark:to-gray-500' }} rounded-full transition-all duration-1000 group-hover:opacity-80" 
                                     style="width: {{ $percentage }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                        <span>Rekor Tertinggi</span>
                        <span class="text-indigo-600 dark:text-indigo-400">{{ $this->formatCurrency($this->maxDailyRevenue) }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Combined Tables Section --}}
        <div class="grid grid-cols-1 gap-8">
            {{-- All Transactions (Ledger) --}}
            <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-indigo-50/30 dark:bg-indigo-900/10">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800 dark:text-white uppercase tracking-tight">Riwayat Pembayaran Lengkap</h3>
                        <p class="text-sm text-slate-400">Daftar seluruh catatan pembayaran yang berhasil ditarik.</p>
                    </div>
                    <div class="flex flex-col xl:flex-row items-start xl:items-center gap-4">
                        <div class="flex items-center gap-2">
                            <button wire:click="exportExcel" wire:loading.attr="disabled"
                                class="px-4 py-2.5 bg-emerald-50 text-emerald-600 border border-emerald-200 dark:bg-emerald-900/30 dark:border-emerald-800 dark:text-emerald-400 font-bold text-xs rounded-xl hover:bg-emerald-100 transition-colors flex items-center gap-2 disabled:opacity-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <span wire:loading.remove wire:target="exportExcel">Excel</span>
                                <span wire:loading wire:target="exportExcel">Exporting...</span>
                            </button>
                            <button wire:click="exportPdf" wire:loading.attr="disabled"
                                class="px-4 py-2.5 bg-rose-50 text-rose-600 border border-rose-200 dark:bg-rose-900/30 dark:border-rose-800 dark:text-rose-400 font-bold text-xs rounded-xl hover:bg-rose-100 transition-colors flex items-center gap-2 disabled:opacity-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                <span wire:loading.remove wire:target="exportPdf">PDF</span>
                                <span wire:loading wire:target="exportPdf">Exporting...</span>
                            </button>
                        </div>
                        <div class="flex items-center p-1 bg-white/50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                            <button wire:click="$set('statusFilter', 'all')" 
                                class="px-4 py-2 rounded-lg text-xs font-bold transition-all {{ $statusFilter === 'all' ? 'bg-indigo-500 text-white shadow-md' : 'text-slate-500 hover:text-indigo-500' }}">
                                SEMUA
                            </button>
                            <button wire:click="$set('statusFilter', 'unpaid')" 
                                class="px-4 py-2 rounded-lg text-xs font-bold transition-all {{ $statusFilter === 'unpaid' ? 'bg-rose-500 text-white shadow-md' : 'text-slate-500 hover:text-rose-500' }}">
                                BELUM LUNAS
                            </button>
                            <button wire:click="$set('statusFilter', 'paid')" 
                                class="px-4 py-2 rounded-lg text-xs font-bold transition-all {{ $statusFilter === 'paid' ? 'bg-emerald-500 text-white shadow-md' : 'text-slate-500 hover:text-emerald-500' }}">
                                LUNAS
                            </button>
                        </div>
                        
                        <div class="flex items-center gap-1 bg-white/50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-1">
                            <select wire:model.live="dateFilterType" class="px-2 py-1.5 bg-transparent border-none focus:ring-0 text-xs font-bold text-indigo-600 dark:text-indigo-400 cursor-pointer outline-none w-[110px] appearance-auto">
                                <option value="paid_at">Tgl Bayar</option>
                                <option value="source_created_at">Tgl Origin</option>
                            </select>
                            <div class="w-px h-4 bg-gray-200 dark:bg-gray-700 mx-1"></div>
                            <input type="date" wire:model.live="startDate" class="px-2 py-1.5 bg-transparent border-none focus:ring-0 text-xs font-bold text-slate-600 dark:text-slate-300 w-[110px] cursor-pointer" title="Dari Tanggal">
                            <span class="text-slate-400 font-bold text-xs">-</span>
                            <input type="date" wire:model.live="endDate" class="px-2 py-1.5 bg-transparent border-none focus:ring-0 text-xs font-bold text-slate-600 dark:text-slate-300 w-[110px] cursor-pointer" title="Sampai Tanggal">
                            @if($startDate || $endDate)
                                <button wire:click="$set('startDate', null); $set('endDate', null)" class="px-2 text-slate-400 hover:text-rose-500 transition-colors" title="Clear Date Filter">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            @endif
                        </div>

                        <div class="relative min-w-[300px]">
                            <input wire:model.live.debounce.300ms="search" type="text" 
                                placeholder="Cari SPK / Invoice..."
                                class="w-full pl-10 pr-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-gray-200 transition-all outline-none">
                            <div class="absolute left-3 top-3 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase tracking-widest font-bold text-slate-400 dark:text-gray-500">
                                <th class="px-8 py-4">Waktu Bayar</th>
                                <th class="px-8 py-4">Pesanan / SPK</th>
                                <th class="px-8 py-4">Tipe Bayar</th>
                                <th class="px-8 py-4 text-right">Total Tagihan</th>
                                <th class="px-8 py-4 text-right">Uang Masuk</th>
                                <th class="px-8 py-4 text-right">Sisa Piutang</th>
                                <th class="px-8 py-4 text-center">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 uppercase text-[11px] font-bold">
                            @forelse($payments as $payment)
                            <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-8 py-5 text-slate-500">{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : '-' }}</td>
                                <td class="px-8 py-5">
                                    <div class="flex flex-col">
                                        <span class="text-slate-800 dark:text-white font-extrabold text-xs tracking-tight">{{ $payment->spk_number }}</span>
                                        <span class="text-[10px] text-slate-500 font-semibold uppercase mt-0.5">
                                            {{ $payment->customer_name ?? '-' }} 
                                            @if($payment->customer_phone && $payment->customer_phone !== '-')
                                                <span class="text-slate-400 font-medium">• {{ $payment->customer_phone }}</span>
                                            @endif
                                        </span>
                                        <span class="text-[10px] text-slate-400 tracking-normal font-medium normal-case italic mt-0.5">
                                            Origin: {{ $payment->source_created_at ? $payment->source_created_at->format('d M Y') : '-' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-8 py-5">
                                    @if($payment->payment_type == 'BEFORE')
                                        <span class="px-2 py-0.5 bg-emerald-100 dark:bg-emerald-500/10 text-emerald-600 rounded text-[9px]">DP / AWAL</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-500/10 text-blue-600 rounded text-[9px]">LUNAS / AKHIR</span>
                                    @endif
                                </td>
                                <td class="px-8 py-5 text-right text-slate-400 italic">{{ $this->formatCurrency($payment->total_bill_snapshot) }}</td>
                                <td class="px-8 py-5 text-right text-indigo-600 dark:text-indigo-400">{{ $this->formatCurrency($payment->amount_paid) }}</td>
                                <td class="px-8 py-5 text-right text-slate-500">{{ $this->formatCurrency($payment->balance_snapshot) }}</td>
                                <td class="px-8 py-5 text-center">
                                    <button class="p-2 hover:bg-slate-100 dark:hover:bg-gray-700 rounded-lg transition-colors text-slate-400 hover:text-indigo-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center gap-4 opacity-30">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        <p class="text-sm">TIDAK ADA DATA PEMBAYARAN</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Pagination --}}
                <div class="p-8 bg-gray-50/50 dark:bg-gray-800/10 border-t border-gray-100 dark:border-gray-800">
                    {{ $payments->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- ApexCharts CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            let revenueChart;

            const initChart = (initialData) => {
                const options = {
                    series: [{
                        name: 'Uang Masuk',
                        data: initialData.map(d => d.total)
                    }],
                    chart: {
                        type: 'area',
                        height: 350,
                        toolbar: { show: false },
                        zoom: { enabled: false },
                        background: 'transparent',
                        foreColor: '#94a3b8',
                        animations: { enabled: true, easing: 'easeinout', speed: 800 }
                    },
                    colors: ['#6366f1'],
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] }
                    },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 3 },
                    xaxis: {
                        labels: { show: false },
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: {
                        labels: {
                            formatter: function (val) {
                                if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + 'jt';
                                if (val >= 1000) return 'Rp ' + (val / 1000).toFixed(0) + 'k';
                                return 'Rp ' + val;
                            }
                        }
                    },
                    grid: { borderColor: 'rgba(148, 163, 184, 0.1)', strokeDashArray: 4 },
                    tooltip: { theme: 'dark' }
                };

                revenueChart = new ApexCharts(document.querySelector("#revenueChart"), options);
                revenueChart.render();
            };

            const updateChartData = (newData) => {
                if (!revenueChart) {
                    initChart(newData);
                    return;
                }

                if (!newData || newData.length === 0) {
                    revenueChart.updateSeries([{ name: 'Amount Paid', data: [] }]);
                    return;
                }

                revenueChart.updateOptions({
                    xaxis: {
                        labels: { show: false }
                    }
                });

                revenueChart.updateSeries([{
                    name: 'Uang Masuk',
                    data: newData.map(d => d.total)
                }]);
            };

            // Initial load
            initChart(@json($this->dailyRevenue));
            
            // Listen for custom event from Livewire
            Livewire.on('revenue-data-updated', (event) => {
                const data = Array.isArray(event) ? (event[0].revenueData || []) : (event.revenueData || []);
                updateChartData(data);
            });

            // Extra protection: re-init if element is somehow lost (rare with wire:ignore)
            Livewire.hook('morph.updated', ({ component }) => {
                if (component.name === 'payment-insights' && !document.querySelector("#revenueChart svg")) {
                    updateChartData(@json($this->dailyRevenue));
                }
            });
        });
    </script>
</div>
