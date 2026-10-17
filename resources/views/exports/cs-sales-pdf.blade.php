@php
    $num = fn ($v) => $v === null ? '–' : number_format($v, 0, ',', '.');
    $rp = fn ($v) => $v === null ? '–' : 'Rp ' . number_format($v, 0, ',', '.');
    $toneColor = ['hijau' => '#059669', 'kuning' => '#d97706', 'merah' => '#e11d48'];
    $medal = [0 => '#d4a017', 1 => '#9ca3af', 2 => '#b45309'];
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 32px; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #1e293b; font-size: 9px; line-height: 1.4; }
        .header { border-bottom: 2px solid #10b981; padding-bottom: 8px; margin-bottom: 16px; }
        .header h1 { color: #047857; font-size: 18px; margin: 0; }
        .header p { margin: 3px 0 0; color: #64748b; }
        .cards { width: 100%; border-collapse: separate; border-spacing: 8px 0; margin: 0 -8px 16px; }
        .card { border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; width: 25%; vertical-align: top; }
        .card .label { font-size: 7px; text-transform: uppercase; color: #94a3b8; font-weight: bold; letter-spacing: .5px; }
        .card .value { font-size: 16px; font-weight: bold; margin: 3px 0; }
        .card .note { color: #64748b; font-size: 8px; }
        h2 { font-size: 11px; margin: 14px 0 6px; color: #0f172a; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #f8fafc; border: 1px solid #e2e8f0; padding: 5px 6px; font-size: 7px; text-transform: uppercase; color: #475569; text-align: left; }
        table.data td { border: 1px solid #e2e8f0; padding: 4px 6px; }
        .r { text-align: right; }
        .footer { margin-top: 12px; color: #94a3b8; font-size: 7px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Dashboard CS</h1>
        <p>Periode {{ $start->translatedFormat('j M Y') }} – {{ $end->translatedFormat('j M Y') }} · dibanding {{ \Carbon\Carbon::parse($prevRange[0])->translatedFormat('j M') }} – {{ \Carbon\Carbon::parse($prevRange[1])->translatedFormat('j M Y') }} · dicetak {{ now()->translatedFormat('j M Y H:i') }}</p>
    </div>

    <table class="cards">
        <tr>
            @foreach($cards as $no => $card)
                <td class="card">
                    <div class="label">{{ [20 => 'Closing Biodata', 27 => 'Chat-to-SPK CR', 29 => 'Average Order Value', 26 => 'Multi-Pair Ratio'][$no] ?? $card['nama'] }}</div>
                    <div class="value">{{ $card['nilai'] }}</div>
                    @if($card['badge'])<div style="color: {{ $toneColor[$card['status']] ?? '#64748b' }}; font-weight: bold;">{{ $card['badge'] }}</div>@endif
                    <div class="note">Bulan lalu: {{ $card['lalu'] }}</div>
                </td>
            @endforeach
        </tr>
    </table>

    <h2>Leaderboard Kinerja Penjualan CS (ranking omset riil)</h2>
    @if($leaderboard === null)
        <p>Data per CS belum tersedia dari sistem pusat.</p>
    @else
        <table class="data">
            <thead>
                <tr><th>Rank</th><th>Nama Agen CS</th><th class="r">SPK Terbit</th><th class="r">Total Invoices</th><th class="r">Omset Deal Pipeline (Net)</th><th class="r">Multi-Pair Ratio</th></tr>
            </thead>
            <tbody>
                @foreach($leaderboard as $i => $row)
                    <tr>
                        {{-- Medali 3 teratas: sel berwarna emas / perak / perunggu --}}
                        <td @if(isset($medal[$i])) style="background: {{ $medal[$i] }}; color: #fff; font-weight: bold; text-align: center;" @endif>{{ $i + 1 }}</td>
                        <td><strong>{{ $row['nama'] }}</strong></td>
                        <td class="r">{{ $num($row['spk']) }} psg</td>
                        <td class="r">{{ $num($row['tagihan']) }} inv</td>
                        <td class="r"><strong>{{ $rp($row['omset']) }}</strong></td>
                        <td class="r">{{ $row['per_tagihan'] === null ? '–' : number_format($row['per_tagihan'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Rincian Transaksi Harian</h2>
    <table class="data">
        <thead>
            <tr><th>Tanggal</th><th class="r">Biodata (CRM)</th><th class="r">SPK Terbit</th><th class="r">Invoices Valid</th><th class="r">Deal Pipeline Net</th><th class="r">AOV Bersih</th><th class="r">Cost per SPK</th></tr>
        </thead>
        <tbody>
            @foreach($daily['rows'] as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row['tanggal'])->translatedFormat('d M Y') }}</td>
                    <td class="r">{{ $num($row['leads']) }}</td>
                    <td class="r">{{ $num($row['spk']) }}</td>
                    <td class="r">{{ $num($row['tagihan']) }}</td>
                    <td class="r">{{ $rp($row['omset']) }}</td>
                    <td class="r">{{ $rp($row['nilai_tagihan']) }}</td>
                    <td class="r">{{ $rp($row['biaya_spk']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer">Definisi angka mengikuti Kamus Metrik v2.0 (sama dengan Dashboard dan Data Manajemen SW). Biaya iklan Meta tanpa pajak. Tagihan dan omset menurut tanggal tagihan dibuat.</p>
</body>
</html>
