<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncService
{
    /**
     * Get the last sync timestamp for a module.
     */
    public function getLastSyncTime(string $module): ?int
    {
        return Cache::get("sync_last_time_{$module}");
    }

    /**
     * Get seconds until next sync is allowed.
     */
    public function getSecondsToNextSync(string $module, int $throttleSeconds = 60): int
    {
        $lastSync = $this->getLastSyncTime($module);
        if (!$lastSync) return 0;

        $elapsed = time() - $lastSync;
        return max(0, $throttleSeconds - $elapsed);
    }

    /**
     * Perform sync if conditions are met.
     */
    public function syncIfAllowed(string $module, callable $syncTask, int $throttleSeconds = 60): bool
    {
        $secondsLeft = $this->getSecondsToNextSync($module, $throttleSeconds);

        if ($secondsLeft > 0) {
            return false; // Throttled
        }

        // Try to get atomic lock for 5 minutes (max execution time)
        $lock = Cache::lock("sync_lock_{$module}", 300);

        if ($lock->get()) {
            try {
                $syncTask();
                
                Cache::put("sync_last_time_{$module}", time(), now()->addDays(1));
                
                return true;
            } catch (\Exception $e) {
                Log::error("Sync failed for module {$module}: " . $e->getMessage());
                return false;
            } finally {
                $lock->release();
            }
        }

        return false;
    }
}
