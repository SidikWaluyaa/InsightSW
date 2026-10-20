<?php

namespace App\Console\Commands;

use App\Services\SleekflowService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncSleekflowAnalyticsCommand extends Command
{
    /**
     * Contoh:
     *   php artisan sync:sleekflow-analytics                       -> 7 hari terakhir (s/d kemarin)
     *   php artisan sync:sleekflow-analytics --days=30
     *   php artisan sync:sleekflow-analytics --from=2026-10-01 --to=2026-10-11
     *   php artisan sync:sleekflow-analytics --include-today       -> ikut tarik data hari ini (masih parsial)
     */
    protected $signature = 'sync:sleekflow-analytics
                            {--days=7 : Jumlah hari ke belakang yang ditarik (rolling window)}
                            {--from= : Tanggal awal (Y-m-d), override --days}
                            {--to= : Tanggal akhir (Y-m-d), default kemarin}
                            {--include-today : Sertakan data hari ini (masih berjalan/parsial)}';

    protected $description = 'Tarik Daily Analytics dari Sleekflow API ke tabel sleekflow_daily_analytics (otomatis, tanpa input manual)';

    public function handle(SleekflowService $service): int
    {
        $tz = config('app.timezone', 'Asia/Jakarta');

        try {
            $to = $this->option('to')
                ? Carbon::createFromFormat('Y-m-d', $this->option('to'), $tz)
                : ($this->option('include-today') ? Carbon::today($tz) : Carbon::yesterday($tz));

            if ($this->option('from')) {
                $from = Carbon::createFromFormat('Y-m-d', $this->option('from'), $tz);
            } else {
                $days = max(1, (int) $this->option('days'));
                $from = $to->copy()->subDays($days - 1);
            }
        } catch (\Throwable $e) {
            $this->error('Format tanggal salah. Gunakan Y-m-d, contoh: 2026-10-01');
            return self::INVALID;
        }

        if ($from->gt($to)) {
            $this->error("--from ({$from->toDateString()}) tidak boleh lebih besar dari --to ({$to->toDateString()}).");
            return self::INVALID;
        }

        // Cegah dua proses sync berjalan bersamaan (mis. cron + manual)
        $lock = Cache::lock('sync_lock_sleekflow_analytics', 600);
        if (!$lock->get()) {
            $this->warn('Sync Sleekflow Analytics lain sedang berjalan. Dilewati.');
            return self::SUCCESS;
        }

        try {
            $this->info("Menarik Sleekflow Daily Analytics {$from->toDateString()} s/d {$to->toDateString()} ...");

            $result = $service->syncDailyAnalytics($from->toDateString(), $to->toDateString());

            if (!$result['success']) {
                $this->error($result['message']);
                return self::FAILURE;
            }

            Cache::put('sync_last_time_sleekflow_analytics', time(), now()->addDays(7));
            $this->info($result['message']);

            return self::SUCCESS;
        } finally {
            $lock->release();
        }
    }
}
