<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-xl md:text-2xl text-slate-800 dark:text-white tracking-tight">
                Payment Insights
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
                        <span class="text-sm font-bold tracking-tight">SYNC REALTIME</span>
                    </div>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="space-y-8" wire:poll.30s>
        {{-- Charts Row --}}
        <div class="grid grid-cols-1 gap-8">
            {{-- Daily Revenue Chart --}}
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="flex items-center justify-between mb-8">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Daily Cash Revenue (Last 14 Days)</h3>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 bg-indigo-500 rounded-full"></span>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Amount Paid</span>
                    </div>
                </div>
                <div id="revenueChart" style="min-height: 350px;"></div>
            </div>
        </div>

        {{-- Combined Tables Section --}}
        <div class="grid grid-cols-1 gap-8">
            {{-- All Transactions (Ledger) --}}
            <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="p-8 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-indigo-50/30 dark:bg-indigo-900/10">
                    <div>
                        <h3 class="font-bold text-lg text-slate-800 dark:text-white">Semua Riwayat Pembayaran</h3>
                        <p class="text-sm text-slate-400">Daftar lengkap seluruh transaksi yang berhasil ditarik</p>
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
                        
                        <div class="flex items-center gap-2 bg-white/50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-1">
                            <input type="date" wire:model.live="startDate" class="px-3 py-1.5 bg-transparent border-none focus:ring-0 text-xs font-bold text-slate-600 dark:text-slate-300 w-[120px] cursor-pointer" title="Dari Tanggal">
                            <span class="text-slate-400 font-bold text-xs">-</span>
                            <input type="date" wire:model.live="endDate" class="px-3 py-1.5 bg-transparent border-none focus:ring-0 text-xs font-bold text-slate-600 dark:text-slate-300 w-[120px] cursor-pointer" title="Sampai Tanggal">
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
                                <th class="px-8 py-4">Tanggal Bayar</th>
                                <th class="px-8 py-4">SPK/Invoice</th>
                                <th class="px-8 py-4">Tipe</th>
                                <th class="px-8 py-4 text-right">Total Tagihan</th>
                                <th class="px-8 py-4 text-right">Tunai Masuk</th>
                                <th class="px-8 py-4 text-right">Sisa Balance</th>
                                <th class="px-8 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 uppercase text-[11px] font-bold">
                            @forelse($payments as $payment)
                            <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-8 py-5 text-slate-500">{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : '-' }}</td>
                                <td class="px-8 py-5">
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-2">
                                            <span class="text-slate-800 dark:text-white">{{ $payment->spk_number }}</span>
                                        </div>
                                        <span class="text-[10px] text-slate-500 font-medium">
                                            {{ $payment->customer_name ?? '-' }} 
                                            @if($payment->customer_phone && $payment->customer_phone !== '-')
                                                <span class="text-slate-400">• {{ $payment->customer_phone }}</span>
                                            @endif
                                        </span>
                                        <span class="text-[9px] text-slate-400 tracking-normal font-medium normal-case italic">Origin: {{ $payment->source_created_at ? $payment->source_created_at->format('M Y') : '-' }}</span>
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

            const updateCharts = () => {
                const revenueData = @json($this->dailyRevenue);

                // Revenue Chart
                const revenueOptions = {
                    series: [{
                        name: 'Amount Paid',
                        data: revenueData.map(d => d.total).reverse()
                    }],
                    chart: {
                        type: 'area',
                        height: 350,
                        toolbar: { show: false },
                        zoom: { enabled: false },
                        background: 'transparent',
                        foreColor: '#94a3b8'
                    },
                    colors: ['#6366f1'],
                    fill: {
                        type: 'gradient',
                        gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] }
                    },
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 3 },
                    xaxis: {
                        categories: revenueData.map(d => {
                            const date = new Date(d.date);
                            return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
                        }).reverse(),
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: {
                        labels: {
                            formatter: function (val) {
                                if (val >= 1000000) return 'Rp ' + (val / 1000000).toFixed(1) + 'jt';
                                return 'Rp ' + (val / 1000).toFixed(0) + 'k';
                            }
                        }
                    },
                    grid: { borderColor: 'rgba(148, 163, 184, 0.1)', strokeDashArray: 4 },
                    theme: { mode: document.documentElement.classList.contains('dark') ? 'dark' : 'light' }
                };

                if (revenueChart) revenueChart.destroy();

                const revenueEl = document.querySelector("#revenueChart");
                
                if (revenueEl) {
                    revenueChart = new ApexCharts(revenueEl, revenueOptions);
                    revenueChart.render();
                }
            };

            updateCharts();
            
            // Re-render on livewire updates
            Livewire.on('post-sync', () => {
                setTimeout(updateCharts, 50);
            });
        });
    </script>
</div>
