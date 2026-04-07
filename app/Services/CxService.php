<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CxService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.dashboard.key');
        $this->baseUrl = config('services.dashboard.base_url');
    }

    /**
     * Fetch CX Summary from API and store in Cache.
     * 
     * @param string $startDate
     * @param string $endDate
     * @param bool $force
     * @return array
     */
    public function fetchAndCache(string $startDate, string $endDate, bool $force = false): array
    {
        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Accept' => 'application/json',
            ])->withoutVerifying()->timeout(60)->get("{$this->baseUrl}/cx-summary", [
                'start' => $startDate,
                'end' => $endDate,
                'refresh' => $force ? 1 : 0
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Store exactly what the component needs
                $cacheKey = "cx_report_data_{$startDate}_{$endDate}";
                Cache::put($cacheKey, $data, now()->addHours(24));
                
                return $data;
            }

            throw new \Exception("API returned status " . $response->status());
        } catch (\Exception $e) {
            Log::error("CxService Fetch Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get data from Cache.
     */
    public function getCachedData(string $startDate, string $endDate): ?array
    {
        return Cache::get("cx_report_data_{$startDate}_{$endDate}");
    }
}
