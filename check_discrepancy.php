<?php require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 

$global = App\Models\SleekflowContact::where(function ($q) {
                $q->whereNull('status_chat')
                  ->orWhere('status_chat', '')
                  ->orWhere('status_chat', 'Contact Filtered');
            })->count();

$perUser = App\Models\SleekflowContact::where(function ($q) {
                $q->whereNull('status_chat')
                  ->orWhere('status_chat', '')
                  ->orWhere('status_chat', 'Contact Filtered');
            })
            ->where('assigned_team', '5000000659')
            ->selectRaw('contact_owner_name, COUNT(*) as count')
            ->groupBy('contact_owner_name')
            ->get();

echo json_encode(['global' => $global, 'perUser' => $perUser], JSON_PRETTY_PRINT);
