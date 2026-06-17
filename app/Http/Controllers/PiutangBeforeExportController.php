<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WarehouseApiService;
use Barryvdh\DomPDF\Facade\Pdf;

class PiutangBeforeExportController extends Controller
{
    public function printPdf(Request $request)
    {
        $searchTerm = $request->input('searchTerm', '');
        $selectedStatus = $request->input('selectedStatus', '');

        try {
            $apiService = new WarehouseApiService();
            $items = $apiService->fetchPiutangBeforeData();
        } catch (\Exception $e) {
            $items = [];
        }

        $collection = collect($items);

        // Apply same filters as in the Livewire component
        if ($searchTerm) {
            $search = strtolower($searchTerm);
            $collection = $collection->filter(function ($item) use ($search) {
                $customerName = strtolower($item['customer']['name'] ?? '');
                $customerPhone = strtolower($item['customer']['phone'] ?? '');
                $invoiceNumber = strtolower($item['invoice_number'] ?? '');

                $spksMatch = false;
                if (!empty($item['work_orders'])) {
                    foreach ($item['work_orders'] as $wo) {
                        $spkNum = strtolower($wo['spk_number'] ?? '');
                        $brand = strtolower($wo['shoe']['brand'] ?? '');
                        $type = strtolower($wo['shoe']['type'] ?? '');
                        $color = strtolower($wo['shoe']['color'] ?? '');
                        $size = strtolower($wo['shoe']['size'] ?? '');

                        if (str_contains($spkNum, $search) ||
                            str_contains($brand, $search) ||
                            str_contains($type, $search) ||
                            str_contains($color, $search) ||
                            str_contains($size, $search)) {
                            $spksMatch = true;
                            break;
                        }
                    }
                }

                return str_contains($customerName, $search) ||
                       str_contains($customerPhone, $search) ||
                       str_contains($invoiceNumber, $search) ||
                       $spksMatch;
            });
        }

        if ($selectedStatus) {
            $collection = $collection->filter(function ($item) use ($selectedStatus) {
                return strtolower($item['financials']['status'] ?? '') === strtolower($selectedStatus);
            });
        }

        $filteredItems = $collection->values()->all();

        $totalOutstanding = $collection->sum(function ($item) {
            return $item['financials']['remaining_balance'] ?? 0;
        });

        $totalPaid = $collection->sum(function ($item) {
            return $item['financials']['paid_amount'] ?? 0;
        });

        $pdf = Pdf::loadView('exports.piutang-before-pdf', [
            'items' => $filteredItems,
            'searchTerm' => $searchTerm,
            'selectedStatus' => $selectedStatus,
            'totalOutstanding' => $totalOutstanding,
            'totalPaid' => $totalPaid,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Piutang_Before_' . now()->format('YmdHis') . '.pdf');
    }
}
