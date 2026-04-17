<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Exception;

class WorkshopApiService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.dashboard.base_url', 'https://info.shoeworkshop.id/api/v1');
        $this->apiKey = config('services.dashboard.key');
    }

    /**
     * Fetch workshop sync data including matrix and metrics.
     *
     * @param string|null $startDate (Format: YYYY-MM-DD)
     * @param string|null $endDate (Format: YYYY-MM-DD)
     * @return array
     * @throws Exception
     */
    public function fetchWorkshopSync(?string $startDate = null, ?string $endDate = null): array
    {
        $endpoint = rtrim($this->baseUrl, '/') . '/workshop-sync';

        try {
            $params = [];
            if ($startDate) $params['start_date'] = $startDate;
            if ($endDate) $params['end_date'] = $endDate;

            $response = Http::timeout(60)
                ->withHeaders([
                    'X-API-KEY' => $this->apiKey,
                    'Accept'    => 'application/json',
                ])
                ->get($endpoint, $params);

            if ($response->status() === 401) {
                throw new Exception("Unauthorized: Invalid API Key.");
            }

            if ($response->status() === 404) {
                throw new Exception("Endpoint Not Found (404): $endpoint");
            }

            if ($response->serverError()) {
                throw new Exception("Server Error (500) from Workshop API.");
            }

            $response->throw();

            $data = $response->json();

            // Expected structure: { success: true, data: { matrix: {...}, metrics: {...}, last_sync: "..." } }
            if (isset($data['data'])) {
                return $data['data'];
            }

            return $data;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new Exception("Connection Timeout: Could not connect to Workshop API.");
        } catch (RequestException $e) {
            throw new Exception("API Request Error: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }
}
