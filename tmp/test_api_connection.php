<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Services\DashboardApiService;
use Illuminate\Support\Facades\Http;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new DashboardApiService();
$startDate = date('Y-m-01');
$endDate = date('Y-m-d');

echo "Testing Dashboard API Connection...\n";
echo "Range: $startDate to $endDate\n";

$result = $service->getDashboardSummary($startDate, $endDate);

if (isset($result['status']) && $result['status'] === 'success') {
    echo "SUCCESS!\n";
    echo "Total Closing: " . ($result['data']['summary']['total_closing'] ?? 'N/A') . "\n";
    echo "Total Revenue: " . ($result['data']['summary']['total_revenue'] ?? 'N/A') . "\n";
    echo "CS Count: " . count($result['data']['per_cs'] ?? []) . "\n";
} else {
    echo "FAILED!\n";
    print_r($result);
}
