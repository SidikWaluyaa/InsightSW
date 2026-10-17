@props(['kpi', 'scope' => null])

{{-- 4 KPI efisiensi biaya iklan (PRD halaman 4) dengan lampu MoM. Data: MetaAdsReportService::kpis(). --}}
@php
    $c = $kpi['cards'];
    $tones = ['hijau' => 'emerald', 'kuning' => 'amber', 'merah' => 'rose'];
    $momBadge = fn ($k) => $k['perubahan'] === null ? null : ($k['perubahan'] >= 0 ? '▲ ' : '▼ ') . number_format(abs($k['perubahan']), 1, ',', '.') . '% vs bulan lalu';
    $momTone = fn ($k) => $tones[$k['status']] ?? 'slate';
    $rp = fn ($v) => $v === null ? '–' : 'Rp ' . number_format($v, 0, ',', '.');
@endphp
<div class="space-y-2">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-ui.stat-card label="Ad Spend (Total)" :value="$rp($c['spend']['nilai'])" icon="cash" compact
            :badge="$momBadge($c['spend'])" badge-tone="slate"
            :note="'Budget harian: ± ' . $rp($c['spend']['nilai'] / max($kpi['days'], 1)) . ' (tanpa pajak). Bulan lalu: ' . $rp($c['spend']['lalu']) . '.'" />
        <x-ui.stat-card label="Chat Results (CTWA)" :value="number_format($c['results']['nilai'], 0, ',', '.')" unit="chat" icon="chat"
            :badge="$momBadge($c['results'])" :badge-tone="$momTone($c['results'])"
            :note="'Prospek WA yang mulai chat dari iklan. Bulan lalu: ' . number_format($c['results']['lalu'], 0, ',', '.') . '.'" />
        <x-ui.stat-card label="Cost per Result" :value="$rp($c['cpr']['nilai'])" unit="/ chat" icon="cursor-click" compact
            :badge="$momBadge($c['cpr'])" :badge-tone="$momTone($c['cpr'])"
            :note="'Ad spend ÷ chat results. Turun = lebih baik. Bulan lalu: ' . $rp($c['cpr']['lalu']) . '.'" />
        <x-ui.stat-card label="Click-Through Rate (CTR)" :value="$c['ctr']['nilai'] === null ? '–' : number_format($c['ctr']['nilai'], 2, ',', '.') . '%'" icon="eye"
            :badge="$momBadge($c['ctr'])" :badge-tone="$momTone($c['ctr'])"
            :note="'Klik link ÷ tayangan. Bulan lalu: ' . ($c['ctr']['lalu'] === null ? '–' : number_format($c['ctr']['lalu'], 2, ',', '.') . '%') . '.'" />
    </div>
    <p class="text-[11px] text-slate-400 dark:text-gray-500">
        Lampu membandingkan dengan {{ \Carbon\Carbon::parse($kpi['prevRange'][0])->translatedFormat('j M') }} – {{ \Carbon\Carbon::parse($kpi['prevRange'][1])->translatedFormat('j M Y') }}@if($scope), {{ $scope }}@endif.
        Ad spend tidak diberi lampu (bukan baik atau buruk); ad spend dan chat dibandingkan per hari.
    </p>
</div>
