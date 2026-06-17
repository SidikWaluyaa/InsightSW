<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WarehouseApiService;
use Barryvdh\DomPDF\Facade\Pdf;

class WorkshopQcExportController extends Controller
{
    public function printPdf(Request $request)
    {
        $searchTerm = $request->input('searchTerm', '');
        $selectedStatus = $request->input('selectedStatus', '');
        $selectedEstimation = $request->input('selectedEstimation', '');
        $estimationStartDate = $request->input('estimationStartDate', '');
        $estimationEndDate = $request->input('estimationEndDate', '');

        try {
            $apiService = new WarehouseApiService();
            $data = $apiService->fetchQcSummary(true);
            $items = $data['items'] ?? [];
        } catch (\Exception $e) {
            $items = [];
        }

        $collection = collect($items);

        // Apply filters
        if ($searchTerm) {
            $search = strtolower($searchTerm);
            $collection = $collection->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['spk_number'] ?? ''), $search) ||
                       str_contains(strtolower($item['customer_name'] ?? ''), $search) ||
                       str_contains(strtolower($item['shoe_brand'] ?? ''), $search) ||
                       str_contains(strtolower($item['shoe_type'] ?? ''), $search);
            });
        }

        if ($selectedStatus === 'on_track') {
            $collection = $collection->filter(fn($item) => !($item['is_overdue'] ?? false) && !($item['is_upcoming'] ?? false));
        } elseif ($selectedStatus === 'overdue') {
            $collection = $collection->filter(fn($item) => $item['is_overdue'] ?? false);
        } elseif ($selectedStatus === 'upcoming') {
            $collection = $collection->filter(fn($item) => $item['is_upcoming'] ?? false);
        }

        if ($selectedEstimation === 'set') {
            $collection = $collection->filter(fn($item) => $item['has_estimation'] ?? false);
        } elseif ($selectedEstimation === 'unset') {
            $collection = $collection->filter(fn($item) => !($item['has_estimation'] ?? false));
        }

        if ($estimationStartDate && $estimationEndDate) {
            $collection = $collection->filter(function ($item) use ($estimationStartDate, $estimationEndDate) {
                if (empty($item['estimation_date'])) {
                    return false;
                }
                $estDate = substr($item['estimation_date'], 0, 10);
                return $estDate >= $estimationStartDate && $estDate <= $estimationEndDate;
            });
        }

        $filteredItems = $collection->values()->all();

        // Calculate dynamic summary based on current filtered dataset
        $totalDiQc = count($filteredItems);
        $totalOverdue = $collection->filter(fn($item) => $item['is_overdue'] ?? false)->count();
        $totalUpcoming = $collection->filter(fn($item) => $item['is_upcoming'] ?? false)->count();

        $pdf = Pdf::loadView('exports.workshop-qc-pdf', [
            'items' => $filteredItems,
            'searchTerm' => $searchTerm,
            'selectedStatus' => $selectedStatus,
            'selectedEstimation' => $selectedEstimation,
            'estimationStartDate' => $estimationStartDate,
            'estimationEndDate' => $estimationEndDate,
            'totalDiQc' => $totalDiQc,
            'totalOverdue' => $totalOverdue,
            'totalUpcoming' => $totalUpcoming,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Data_QC_' . now()->format('YmdHis') . '.pdf');
    }
}
