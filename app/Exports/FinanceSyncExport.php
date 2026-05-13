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
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class FinanceSyncExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting
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
            'TANGGAL SPK',
            'NOMOR SPK',
            'NAMA CUSTOMER',
            'NOMOR TELEPON',
            'STATUS PEMBAYARAN',
            'TOTAL TAGIHAN (NETT)',
            'ONGKIR',
            'TOTAL BAYAR',
            'SISA PIUTANG',
        ];
    }

    public function map($trx): array
    {
        $status = match($trx->status_pembayaran) {
            'L' => 'LUNAS',
            'C', 'BL', 'DP' => 'DP/CICIL',
            default => 'BELUM BAYAR'
        };

        return [
            $trx->source_created_at ? $trx->source_created_at->format('d/m/Y H:i') : '-',
            $trx->spk_number,
            strtoupper($trx->customer_name ?? '-'),
            $trx->customer_phone ?? '-',
            $status,
            $trx->total_bill - $trx->shipping_cost,
            $trx->shipping_cost,
            $trx->amount_paid,
            $trx->remaining_balance,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => '"Rp "#,##0',
            'G' => '"Rp "#,##0',
            'H' => '"Rp "#,##0',
            'I' => '"Rp "#,##0',
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
