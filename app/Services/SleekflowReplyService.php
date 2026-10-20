<?php

namespace App\Services;

use App\Models\SleekflowConversationReply;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Menghitung balasan pertama per percakapan dari API pesan Sleekflow, dipisah menurut pengirim:
 * AI greeting (AiAgentAction), pesan otomatis (AutomatedMessage), dan staf CS (Normal / QuickReply).
 *
 * Alasan: analitik harian Sleekflow (sleekflow_daily_analytics) tidak menghitung balasan AI maupun otomatis
 * (19 Okt 2026: AI aktif, tetapi pesan otomatis tercatat 0 dan waktu balas pertama tetap ±41 menit).
 */
class SleekflowReplyService
{
    protected const PAGE = 50;

    protected const MSG_PAGE = 100;

    /** Jeda antar request supaya tidak kena batas Sleekflow. */
    protected const PAUSE_MS = 300;

    protected const TYPE_AI = ['AiAgentAction'];

    protected const TYPE_AUTO = ['AutomatedMessage'];

    protected const TYPE_STAFF = ['Normal', 'QuickReply'];

    protected string $base;

    protected string $key;

    public function __construct()
    {
        $this->base = rtrim(config('services.sleekflow.base_url') ?: 'https://sleekflow-core-app-seas-production.azurewebsites.net/api', '/');
        $this->key = (string) config('services.sleekflow.key', '');
    }

    /**
     * Tarik & simpan balasan pertama untuk tanggal WIB [from, to].
     *
     * @return array{percakapan: int, baris: int}
     */
    public function syncRange(string $from, string $to, ?callable $progress = null): array
    {
        $tz = config('app.timezone', 'Asia/Jakarta');
        $start = Carbon::parse($from, $tz)->startOfDay();
        $end = Carbon::parse($to, $tz)->endOfDay();
        $sinceUtc = $start->copy()->utc();

        $conversations = 0;
        $rows = [];

        foreach ($this->conversationsSince($sinceUtc) as $conv) {
            $conversations++;
            $messages = $this->messagesSince($conv['conversationId'], $sinceUtc);

            foreach ($this->firstReplies($conv['conversationId'], $messages, $start, $end, $tz) as $row) {
                $rows[] = $row;
            }

            if (count($rows) >= 200) {
                $this->save($rows);
                $rows = [];
            }

            if ($progress && $conversations % 50 === 0) {
                $progress($conversations);
            }
        }

        $this->save($rows);

        return [
            'percakapan' => $conversations,
            'baris' => SleekflowConversationReply::whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])->count(),
        ];
    }

    /**
     * Satu baris per tanggal yang punya pesan masuk dari customer: waktu balasan pertama tiap jenis setelah pesan masuk
     * pertama di hari itu (balasan boleh di hari berikutnya), dan jumlah pesan per jenis di hari itu.
     */
    protected function firstReplies(string $conversationId, array $messages, Carbon $start, Carbon $end, string $tz): array
    {
        $parsed = [];
        foreach ($messages as $m) {
            if (! is_array($m) || empty($m['createdAt']) || ($m['channel'] ?? '') === 'note') {
                continue; // catatan internal bukan pesan ke customer
            }
            $parsed[] = [
                'at' => Carbon::parse($m['createdAt'])->setTimezone($tz),
                'out' => ! empty($m['isSentFromSleekflow']),
                'type' => $m['deliveryType'] ?? null,
                'channel' => $m['channel'] ?? null,
            ];
        }
        usort($parsed, fn ($a, $b) => $a['at'] <=> $b['at']);

        $rows = [];
        $days = collect($parsed)->filter(fn ($p) => ! $p['out'] && $p['at']->between($start, $end))
            ->groupBy(fn ($p) => $p['at']->toDateString());

        foreach ($days as $date => $inbound) {
            $firstIn = $inbound->first()['at'];
            $firstOf = fn (array $types) => collect($parsed)->first(fn ($p) => $p['out'] && in_array($p['type'], $types, true) && $p['at']->gte($firstIn))['at'] ?? null;
            $onDay = collect($parsed)->filter(fn ($p) => $p['at']->toDateString() === $date);
            $countOf = fn (array $types) => $onDay->filter(fn ($p) => $p['out'] && in_array($p['type'], $types, true))->count();

            [$ai, $auto, $staff] = [$firstOf(self::TYPE_AI), $firstOf(self::TYPE_AUTO), $firstOf(self::TYPE_STAFF)];
            $secs = fn (?Carbon $t) => $t ? (int) $firstIn->diffInSeconds($t) : null;

            $rows[] = [
                'tanggal' => $date,
                'conversation_id' => $conversationId,
                'channel' => $inbound->first()['channel'],
                'first_in_at' => $firstIn->toDateTimeString(),
                'first_ai_at' => $ai?->toDateTimeString(),
                'first_auto_at' => $auto?->toDateTimeString(),
                'first_staff_at' => $staff?->toDateTimeString(),
                'ai_seconds' => $secs($ai),
                'auto_seconds' => $secs($auto),
                'staff_seconds' => $secs($staff),
                'msg_in' => $inbound->count(),
                'msg_ai' => $countOf(self::TYPE_AI),
                'msg_auto' => $countOf(self::TYPE_AUTO),
                'msg_staff' => $countOf(self::TYPE_STAFF),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $rows;
    }

    protected function save(array $rows): void
    {
        foreach (array_chunk($rows, 500) as $chunk) {
            SleekflowConversationReply::upsert($chunk, ['conversation_id', 'tanggal'], [
                'channel', 'first_in_at', 'first_ai_at', 'first_auto_at', 'first_staff_at',
                'ai_seconds', 'auto_seconds', 'staff_seconds', 'msg_in', 'msg_ai', 'msg_auto', 'msg_staff', 'updated_at',
            ]);
        }
    }

    /** Percakapan yang aktif sejak $sinceUtc (API mengurutkan dari yang terakhir aktif). */
    protected function conversationsSince(Carbon $sinceUtc): \Generator
    {
        for ($offset = 0; $offset < 20000; $offset += self::PAGE) {
            $page = $this->get('/conversation/all', ['limit' => self::PAGE, 'offset' => $offset]);
            if (! $page) {
                return;
            }
            foreach ($page as $c) {
                if (! is_array($c) || empty($c['conversationId'])) {
                    continue;
                }
                if (! empty($c['updatedTime']) && Carbon::parse($c['updatedTime'])->lt($sinceUtc)) {
                    return; // sisanya lebih lama dari rentang
                }
                yield $c;
            }
        }
    }

    /** Pesan percakapan sejak $sinceUtc (API mengurutkan dari yang terbaru). */
    protected function messagesSince(string $conversationId, Carbon $sinceUtc): array
    {
        $all = [];
        for ($offset = 0; $offset < self::MSG_PAGE * 10; $offset += self::MSG_PAGE) {
            $page = $this->get("/conversation/message/{$conversationId}", ['limit' => self::MSG_PAGE, 'offset' => $offset]);
            if (! $page) {
                break;
            }
            $all = array_merge($all, $page);
            $oldest = collect($page)->filter(fn ($m) => is_array($m) && ! empty($m['createdAt']))->min('createdAt');
            if (count($page) < self::MSG_PAGE || ($oldest && Carbon::parse($oldest)->lt($sinceUtc))) {
                break;
            }
        }

        return $all;
    }

    /** GET dengan retry; mengembalikan list atau [] bila gagal. */
    protected function get(string $path, array $query): array
    {
        for ($try = 1; $try <= 3; $try++) {
            usleep(self::PAUSE_MS * 1000);
            try {
                $res = Http::withHeaders(['X-Sleekflow-Api-Key' => $this->key])->timeout(60)->get($this->base . $path, $query);
                $json = $res->json();
                if ($res->ok() && is_array($json) && array_is_list($json)) {
                    return $json;
                }
                if ($res->status() === 404) {
                    return [];
                }
            } catch (\Throwable $e) {
                Log::warning("SleekflowReply: {$path} gagal (percobaan {$try}): " . $e->getMessage());
            }
            sleep($try);
        }

        return [];
    }
}
