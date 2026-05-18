<?php require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 
$stats = App\Models\SleekflowContact::where('status_chat', 'Konsultasi')
    ->whereBetween('created_at_sleekflow', ['2026-05-01 00:00:00', '2026-05-18 23:59:59'])
    ->count();
echo "Total Konsultasi created in May: " . $stats;
