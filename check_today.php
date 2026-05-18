<?php require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 
$stats = App\Models\SleekflowContact::whereDate('created_at_sleekflow', date('Y-m-d'))
    ->selectRaw('status_chat, COUNT(*) as count')
    ->groupBy('status_chat')
    ->get();
echo json_encode(['date' => date('Y-m-d'), 'stats' => $stats], JSON_PRETTY_PRINT);
