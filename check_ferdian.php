<?php require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 
$stats = App\Models\SleekflowContact::where('status_chat', 'Konsultasi')
    ->where('contact_owner_name', 'Ferdian Franlin')
    ->selectRaw('DATE(created_at_sleekflow) as date, COUNT(*) as count')
    ->groupBy('date')
    ->get();
echo json_encode($stats, JSON_PRETTY_PRINT);
