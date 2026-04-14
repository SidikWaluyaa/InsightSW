<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CsFollowupExport extends DefaultValueBinder implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithCustomValueBinder
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function bindValue(Cell $cell, $value)
    {
        // Force column C (Nomor WhatsApp) to be a string
        if ($cell->getColumn() === 'C') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }

        // Return default behavior for other columns
        return parent::bindValue($cell, $value);
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'NAMA BELAKANG',
            'NAMA DEPAN',
            'NOMOR WHATSAPP',
            'EMAIL',
            'STATUS CHAT',
            'OWNER',
            'TIM',
            'PESAN CUSTOMER TERAKHIR',
            'RESPON KOMPAN TERAKHIR',
            'JARAK (HARI)',
        ];
    }

    public function map($contact): array
    {
        $lastCustomer = $contact->last_contact_from_customers;
        $lastCompany = $contact->last_contacted_from_company;
        
        $gap = '-';
        if ($lastCustomer) {
            if (!$lastCompany) {
                $gap = $lastCustomer->diffInDays(now());
            } elseif ($lastCustomer > $lastCompany) {
                $gap = $lastCustomer->diffInDays($lastCompany);
            }
        }

        return [
            strtoupper($contact->last_name ?? '-'),
            strtoupper($contact->first_name ?? '-'),
            $contact->phone_number ?? '-',
            $contact->email ?? '-',
            strtoupper($contact->status_chat ?? 'OPEN'),
            strtoupper($contact->contact_owner_name ?? '-'),
            strtoupper($contact->assigned_team ?? '-'),
            $lastCustomer ? $lastCustomer->format('d M Y H:i') : '-',
            $lastCompany ? $lastCompany->format('d M Y H:i') : '-',
            $gap === '-' ? '-' : "$gap Hari",
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $rowCount = $this->query->count();
        return [
            // Style the first row (headings)
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0284C7'], // Sky 600
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Set borders for the entire table
            'A1:J' . ($rowCount + 1) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '94A3B8'], // Slate 400
                    ],
                ],
            ],
        ];
    }
}
