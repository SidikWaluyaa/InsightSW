<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleSheetService;
use App\Models\QualityControlSnapshot;

class CaptureQCSnapshot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qc:capture-snapshot';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Capture the daily morning baseline for Quality Control Dashboard';

    /**
     * Execute the console command.
     */
    public function handle(GoogleSheetService $service)
    {
        $this->info('Starting Quality Control Snapshot capture...');

        // Defaults used in the dashboard
        $url = 'https://docs.google.com/spreadsheets/d/1Gok4uNalu5P5pRXrCWqcPwtOmodBuAZwI_L0OHH2gS8/edit';
        $gid = '1019775130';

        try {
            $data = $service->fetchData($url, $gid);

            if ($data->isEmpty()) {
                $this->error('Failed: No data retrieved from Google Sheet.');
                return 1;
            }

            // Calculate All-time Verified (Logika Kumulatif)
            // Normalized header from GoogleSheetService is 'checklist'
            $allTimeVerified = $data->filter(function($item) {
                $check = strtoupper(trim($item['checklist'] ?? ''));
                return $check === 'TRUE';
            })->count();

            $today = date('Y-m-d');

            // Save or Update today's baseline
            QualityControlSnapshot::updateOrCreate(
                ['snapshot_date' => $today],
                ['baseline_count' => $allTimeVerified]
            );

            $this->info("Success: Today's Baseline ($today) captured at $allTimeVerified Order.");
            return 0;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }
}
