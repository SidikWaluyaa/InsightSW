<!DOCTYPE html>
<html>
<head>
    <title>Finance Dashboard Export</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        @page { margin: 1cm; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; table-layout: fixed; }
        th, td { border: 1px solid #e2e8f0; padding: 8px 6px; text-align: left; word-wrap: break-word; }
        th { background-color: #4F46E5; color: white; font-weight: bold; text-transform: uppercase; font-size: 8px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header { margin-bottom: 20px; text-align: center; border-bottom: 2px solid #4F46E5; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; color: #1e293b; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #64748b; font-size: 10px; }
        .footer-totals { background-color: #f8fafc; font-weight: bold; }
        .status-badge { font-weight: bold; font-size: 8px; padding: 2px 4px; border-radius: 3px; display: inline-block; }
        .status-lunas { color: #059669; }
        .status-partial { color: #d97706; }
        .status-unpaid { color: #dc2626; }
        .currency { font-family: monospace; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Keuangan (Finance Dashboard)</h1>
        <p>
            Dicetak pada: {{ now()->format('d/m/Y H:i') }} | 
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        </p>
        @if($search || $statusFilter)
        <p>
            Filter - Pencarian: {{ $search ?: 'Semua' }} | 
            Status: {{ $statusFilter ?: 'Semua' }}
        </p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th width="12%">Tgl SPK</th>
                <th width="14%">No SPK</th>
                <th width="18%">Customer</th>
                <th width="10%">Status</th>
                <th class="text-right" width="12%">Tagihan</th>
                <th class="text-right" width="10%">Ongkir</th>
                <th class="text-right" width="12%">Terbayar</th>
                <th class="text-right" width="12%">Piutang</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalTagihan = 0; 
                $totalOngkir = 0; 
                $totalTerbayar = 0; 
                $totalPiutang = 0; 
            @endphp
            @forelse($transactions as $trx)
                @php 
                    $netTagihan = $trx->total_bill - $trx->shipping_cost;
                    $totalTagihan += $netTagihan;
                    $totalOngkir += $trx->shipping_cost;
                    $totalTerbayar += $trx->amount_paid;
                    $totalPiutang += $trx->remaining_balance;

                    $status = match($trx->status_pembayaran) {
                        'L' => ['label' => 'LUNAS', 'class' => 'status-lunas'],
                        'C', 'BL', 'DP' => ['label' => 'DP/CICIL', 'class' => 'status-partial'],
                        default => ['label' => 'BLM BAYAR', 'class' => 'status-unpaid']
                    };
                @endphp
                <tr>
                    <td>{{ $trx->source_created_at ? $trx->source_created_at->format('d/m/y H:i') : '-' }}</td>
                    <td style="font-weight: bold;">{{ $trx->spk_number }}</td>
                    <td>
                        <div>{{ strtoupper($trx->customer_name) }}</div>
                        <div style="font-size: 8px; color: #64748b; margin-top: 2px;">{{ $trx->customer_phone ?: '-' }}</div>
                    </td>


                    <td class="text-center">
                        <span class="status-badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                    </td>
                    <td class="text-right currency">{{ number_format($netTagihan, 0, ',', '.') }}</td>
                    <td class="text-right currency">{{ number_format($trx->shipping_cost, 0, ',', '.') }}</td>
                    <td class="text-right currency" style="color: #4F46E5;">{{ number_format($trx->amount_paid, 0, ',', '.') }}</td>
                    <td class="text-right currency" style="color: #dc2626;">{{ number_format($trx->remaining_balance, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px;">Data tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="footer-totals">
                <td colspan="4" class="text-right">TOTAL KESELURUHAN:</td>
                <td class="text-right currency">{{ number_format($totalTagihan, 0, ',', '.') }}</td>
                <td class="text-right currency">{{ number_format($totalOngkir, 0, ',', '.') }}</td>
                <td class="text-right currency" style="color: #4F46E5;">{{ number_format($totalTerbayar, 0, ',', '.') }}</td>
                <td class="text-right currency" style="color: #dc2626;">{{ number_format($totalPiutang, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px; font-size: 8px; color: #94a3b8; text-align: center;">
        Laporan ini digenerate secara otomatis oleh sistem Algoritma Insight.
    </div>
</body>
</html>
