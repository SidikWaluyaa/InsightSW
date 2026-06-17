<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WarehouseApiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class WarehouseShoerackExportController extends Controller
{
    public function printPdf(Request $request)
    {
        $searchTerm = $request->input('searchTerm', '');
        $selectedStatus = $request->input('selectedStatus', '');
        $showDonationOnly = $request->input('showDonationOnly') == '1';

        try {
            $apiService = new WarehouseApiService();
            $items = $apiService->fetchShoerackData();
        } catch (\Exception $e) {
            $items = [];
        }

        $collection = collect($items);

        // Apply same filters as in the Livewire component
        if ($searchTerm) {
            $search = strtolower($searchTerm);
            $collection = $collection->filter(function ($item) use ($search) {
                $customerName = strtolower($item['customer']['name'] ?? '');
                $spkNumber = strtolower($item['spk_number'] ?? '');
                $rackCode = strtolower($item['storage']['rack_code'] ?? '');
                $brand = strtolower($item['shoe']['brand'] ?? '');
                $type = strtolower($item['shoe']['type'] ?? '');
                $color = strtolower($item['shoe']['color'] ?? '');
                $size = strtolower($item['shoe']['size'] ?? '');

                return str_contains($customerName, $search) ||
                       str_contains($spkNumber, $search) ||
                       str_contains($rackCode, $search) ||
                       str_contains($brand, $search) ||
                       str_contains($type, $search) ||
                       str_contains($color, $search) ||
                       str_contains($size, $search);
            });
        }

        if ($selectedStatus) {
            $collection = $collection->filter(function ($item) use ($selectedStatus) {
                return strtoupper($item['wo_status'] ?? '') === strtoupper($selectedStatus);
            });
        }

        if ($showDonationOnly) {
            $collection = $collection->filter(function ($item) {
                return ($item['storage']['is_donation_candidate'] ?? false) || ($item['storage']['days_stored'] ?? 0) >= 90;
            });
        }

        $pdf = Pdf::loadView('exports.warehouse-shoerack-pdf', [
            'items' => $collection->values()->all(),
            'searchTerm' => $searchTerm,
            'selectedStatus' => $selectedStatus,
            'showDonationOnly' => $showDonationOnly,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Sepatu_di_Rak_' . now()->format('YmdHis') . '.pdf');
    }
}
