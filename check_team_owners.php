<?php require 'vendor/autoload.php'; $app = require_once 'bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); 
$owners = App\Models\SleekflowContact::where('assigned_team', '5000000659')
    ->whereNotNull('contact_owner_name')
    ->distinct('contact_owner_name')
    ->pluck('contact_owner_name');
echo json_encode($owners, JSON_PRETTY_PRINT);
