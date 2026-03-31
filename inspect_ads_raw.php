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

echo "--- DEEP AD-LEVEL INSPECTION ($date) ---\n";

// Fetch ads for "Testimoni" to find correct action key
$ads = fetchMeta("$baseUrl/$adAccountId/insights", [
    'access_token' => $accessToken,
    'level' => 'ad',
    'time_range' => json_encode(['since' => $date, 'until' => $date]),
    'fields' => 'ad_name,spend,actions',
    'limit' => 20
]);

if (isset($ads['data'])) {
    foreach ($ads['data'] as $ad) {
        if (strpos($ad['ad_name'], 'Testimoni') !== false || (float)$ad['spend'] > 0) {
            echo "\nAd: {$ad['ad_name']}\n";
            echo "Spend: {$ad['spend']}\n";
            foreach ($ad['actions'] ?? [] as $a) {
                echo "  Action: {$a['action_type']} -> {$a['value']}\n";
            }
        }
    }
} else {
    echo "FAILED: " . json_encode($ads) . "\n";
}

echo "\n--- INSPECTION FINISHED ---\n";
