<?php
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['X-API-KEY: sws_live_6f8g9h0j1k2l3m4n5o6p7q8r9s0']
]);
$apis = [
    'https://info.shoeworkshop.id/api/v1/warehouse-inventory-sync',
    'https://info.shoeworkshop.id/api/v1/warehouse-request-sync',
    'https://info.shoeworkshop.id/api/v1/warehouse-transaction-sync'
];
foreach ($apis as $api) {
    curl_setopt($ch, CURLOPT_URL, $api);
    echo "\n=== $api ===\n";
    $res = curl_exec($ch);
    echo substr(print_r(json_decode($res, true), true), 0, 1000) . "\n...\n";
}
curl_close($ch);
