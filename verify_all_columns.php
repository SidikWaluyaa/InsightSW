<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MetaAdsReport;

$date = '2026-03-30';
$campaign = 'Kampanye Testimoni';

$data = MetaAdsReport::where('campaign_name', 'LIKE', "%$campaign%")
    ->where('date', $date)
    ->selectRaw('SUM(clicks_all) as clicks_all, SUM(impressions) as impressions, SUM(spend) as spend')
    ->first();

echo "--- FINAL COLUMN RECONCILIATION ($date) ---\n";
echo "Campaign: $campaign\n";
echo "Clicks (All) in DB: " . $data->clicks_all . "\n";
echo "Impressions: " . $data->impressions . "\n";

if ($data->impressions > 0) {
    echo "CTR (All) Calculated: " . number_format(($data->clicks_all / $data->impressions * 100), 2) . "%\n";
}
if ($data->clicks_all > 0) {
    echo "CPC (All) Calculated: Rp " . number_format(($data->spend / $data->clicks_all), 0, ',', '.') . "\n";
}

echo "--- VERIFICATION FINISHED ---\n";
