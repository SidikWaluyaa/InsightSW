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
    ->selectRaw('SUM(spend) as spend, SUM(impressions) as impressions, SUM(clicks) as clicks, SUM(link_click) as link_clicks')
    ->first();

echo "--- FINAL DATA RECONCILIATION ($date) ---\n";
echo "Campaign: $campaign\n";
echo "Spend: Rp " . number_format($data->spend, 0, ',', '.') . "\n";
echo "Impressions: " . number_format($data->impressions, 0, '.', '.') . "\n";
echo "Clicks (All): " . $data->clicks . "\n";
echo "Link Clicks: " . $data->link_clicks . "\n";

if ($data->impressions > 0) {
    echo "CTR (All): " . number_format(($data->clicks / $data->impressions * 100), 2) . "%\n";
}
if ($data->clicks > 0) {
    echo "CPC (All): Rp " . number_format(($data->spend / $data->clicks), 0, ',', '.') . "\n";
}

echo "--- VERIFICATION FINISHED ---\n";
