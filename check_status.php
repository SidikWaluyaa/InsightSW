<?php require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 
$stats = App\Models\SleekflowContact::selectRaw('status_chat, COUNT(*) as count')
    ->groupBy('status_chat')
    ->get();
echo json_encode($stats, JSON_PRETTY_PRINT);
