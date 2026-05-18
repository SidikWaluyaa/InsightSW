<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Naikkan limit memori dan waktu eksekusi agar tidak timeout
ini_set('memory_limit', '1024M');
set_time_limit(0);

$sqlPath = __DIR__.'/sleekflow_contacts.sql';
if (!file_exists($sqlPath)) {
    die("File sleekflow_contacts.sql tidak ditemukan di root directory!\n");
}

echo "Membaca file sleekflow_contacts.sql (16.4 MB)...\n";
$sql = file_get_contents($sqlPath);

echo "Mengekstrak data INSERT INTO...\n";
// Gunakan regex untuk mengambil semua query INSERT INTO secara aman
preg_match_all('/INSERT INTO `sleekflow_contacts`[\s\S]+?;/U', $sql, $matches);

$queries = $matches[0] ?? [];
$totalQueries = count($queries);

if ($totalQueries === 0) {
    die("Tidak ditemukan query INSERT INTO di dalam file SQL!\n");
}

echo "Ditemukan {$totalQueries} blok kueri INSERT INTO. Memulai impor...\n";

try {
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
    // Kosongkan tabel terlebih dahulu agar bersih
    echo "Mengosongkan tabel sleekflow_contacts lama...\n";
    DB::statement('TRUNCATE TABLE sleekflow_contacts');
    
    $importedCount = 0;
    foreach ($queries as $index => $query) {
        $progress = $index + 1;
        echo "Mengimpor blok {$progress}/{$totalQueries}...\n";
        DB::unprepared($query);
        $importedCount++;
    }
    
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "\nSUKSES! Berhasil mengimpor {$importedCount} blok kueri secara sempurna!\n";
    echo "Seluruh data kontak Sleekflow versi terbaru sekarang sudah masuk ke database production server.\n";
} catch (\Exception $e) {
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    echo "\nGagal mengimpor data: " . $e->getMessage() . "\n";
}
