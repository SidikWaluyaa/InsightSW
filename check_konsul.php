<?php require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 
$stats = App\Models\SleekflowContact::where('status_chat', 'Konsultasi')
    ->selectRaw('contact_owner_name, COUNT(*) as count')
    ->groupBy('contact_owner_name')
    ->get();
echo json_encode($stats, JSON_PRETTY_PRINT);
