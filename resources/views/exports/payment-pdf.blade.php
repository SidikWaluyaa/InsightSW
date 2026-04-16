<!DOCTYPE html>
<html>
<head>
    <title>Payment Ledger</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px 6px; text-align: left; }
        th { background-color: #4F46E5; color: white; font-weight: bold; text-transform: uppercase; font-size: 9px;}
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header { margin-bottom: 20px; text-align: center; border-bottom: 2px solid #4F46E5; padding-bottom: 10px;}
        .header h1 { margin: 0; font-size: 20px; color: #1e293b; text-transform: uppercase;}
        .header p { margin: 5px 0 0; color: #64748b; font-size: 11px; }
        .customer-name { font-weight: bold; color: #0f172a; }
        .customer-phone { font-size: 8.5px; color: #64748b; }
        .type-badge { font-weight: bold; font-size: 8px; }
        .type-dp { color: #059669; }
        .type-lunas { color: #2563eb; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Pembayaran (Payment Ledger)</h1>
        <p>Tanggal Cetak: {{ now()->format('d M Y, H:i') }} | Filter: {{ strtoupper($statusFilter) }} | Pencarian: {{ $search ?: 'Semua' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" width="3%">No</th>
                <th width="12%">Tanggal Bayar</th>
                <th width="16%">SPK / Invoice</th>
                <th width="16%">Customer</th>
                <th width="10%">Tipe</th>
                <th class="text-right" width="14%">Total Tagihan</th>
                <th class="text-right" width="14%">Tunai Masuk</th>
                <th class="text-right" width="15%">Sisa Balance</th>
            </tr>
        </thead>
        <tbody>
            @php $totalMasuk = 0; $totalHutang = 0; @endphp
            @forelse($payments as $index => $payment)
                @php 
                    $totalMasuk += $payment->amount_paid;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : '-' }}</td>
                    <td>
                        <strong>{{ $payment->spk_number }}</strong>
                        <br>
                        <span style="font-size: 8px; color: #64748b;">Origin: {{ $payment->source_created_at ? $payment->source_created_at->format('M Y') : '-' }}</span>
                    </td>
                    <td>
                        <span class="customer-name">{{ $payment->customer_name ?: '-' }}</span>
                        <br>
                        <span class="customer-phone">{{ $payment->customer_phone ?: '-' }}</span>
                    </td>
                    <td>
                        @if($payment->payment_type === 'BEFORE')
                            <span class="type-badge type-dp">DP / AWAL</span>
                        @else
                            <span class="type-badge type-lunas">LUNAS / AKHIR</span>
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($payment->total_bill_snapshot, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #4F46E5; font-weight: bold;">Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($payment->balance_snapshot, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 30px;">Tidak ada data yang ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc;">
                <th colspan="6" class="text-right" style="color: #333; background: #f8fafc; border-top: 2px solid #4F46E5;">TOTAL TUNAI MASUK (Masa Filter):</th>
                <th colspan="2" class="text-right" style="color: #4F46E5; font-size: 11px; background: #f8fafc; border-top: 2px solid #4F46E5;">
                    Rp {{ number_format($totalMasuk, 0, ',', '.') }}
                </th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
