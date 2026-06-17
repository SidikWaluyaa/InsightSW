<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WarehouseApiService;
use Barryvdh\DomPDF\Facade\Pdf;

class WorkshopProductionExportController extends Controller
{
    public function printPdf(Request $request)
    {
        $searchTerm = $request->input('searchTerm', '');
        $selectedStatus = $request->input('selectedStatus', '');
        $selectedService = $request->input('selectedService', '');
        $selectedCategory = $request->input('selectedCategory', '');
        $selectedEstimation = $request->input('selectedEstimation', '');
        $estimationStartDate = $request->input('estimationStartDate', '');
        $estimationEndDate = $request->input('estimationEndDate', '');

        try {
            $apiService = new WarehouseApiService();
            $data = $apiService->fetchProductionSummary(true);
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

        if ($selectedService) {
            $collection = $collection->filter(function ($item) use ($selectedService) {
                return in_array($selectedService, $item['services'] ?? []);
            });
        }

        if ($selectedCategory) {
            $collection = $collection->filter(function ($item) use ($selectedCategory) {
                $itemCategories = $this->getItemServiceCategories($item['services'] ?? []);
                if ($selectedCategory === 'Lainnya' && empty($item['services'])) {
                    return true;
                }
                return in_array($selectedCategory, $itemCategories);
            });
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
        $totalDiProduksi = count($filteredItems);
        $totalOverdue = $collection->filter(fn($item) => $item['is_overdue'] ?? false)->count();
        $totalUpcoming = $collection->filter(fn($item) => $item['is_upcoming'] ?? false)->count();

        $pdf = Pdf::loadView('exports.workshop-production-pdf', [
            'items' => $filteredItems,
            'searchTerm' => $searchTerm,
            'selectedStatus' => $selectedStatus,
            'selectedService' => $selectedService,
            'selectedCategory' => $selectedCategory,
            'selectedEstimation' => $selectedEstimation,
            'estimationStartDate' => $estimationStartDate,
            'estimationEndDate' => $estimationEndDate,
            'totalDiProduksi' => $totalDiProduksi,
            'totalOverdue' => $totalOverdue,
            'totalUpcoming' => $totalUpcoming,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Data_Produksi_' . now()->format('YmdHis') . '.pdf');
    }

    public function getItemServiceCategories(array $services): array
    {
        $categories = [];
        
        foreach ($services as $service) {
            $serviceUpper = strtoupper($service);
            
            // Check Reparasi Sol
            if (str_contains($serviceUpper, 'SOL') || 
                str_contains($serviceUpper, 'SOLE') || 
                str_contains($serviceUpper, 'JAHIT') || 
                str_contains($serviceUpper, 'LEM') || 
                str_contains($serviceUpper, 'BONGPAS') || 
                str_contains($serviceUpper, 'BONGKAR') || 
                str_contains($serviceUpper, 'MIDSOLE') || 
                str_contains($serviceUpper, 'ALAS') || 
                str_contains($serviceUpper, 'BEMPER') || 
                str_contains($serviceUpper, 'HEEL CAGE') || 
                str_contains($serviceUpper, 'STABILIZER') || 
                str_contains($serviceUpper, 'TPR') || 
                str_contains($serviceUpper, 'BIRKEN') || 
                str_contains($serviceUpper, 'VIBRAM') || 
                str_contains($serviceUpper, 'YEZZY') || 
                str_contains($serviceUpper, 'HAK') ||
                str_contains($serviceUpper, 'SWAP')) {
                $categories['Reparasi Sol'] = true;
            }
            // Check Reparasi Upper
            elseif (str_contains($serviceUpper, 'UPPER') || 
                str_contains($serviceUpper, 'LINING') || 
                str_contains($serviceUpper, 'PADDED') || 
                str_contains($serviceUpper, 'HEEL TAB') || 
                str_contains($serviceUpper, 'LIDAH') || 
                str_contains($serviceUpper, 'STRIPE') || 
                str_contains($serviceUpper, 'ZIPPER') || 
                str_contains($serviceUpper, 'CUFF') || 
                str_contains($serviceUpper, 'LACEGUARD') || 
                str_contains($serviceUpper, 'COLLAR') || 
                str_contains($serviceUpper, 'ELASTIS') || 
                str_contains($serviceUpper, 'AKSESORIS') || 
                str_contains($serviceUpper, 'CUSTOM') || 
                str_contains($serviceUpper, 'PATCH') ||
                str_contains($serviceUpper, 'LAPIS') || 
                str_contains($serviceUpper, 'BUSA')) {
                $categories['Reparasi Upper'] = true;
            }
            // Check Repaint / Treatment
            elseif (str_contains($serviceUpper, 'REPAINT') || 
                str_contains($serviceUpper, 'UNYELLOWING') || 
                str_contains($serviceUpper, 'CLEANING') || 
                str_contains($serviceUpper, 'TREATMENT') || 
                str_contains($serviceUpper, 'COLOR') || 
                str_contains($serviceUpper, 'WARNA') || 
                str_contains($serviceUpper, 'DTF') || 
                str_contains($serviceUpper, 'LASER') || 
                str_contains($serviceUpper, 'DYE')) {
                $categories['Repaint'] = true;
            }
            else {
                $categories['Lainnya'] = true;
            }
        }
        
        return array_keys($categories);
    }
}
