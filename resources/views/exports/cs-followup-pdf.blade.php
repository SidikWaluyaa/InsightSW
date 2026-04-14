<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page { margin: 40px; }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            color: #1e293b;
            font-size: 10px;
            line-height: 1.5;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #0284c7;
            font-size: 22px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 5px 0 0;
            color: #64748b;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .meta {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 8px;
            text-align: left;
            text-transform: uppercase;
            font-size: 8px;
            font-weight: bold;
            color: #475569;
        }
        .table td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            vertical-align: middle;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-urgent { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-info { background-color: #e0f2fe; color: #075985; }
        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            text-align: center;
            color: #94a3b8;
            font-size: 8px;
            border-top: 1px solid #f1f5f9;
            padding-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CS Followup Report</h1>
        <p>Insight System Framework &bull; {{ now()->format('d F Y') }}</p>
    </div>

    <div class="meta" style="width: 100%;">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="border: none; padding: 0;">
                    <strong>Filter Tab:</strong> {{ strtoupper($activeTab) }}<br>
                    <strong>Status:</strong> {{ $selectedStatus ?: 'SEMUA' }}
                </td>
                <td style="border: none; padding: 0; text-align: right;">
                    <strong>Total Data:</strong> {{ count($contacts) }} Baris<br>
                    <strong>Waktu Ekspor:</strong> {{ now()->format('H:i:s') }} WIB
                </td>
            </tr>
        </table>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="3%">NO</th>
                <th width="22%">CUSTOMER</th>
                <th width="12%">WHATSAPP</th>
                <th width="10%">STATUS</th>
                <th width="12%">TIM / OWNER</th>
                <th width="15%">PESAN TERAKHIR</th>
                <th width="15%">RESPON TERAKHIR</th>
                <th width="11%">GAP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contacts as $index => $contact)
                @php
                    $lastCustomer = $contact->last_contact_from_customers;
                    $lastCompany = $contact->last_contacted_from_company;
                    $gapDays = 0;
                    if ($lastCustomer) {
                        $gapDays = $lastCompany ? $lastCustomer->diffInDays($lastCompany) : $lastCustomer->diffInDays(now());
                    }
                    
                    $badgeBg = '#e0f2fe'; $badgeColor = '#075985'; // Info
                    if ($gapDays >= 7) { $badgeBg = '#fee2e2'; $badgeColor = '#991b1b'; } // Urgent
                    elseif ($gapDays >= 3) { $badgeBg = '#fef3c7'; $badgeColor = '#92400e'; } // Warning
                @endphp
                <tr>
                    <td align="center">{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ strtoupper($contact->first_name . ' ' . $contact->last_name) }}</strong>
                    </td>
                    <td>{{ $contact->phone_number }}</td>
                    <td align="center">
                        {{ $contact->status_chat ?: 'OPEN' }}
                    </td>
                    <td>
                        {{ $contact->assigned_team ?: '-' }}<br>
                        <span style="color: #64748b; font-size: 7px;">{{ $contact->contact_owner_name ?: '-' }}</span>
                    </td>
                    <td>{{ $lastCustomer ? $lastCustomer->format('d/m/y H:i') : '-' }}</td>
                    <td>{{ $lastCompany ? $lastCompany->format('d/m/y H:i') : '-' }}</td>
                    <td align="center" style="background-color: {{ $badgeBg }}; color: {{ $badgeColor }}; font-weight: bold;">
                        {{ $gapDays }} HARI
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated by Algoritma Marketing System &bull; Confidential &bull; Page {{ '{PAGE_NUM}' }} of {{ '{PAGE_COUNT}' }}
    </div>
</body>
</html>
