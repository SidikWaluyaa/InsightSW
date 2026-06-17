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
    public function fetchSummary(string $startDate, string $endDate, bool $forceRefresh = false): array
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
                    'force_refresh' => $forceRefresh ? 1 : 0,
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

    /**
     * Fetch warehouse manifest summary data from API.
     */
    public function fetchManifestSummary(string $startDate, string $endDate, bool $forceRefresh = false): array
    {
        $endpoint = rtrim($this->baseUrl, '/') . '/warehouse-manifest-summary';

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-KEY' => $this->apiKey,
                    'Accept'    => 'application/json',
                ])
                ->get($endpoint, [
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                    'force_refresh' => $forceRefresh ? 1 : 0,
                ]);

            if ($response->status() === 401) {
                throw new Exception("Unauthorized: Invalid API Key.");
            }

            if ($response->status() === 404) {
                throw new Exception("Endpoint Not Found (404): $endpoint");
            }

            if ($response->serverError()) {
                throw new Exception("Server Error (500) from Manifest API.");
            }

            $response->throw();

            $data = $response->json();

            if (isset($data['data'])) {
                return $data['data'];
            }

            return $data;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new Exception("Connection Timeout: Could not connect to Manifest API.");
        } catch (RequestException $e) {
            throw new Exception("API Request Error: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Fetch warehouse shoerack sync data.
     */
    public function fetchShoerackData(bool $forceRefresh = false): array
    {
        $endpoint = rtrim($this->baseUrl, '/') . '/warehouse-shoerack-sync';

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-KEY' => $this->apiKey,
                    'Accept'    => 'application/json',
                ])
                ->get($endpoint, [
                    'force_refresh' => $forceRefresh ? 1 : 0,
                ]);

            if ($response->status() === 401) {
                throw new Exception("Unauthorized: Invalid API Key.");
            }

            if ($response->status() === 404) {
                throw new Exception("Endpoint Not Found (404): $endpoint");
            }

            if ($response->serverError()) {
                throw new Exception("Server Error (500) from Shoerack API.");
            }

            $response->throw();

            $data = $response->json();

            if (isset($data['data'])) {
                return $data['data'];
            }

            return $data;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new Exception("Connection Timeout: Could not connect to Shoerack API.");
        } catch (RequestException $e) {
            throw new Exception("API Request Error: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Fetch warehouse piutang before data.
     */
    public function fetchPiutangBeforeData(bool $forceRefresh = false): array
    {
        $endpoint = rtrim($this->baseUrl, '/') . '/warehouse-piutang-before-sync';

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-KEY' => $this->apiKey,
                    'Accept'    => 'application/json',
                ])
                ->get($endpoint, [
                    'force_refresh' => $forceRefresh ? 1 : 0,
                ]);

            if ($response->status() === 401) {
                throw new Exception("Unauthorized: Invalid API Key.");
            }

            if ($response->status() === 404) {
                throw new Exception("Endpoint Not Found (404): $endpoint");
            }

            if ($response->serverError()) {
                throw new Exception("Server Error (500) from Piutang Before API.");
            }

            $response->throw();

            $data = $response->json();

            if (isset($data['data'])) {
                return $data['data'];
            }

            return $data;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new Exception("Connection Timeout: Could not connect to Piutang Before API.");
        } catch (RequestException $e) {
            throw new Exception("API Request Error: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Fetch warehouse piutang after data.
     */
    public function fetchPiutangAfterData(bool $forceRefresh = false): array
    {
        $endpoint = rtrim($this->baseUrl, '/') . '/warehouse-piutang-sync';

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-KEY' => $this->apiKey,
                    'Accept'    => 'application/json',
                ])
                ->get($endpoint, [
                    'force_refresh' => $forceRefresh ? 1 : 0,
                ]);

            if ($response->status() === 401) {
                throw new Exception("Unauthorized: Invalid API Key.");
            }

            if ($response->status() === 404) {
                throw new Exception("Endpoint Not Found (404): $endpoint");
            }

            if ($response->serverError()) {
                throw new Exception("Server Error (500) from Piutang After API.");
            }

            $response->throw();

            $data = $response->json();

            if (isset($data['data'])) {
                return $data['data'];
            }

            return $data;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new Exception("Connection Timeout: Could not connect to Piutang After API.");
        } catch (RequestException $e) {
            throw new Exception("API Request Error: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Fetch warehouse sortir summary data.
     */
    public function fetchSortirSummary(bool $forceRefresh = false): array
    {
        $endpoint = rtrim($this->baseUrl, '/') . '/warehouse-sortir-summary';

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-KEY' => $this->apiKey,
                    'Accept'    => 'application/json',
                ])
                ->get($endpoint, [
                    'force_refresh' => $forceRefresh ? 1 : 0,
                ]);

            if ($response->status() === 401) {
                throw new Exception("Unauthorized: Invalid API Key.");
            }

            if ($response->status() === 404) {
                throw new Exception("Endpoint Not Found (404): $endpoint");
            }

            if ($response->serverError()) {
                throw new Exception("Server Error (500) from Sortir API.");
            }

            $response->throw();

            $data = $response->json();

            if (isset($data['data'])) {
                return $data['data'];
            }

            return $data;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new Exception("Connection Timeout: Could not connect to Sortir API.");
        } catch (RequestException $e) {
            throw new Exception("API Request Error: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Fetch warehouse production summary data.
     */
    public function fetchProductionSummary(bool $forceRefresh = false): array
    {
        $endpoint = rtrim($this->baseUrl, '/') . '/warehouse-production-summary';

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-KEY' => $this->apiKey,
                    'Accept'    => 'application/json',
                ])
                ->get($endpoint, [
                    'force_refresh' => $forceRefresh ? 1 : 0,
                ]);

            if ($response->status() === 401) {
                throw new Exception("Unauthorized: Invalid API Key.");
            }

            if ($response->status() === 404) {
                throw new Exception("Endpoint Not Found (404): $endpoint");
            }

            if ($response->serverError()) {
                throw new Exception("Server Error (500) from Production API.");
            }

            $response->throw();

            $data = $response->json();

            if (isset($data['data'])) {
                return $data['data'];
            }

            return $data;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new Exception("Connection Timeout: Could not connect to Production API.");
        } catch (RequestException $e) {
            throw new Exception("API Request Error: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Fetch warehouse qc summary data.
     */
    public function fetchQcSummary(bool $forceRefresh = false): array
    {
        $endpoint = rtrim($this->baseUrl, '/') . '/warehouse-qc-summary';

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'X-API-KEY' => $this->apiKey,
                    'Accept'    => 'application/json',
                ])
                ->get($endpoint, [
                    'force_refresh' => $forceRefresh ? 1 : 0,
                ]);

            if ($response->status() === 401) {
                throw new Exception("Unauthorized: Invalid API Key.");
            }

            if ($response->status() === 404) {
                throw new Exception("Endpoint Not Found (404): $endpoint");
            }

            if ($response->serverError()) {
                throw new Exception("Server Error (500) from QC API.");
            }

            $response->throw();

            $data = $response->json();

            if (isset($data['data'])) {
                return $data['data'];
            }

            return $data;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new Exception("Connection Timeout: Could not connect to QC API.");
        } catch (RequestException $e) {
            throw new Exception("API Request Error: " . $e->getMessage());
        } catch (Exception $e) {
            throw $e;
        }
    }
}

