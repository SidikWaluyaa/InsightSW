<?php

namespace App\Services;

use App\Models\WorkshopMatrix;
use App\Models\WorkshopMetric;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkshopSyncService
{
    protected WorkshopApiService $api;

    public function __construct(WorkshopApiService $api)
    {
        $this->api = $api;
    }

    /**
     * Synchronize entire workshop data.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function sync(?string $startDate = null, ?string $endDate = null): array
    {
        try {
            DB::beginTransaction();

            $data = $this->api->fetchWorkshopSync($startDate, $endDate);

            // 1. Process Matrix Data
            if (isset($data['matrix'])) {
                foreach ($data['matrix'] as $phaseName => $phaseData) {
                    $totalGroup = $phaseData['total'] ?? 0;
                    $bottleneckKey = $phaseData['bottleneck'] ?? null;
                    
                    foreach ($phaseData as $stageKey => $stageVal) {
                        // Skip reserved keys that are not actual stages
                        if (in_array($stageKey, ['total', 'total_followup', 'bottleneck'])) {
                            continue;
                        }

                        // stageVal is an array with 'count' and 'avg_hours'
                        $count = is_array($stageVal) ? ($stageVal['count'] ?? 0) : 0;
                        $avgHours = is_array($stageVal) ? ($stageVal['avg_hours'] ?? 0) : 0;
                        
                        WorkshopMatrix::updateOrCreate(
                            ['phase' => $phaseName, 'sub_stage' => $stageKey],
                            [
                                'count' => $count,
                                'avg_hours' => $avgHours,
                                'total_group_at_sync' => $totalGroup,
                                'is_bottleneck' => $stageKey === $bottleneckKey
                            ]
                        );
                    }
                }
            }

            // 2. Process Metrics, Analytics, and Operational Data
            if (isset($data['metrics'])) {
                $snapshot = $data['metrics']['snapshot'] ?? [];
                $historical = $data['metrics']['historical'] ?? [];
                $a = $data['analytics'] ?? [];
                $o = $data['operational'] ?? [];

                WorkshopMetric::create([
                    // Basic Metrics (Snapshot)
                    'in_progress'   => $snapshot['in_progress'] ?? 0,
                    'urgent'        => $snapshot['urgent'] ?? 0,
                    'overdue'       => $snapshot['overdue'] ?? 0,
                    
                    // Historical Metrics (Period)
                    'total_revenue' => $historical['revenue'] ?? 0,
                    'throughput'    => $historical['throughput'] ?? 0,
                    'avg_lead_time' => $historical['avg_lead_time'] ?? 0,
                    'qc_pass_rate'  => $historical['qc_pass_rate'] ?? 0,

                    // Analytics Suite
                    'pipeline'      => $a['pipeline'] ?? null,
                    'trends'        => $a['trends'] ?? null,
                    'workload'      => $a['workload'] ?? null,
                    'service_mix'   => $a['service_mix'] ?? null,
                    'leaderboard'   => $a['leaderboard'] ?? null,

                    // Operational Alerts
                    'urgent_orders'   => $o['urgent_orders'] ?? null,
                    'stock_alerts'    => $o['stock_alerts'] ?? null,
                    'recent_activity' => $o['recent_activity'] ?? null,

                    'last_sync_at'  => now(),
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Workshop data synchronized successfully.',
                'last_sync' => now()->toDateTimeString()
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Workshop Sync Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
