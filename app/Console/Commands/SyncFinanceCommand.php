<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\FinanceSyncService;

class SyncFinanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:finance {--start= : Start date (YYYY-MM-DD)} {--end= : End date (YYYY-MM-DD)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync financial data from pusat (info.shoeworkshop.id)';

    /**
     * Execute the console command.
     */
    public function handle(FinanceSyncService $service)
    {
        $start = $this->option('start');
        $end = $this->option('end');

        if ($start || $end) {
            $this->info("Starting Finance Sync for range: " . ($start ?? 'All') . " to " . ($end ?? 'Now') . "...");
            $result = $service->sync($start, $end);
        } else {
            $this->info('Starting Rolling Finance Sync (Last 60 Days)...');
            $result = $service->syncRolling(60);
        }

        if ($result['success']) {
            $this->info($result['message']);
            return self::SUCCESS;
        }

        $this->error($result['message']);
        return self::FAILURE;
    }
}
