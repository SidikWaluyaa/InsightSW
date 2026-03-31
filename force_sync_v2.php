<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\MetaAdsService;
use App\Models\MetaAdsReport;
use Illuminate\Support\Facades\DB;

$date = '2026-03-30';
$accountId = 'act_1922369221497688';

echo "--- FORCE SYNC START FOR $date ---\n";

$service = new MetaAdsService();
$success = $service->fetchAndSync($accountId, $date);

if ($success) {
    echo "Sync method execution: SUCCESS\n";
    
    // Verify the data for Testimoni
    $testimoni = MetaAdsReport::where('date', $date)
        ->where('campaign_name', 'LIKE', '%Testimoni%')
        ->selectRaw('campaign_name, SUM(spend) as total_spend, SUM(results) as total_results')
        ->groupBy('campaign_name')
        ->first();
        
    if ($testimoni) {
        echo "\nRESULT IN DATABASE:\n";
        echo "Campaign: " . $testimoni->campaign_name . "\n";
        echo "Total Spend: Rp " . number_format($testimoni->total_spend, 0, ',', '.') . "\n";
        echo "Total Results: " . $testimoni->total_results . "\n";
    } else {
        echo "NOT FOUND: No data for Testimoni on $date after sync.\n";
    }
} else {
    echo "Sync method execution: FAILED\n";
}

echo "\n--- FORCE SYNC FINISHED ---\n";
