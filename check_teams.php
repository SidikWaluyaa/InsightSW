<?php require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 
$teams = App\Models\SleekflowContact::select('assigned_team', 'contact_owner_name')
    ->whereNotNull('assigned_team')
    ->where('assigned_team', '!=', '')
    ->groupBy('assigned_team', 'contact_owner_name')
    ->get()
    ->groupBy('assigned_team');
echo json_encode($teams, JSON_PRETTY_PRINT);
