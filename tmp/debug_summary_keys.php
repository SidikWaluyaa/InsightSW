<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Services\DashboardApiService;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new DashboardApiService();
$result = $service->getDashboardSummary(date('Y-m-d'), date('Y-m-d'));

if (isset($result['data']['summary'])) {
    echo "Keys in Summary: " . implode(', ', array_keys($result['data']['summary'])) . "\n";
    print_r($result['data']['summary']);
} else {
    echo "Summary not found\n";
}
