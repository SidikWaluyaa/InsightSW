<?php
$logPath = 'storage/logs/laravel.log';
$content = file_get_contents($logPath);
$matches = [];
preg_match_all('/CX Upsell API Response: (.*?) {"url"/s', $content, $matches);

if (!empty($matches[1])) {
    $lastJson = end($matches[1]);
    // The log might contain some extra context, let's try to extract just the JSON
    $start = strpos($lastJson, '{"status"');
    if ($start !== false) {
        $json = substr($lastJson, $start);
        file_put_contents('last_api_response.json', $json);
        echo "Last JSON response saved to last_api_response.json\n";
    } else {
        echo "Could not find start of JSON in: " . substr($lastJson, 0, 100) . "...\n";
    }
} else {
    echo "No 'CX Upsell API Response' found in logs.\n";
}
