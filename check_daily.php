<?php require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 
$stats = App\Models\SleekflowContact::selectRaw('DATE(created_at_sleekflow) as date, COUNT(*) as count')
    ->where('created_at_sleekflow', '>=', '2026-04-20 00:00:00')
    ->groupBy('date')
    ->orderBy('date')
    ->get();
echo json_encode($stats, JSON_PRETTY_PRINT);
