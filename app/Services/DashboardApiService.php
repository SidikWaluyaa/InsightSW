<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardApiService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.dashboard.key');
        $this->baseUrl = config('services.dashboard.base_url');
    }

    /**
     * Get dashboard summary from external API with Caching.
     * 
     * @param string $startDate Format: YYYY-MM-DD
     * @param string $endDate Format: YYYY-MM-DD
     * @param bool $forceRefresh
     * @return array
     */
    public function getDashboardSummary(string $startDate, string $endDate, bool $forceRefresh = false): array
    {
        $cacheKey = "dashboard_summary_{$startDate}_{$endDate}";

        if (!$forceRefresh && \Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return \Illuminate\Support\Facades\Cache::get($cacheKey);
        }

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Accept' => 'application/json',
            ])->withoutVerifying()->timeout(60)->get("{$this->baseUrl}/dashboard-summary", [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'force_refresh' => $forceRefresh ? 1 : 0,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                \Illuminate\Support\Facades\Cache::put($cacheKey, $data, now()->addHour());
                return $data;
            }

            Log::error('Dashboard API Error: ' . $response->status() . ' - ' . $response->body());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil data dari API: ' . $response->status(),
                'data' => null
            ];
        } catch (\Exception $e) {
            Log::error('Dashboard API Exception: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Terjadi kesalahan koneksi: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}
