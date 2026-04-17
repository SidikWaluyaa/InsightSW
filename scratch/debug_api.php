<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$apiKey = 'sws_live_6f8g9h0j1k2l3m4n5o6p7q8r9s0';
$url = 'https://info.shoeworkshop.id/api/v1/warehouse-sortir-sync';

$response = Illuminate\Support\Facades\Http::withHeaders([
    'X-API-KEY' => $apiKey,
    'Accept' => 'application/json',
])->get($url);

if ($response->successful()) {
    $data = $response->json();
    $items = $data['data'] ?? [];
    if (!empty($items)) {
        print_r($items[0]);
    } else {
        echo "No items found in API response.\n";
    }
} else {
    echo "API Request failed: " . $response->status() . "\n";
    echo $response->body() . "\n";
}
