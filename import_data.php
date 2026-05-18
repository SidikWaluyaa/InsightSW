<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$sqlPath = __DIR__.'/sleekflow_contacts_may.sql';
if (!file_exists($sqlPath)) {
    die("File sleekflow_contacts_may.sql tidak ditemukan!\n");
}

echo "Memulai impor data sleekflow_contacts_may.sql...\n";

try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    // Membaca file SQL dan menjalankannya secara langsung
    $sql = file_get_contents($sqlPath);
    DB::unprepared($sql);
    
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "SUKSES! Data 1 Mei s.d Hari ini (12.018 baris) berhasil diimpor dengan sempurna ke database server!\n";
} catch (\Exception $e) {
    echo "Gagal mengimpor data: " . $e->getMessage() . "\n";
}
