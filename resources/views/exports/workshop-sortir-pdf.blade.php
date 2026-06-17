<!DOCTYPE html>
<html>
<head>
    <title>Laporan Data Sortir</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        @page { margin: 1cm; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; table-layout: fixed; }
        th, td { border: 1px solid #e2e8f0; padding: 8px 6px; text-align: left; word-wrap: break-word; vertical-align: top; }
        th { background-color: #4f46e5; color: white; font-weight: bold; text-transform: uppercase; font-size: 8px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header { margin-bottom: 20px; text-align: center; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; color: #1e293b; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #64748b; font-size: 10px; }
        .summary-row { background-color: #eef2ff; }
        .summary-row td { font-weight: bold; font-size: 10px; }
        .status-badge { font-weight: bold; font-size: 8px; padding: 2.5px 5px; border-radius: 4px; display: inline-block; text-align: center; }
        .status-overdue { background-color: #fff5f5; color: #9b1c1c; border: 1px solid #feb2b2; }
        .status-ontrack { background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .status-warn { background-color: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .service-pill { font-size: 7px; font-weight: bold; background-color: #1e293b; color: white; padding: 2px 5px; border-radius: 3px; display: inline-block; margin: 1px 1px; text-transform: uppercase; }
        .spk-num { font-weight: bold; font-family: monospace; font-size: 10px; color: #4f46e5; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Data Sortir (Sedang Disortir)</h1>
        <p>
            Dicetak pada: {{ now()->format('d/m/Y H:i') }} |
            Total Di Sortir: {{ $totalDiSortir }} |
            Stagnan (&gt; 3 Hari): {{ $totalOverdue }} |
            Rerata Durasi: {{ $avgDaysFormatted }} Hari
        </p>
        @if($searchTerm || $selectedStatus || $selectedService || $selectedCategory || $selectedEstimation || $estimationStartDate || $estimationEndDate)
        <p style="font-size: 9px; margin-top: 5px; font-weight: bold;">
            Filter - Pencarian: "{{ $searchTerm ?: 'Semua' }}" |
            Status: "{{ $selectedStatus ?: 'Semua' }}" |
            Jasa: "{{ $selectedService ?: 'Semua' }}" |
            Kategori: "{{ $selectedCategory ?: 'Semua' }}" |
            Estimasi: "{{ $selectedEstimation ?: 'Semua' }}" @if($estimationStartDate && $estimationEndDate) | Rentang: {{ $estimationStartDate }} s/d {{ $estimationEndDate }} @endif
        </p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th width="14%">SPK / Order</th>
                <th width="16%">Pelanggan</th>
                <th width="16%">Detail Sepatu</th>
                <th width="22%">Layanan / Jasa</th>
                <th width="14%">Masuk Sortir</th>
                <th class="text-center" width="10%">Estimasi Selesai</th>
                <th class="text-center" width="8%">Durasi</th>
                <th class="text-center" width="10%">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                @php
                    $isOverdue = $item['is_overdue'] ?? false;
                    $statusText = $isOverdue ? ($item['warning_message'] ?: 'TERTAHAN') : 'ON TRACK';
                    $statusClass = $isOverdue ? 'status-overdue' : 'status-ontrack';
                @endphp
                <tr>
                    {{-- SPK / ORDER --}}
                    <td>
                        <div class="spk-num">{{ $item['spk_number'] ?? '-' }}</div>
                    </td>

                    {{-- PELANGGAN --}}
                    <td>
                        <div style="font-weight: bold; text-transform: uppercase;">{{ $item['customer_name'] ?? '-' }}</div>
                    </td>

                    {{-- DETAIL SEPATU --}}
                    <td>
                        <div style="font-weight: bold; text-transform: uppercase;">{{ $item['shoe_brand'] ?: 'UNKNOWN' }}</div>
                        @if(!empty($item['shoe_type']))
                            <div style="font-size: 8px; color: #64748b; margin-top: 2px;">{{ $item['shoe_type'] }}</div>
                        @endif
                    </td>

                    {{-- LAYANAN / JASA --}}
                    <td>
                        @if(!empty($item['services']))
                            @foreach($item['services'] as $serviceName)
                                <span class="service-pill">{{ $serviceName }}</span>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>

                    {{-- WAKTU MASUK SORTIR --}}
                    <td>
                        {{ $item['entered_sortir_at_formatted'] ?: '-' }}
                    </td>

                    {{-- ESTIMASI SELESAI --}}
                    <td class="text-center">
                        @if($item['has_estimation'])
                            <span class="status-badge status-ontrack">{{ strtoupper($item['estimation_date_formatted']) }}</span>
                        @else
                            <span class="status-badge status-warn">BELUM SET</span>
                        @endif
                    </td>

                    {{-- DURASI SORTIR --}}
                    <td class="text-center font-mono" style="font-weight: bold;">
                        {{ $item['days_in_sortir'] ?? 0 }} HARI
                    </td>

                    {{-- STATUS / WARNING --}}
                    <td class="text-center">
                        <span class="status-badge {{ $statusClass }}">{{ strtoupper($statusText) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #94a3b8; font-weight: bold;">
                        Tidak ada data sortir ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Summary Footer --}}
    @if(count($items) > 0)
    <table style="margin-top: 15px; width: 40%; margin-left: auto;">
        <tr class="summary-row">
            <td style="font-size: 9px; text-transform: uppercase; color: #64748b;">Total Item</td>
            <td class="text-right" style="font-family: monospace; color: #4f46e5; font-size: 12px;">{{ $totalDiSortir }}</td>
        </tr>
        <tr class="summary-row">
            <td style="font-size: 9px; text-transform: uppercase; color: #64748b;">Total Stagnan (&gt; 3 Hari)</td>
            <td class="text-right" style="font-family: monospace; color: #dc2626; font-size: 12px;">{{ $totalOverdue }}</td>
        </tr>
    </table>
    @endif

    <div style="margin-top: 30px; font-size: 8px; color: #94a3b8; text-align: center;">
        Laporan ini digenerate secara otomatis oleh sistem Algoritma Insight.
    </div>
</body>
</html>
