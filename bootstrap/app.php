<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);

        // Menu yang dinonaktifkan sementara (config/menu.php) tidak bisa dibuka lewat URL
        $middleware->web(append: [\App\Http\Middleware\DisabledMenu::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->withSchedule(function ($schedule) {
        $schedule->command('qc:capture-snapshot')->dailyAt('08:00');

        $syncLog = storage_path('logs/scheduler-sync.log');

        // === Meta Ads ===
        // Rekonsiliasi harian: tarik ulang 3 hari terakhir (atribusi konversi Meta bisa berubah)
        $schedule->command('sync:meta-ads --preset=last_3d')
            ->dailyAt('01:00')->withoutOverlapping(30)->appendOutputTo($syncLog);
        // Penyegaran data hari berjalan tiap jam (07:05 - 23:05 WIB)
        $schedule->command('sync:meta-ads --preset=today')
            ->hourlyAt(5)->between('07:00', '23:59')->withoutOverlapping(30)->appendOutputTo($syncLog);

        // === Sleekflow Daily Analytics ===
        // Rekonsiliasi harian: 7 hari terakhir s/d kemarin (data final)
        $schedule->command('sync:sleekflow-analytics --days=7')
            ->dailyAt('01:30')->withoutOverlapping(30)->appendOutputTo($syncLog);
        // Penyegaran tiap jam: kemarin + hari ini (hari ini masih parsial)
        $schedule->command('sync:sleekflow-analytics --days=2 --include-today')
            ->hourlyAt(20)->between('07:00', '23:59')->withoutOverlapping(30)->appendOutputTo($syncLog);
    })
    ->create();
