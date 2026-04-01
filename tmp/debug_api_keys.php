<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Services\DashboardApiService;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new DashboardApiService();
$result = $service->getDashboardSummary(date('Y-m-d'), date('Y-m-d'));

if (isset($result['data']['per_cs'])) {
    echo "Keys in first CS item: " . implode(', ', array_keys($result['data']['per_cs'][0] ?? [])) . "\n";
    print_r($result['data']['per_cs'][0] ?? []);
} else {
    echo "per_cs not found or empty\n";
}
