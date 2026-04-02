<?php

namespace App\Services;

use App\Models\FinanceSync;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FinanceSyncService
{
    protected $baseUrl = 'https://info.shoeworkshop.id/api/v1/finance-sync';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('DASHBOARD_API_KEY');
    }

    /**
     * Perform a rolling sync for the last X days.
     * 
     * @param int $days
     * @param int|null $userId
     * @return array
     */
    public function syncRolling($days = 60, $userId = null)
    {
        $startDate = now()->subDays($days)->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        return $this->sync($startDate, $endDate, $userId);
    }

    /**
     * Sync finance data from remote API to local database.
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int|null $userId
     * @return array
     */
    public function sync($startDate = null, $endDate = null, $userId = null)
    {
        $startDate = $startDate ?: '2026-02-01';
        $endDate = $endDate ?: now()->format('Y-m-d');

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl, [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);

            if ($response->failed()) {
                $errorMsg = $response->json('message') ?? $response->body() ?? 'Unknown error';
                Log::error('Finance Sync Failed: ' . $errorMsg);
                
                \App\Models\FinanceSyncLog::create([
                    'user_id' => $userId,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'records_count' => 0,
                    'status' => 'FAILED',
                    'message' => 'API Request failed: ' . $errorMsg,
                ]);

                return [
                    'success' => false,
                    'message' => 'API Request failed: ' . $errorMsg,
                ];
            }

            $data = $response->json('data') ?? [];
            $count = 0;

            foreach ($data as $item) {
                FinanceSync::updateOrCreate(
                    ['spk_number' => $item['spk_number']],
                    [
                        'status' => $item['status'] ?? 'IN_PROGRESS',
                        'customer_name' => $item['customer_name'] ?? '-',
                        'customer_phone' => $item['customer_phone'] ?? '-',
                        'status_pembayaran' => $item['status_pembayaran'],
                        'spk_status' => $item['spk_status'] ?? 'BELUM SELESAI',
                        'amount_paid' => (float)$item['amount_paid'],
                        'total_bill' => (float)$item['total_bill'],
                        'discount' => (float)$item['discount'],
                        'shipping_cost' => (float)$item['shipping_cost'],
                        'remaining_balance' => (float)$item['remaining_balance'],
                        'invoice_awal_url' => $item['invoice_awal_url'] ?? null,
                        'invoice_akhir_url' => $item['invoice_akhir_url'] ?? null,
                        'estimasi_selesai' => $item['estimasi_selesai'] ? Carbon::parse($item['estimasi_selesai']) : null,
                        'source_created_at' => $item['created_at'] ? Carbon::parse($item['created_at']) : null,
                        'source_updated_at' => $item['updated_at'] ? Carbon::parse($item['updated_at']) : null,
                    ]
                );
                $count++;
            }

            \App\Models\FinanceSyncLog::create([
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'records_count' => $count,
                'status' => 'SUCCESS',
                'message' => "Successfully synced $count records.",
            ]);

            return [
                'success' => true,
                'count' => $count,
                'message' => "Successfully synced $count records.",
            ];

        } catch (\Exception $e) {
            Log::error('Finance Sync Exception: ' . $e->getMessage());
            
            \App\Models\FinanceSyncLog::create([
                'user_id' => $userId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'records_count' => 0,
                'status' => 'FAILED',
                'message' => 'Error: ' . $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }
}
