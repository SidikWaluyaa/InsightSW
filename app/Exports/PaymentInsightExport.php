<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class PaymentInsightExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected EloquentBuilder|QueryBuilder $query;

    public function __construct(EloquentBuilder|QueryBuilder $query)
    {
        $this->query = $query;
    }

    public function query(): EloquentBuilder|QueryBuilder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'TANGGAL BAYAR',
            'NOMOR SPK/INVOICE',
            'NAMA CUSTOMER',
            'NOMOR TELEPON',
            'TIPE PEMBAYARAN',
            'TOTAL TAGIHAN',
            'TUNAI MASUK',
            'SISA BALANCE',
            'ORIGIN INVOICE',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : '-',
            $payment->spk_number,
            strtoupper($payment->customer_name ?? '-'),
            $payment->customer_phone ?? '-',
            $payment->payment_type_label,
            $payment->total_bill_snapshot,
            $payment->amount_paid,
            $payment->balance_snapshot,
            $payment->source_created_at ? $payment->source_created_at->format('M Y') : '-',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '"Rp "#,##0',
            'G' => '"Rp "#,##0',
            'H' => '"Rp "#,##0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'], // Indigo 600
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
