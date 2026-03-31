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
            $accessToken = trim($value);
            // Handle quotes if any
            $accessToken = trim($accessToken, '"\'');
            break;
        }
    }
}

if (!$accessToken) {
    die("FATAL: META_ADS_ACCESS_TOKEN not found in .env\n");
}

function fetchMeta($url, $params) {
    $query = http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$url?$query");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $res = curl_exec($ch);
    if (curl_errno($ch)) return ['error' => curl_error($ch)];
    return json_decode($res, true);
}

echo "--- DEEP DEBUG START ($date) ---\n";

// [1] Campaign Level
echo "\n[1] Fetching at level=campaign for $adAccountId...\n";
$campaigns = fetchMeta("$baseUrl/$adAccountId/insights", [
    'access_token' => $accessToken,
    'level' => 'campaign',
    'time_range' => json_encode(['since' => $date, 'until' => $date]),
    'fields' => 'campaign_name,spend,reach,actions'
]);

if (isset($campaigns['data'])) {
    foreach ($campaigns['data'] as $c) {
        echo "Campaign: {$c['campaign_name']}\n";
        echo "  Spend: {$c['spend']}\n";
        echo "  Reach: {$c['reach']}\n";
        foreach ($c['actions'] ?? [] as $a) {
            echo "    Action: {$a['action_type']} -> {$a['value']}\n";
        }
    }
} else {
    echo "FAILED: " . json_encode($campaigns) . "\n";
}

// [2] Ad Level Summary
echo "\n[2] Fetching at level=ad for $adAccountId to check totals...\n";
$ads = fetchMeta("$baseUrl/$adAccountId/insights", [
    'access_token' => $accessToken,
    'level' => 'ad',
    'time_range' => json_encode(['since' => $date, 'until' => $date]),
    'fields' => 'ad_name,spend,campaign_name',
    'limit' => 500
]);

if (isset($ads['data'])) {
    $totals = [];
    foreach ($ads['data'] as $ad) {
        $cName = $ad['campaign_name'];
        if (!isset($totals[$cName])) $totals[$cName] = 0;
        $totals[$cName] += (float) $ad['spend'];
    }
    echo "\nSummarized Ad Spend per Campaign:\n";
    foreach ($totals as $name => $spend) {
        echo "  $name: Rp " . number_format($spend, 0, ',', '.') . "\n";
    }
}

echo "\n--- DEBUG FINISHED ---\n";
