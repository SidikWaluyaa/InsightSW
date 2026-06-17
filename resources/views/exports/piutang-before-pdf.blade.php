<!DOCTYPE html>
<html>
<head>
    <title>Laporan Piutang Before</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        @page { margin: 1cm; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; table-layout: fixed; }
        th, td { border: 1px solid #e2e8f0; padding: 8px 6px; text-align: left; word-wrap: break-word; vertical-align: top; }
        th { background-color: #d97706; color: white; font-weight: bold; text-transform: uppercase; font-size: 8px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header { margin-bottom: 20px; text-align: center; border-bottom: 2px solid #d97706; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; color: #1e293b; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #64748b; font-size: 10px; }
        .summary-row { background-color: #fffbeb; }
        .summary-row td { font-weight: bold; font-size: 10px; }
        .status-badge { font-weight: bold; font-size: 8px; padding: 2.5px 5px; border-radius: 4px; display: inline-block; text-align: center; }
        .status-belum { background-color: #fff5f5; color: #9b1c1c; border: 1px solid #feb2b2; }
        .status-sebagian { background-color: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .status-lunas { background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .service-pill { font-size: 7px; font-weight: bold; background-color: #1e293b; color: white; padding: 2px 5px; border-radius: 3px; display: inline-block; margin: 1px 1px; text-transform: uppercase; }
        .amount-outstanding { color: #d97706; font-weight: bold; font-family: monospace; }
        .amount-paid { color: #64748b; font-size: 8px; }
        .invoice-num { color: #059669; font-weight: bold; font-family: monospace; font-size: 10px; }
        .spk-num { font-size: 8px; color: #94a3b8; font-family: monospace; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Piutang Before (Outstanding)</h1>
        <p>
            Dicetak pada: {{ now()->format('d/m/Y H:i') }} |
            Total Invoice: {{ count($items) }} |
            Total Outstanding: Rp {{ number_format($totalOutstanding, 0, ',', '.') }} |
            Total Terbayar: Rp {{ number_format($totalPaid, 0, ',', '.') }}
        </p>
        @if($searchTerm || $selectedStatus)
        <p style="font-size: 9px; margin-top: 5px; font-weight: bold;">
            Filter - Pencarian: "{{ $searchTerm ?: 'Semua' }}" |
            Status: "{{ $selectedStatus ?: 'Semua' }}"
        </p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th width="14%">Invoice / SPK</th>
                <th width="14%">Pelanggan</th>
                <th width="18%">Detail Sepatu</th>
                <th width="22%">Layanan / Jasa</th>
                <th class="text-right" width="14%">Outstanding</th>
                <th class="text-center" width="10%">Status</th>
                <th class="text-center" width="8%">Status SPK</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                @php
                    $statusText = strtoupper($item['financials']['status'] ?? '');
                    $statusClass = 'status-belum';
                    if ($statusText === 'LUNAS') {
                        $statusClass = 'status-lunas';
                    } elseif ($statusText === 'BAYAR SEBAGIAN') {
                        $statusClass = 'status-sebagian';
                    }

                    // Collect all services across work orders
                    $allServices = [];
                    if (!empty($item['work_orders'])) {
                        foreach ($item['work_orders'] as $wo) {
                            if (!empty($wo['services'])) {
                                foreach ($wo['services'] as $svc) {
                                    $allServices[] = $svc['name'] ?? '';
                                }
                            }
                        }
                    }
                @endphp
                <tr>
                    {{-- INVOICE / SPK --}}
                    <td>
                        <div class="invoice-num">{{ $item['invoice_number'] ?? '-' }}</div>
                        @if(!empty($item['work_orders']))
                            @foreach($item['work_orders'] as $wo)
                                <div class="spk-num">{{ $wo['spk_number'] ?? '' }}</div>
                            @endforeach
                        @endif
                    </td>

                    {{-- PELANGGAN --}}
                    <td>
                        <div style="font-weight: bold; text-transform: uppercase;">{{ $item['customer']['name'] ?? '-' }}</div>
                        <div style="font-size: 8px; color: #64748b; margin-top: 2px; font-family: monospace;">{{ $item['customer']['phone'] ?? '-' }}</div>
                    </td>

                    {{-- DETAIL SEPATU --}}
                    <td>
                        @if(!empty($item['work_orders']))
                            @foreach($item['work_orders'] as $wo)
                                <div style="margin-bottom: 4px;">
                                    <div style="font-weight: bold; text-transform: uppercase;">{{ $wo['shoe']['brand'] ?? '' }} {{ $wo['shoe']['type'] ?? '' }}</div>
                                    <div style="font-size: 8px; color: #64748b;">
                                        {{ !empty($wo['shoe']['color']) ? strtoupper($wo['shoe']['color']) : 'WARNA N/A' }} • SIZE {{ $wo['shoe']['size'] ?: '-' }}
                                    </div>
                                </div>
                            @endforeach
                        @else
                            -
                        @endif
                    </td>

                    {{-- LAYANAN / JASA --}}
                    <td>
                        @forelse($allServices as $serviceName)
                            <span class="service-pill">{{ $serviceName }}</span>
                        @empty
                            -
                        @endforelse
                    </td>

                    {{-- OUTSTANDING --}}
                    <td class="text-right">
                        <div class="amount-outstanding">Rp {{ number_format($item['financials']['remaining_balance'] ?? 0, 0, ',', '.') }}</div>
                        <div class="amount-paid">Lunas: Rp {{ number_format($item['financials']['paid_amount'] ?? 0, 0, ',', '.') }}</div>
                    </td>

                    {{-- STATUS --}}
                    <td class="text-center">
                        <span class="status-badge {{ $statusClass }}">{{ $statusText ?: 'UNKNOWN' }}</span>
                    </td>

                    {{-- STATUS SPK --}}
                    <td class="text-center" style="font-size: 8px; font-weight: bold;">
                        {{ strtoupper($item['spk_status'] ?? '-') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #94a3b8; font-weight: bold;">
                        Tidak ada data piutang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Summary Footer --}}
    @if(count($items) > 0)
    <table style="margin-top: 15px; width: 40%; margin-left: auto;">
        <tr class="summary-row">
            <td style="font-size: 9px; text-transform: uppercase; color: #64748b;">Total Outstanding</td>
            <td class="text-right" style="font-family: monospace; color: #d97706; font-size: 12px;">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</td>
        </tr>
        <tr class="summary-row">
            <td style="font-size: 9px; text-transform: uppercase; color: #64748b;">Total Terbayar</td>
            <td class="text-right" style="font-family: monospace; color: #059669; font-size: 12px;">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td>
        </tr>
    </table>
    @endif

    <div style="margin-top: 30px; font-size: 8px; color: #94a3b8; text-align: center;">
        Laporan ini digenerate secara otomatis oleh sistem Algoritma Insight.
    </div>
</body>
</html>
