<?php

namespace App\Services;

use App\Models\WarehouseInventory;
use App\Models\WarehouseRequest;
use App\Models\WarehouseTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WarehouseSyncService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.dashboard.base_url', 'https://info.shoeworkshop.id/api/v1');
        $this->apiKey = config('services.dashboard.key');
    }

    protected function getHeaders()
    {
        return [
            'X-API-KEY' => $this->apiKey,
            'Accept' => 'application/json',
        ];
    }

    public function syncInventory()
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                            ->timeout(60)
                            ->get("{$this->baseUrl}/warehouse-inventory-sync");

            if ($response->successful()) {
                $data = $response->json();
                
                // If API returns a wrapping 'data' array or just the raw array
                $items = isset($data['data']) ? $data['data'] : $data;

                if (!is_array($items) || empty($items)) {
                    return ['success' => true, 'message' => 'No inventory data to sync.'];
                }

                $upsertData = [];
                foreach ($items as $item) {
                    $upsertData[] = [
                        'item_id' => $item['id'] ?? 0,
                        'name' => $item['name'] ?? 'Unknown Item',
                        'category' => $item['category'] ?? null,
                        'sub_category' => $item['sub_category'] ?? null,
                        'unit' => $item['unit'] ?? null,
                        'current_stock' => $item['current_stock'] ?? 0,
                        'reserved_stock' => $item['reserved_stock'] ?? 0,
                        'min_stock' => $item['min_stock'] ?? 0,
                        'available_stock' => $item['available_stock'] ?? 0,
                        'unit_price' => $item['unit_price'] ?? 0,
                        'total_valuation' => $item['total_valuation'] ?? 0,
                        'status' => $item['status'] ?? null,
                        'source_last_updated' => !empty($item['last_updated']) ? date('Y-m-d H:i:s', strtotime($item['last_updated'])) : null,
                    ];
                }

                foreach (array_chunk($upsertData, 500) as $chunk) {
                    WarehouseInventory::upsert($chunk, ['item_id'], [
                        'name', 'category', 'sub_category', 'unit', 'current_stock',
                        'reserved_stock', 'min_stock', 'available_stock', 'unit_price',
                        'total_valuation', 'status', 'source_last_updated'
                    ]);
                }

                return [
                    'success' => true, 
                    'message' => 'Inventory synced (' . count($upsertData) . ' items)',
                    'count' => count($upsertData)
                ];
            }

            return ['success' => false, 'message' => 'API Error: ' . $response->status()];
        } catch (\Exception $e) {
            Log::error("Inventory Sync Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function syncRequests()
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                            ->timeout(60)
                            ->get("{$this->baseUrl}/warehouse-request-sync");

            if ($response->successful()) {
                $data = $response->json();
                $requests = isset($data['data']) ? $data['data'] : $data;

                if (!is_array($requests) || empty($requests)) {
                    return ['success' => true, 'message' => 'No request data to sync.'];
                }

                $upsertData = [];
                foreach ($requests as $req) {
                    $upsertData[] = [
                        'request_id' => $req['id'] ?? (int)(microtime(true) * 1000), // fallback id
                        'spk_number' => $req['spk_number'] ?? null,
                        'status' => $req['status'] ?? 'UNKNOWN',
                        'material_details' => isset($req['material_details']) ? json_encode($req['material_details']) : null,
                        'requested_at' => !empty($req['requested_at']) ? date('Y-m-d H:i:s', strtotime($req['requested_at'])) : null,
                        'source_last_updated' => !empty($req['updated_at']) ? date('Y-m-d H:i:s', strtotime($req['updated_at'])) : null,
                    ];
                }

                foreach (array_chunk($upsertData, 500) as $chunk) {
                    WarehouseRequest::upsert($chunk, ['request_id'], [
                        'spk_number', 'status', 'material_details', 'requested_at', 'source_last_updated'
                    ]);
                }

                return [
                    'success' => true, 
                    'message' => 'Requests synced (' . count($upsertData) . ' items)',
                    'count' => count($upsertData)
                ];
            }

            return ['success' => false, 'message' => 'API Error: ' . $response->status()];
        } catch (\Exception $e) {
            Log::error("Request Sync Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function syncTransactions()
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                            ->timeout(60)
                            ->get("{$this->baseUrl}/warehouse-transaction-sync");

            if ($response->successful()) {
                $data = $response->json();
                $transactions = isset($data['data']) ? $data['data'] : $data;

                if (!is_array($transactions) || empty($transactions)) {
                    return ['success' => true, 'message' => 'No transaction data to sync.'];
                }

                $upsertData = [];
                foreach ($transactions as $trx) {
                    $upsertData[] = [
                        'transaction_id' => $trx['id'] ?? (int)(microtime(true) * 1000), // fallback id
                        'type' => $trx['type'] ?? 'UNKNOWN',
                        'notes' => $trx['notes'] ?? null,
                        'details' => isset($trx['details']) ? json_encode($trx['details']) : null,
                        'transaction_date' => !empty($trx['transaction_date']) ? date('Y-m-d H:i:s', strtotime($trx['transaction_date'])) : null,
                    ];
                }

                foreach (array_chunk($upsertData, 500) as $chunk) {
                    WarehouseTransaction::upsert($chunk, ['transaction_id'], [
                        'type', 'notes', 'details', 'transaction_date'
                    ]);
                }

                return [
                    'success' => true, 
                    'message' => 'Transactions synced (' . count($upsertData) . ' items)',
                    'count' => count($upsertData)
                ];
            }

            return ['success' => false, 'message' => 'API Error: ' . $response->status()];
        } catch (\Exception $e) {
            Log::error("Transaction Sync Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
