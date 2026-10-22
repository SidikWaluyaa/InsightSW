<?php

/*
|--------------------------------------------------------------------------
| Menu yang dinonaktifkan sementara
|--------------------------------------------------------------------------
|
| Nama route di sini disembunyikan dari sidebar dan, bila dibuka lewat URL, dialihkan ke halaman awal pengguna
| (App\Http\Middleware\DisabledMenu). Hapus dari daftar untuk mengaktifkan kembali; halamannya tidak dihapus.
|
*/

return [
    'nonaktif' => [
        // Marketing
        'daily-report',
        'weekly-report',
        'budget-transfer',
        'monthly-settings',
        // CS
        'cs-kpi',
        'cs-forecasting',
        'cs-tracking',
    ],
];
