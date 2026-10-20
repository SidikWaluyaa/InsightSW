<?php

namespace App\Console\Commands;

use App\Services\SleekflowReplyService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncSleekflowRepliesCommand extends Command
{
    /**
     * Contoh:
     *   php artisan sync:sleekflow-replies                         -> kemarin
     *   php artisan sync:sleekflow-replies --today                 -> hari ini (masih berjalan)
     *   php artisan sync:sleekflow-replies --from=2026-10-19 --to=2026-10-20
     */
    protected $signature = 'sync:sleekflow-replies
                            {--from= : Tanggal awal (Y-m-d)}
                            {--to= : Tanggal akhir (Y-m-d)}
                            {--today : Tarik hari ini}';

    protected $description = 'Hitung balasan pertama AI greeting, pesan otomatis, dan staf CS per percakapan dari API pesan Sleekflow';

    public function handle(SleekflowReplyService $service): int
    {
        $tz = config('app.timezone', 'Asia/Jakarta');

        try {
            $default = $this->option('today') ? Carbon::today($tz) : Carbon::yesterday($tz);
            $from = $this->option('from') ? Carbon::createFromFormat('Y-m-d', $this->option('from'), $tz) : $default;
            $to = $this->option('to') ? Carbon::createFromFormat('Y-m-d', $this->option('to'), $tz) : ($this->option('from') ? $from->copy() : $default);
        } catch (\Throwable) {
            $this->error('Format tanggal salah. Gunakan Y-m-d, contoh: 2026-10-19');

            return self::INVALID;
        }

        if ($from->gt($to)) {
            $this->error('--from tidak boleh lebih besar dari --to.');

            return self::INVALID;
        }

        $lock = Cache::lock('sync_lock_sleekflow_replies', 3600);
        if (! $lock->get()) {
            $this->warn('Sync balasan Sleekflow lain sedang berjalan. Dilewati.');

            return self::SUCCESS;
        }

        try {
            $this->info("Menghitung balasan pertama Sleekflow {$from->toDateString()} s/d {$to->toDateString()} ...");
            $result = $service->syncRange($from->toDateString(), $to->toDateString(), fn ($n) => $this->line("  {$n} percakapan dibaca"));
            Cache::put('sync_last_time_sleekflow_replies', time(), now()->addDays(7));
            $this->info("Selesai: {$result['percakapan']} percakapan dibaca, {$result['baris']} baris tersimpan untuk rentang ini.");

            return self::SUCCESS;
        } finally {
            $lock->release();
        }
    }
}
