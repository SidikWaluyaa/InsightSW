<!DOCTYPE html>
<html>
<head>
    <title>Laporan Sepatu di Rak</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        @page { margin: 1cm; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; table-layout: fixed; }
        th, td { border: 1px solid #e2e8f0; padding: 8px 6px; text-align: left; word-wrap: break-word; }
        th { background-color: #22AF85; color: white; font-weight: bold; text-transform: uppercase; font-size: 8px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header { margin-bottom: 20px; text-align: center; border-bottom: 2px solid #22AF85; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; color: #1e293b; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #64748b; font-size: 10px; }
        .status-badge { font-weight: bold; font-size: 8px; padding: 2.5px 5px; border-radius: 4px; display: inline-block; text-align: center; }
        .status-selesai { background-color: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .status-production { background-color: #f5f3ff; color: #5b21b6; border: 1px solid #ddd6fe; }
        .status-revisi { background-color: #fff5f5; color: #9b1c1c; border: 1px solid #feb2b2; }
        .duration-today { background-color: #f1f5f9; color: #475569; }
        .duration-warning { background-color: #fff5f5; color: #c53030; font-weight: bold; }
        .duration-normal { background-color: #f8fafc; color: #64748b; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daftar Sepatu di Rak Penyimpanan</h1>
        <p>
            Dicetak pada: {{ now()->format('d/m/Y H:i') }} | 
            Total Item: {{ count($items) }} Pasang
        </p>
        @if($searchTerm || $selectedStatus || $showDonationOnly)
        <p style="font-size: 9px; margin-top: 5px; font-weight: bold;">
            Filter - Pencarian: "{{ $searchTerm ?: 'Semua' }}" | 
            Status SPK: "{{ $selectedStatus ?: 'Semua' }}" | 
            Hanya Donasi (&gt; 3 Bulan): "{{ $showDonationOnly ? 'Ya' : 'Tidak' }}"
        </p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th width="15%">No. SPK</th>
                <th width="20%">Pelanggan</th>
                <th width="23%">Detail Sepatu</th>
                <th class="text-center" width="10%">Status SPK</th>
                <th class="text-center" width="10%">Posisi Rak</th>
                <th width="12%">Tgl Masuk Rak</th>
                <th class="text-center" width="10%">Durasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                @php
                    $isDonation = ($item['storage']['is_donation_candidate'] ?? false) || ($item['storage']['days_stored'] ?? 0) >= 90;
                    $days = $item['storage']['days_stored'] ?? 0;
                    $status = strtoupper($item['wo_status'] ?? '');
                    
                    $statusClass = 'status-production';
                    if ($status === 'SELESAI') {
                        $statusClass = 'status-selesai';
                    } elseif ($status === 'REVISI') {
                        $statusClass = 'status-revisi';
                    }

                    $durationClass = 'duration-normal';
                    $durationLabel = $days . ' Hari';
                    if ($isDonation) {
                        $durationClass = 'duration-warning';
                        $durationLabel = $days . ' Hari (> 3 BLN)';
                    } elseif ($days === 0 || strtoupper($item['storage']['days_stored_formatted'] ?? '') === 'HARI INI') {
                        $durationClass = 'duration-today';
                        $durationLabel = 'Hari Ini';
                    }
                @endphp
                <tr>
                    <td style="font-weight: bold; font-family: monospace;">{{ $item['spk_number'] ?? '-' }}</td>
                    <td>
                        <div style="font-weight: bold;">{{ strtoupper($item['customer']['name'] ?? '-') }}</div>
                        <div style="font-size: 8px; color: #64748b; margin-top: 2px;">{{ $item['customer']['phone'] ?? '-' }}</div>
                    </td>
                    <td>
                        <div style="font-weight: bold;">{{ $item['shoe']['brand'] ?? '' }} {{ $item['shoe']['type'] ?? '' }}</div>
                        <div style="font-size: 8px; color: #64748b; margin-top: 2px;">
                            {{ !empty($item['shoe']['color']) ? strtoupper($item['shoe']['color']) : 'WARNA N/A' }} • SIZE {{ $item['shoe']['size'] ?: '-' }}
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="status-badge {{ $statusClass }}">{{ $status ?: 'UNKNOWN' }}</span>
                    </td>
                    <td class="text-center" style="font-weight: bold; color: #0f766e;">
                        RAK: {{ $item['storage']['rack_code'] ?? '-' }}
                    </td>
                    <td>
                        {{ !empty($item['storage']['stored_at']) ? \Carbon\Carbon::parse($item['storage']['stored_at'])->format('d/m/Y H:i') : '-' }}
                    </td>
                    <td class="text-center">
                        <span class="status-badge {{ $durationClass }}">{{ $durationLabel }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #94a3b8; font-weight: bold;">
                        Tidak ada data sepatu di rak ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 30px; font-size: 8px; color: #94a3b8; text-align: center;">
        Laporan ini digenerate secara otomatis oleh sistem Algoritma Insight.
    </div>
</body>
</html>
