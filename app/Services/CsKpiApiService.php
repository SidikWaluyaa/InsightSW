<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class CsKpiApiService
{
    protected string $url;
    protected string $apiKey;

    public function __construct()
    {
        $this->url = config('services.dashboard.base_url', 'https://info.shoeworkshop.id/api/v1') . '/cs-kpi-leaderboard';
        $this->apiKey = config('services.dashboard.key', 'sws_live_6f8g9h0j1k2l3m4n5o6p7q8r9s0');
    }

    /**
     * Fetch CS KPI Leaderboard data from API.
     * Falls back to mock data if unauthorized/redirected.
     *
     * @param string $startDate (Format: YYYY-MM-DD)
     * @param string $endDate (Format: YYYY-MM-DD)
     * @return array
     */
    public function fetchKpiData(string $startDate, string $endDate): array
    {
        try {
            $response = Http::timeout(15)
                ->get($this->url, [
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                    'api_key'    => $this->apiKey,
                ]);

            if ($response->redirect() || $response->status() === 401 || $response->status() === 403) {
                throw new Exception("API redirected or returned unauthorized: " . $response->status());
            }

            if ($response->failed()) {
                throw new Exception("API response failed with status: " . $response->status());
            }

            $data = $response->json();
            if (isset($data['status']) && $data['status'] === 'success' && isset($data['data'])) {
                // Map the flat array of CS data to calculate summaries & per-CS structure
                $totalRevenue = 0;
                $totalClosing = 0;
                $totalClosingDirect = 0;
                $totalClosingFollowup = 0;
                $totalSepatuDiterima = 0;
                $totalSepatuDiterimaOnline = 0;
                $totalSepatuDiterimaOffline = 0;
                $totalSpkPending = 0;
                $totalBatal = 0;
                $perCs = [];

                foreach ($data['data'] as $item) {
                    $csName = $item['cs_name'] ?? '-';
                    $leads = (int) ($item['total_leads'] ?? 0);
                    $intake = (int) ($item['incoming_items'] ?? 0);
                    $intakeOnline = (int) ($item['incoming_items_online'] ?? 0);
                    $intakeOffline = (int) ($item['incoming_items_offline'] ?? 0);
                    
                    $closing = (int) ($item['closings'] ?? 0);
                    $closingDirect = (int) ($item['closing_direct'] ?? 0);
                    $closingFollowup = (int) ($item['closing_via_followup'] ?? 0);
                    
                    $sepatuDiterima = (int) ($item['sepatu_diterima'] ?? 0);
                    $sepatuDiterimaOnline = (int) ($item['sepatu_diterima_online'] ?? 0);
                    $sepatuDiterimaOffline = (int) ($item['sepatu_diterima_offline'] ?? 0);
                    
                    $spkPending = (int) ($item['sepatu_spk_pending'] ?? 0);
                    $batal = (int) ($item['sepatu_batal'] ?? 0);
                    $revenue = (float) ($item['revenue'] ?? 0);

                    // Determine avatar color
                    $colors = ['bg-amber-500', 'bg-indigo-400', 'bg-orange-500', 'bg-slate-700', 'bg-cyan-600', 'bg-emerald-600', 'bg-blue-600', 'bg-rose-600', 'bg-teal-600', 'bg-fuchsia-600', 'bg-violet-600', 'bg-pink-600'];
                    $colorIndex = abs(crc32($csName)) % count($colors);
                    $avatarColor = $colors[$colorIndex];

                    $perCs[] = [
                        'cs_name' => $csName,
                        'leads' => $leads,
                        'intake' => $intake,
                        'intake_detail' => ['online' => $intakeOnline, 'offline' => $intakeOffline],
                        'closing' => $closing,
                        'closing_detail' => ['direct' => $closingDirect, 'followup' => $closingFollowup],
                        'sepatu_diterima' => $sepatuDiterima,
                        'sepatu_diterima_detail' => ['online' => $sepatuDiterimaOnline, 'offline' => $sepatuDiterimaOffline],
                        'spk_pending' => $spkPending,
                        'batal' => $batal,
                        'revenue' => $revenue,
                        'avatar_color' => $avatarColor
                    ];

                    // Sum totals
                    $totalRevenue += $revenue;
                    $totalClosing += $closing;
                    $totalClosingDirect += $closingDirect;
                    $totalClosingFollowup += $closingFollowup;
                    $totalSepatuDiterima += $sepatuDiterima;
                    $totalSepatuDiterimaOnline += $sepatuDiterimaOnline;
                    $totalSepatuDiterimaOffline += $sepatuDiterimaOffline;
                    $totalSpkPending += $spkPending;
                    $totalBatal += $batal;
                }

                // Sort perCs by closing descending
                usort($perCs, function($a, $b) {
                    if ($b['closing'] === $a['closing']) {
                        return $b['revenue'] <=> $a['revenue'];
                    }
                    return $b['closing'] <=> $a['closing'];
                });

                $topCs = [
                    'name' => '-',
                    'closing' => 0,
                    'revenue' => 0
                ];
                if (count($perCs) > 0) {
                    $topCs = [
                        'name' => $perCs[0]['cs_name'],
                        'closing' => $perCs[0]['closing'],
                        'revenue' => $perCs[0]['revenue']
                    ];
                }

                return [
                    'is_mock' => false,
                    'summary' => [
                        'total_revenue' => $totalRevenue,
                        'total_closing' => $totalClosing,
                        'total_closing_detail' => [
                            'direct' => $totalClosingDirect,
                            'followup' => $totalClosingFollowup
                        ],
                        'total_sepatu_diterima' => $totalSepatuDiterima,
                        'total_sepatu_diterima_detail' => [
                            'online' => $totalSepatuDiterimaOnline,
                            'offline' => $totalSepatuDiterimaOffline
                        ],
                        'total_spk_pending' => $totalSpkPending,
                        'total_batal' => $totalBatal,
                        'top_cs' => $topCs
                    ],
                    'per_cs' => $perCs,
                    'period' => [
                        'start' => $startDate,
                        'end' => $endDate,
                    ],
                    'metadata' => [
                        'last_updated' => now()->toIso8601String(),
                        'timezone' => 'Asia/Jakarta'
                    ]
                ];
            }

            throw new Exception("Invalid response format from KPI API.");

        } catch (Exception $e) {
            Log::info("CsKpiApiService fell back to mock data: " . $e->getMessage());
            return $this->getMockData($startDate, $endDate);
        }
    }

    /**
     * Generate structured mock data matching the designs in Screenshots 1 & 2.
     * Scale values based on the number of days in the selected date range.
     */
    protected function getMockData(string $startDate, string $endDate): array
    {
        $startStr = $startDate ?: date('Y-m-d');
        $endStr = $endDate ?: date('Y-m-d');
        
        $startTime = strtotime($startStr);
        $endTime = strtotime($endStr);
        
        // Calculate days difference
        $diff = $endTime - $startTime;
        $days = max(1, round($diff / (60 * 60 * 24)) + 1);
        
        // Base range is 18 days (2026-06-01 to 2026-06-18)
        $factor = $days / 18.0;

        $basePerCs = [
            [
                'cs_name' => 'Rizqi',
                'leads' => 159,
                'intake' => 159,
                'intake_detail' => ['online' => 128, 'offline' => 31],
                'closing' => 159,
                'closing_detail' => ['direct' => 159, 'followup' => 0],
                'sepatu_diterima' => 91,
                'sepatu_diterima_detail' => ['online' => 60, 'offline' => 31],
                'spk_pending' => 67,
                'batal' => 1,
                'revenue' => 25516000,
                'avatar_color' => 'bg-amber-500'
            ],
            [
                'cs_name' => 'Alqia',
                'leads' => 158,
                'intake' => 157,
                'intake_detail' => ['online' => 134, 'offline' => 23],
                'closing' => 157,
                'closing_detail' => ['direct' => 157, 'followup' => 0],
                'sepatu_diterima' => 96,
                'sepatu_diterima_detail' => ['online' => 73, 'offline' => 23],
                'spk_pending' => 61,
                'batal' => 0,
                'revenue' => 24351498,
                'avatar_color' => 'bg-indigo-400'
            ],
            [
                'cs_name' => 'Ferdian',
                'leads' => 123,
                'intake' => 124,
                'intake_detail' => ['online' => 112, 'offline' => 12],
                'closing' => 124,
                'closing_detail' => ['direct' => 124, 'followup' => 0],
                'sepatu_diterima' => 56,
                'sepatu_diterima_detail' => ['online' => 46, 'offline' => 10],
                'spk_pending' => 67,
                'batal' => 1,
                'revenue' => 20460000,
                'avatar_color' => 'bg-orange-500'
            ],
            [
                'cs_name' => 'Alwan',
                'leads' => 117,
                'intake' => 118,
                'intake_detail' => ['online' => 95, 'offline' => 23],
                'closing' => 118,
                'closing_detail' => ['direct' => 117, 'followup' => 1],
                'sepatu_diterima' => 84,
                'sepatu_diterima_detail' => ['online' => 61, 'offline' => 23],
                'spk_pending' => 33,
                'batal' => 1,
                'revenue' => 20585000,
                'avatar_color' => 'bg-slate-700'
            ],
            [
                'cs_name' => 'Vina',
                'leads' => 111,
                'intake' => 110,
                'intake_detail' => ['online' => 82, 'offline' => 28],
                'closing' => 110,
                'closing_detail' => ['direct' => 110, 'followup' => 0],
                'sepatu_diterima' => 59,
                'sepatu_diterima_detail' => ['online' => 31, 'offline' => 28],
                'spk_pending' => 51,
                'batal' => 0,
                'revenue' => 15575000,
                'avatar_color' => 'bg-cyan-600'
            ],
            [
                'cs_name' => 'Sabila',
                'leads' => 107,
                'intake' => 107,
                'intake_detail' => ['online' => 97, 'offline' => 10],
                'closing' => 107,
                'closing_detail' => ['direct' => 107, 'followup' => 0],
                'sepatu_diterima' => 69,
                'sepatu_diterima_detail' => ['online' => 59, 'offline' => 10],
                'spk_pending' => 37,
                'batal' => 1,
                'revenue' => 20557500,
                'avatar_color' => 'bg-emerald-600'
            ],
            [
                'cs_name' => 'Mery',
                'leads' => 101,
                'intake' => 101,
                'intake_detail' => ['online' => 75, 'offline' => 26],
                'closing' => 101,
                'closing_detail' => ['direct' => 101, 'followup' => 0],
                'sepatu_diterima' => 73,
                'sepatu_diterima_detail' => ['online' => 48, 'offline' => 25],
                'spk_pending' => 28,
                'batal' => 0,
                'revenue' => 21605000,
                'avatar_color' => 'bg-blue-600'
            ],
            [
                'cs_name' => 'Yuniar',
                'leads' => 0,
                'intake' => 0,
                'intake_detail' => ['online' => 0, 'offline' => 0],
                'closing' => 0,
                'closing_detail' => ['direct' => 0, 'followup' => 0],
                'sepatu_diterima' => 0,
                'sepatu_diterima_detail' => ['online' => 0, 'offline' => 0],
                'spk_pending' => 0,
                'batal' => 0,
                'revenue' => 0,
                'avatar_color' => 'bg-rose-600'
            ]
        ];

        $scaledPerCs = [];
        
        $totalRevenue = 0;
        $totalClosing = 0;
        $totalClosingDirect = 0;
        $totalClosingFollowup = 0;
        $totalSepatuDiterima = 0;
        $totalSepatuDiterimaOnline = 0;
        $totalSepatuDiterimaOffline = 0;
        $totalSpkPending = 0;
        $totalBatal = 0;

        foreach ($basePerCs as $cs) {
            $scaledLeads = (int) round($cs['leads'] * $factor);
            $scaledIntake = (int) round($cs['intake'] * $factor);
            
            $scaledIntakeOnline = (int) round($cs['intake_detail']['online'] * $factor);
            $scaledIntakeOffline = (int) round($cs['intake_detail']['offline'] * $factor);
            
            $scaledClosing = (int) round($cs['closing'] * $factor);
            $scaledClosingDirect = (int) round($cs['closing_detail']['direct'] * $factor);
            $scaledClosingFollowup = (int) round($cs['closing_detail']['followup'] * $factor);
            
            $scaledSepatuDiterima = (int) round($cs['sepatu_diterima'] * $factor);
            $scaledSepatuDiterimaOnline = (int) round($cs['sepatu_diterima_detail']['online'] * $factor);
            $scaledSepatuDiterimaOffline = (int) round($cs['sepatu_diterima_detail']['offline'] * $factor);
            
            $scaledSpkPending = (int) round($cs['spk_pending'] * $factor);
            $scaledBatal = (int) round($cs['batal'] * $factor);
            $scaledRevenue = (int) round($cs['revenue'] * $factor);

            $scaledPerCs[] = [
                'cs_name' => $cs['cs_name'],
                'leads' => $scaledLeads,
                'intake' => $scaledIntake,
                'intake_detail' => ['online' => $scaledIntakeOnline, 'offline' => $scaledIntakeOffline],
                'closing' => $scaledClosing,
                'closing_detail' => ['direct' => $scaledClosingDirect, 'followup' => $scaledClosingFollowup],
                'sepatu_diterima' => $scaledSepatuDiterima,
                'sepatu_diterima_detail' => ['online' => $scaledSepatuDiterimaOnline, 'offline' => $scaledSepatuDiterimaOffline],
                'spk_pending' => $scaledSpkPending,
                'batal' => $scaledBatal,
                'revenue' => $scaledRevenue,
                'avatar_color' => $cs['avatar_color']
            ];

            // Sum totals
            $totalRevenue += $scaledRevenue;
            $totalClosing += $scaledClosing;
            $totalClosingDirect += $scaledClosingDirect;
            $totalClosingFollowup += $scaledClosingFollowup;
            $totalSepatuDiterima += $scaledSepatuDiterima;
            $totalSepatuDiterimaOnline += $scaledSepatuDiterimaOnline;
            $totalSepatuDiterimaOffline += $scaledSepatuDiterimaOffline;
            $totalSpkPending += $scaledSpkPending;
            $totalBatal += $scaledBatal;
        }

        // Find top CS performer based on scaled closing
        usort($scaledPerCs, function($a, $b) {
            return $b['closing'] <=> $a['closing'];
        });

        $topCs = [
            'name' => '-',
            'closing' => 0,
            'revenue' => 0
        ];
        if (count($scaledPerCs) > 0) {
            $topCs = [
                'name' => $scaledPerCs[0]['cs_name'],
                'closing' => $scaledPerCs[0]['closing'],
                'revenue' => $scaledPerCs[0]['revenue']
            ];
        }

        return [
            'is_mock' => true,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_closing' => $totalClosing,
                'total_closing_detail' => [
                    'direct' => $totalClosingDirect,
                    'followup' => $totalClosingFollowup
                ],
                'total_sepatu_diterima' => $totalSepatuDiterima,
                'total_sepatu_diterima_detail' => [
                    'online' => $totalSepatuDiterimaOnline,
                    'offline' => $totalSepatuDiterimaOffline
                ],
                'total_spk_pending' => $totalSpkPending,
                'total_batal' => $totalBatal,
                'top_cs' => $topCs
            ],
            'per_cs' => $scaledPerCs,
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'metadata' => [
                'last_updated' => now()->toIso8601String(),
                'timezone' => 'Asia/Jakarta'
            ]
        ];
    }
}
