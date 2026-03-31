<?php

$date = '2026-03-30';
$adAccountId = 'act_1922369221497688';
$baseUrl = "https://graph.facebook.com/v19.0";

// Manually load .env
$accessToken = '';
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        if (trim($name) === 'META_ADS_ACCESS_TOKEN') {
            $accessToken = trim(trim($value), '"\'');
            break;
        }
    }
}

function fetchMeta($url, $params) {
    $query = http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$url?$query");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $res = curl_exec($ch);
    return json_decode($res, true);
}

echo "--- CLICK METRICS INSPECTION ($date) ---\n";

$insights = fetchMeta("$baseUrl/$adAccountId/insights", [
    'access_token' => $accessToken,
    'level' => 'campaign',
    'time_range' => json_encode(['since' => $date, 'until' => $date]),
    'fields' => 'campaign_name,clicks,unique_clicks,inline_link_clicks,unique_inline_link_clicks,ctr,cpc,spend,impressions',
    'filtering' => json_encode([
        ['field' => 'campaign.name', 'operator' => 'CONTAIN', 'value' => 'Testimoni']
    ])
]);

if (isset($insights['data'][0])) {
    $c = $insights['data'][0];
    echo "Campaign: {$c['campaign_name']}\n";
    echo "Spend: " . $c['spend'] . "\n";
    echo "Impressions: " . $c['impressions'] . "\n";
    echo "Clicks (Regular 'clicks' field): " . $c['clicks'] . "\n";
    echo "Inline Link Clicks: " . $c['inline_link_clicks'] . "\n";
    echo "CTR (Regular 'ctr' field): " . $c['ctr'] . "%\n";
    echo "CPC (Regular 'cpc' field): " . $c['cpc'] . "\n";
    
    // Calculated
    if ($c['clicks'] > 0) {
        echo "Calculated CTR All: " . ($c['clicks'] / $c['impressions'] * 100) . "%\n";
        echo "Calculated CPC All: " . ($c['spend'] / $c['clicks']) . "\n";
    }
} else {
    echo "FAILED to find campaign info.\n";
}

echo "\n--- INSPECTION FINISHED ---\n";
