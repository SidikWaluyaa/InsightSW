<?php require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 
$count = App\Models\SleekflowContact::whereBetween('created_at_sleekflow', ['2026-05-01 00:00:00', '2026-05-18 23:59:59'])
    ->where(function ($q) {
        $q->whereNull('status_chat')
          ->orWhere('status_chat', '')
          ->orWhere('status_chat', 'Contact Filtered');
    })->count();
echo "May 1 - May 18 unhandled: " . $count;
