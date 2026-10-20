<?php

namespace App\Console\Commands;

use App\Services\MetaAdsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SyncMetaAdsCommand extends Command
{
    /**
     * Contoh:
     *   php artisan sync:meta-ads                                  -> preset last_3d (atribusi konversi Meta bisa berubah 1-3 hari)
     *   php artisan sync:meta-ads --preset=today
     *   php artisan sync:meta-ads --from=2026-10-01 --to=2026-10-11
     */
    protected $signature = 'sync:meta-ads
                            {--preset=last_3d : Date preset Meta (today, yesterday, last_3d, last_7d, last_30d, ...)}
                            {--from= : Tanggal awal (Y-m-d), override --preset}
                            {--to= : Tanggal akhir (Y-m-d), default hari ini}
                            {--account= : Ad Account ID (default dari META_AD_ACCOUNT_ID)}';

    protected $description = 'Tarik data insight Meta Ads (level ad, harian) ke tabel meta_ads_reports secara otomatis';

    public function handle(MetaAdsService $service): int
    {
        $accountId = $this->option('account') ?: config('services.meta.ad_account_id');

        if (empty($accountId)) {
            $this->error('META_AD_ACCOUNT_ID belum diisi di .env.');
            return self::INVALID;
        }

        $tz = config('app.timezone', 'Asia/Jakarta');

        if ($this->option('from')) {
            try {
                $from = Carbon::createFromFormat('Y-m-d', $this->option('from'), $tz);
                $to = $this->option('to') ? Carbon::createFromFormat('Y-m-d', $this->option('to'), $tz) : Carbon::today($tz);
            } catch (\Throwable $e) {
                $this->error('Format tanggal salah. Gunakan Y-m-d, contoh: 2026-10-01');
                return self::INVALID;
            }

            if ($from->gt($to)) {
                $this->error('--from tidak boleh lebih besar dari --to.');
                return self::INVALID;
            }

            $range = ['since' => $from->toDateString(), 'until' => $to->toDateString()];
            $label = "{$range['since']} s/d {$range['until']}";
        } else {
            $range = (string) $this->option('preset');
            $label = "preset {$range}";
        }

        // Pakai nama lock yang sama dengan auto-sync di halaman Meta Ads agar tidak tabrakan
        $lock = Cache::lock('sync_lock_meta_ads_sync', 900);
        if (!$lock->get()) {
            $this->warn('Sync Meta Ads lain sedang berjalan. Dilewati.');
            return self::SUCCESS;
        }

        try {
            $this->info("Menarik Meta Ads {$accountId} ({$label}) ...");

            $ok = $service->fetchAndSync($accountId, $range);

            if (!$ok) {
                $this->error('Sync Meta Ads gagal (cek META_ADS_ACCESS_TOKEN & storage/logs/laravel.log).');
                return self::FAILURE;
            }

            // Sinkron dengan indikator "last sync" di halaman Meta Ads
            Cache::put('sync_last_time_meta_ads_sync', time(), now()->addDays(1));
            $this->info('Sync Meta Ads selesai.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Sync Meta Ads error: ' . $e->getMessage());
            report($e);
            return self::FAILURE;
        } finally {
            $lock->release();
        }
    }
}
