<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$metric = \App\Models\WorkshopMetric::latest('last_sync_at')->first();

if (!$metric) {
    echo "No metrics found in DB.\n";
    exit;
}

echo "PIPELINE:\n";
var_dump($metric->pipeline);
echo "\nTRENDS:\n";
var_dump($metric->trends);
echo "\nJSON ENCODED:\n";
echo json_encode($metric);
echo "\n";
