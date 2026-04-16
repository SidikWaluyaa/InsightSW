<?php

namespace App\Services;

use App\Models\PaymentSync;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentSyncService
{
    protected $baseUrl = 'https://info.shoeworkshop.id/api/v1/payment-sync';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('PAYMENT_SYNC_API_KEY', 'sws_live_6f8g9h0j1k2l3m4n5o6p7q8r9s0');
    }

    /**
     * Sync payment data from remote API to local database.
     * 
     * @param int $limit
     * @return array
     */
    public function sync($limit = 500)
    {
        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get($this->baseUrl, [
                'limit' => $limit,
            ]);

            if ($response->failed()) {
                $errorMsg = $response->json('message') ?? $response->body() ?? 'Unknown error';
                Log::error('Payment Sync Failed: ' . $errorMsg);
                
                return [
                    'success' => false,
                    'message' => 'API Request failed: ' . $errorMsg,
                ];
            }

            $data = $response->json('data') ?? [];
            $upsertData = [];
            $now = now()->format('Y-m-d H:i:s');

            foreach ($data as $item) {
                // Ensure paid_at is not null to satisfy unique index safely, or let it be if DB handles it.
                // The actual payload seems to have valid paid_at dates.
                $upsertData[] = [
                    'spk_number' => $item['invoice_number'] ?? '-',
                    'paid_at' => $item['paid_at'] ? Carbon::parse($item['paid_at'])->format('Y-m-d H:i:s') : null,
                    'customer_name' => $item['customer_name'] ?? '-',
                    'customer_phone' => $item['customer_phone'] ?? '-',
                    'amount_paid' => (float)($item['amount_paid'] ?? 0),
                    'payment_type' => strtoupper($item['payment_type'] ?? 'BEFORE'),
                    'total_bill_snapshot' => (float)($item['total_bill_snapshot'] ?? 0),
                    'balance_snapshot' => (float)($item['balance_snapshot'] ?? 0),
                    'source_created_at' => $item['created_at'] ? Carbon::parse($item['created_at'])->format('Y-m-d H:i:s') : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Chunking the upsert is good practice for very large payloads
            $chunks = array_chunk($upsertData, 500);
            foreach ($chunks as $chunk) {
                PaymentSync::upsert(
                    $chunk,
                    ['spk_number', 'paid_at'], // Unique constraints to match against
                    ['customer_name', 'customer_phone', 'amount_paid', 'payment_type', 'total_bill_snapshot', 'balance_snapshot', 'source_created_at', 'updated_at'] // Columns to update if match found
                );
            }
            
            $count = count($upsertData);

            return [
                'success' => true,
                'count' => $count,
                'message' => "Successfully synced $count records.",
            ];

        } catch (\Exception $e) {
            Log::error('Payment Sync Exception: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }
}
