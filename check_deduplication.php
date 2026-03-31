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

echo "--- CAMPAIGN vs AD DEDUPLICATION CHECK ($date) ---\n";

// 1. Get Campaign Totals
$campaigns = fetchMeta("$baseUrl/$adAccountId/insights", [
    'access_token' => $accessToken,
    'level' => 'campaign',
    'time_range' => json_encode(['since' => $date, 'until' => $date]),
    'fields' => 'campaign_name,spend,actions'
]);

$campaignTotals = [];
if (isset($campaigns['data'])) {
    foreach ($campaigns['data'] as $c) {
        $res = 0;
        foreach ($c['actions'] ?? [] as $a) {
            if ($a['action_type'] === 'onsite_conversion.messaging_conversation_started') $res = $a['value'];
        }
        $campaignTotals[$c['campaign_name']] = $res;
        echo "Campaign: {$c['campaign_name']} | Official Meta Results: $res\n";
    }
}

// 2. Get Sum of Ads
$ads = fetchMeta("$baseUrl/$adAccountId/insights", [
    'access_token' => $accessToken,
    'level' => 'ad',
    'time_range' => json_encode(['since' => $date, 'until' => $date]),
    'fields' => 'campaign_name,actions',
    'limit' => 500
]);

$adSums = [];
if (isset($ads['data'])) {
    foreach ($ads['data'] as $ad) {
        $name = $ad['campaign_name'];
        if (!isset($adSums[$name])) $adSums[$name] = 0;
        foreach ($ad['actions'] ?? [] as $a) {
             if ($a['action_type'] === 'onsite_conversion.messaging_conversation_started') $adSums[$name] += $a['value'];
        }
    }
}

echo "\nCOMPARISON (Campaign Total vs Sum of Ads):\n";
foreach ($campaignTotals as $name => $total) {
    $sum = $adSums[$name] ?? 0;
    $diff = $sum - $total;
    echo "  $name: Meta UI ($total) vs Sum of Ads ($sum) | Diff: $diff\n";
}

echo "\n--- CHECK FINISHED ---\n";
