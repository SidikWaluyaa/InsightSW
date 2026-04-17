{{-- 2. TOP KPI CARDS (6 Grid) --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
    @php
        $kpis = [
            ['label' => 'Sedang Proses', 'value' => $this->metrics->in_progress, 'color' => 'indigo', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z'],
            ['label' => 'Selesai', 'value' => $this->metrics->throughput, 'color' => 'emerald', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'Mendesak', 'value' => $this->metrics->urgent, 'color' => 'amber', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
            ['label' => 'Terlambat', 'value' => $this->metrics->overdue, 'color' => 'rose', 'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['label' => 'Lulus QC', 'value' => number_format($this->metrics->qc_pass_rate, 1).'%', 'color' => 'blue', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
            ['label' => 'Pendapatan', 'value' => $this->formatCurrency($this->metrics->total_revenue), 'color' => 'slate', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'is_price' => true],
        ];
    @endphp

    @foreach($kpis as $kpi)
        <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm hover:shadow-xl hover:shadow-{{ $kpi['color'] }}-500/5 transition-all group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-{{ $kpi['color'] }}-500/10 flex items-center justify-center text-{{ $kpi['color'] }}-500 group-hover:scale-110 group-hover:bg-{{ $kpi['color'] }}-500 group-hover:text-white transition-all duration-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $kpi['icon'] }}" /></svg>
                </div>
                <div class="w-1.5 h-1.5 rounded-full bg-slate-200"></div>
            </div>
            <h3 class="{{ isset($kpi['is_price']) ? 'text-lg' : 'text-3xl' }} font-black text-slate-800 dark:text-white tracking-tight">{{ $kpi['value'] }}</h3>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">{{ $kpi['label'] }}</p>
        </div>
    @endforeach
</div>
