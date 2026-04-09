<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Exception;

class WarehouseApiService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.dashboard.base_url', 'https://info.shoeworkshop.id/api/v1');
        $this->apiKey = config('services.dashboard.key');
    }

    /**
     * Fetch warehouse summary data including analytics and inventory.
     *
     * @param string $startDate (Format: YYYY-MM-DD)
     * @param string $endDate (Format: YYYY-MM-DD)
     * @return array
     * @throws Exception
     */
    public function fetchSummary(string $startDate, string $endDate): array
    {
        $endpoint = rtrim($this->baseUrl, '/') . '/warehouse-summary';

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-KEY' => $this->apiKey,
                    'Accept'    => 'application/json',
                ])
                ->get($endpoint, [
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                ]);

            if ($response->status() === 401) {
                throw new Exception("Unauthorized: Invalid API Key.");
            }

            if ($response->status() === 404) {
                throw new Exception("Endpoint Not Found (404): $endpoint");
            }

            if ($response->serverError()) {
                throw new Exception("Server Error (500) from Warehouse API.");
            }

            $response->throw();

            $data = $response->json();

            // Structure check based on requirements
            // Expected: { success: true, data: { summary: {...}, qc_analytics: {...}, efficiency: {...}, inventory: {...} } }
            if (isset($data['data'])) {
                return $data['data'];
            }

            return $data;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new Exception("Connection Timeout: Could not connect to Warehouse API.");
        } catch (RequestException $e) {
            throw new Exception("API Request Error: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }
}
