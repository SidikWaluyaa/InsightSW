<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Exception;

class WorkshopCxApiService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('app.dashboard_api_url') ?: env('DASHBOARD_API_URL', 'https://info.shoeworkshop.id/api/v1');
        $this->apiKey = config('app.dashboard_api_key') ?: env('DASHBOARD_API_KEY', '');
    }

    /**
     * Fetch CX After Confirmation data from the workshop system.
     *
     * @param string $startDate (Format: YYYY-MM-DD)
     * @param string $endDate (Format: YYYY-MM-DD)
     * @return array
     * @throws Exception
     */
    public function fetchCxAfterConfirmation(string $startDate, string $endDate): array
    {
        $endpoint = rtrim($this->baseUrl, '/') . '/cx-after-confirmation';

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
                throw new Exception("Unauthorized: Invalid API Key. Please check DASHBOARD_API_KEY.");
            }

            if ($response->status() === 404) {
                throw new Exception("Endpoint Not Found (404): $endpoint");
            }

            if ($response->serverError()) {
                throw new Exception("Server Error (500) from Workshop API.");
            }

            // Throw exception if response is not 2xx
            $response->throw();

            $data = $response->json();

            if (!isset($data['data'])) {
                // If API succeeds but format is unexpected
                return [];
            }

            return $data['data'];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new Exception("Connection Timeout: Could not connect to Workshop API. Please check your network or try again later.");
        } catch (RequestException $e) {
            throw new Exception("API Request Error: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }
}
