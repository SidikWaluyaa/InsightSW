<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class CsForecastingApiService
{
    protected string $url;
    protected string $apiKey;

    public function __construct()
    {
        $this->url = config('services.dashboard.base_url', 'https://info.shoeworkshop.id/api/v1') . '/cs-forecasting';
        $this->apiKey = config('services.dashboard.key', 'sws_live_6f8g9h0j1k2l3m4n5o6p7q8r9s0');
    }

    /**
     * Fetch CS Forecasting data from API.
     * Falls back to mock data if API fails.
     *
     * @param int $year
     * @return array
     */
    public function fetchForecastingData(int $year): array
    {
        try {
            $response = Http::timeout(15)
                ->get($this->url, [
                    'year'    => $year,
                    'api_key' => $this->apiKey,
                ]);

            if ($response->redirect() || $response->status() === 401 || $response->status() === 403) {
                throw new Exception("API redirected or returned unauthorized: " . $response->status());
            }

            if ($response->failed()) {
                throw new Exception("API response failed with status: " . $response->status());
            }

            $data = $response->json();
            if (isset($data['status']) && $data['status'] === 'success' && isset($data['data'])) {
                return [
                    'is_mock' => false,
                    'data' => $data['data']
                ];
            }

            throw new Exception("Invalid response format from Forecasting API.");

        } catch (Exception $e) {
            Log::info("CsForecastingApiService fell back to mock data: " . $e->getMessage());
            return [
                'is_mock' => true,
                'data' => $this->getMockData($year)
            ];
        }
    }

    /**
     * Generate mock forecasting data in case the API is offline.
     */
    protected function getMockData(int $year): array
    {
        // Static mockup data based on real response for 2026
        $data2026 = [
            [
                "month_name" => "Januari 2026",
                "days_in_period" => 31,
                "closing_online" => 0,
                "closing_online_pct" => 0,
                "closing_online_per_day" => 0,
                "closing_followup" => 0,
                "closing_followup_pct" => 0,
                "closing_followup_per_day" => 0,
                "closing_offline" => 0,
                "closing_offline_pct" => 0,
                "closing_offline_per_day" => 0,
                "closing_tidak_kirim" => 0,
                "closing_tidak_kirim_pct" => 0,
                "closing_tidak_kirim_per_day" => 0,
                "total_closing" => 0,
                "sepatu_masuk_online" => 0,
                "sepatu_masuk_offline" => 0,
                "sepatu_online_pct" => 0,
                "sepatu_offline_pct" => 0,
                "omset_total" => "0.00",
                "terbayar" => 0,
                "terbayar_pct" => 0,
                "total_dp" => 0,
                "dp_pct" => 0,
                "total_lunas_awal" => 0,
                "lunas_awal_pct" => 0,
                "total_pelunasan" => 0,
                "pelunasan_pct" => 0,
                "tambah_jasa" => 0,
                "tambah_jasa_pct" => 0,
                "oto" => 0,
                "oto_pct" => 0,
                "ongkir" => 0,
                "ongkir_pct" => 0
            ],
            [
                "month_name" => "Februari 2026",
                "days_in_period" => 28,
                "closing_online" => 444,
                "closing_online_pct" => 78.86,
                "closing_online_per_day" => 15.86,
                "closing_followup" => 2,
                "closing_followup_pct" => 0.36,
                "closing_followup_per_day" => 0.07,
                "closing_offline" => 117,
                "closing_offline_pct" => 20.78,
                "closing_offline_per_day" => 4.18,
                "closing_tidak_kirim" => 79,
                "closing_tidak_kirim_pct" => 17.71,
                "closing_tidak_kirim_per_day" => 2.82,
                "total_closing" => 563,
                "sepatu_masuk_online" => 428,
                "sepatu_masuk_offline" => 169,
                "sepatu_online_pct" => 71.69,
                "sepatu_offline_pct" => 28.31,
                "omset_total" => "2950000.00",
                "terbayar" => "8756740.00",
                "terbayar_pct" => 296.84,
                "total_dp" => "2632500.00",
                "dp_pct" => 89.24,
                "total_lunas_awal" => "870000.00",
                "lunas_awal_pct" => 29.49,
                "total_pelunasan" => "5254240.00",
                "pelunasan_pct" => 178.11,
                "tambah_jasa" => 0,
                "tambah_jasa_pct" => 0,
                "oto" => 0,
                "oto_pct" => 0,
                "ongkir" => 0,
                "ongkir_pct" => 0
            ],
            [
                "month_name" => "Maret 2026",
                "days_in_period" => 31,
                "closing_online" => 761,
                "closing_online_pct" => 80.02,
                "closing_online_per_day" => 24.55,
                "closing_followup" => 10,
                "closing_followup_pct" => 1.05,
                "closing_followup_per_day" => 0.32,
                "closing_offline" => 180,
                "closing_offline_pct" => 18.93,
                "closing_offline_per_day" => 5.81,
                "closing_tidak_kirim" => 256,
                "closing_tidak_kirim_pct" => 33.2,
                "closing_tidak_kirim_per_day" => 8.26,
                "total_closing" => 951,
                "sepatu_masuk_online" => 550,
                "sepatu_masuk_offline" => 215,
                "sepatu_online_pct" => 71.9,
                "sepatu_offline_pct" => 28.1,
                "omset_total" => "196874975.00",
                "terbayar" => "173009722.00",
                "terbayar_pct" => 87.88,
                "total_dp" => "171013507.00",
                "dp_pct" => 86.86,
                "total_lunas_awal" => 0,
                "lunas_awal_pct" => 0,
                "total_pelunasan" => "1986215.00",
                "pelunasan_pct" => 1.01,
                "tambah_jasa" => 0,
                "tambah_jasa_pct" => 0,
                "oto" => 0,
                "oto_pct" => 0,
                "ongkir" => "10000.00",
                "ongkir_pct" => 0.01
            ],
            [
                "month_name" => "April 2026",
                "days_in_period" => 30,
                "closing_online" => 1129,
                "closing_online_pct" => 82.83,
                "closing_online_per_day" => 37.63,
                "closing_followup" => 15,
                "closing_followup_pct" => 1.1,
                "closing_followup_per_day" => 0.5,
                "closing_offline" => 219,
                "closing_offline_pct" => 16.07,
                "closing_offline_per_day" => 7.3,
                "closing_tidak_kirim" => 369,
                "closing_tidak_kirim_pct" => 32.26,
                "closing_tidak_kirim_per_day" => 12.3,
                "total_closing" => 1363,
                "sepatu_masuk_online" => 697,
                "sepatu_masuk_offline" => 207,
                "sepatu_online_pct" => 77.1,
                "sepatu_offline_pct" => 22.9,
                "omset_total" => "286524397.00",
                "terbayar" => "252317076.00",
                "terbayar_pct" => 88.06,
                "total_dp" => "132769125.00",
                "dp_pct" => 46.34,
                "total_lunas_awal" => "300415.00",
                "lunas_awal_pct" => 0.1,
                "total_pelunasan" => "118922536.00",
                "pelunasan_pct" => 41.51,
                "tambah_jasa" => "125000.00",
                "tambah_jasa_pct" => 0.04,
                "oto" => 0,
                "oto_pct" => 0,
                "ongkir" => "200000.00",
                "ongkir_pct" => 0.07
            ],
            [
                "month_name" => "Mei 2026",
                "days_in_period" => 31,
                "closing_online" => 1285,
                "closing_online_pct" => 82.96,
                "closing_online_per_day" => 41.45,
                "closing_followup" => 10,
                "closing_followup_pct" => 0.65,
                "closing_followup_per_day" => 0.32,
                "closing_offline" => 254,
                "closing_offline_pct" => 16.4,
                "closing_offline_per_day" => 8.19,
                "closing_tidak_kirim" => 467,
                "closing_tidak_kirim_pct" => 36.06,
                "closing_tidak_kirim_per_day" => 15.06,
                "total_closing" => 1549,
                "sepatu_masuk_online" => 782,
                "sepatu_masuk_offline" => 264,
                "sepatu_online_pct" => 74.76,
                "sepatu_offline_pct" => 25.24,
                "omset_total" => "315941702.00",
                "terbayar" => "295269987.00",
                "terbayar_pct" => 93.46,
                "total_dp" => "45357180.00",
                "dp_pct" => 14.36,
                "total_lunas_awal" => "139451253.00",
                "lunas_awal_pct" => 44.14,
                "total_pelunasan" => "102539858.00",
                "pelunasan_pct" => 32.46,
                "tambah_jasa" => "605000.00",
                "tambah_jasa_pct" => 0.19,
                "oto" => 0,
                "oto_pct" => 0,
                "ongkir" => "7316696.00",
                "ongkir_pct" => 2.32
            ],
            [
                "month_name" => "Juni 2026",
                "days_in_period" => 22,
                "closing_online" => 849,
                "closing_online_pct" => 82.35,
                "closing_online_per_day" => 38.59,
                "closing_followup" => 1,
                "closing_followup_pct" => 0.1,
                "closing_followup_per_day" => 0.05,
                "closing_offline" => 181,
                "closing_offline_pct" => 17.56,
                "closing_offline_per_day" => 8.23,
                "closing_tidak_kirim" => 343,
                "closing_tidak_kirim_pct" => 40.35,
                "closing_tidak_kirim_per_day" => 15.59,
                "total_closing" => 1031,
                "sepatu_masuk_online" => 475,
                "sepatu_masuk_offline" => 166,
                "sepatu_online_pct" => 74.1,
                "sepatu_offline_pct" => 25.9,
                "omset_total" => "223694998.00",
                "terbayar" => "206613465.00",
                "terbayar_pct" => 92.36,
                "total_dp" => "23836357.00",
                "dp_pct" => 10.66,
                "total_lunas_awal" => "158503521.00",
                "lunas_awal_pct" => 70.86,
                "total_pelunasan" => "11285749.00",
                "pelunasan_pct" => 5.05,
                "tambah_jasa" => "3466584.00",
                "tambah_jasa_pct" => 1.55,
                "oto" => 0,
                "oto_pct" => 0,
                "ongkir" => "9521254.00",
                "ongkir_pct" => 4.26
            ],
            [
                "month_name" => "Juli 2026",
                "days_in_period" => 31,
                "closing_online" => 0,
                "closing_online_pct" => 0,
                "closing_online_per_day" => 0,
                "closing_followup" => 0,
                "closing_followup_pct" => 0,
                "closing_followup_per_day" => 0,
                "closing_offline" => 0,
                "closing_offline_pct" => 0,
                "closing_offline_per_day" => 0,
                "closing_tidak_kirim" => 0,
                "closing_tidak_kirim_pct" => 0,
                "closing_tidak_kirim_per_day" => 0,
                "total_closing" => 0,
                "sepatu_masuk_online" => 0,
                "sepatu_masuk_offline" => 0,
                "sepatu_online_pct" => 0,
                "sepatu_offline_pct" => 0,
                "omset_total" => "0.00",
                "terbayar" => 0,
                "terbayar_pct" => 0,
                "total_dp" => 0,
                "dp_pct" => 0,
                "total_lunas_awal" => 0,
                "lunas_awal_pct" => 0,
                "total_pelunasan" => 0,
                "pelunasan_pct" => 0,
                "tambah_jasa" => 0,
                "tambah_jasa_pct" => 0,
                "oto" => 0,
                "oto_pct" => 0,
                "ongkir" => 0,
                "ongkir_pct" => 0
            ],
            [
                "month_name" => "Agustus 2026",
                "days_in_period" => 31,
                "closing_online" => 0,
                "closing_online_pct" => 0,
                "closing_online_per_day" => 0,
                "closing_followup" => 0,
                "closing_followup_pct" => 0,
                "closing_followup_per_day" => 0,
                "closing_offline" => 0,
                "closing_offline_pct" => 0,
                "closing_offline_per_day" => 0,
                "closing_tidak_kirim" => 0,
                "closing_tidak_kirim_pct" => 0,
                "closing_tidak_kirim_per_day" => 0,
                "total_closing" => 0,
                "sepatu_masuk_online" => 0,
                "sepatu_masuk_offline" => 0,
                "sepatu_online_pct" => 0,
                "sepatu_offline_pct" => 0,
                "omset_total" => "0.00",
                "terbayar" => 0,
                "terbayar_pct" => 0,
                "total_dp" => 0,
                "dp_pct" => 0,
                "total_lunas_awal" => 0,
                "lunas_awal_pct" => 0,
                "total_pelunasan" => 0,
                "pelunasan_pct" => 0,
                "tambah_jasa" => 0,
                "tambah_jasa_pct" => 0,
                "oto" => 0,
                "oto_pct" => 0,
                "ongkir" => 0,
                "ongkir_pct" => 0
            ],
            [
                "month_name" => "September 2026",
                "days_in_period" => 30,
                "closing_online" => 0,
                "closing_online_pct" => 0,
                "closing_online_per_day" => 0,
                "closing_followup" => 0,
                "closing_followup_pct" => 0,
                "closing_followup_per_day" => 0,
                "closing_offline" => 0,
                "closing_offline_pct" => 0,
                "closing_offline_per_day" => 0,
                "closing_tidak_kirim" => 0,
                "closing_tidak_kirim_pct" => 0,
                "closing_tidak_kirim_per_day" => 0,
                "total_closing" => 0,
                "sepatu_masuk_online" => 0,
                "sepatu_masuk_offline" => 0,
                "sepatu_online_pct" => 0,
                "sepatu_offline_pct" => 0,
                "omset_total" => "0.00",
                "terbayar" => 0,
                "terbayar_pct" => 0,
                "total_dp" => 0,
                "dp_pct" => 0,
                "total_lunas_awal" => 0,
                "lunas_awal_pct" => 0,
                "total_pelunasan" => 0,
                "pelunasan_pct" => 0,
                "tambah_jasa" => 0,
                "tambah_jasa_pct" => 0,
                "oto" => 0,
                "oto_pct" => 0,
                "ongkir" => 0,
                "ongkir_pct" => 0
            ],
            [
                "month_name" => "Oktober 2026",
                "days_in_period" => 31,
                "closing_online" => 0,
                "closing_online_pct" => 0,
                "closing_online_per_day" => 0,
                "closing_followup" => 0,
                "closing_followup_pct" => 0,
                "closing_followup_per_day" => 0,
                "closing_offline" => 0,
                "closing_offline_pct" => 0,
                "closing_offline_per_day" => 0,
                "closing_tidak_kirim" => 0,
                "closing_tidak_kirim_pct" => 0,
                "closing_tidak_kirim_per_day" => 0,
                "total_closing" => 0,
                "sepatu_masuk_online" => 0,
                "sepatu_masuk_offline" => 0,
                "sepatu_online_pct" => 0,
                "sepatu_offline_pct" => 0,
                "omset_total" => "0.00",
                "terbayar" => 0,
                "terbayar_pct" => 0,
                "total_dp" => 0,
                "dp_pct" => 0,
                "total_lunas_awal" => 0,
                "lunas_awal_pct" => 0,
                "total_pelunasan" => 0,
                "pelunasan_pct" => 0,
                "tambah_jasa" => 0,
                "tambah_jasa_pct" => 0,
                "oto" => 0,
                "oto_pct" => 0,
                "ongkir" => 0,
                "ongkir_pct" => 0
            ],
            [
                "month_name" => "November 2026",
                "days_in_period" => 30,
                "closing_online" => 0,
                "closing_online_pct" => 0,
                "closing_online_per_day" => 0,
                "closing_followup" => 0,
                "closing_followup_pct" => 0,
                "closing_followup_per_day" => 0,
                "closing_offline" => 0,
                "closing_offline_pct" => 0,
                "closing_offline_per_day" => 0,
                "closing_tidak_kirim" => 0,
                "closing_tidak_kirim_pct" => 0,
                "closing_tidak_kirim_per_day" => 0,
                "total_closing" => 0,
                "sepatu_masuk_online" => 0,
                "sepatu_masuk_offline" => 0,
                "sepatu_online_pct" => 0,
                "sepatu_offline_pct" => 0,
                "omset_total" => "0.00",
                "terbayar" => 0,
                "terbayar_pct" => 0,
                "total_dp" => 0,
                "dp_pct" => 0,
                "total_lunas_awal" => 0,
                "lunas_awal_pct" => 0,
                "total_pelunasan" => 0,
                "pelunasan_pct" => 0,
                "tambah_jasa" => 0,
                "tambah_jasa_pct" => 0,
                "oto" => 0,
                "oto_pct" => 0,
                "ongkir" => 0,
                "ongkir_pct" => 0
            ],
            [
                "month_name" => "Desember 2026",
                "days_in_period" => 31,
                "closing_online" => 0,
                "closing_online_pct" => 0,
                "closing_online_per_day" => 0,
                "closing_followup" => 0,
                "closing_followup_pct" => 0,
                "closing_followup_per_day" => 0,
                "closing_offline" => 0,
                "closing_offline_pct" => 0,
                "closing_offline_per_day" => 0,
                "closing_tidak_kirim" => 0,
                "closing_tidak_kirim_pct" => 0,
                "closing_tidak_kirim_per_day" => 0,
                "total_closing" => 0,
                "sepatu_masuk_online" => 0,
                "sepatu_masuk_offline" => 0,
                "sepatu_online_pct" => 0,
                "sepatu_offline_pct" => 0,
                "omset_total" => "0.00",
                "terbayar" => 0,
                "terbayar_pct" => 0,
                "total_dp" => 0,
                "dp_pct" => 0,
                "total_lunas_awal" => 0,
                "lunas_awal_pct" => 0,
                "total_pelunasan" => 0,
                "pelunasan_pct" => 0,
                "tambah_jasa" => 0,
                "tambah_jasa_pct" => 0,
                "oto" => 0,
                "oto_pct" => 0,
                "ongkir" => 0,
                "ongkir_pct" => 0
            ]
        ];

        if ($year === 2026) {
            return $data2026;
        }

        // Return scaled/adjusted data for other years to make it realistic
        $factor = ($year === 2025) ? 0.85 : 1.15;
        $modifiedData = [];

        foreach ($data2026 as $month) {
            $newMonth = $month;
            $newMonth['month_name'] = str_replace("2026", (string) $year, $month['month_name']);
            
            // Adjust closing
            $newMonth['closing_online'] = (int) round($month['closing_online'] * $factor);
            $newMonth['closing_online_per_day'] = round($month['closing_online_per_day'] * $factor, 2);
            $newMonth['closing_followup'] = (int) round($month['closing_followup'] * $factor);
            $newMonth['closing_followup_per_day'] = round($month['closing_followup_per_day'] * $factor, 2);
            $newMonth['closing_offline'] = (int) round($month['closing_offline'] * $factor);
            $newMonth['closing_offline_per_day'] = round($month['closing_offline_per_day'] * $factor, 2);
            $newMonth['closing_tidak_kirim'] = (int) round($month['closing_tidak_kirim'] * $factor);
            $newMonth['closing_tidak_kirim_per_day'] = round($month['closing_tidak_kirim_per_day'] * $factor, 2);
            
            $newMonth['total_closing'] = $newMonth['closing_online'] + $newMonth['closing_followup'] + $newMonth['closing_offline'];

            // Adjust sepatu masuk
            $newMonth['sepatu_masuk_online'] = (int) round($month['sepatu_masuk_online'] * $factor);
            $newMonth['sepatu_masuk_offline'] = (int) round($month['sepatu_masuk_offline'] * $factor);

            // Adjust financial numbers
            $newMonth['omset_total'] = (string) round((float) $month['omset_total'] * $factor, 2);
            $newMonth['terbayar'] = (string) round((float) $month['terbayar'] * $factor, 2);
            $newMonth['total_dp'] = (string) round((float) $month['total_dp'] * $factor, 2);
            $newMonth['total_lunas_awal'] = (string) round((float) $month['total_lunas_awal'] * $factor, 2);
            $newMonth['total_pelunasan'] = (string) round((float) $month['total_pelunasan'] * $factor, 2);
            $newMonth['tambah_jasa'] = (string) round((float) $month['tambah_jasa'] * $factor, 2);
            $newMonth['oto'] = (string) round((float) $month['oto'] * $factor, 2);
            $newMonth['ongkir'] = (string) round((float) $month['ongkir'] * $factor, 2);

            $modifiedData[] = $newMonth;
        }

        return $modifiedData;
    }
}
