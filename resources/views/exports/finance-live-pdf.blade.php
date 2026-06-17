<!DOCTYPE html>
<html>
<head>
    <title>Finance Live Dashboard Export</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        @page { margin: 1cm; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; table-layout: fixed; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 5px; text-align: left; word-wrap: break-word; font-size: 9px; }
        th { background-color: #4F46E5; color: white; font-weight: bold; text-transform: uppercase; font-size: 8px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header { margin-bottom: 15px; text-align: center; border-bottom: 2px solid #4F46E5; padding-bottom: 8px; }
        .header h1 { margin: 0; font-size: 16px; color: #1e293b; text-transform: uppercase; }
        .header p { margin: 4px 0 0; color: #64748b; font-size: 9px; }
        .footer-totals { background-color: #f8fafc; font-weight: bold; }
        .status-badge { font-weight: bold; font-size: 8px; padding: 2px 4px; border-radius: 3px; display: inline-block; text-align: center; }
        .status-lunas { background-color: #d1fae5; color: #065f46; }
        .status-partial { background-color: #fef3c7; color: #92400e; }
        .status-unpaid { background-color: #fee2e2; color: #991b1b; }
        .currency { font-family: monospace; }
        
        /* Payment type badges */
        .type-before { background-color: #dbeafe; color: #1e40af; }
        .type-after { background-color: #d1fae5; color: #065f46; }
        .type-tambah { background-color: #e0e7ff; color: #3730a3; }
        .type-lunas-awal { background-color: #fef3c7; color: #92400e; }
        .type-ongkir { background-color: #ffe4e6; color: #9f1239; }
        .type-default { background-color: #f1f5f9; color: #334155; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Keuangan Live (Dashboard Finance) - {{ strtoupper($type) }}</h1>
        <p>
            Dicetak pada: {{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} | 
            Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
        </p>
        <p>
            Filter - Pencarian: {{ $search ?: 'Semua' }} 
            @if($type === 'invoices')
                | Status Pembayaran: {{ $statusFilter === 'all' ? 'Semua' : ($statusFilter === 'L' ? 'Lunas' : ($statusFilter === 'BL' ? 'DP/Cicil' : ($statusFilter === 'BB' ? 'Belum Bayar' : $statusFilter))) }}
            @else
                | Tipe Pembayaran: {{ $paymentTypeFilter === 'all' ? 'Semua' : $paymentTypeFilter }}
            @endif
        </p>
    </div>

    @if($type === 'invoices')
        {{-- INVOICES TABLE --}}
        <table>
            <thead>
                <tr>
                    <th width="5%" class="text-center">#</th>
                    <th width="15%">No Invoice</th>
                    <th width="20%">Customer</th>
                    <th class="text-right" width="10%">Total</th>
                    <th class="text-right" width="10%">Ongkir</th>
                    <th class="text-right" width="10%">Diskon</th>
                    <th class="text-right" width="10%">Terbayar</th>
                    <th class="text-right" width="10%">Sisa</th>
                    <th class="text-center" width="10%">Status</th>
                    <th class="text-center" width="10%">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalBill = 0;
                    $totalShipping = 0;
                    $totalDiscount = 0;
                    $totalPaid = 0;
                    $totalBalance = 0;
                @endphp
                @forelse($data as $index => $invoice)
                    @php
                        $totalBill += (float)($invoice['total_bill'] ?? 0);
                        $totalShipping += (float)($invoice['shipping_cost'] ?? 0);
                        $totalDiscount += (float)($invoice['discount'] ?? 0);
                        $totalPaid += (float)($invoice['amount_paid'] ?? 0);
                        $totalBalance += (float)($invoice['remaining_balance'] ?? 0);

                        $status = $invoice['status_pembayaran'] ?? 'BB';
                        $statusClass = match($status) {
                            'L' => 'status-lunas',
                            'BL', 'DP', 'C' => 'status-partial',
                            default => 'status-unpaid'
                        };
                        $statusText = match($status) {
                            'L' => 'LUNAS',
                            'BL', 'DP', 'C' => 'DP/CICIL',
                            default => 'BELUM BAYAR'
                        };
                    @endphp
                    <tr>
                        <td class="text-center font-mono">{{ $index + 1 }}</td>
                        <td style="font-weight: bold;">{{ $invoice['spk_number'] }}</td>
                        <td>{{ strtoupper($invoice['customer_name'] ?? '-') }}</td>
                        <td class="text-right currency">{{ number_format($invoice['total_bill'] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right currency">{{ number_format($invoice['shipping_cost'] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right currency">{{ number_format($invoice['discount'] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right currency" style="color: #4F46E5;">{{ number_format($invoice['amount_paid'] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right currency" style="color: #dc2626; font-weight: bold;">{{ number_format($invoice['remaining_balance'] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                        </td>
                        <td class="text-center font-mono">{{ $invoice['created_at'] ? \Carbon\Carbon::parse($invoice['created_at'])->format('d/m/Y') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center" style="padding: 20px;">Data invoice tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="footer-totals">
                    <td colspan="3" class="text-right">TOTAL:</td>
                    <td class="text-right currency">{{ number_format($totalBill, 0, ',', '.') }}</td>
                    <td class="text-right currency">{{ number_format($totalShipping, 0, ',', '.') }}</td>
                    <td class="text-right currency">{{ number_format($totalDiscount, 0, ',', '.') }}</td>
                    <td class="text-right currency" style="color: #4F46E5;">{{ number_format($totalPaid, 0, ',', '.') }}</td>
                    <td class="text-right currency" style="color: #dc2626;">{{ number_format($totalBalance, 0, ',', '.') }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    @else
        {{-- PAYMENTS TABLE --}}
        <table>
            <thead>
                <tr>
                    <th width="5%" class="text-center">#</th>
                    <th width="15%">No Invoice</th>
                    <th width="20%">Customer</th>
                    <th class="text-right" width="12%">Jumlah Bayar</th>
                    <th class="text-center" width="15%">Tipe Pembayaran</th>
                    <th width="23%">Catatan</th>
                    <th class="text-center" width="10%">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalAmount = 0;
                @endphp
                @forelse($data as $index => $payment)
                    @php
                        $totalAmount += (float)($payment['amount_paid'] ?? 0);

                        $type = strtoupper($payment['payment_type'] ?? 'BEFORE');
                        $typeLabel = match($type) {
                            'BEFORE' => 'DP AWAL',
                            'AFTER' => 'PELUNASAN',
                            'TAMBAH_JASA' => 'TAMBAH JASA',
                            'LUNAS_AWAL' => 'LUNAS AWAL',
                            'ONGKIR' => 'ONGKIR',
                            default => $type
                        };
                        $typeClass = match($type) {
                            'BEFORE' => 'type-before',
                            'AFTER' => 'type-after',
                            'TAMBAH_JASA' => 'type-tambah',
                            'LUNAS_AWAL' => 'type-lunas-awal',
                            'ONGKIR' => 'type-ongkir',
                            default => 'type-default'
                        };
                    @endphp
                    <tr>
                        <td class="text-center font-mono">{{ $index + 1 }}</td>
                        <td style="font-weight: bold;">{{ $payment['invoice_number'] }}</td>
                        <td>{{ strtoupper($payment['customer_name'] ?? '-') }}</td>
                        <td class="text-right currency" style="color: #059669; font-weight: bold;">{{ number_format($payment['amount_paid'] ?? 0, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="status-badge {{ $typeClass }}">{{ $typeLabel }}</span>
                        </td>
                        <td style="font-style: italic; color: #475569;">
                            {{ $payment['notes'] ?: ($payment['pic_name'] ? 'By ' . $payment['pic_name'] : '-') }}
                        </td>
                        <td class="text-center font-mono">{{ $payment['paid_at'] ? \Carbon\Carbon::parse($payment['paid_at'])->format('d/m/Y') : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 20px;">Data pembayaran tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="footer-totals">
                    <td colspan="3" class="text-right">TOTAL PEMBAYARAN:</td>
                    <td class="text-right currency" style="color: #059669;">{{ number_format($totalAmount, 0, ',', '.') }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    @endif

    <div style="margin-top: 30px; font-size: 8px; color: #94a3b8; text-align: center;">
        Laporan ini digenerate secara otomatis oleh sistem Algoritma Insight (Live Dashboard Finance).
    </div>
</body>
</html>
