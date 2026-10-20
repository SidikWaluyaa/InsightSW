<?php

namespace App\Services;

use App\Models\SleekflowContact;
use App\Models\SleekflowDailyAnalytics;
use Illuminate\Support\Str;
use Throwable;

/**
 * Halaman SleekFlow Activity (PRD halaman 2: beban kerja & SLA CS).
 *
 * Dua sumber dengan tingkat kelengkapan berbeda:
 * - sleekflow_daily_analytics (sinkron terjadwal per hari, lengkap): chat masuk, waktu balas, pesan otomatis.
 * - sleekflow_contacts (kontak per agen & status chat): hanya lengkap untuk tanggal yang sudah ditarik
 *   (sinkron kontak lambat, tidak dijadwalkan), jadi halaman menampilkan tingkat kelengkapannya.
 */
class SleekflowActivityService
{
    /** Tim CS di Sleekflow (sama dengan SleekflowService::getAnalyticsData). */
    protected const CS_TEAM = '5000000659';

    /** Status chat yang dihitung sebagai konsultasi aktif. */
    public const KONSUL = ['Konsultasi', 'Follow Up Konsultasi', 'Progress'];

    /** Target SLA balas pertama (PRD): di bawah 3 menit. */
    public const SLA_SECONDS = 180;

    public function __construct(protected ManagementMetricsService $metrics)
    {
    }

    /**
     * Alur 1: total chat masuk, waktu balas pertama, rasio otomasi bot, biaya bubble chat.
     */
    public function kpis(string $start, string $end): array
    {
        $now = $this->metrics->collect($start, $end);
        [$ps, $pe] = $this->metrics->previousRange($start, $end);
        $prev = $this->metrics->collect($ps, $pe);

        $messages = SleekflowDailyAnalytics::query()
            ->whereDate('date_time', '>=', $start)->whereDate('date_time', '<=', $end)
            ->selectRaw('SUM(number_of_messages_sent) sent, SUM(number_of_automated_messages) automated')
            ->first();
        $sent = (float) ($messages->sent ?? 0);

        $card = fn (int $no) => [
            'nilai' => $now['values'][$no],
            'lalu' => $prev['values'][$no],
            'teks' => $this->metrics->format($no, $now['values'][$no]),
            'teks_lalu' => $this->metrics->format($no, $prev['values'][$no]),
        ] + $this->metrics->compare($no, $now['values'][$no], $prev['values'][$no], $now['days'], $prev['days']);

        return [
            'chat' => $card(15),
            'frt' => $card(17),
            'bot' => [
                'automated' => (float) ($messages->automated ?? 0),
                'sent' => $sent,
                'rasio' => $sent > 0 ? $messages->automated / $sent * 100 : null,
            ],
            'days' => $now['days'],
            'prevRange' => [$ps, $pe],
            'errors' => $now['errors'] + array_map(fn ($e) => 'Bulan lalu: ' . $e, $prev['errors']),
        ];
    }

    /**
     * Kelengkapan data kontak: kontak tim CS yang tersimpan dibanding chat masuk baru dari analitik harian.
     */
    public function coverage(string $start, string $end, ?float $chats): array
    {
        $contacts = $this->contactQuery($start, $end)->count();

        return ['kontak' => $contacts, 'chat' => $chats, 'persen' => $chats ? $contacts / $chats * 100 : null];
    }

    /**
     * Alur 2: beban kerja per agen CS dari kontak Sleekflow (pemilik kontak), plus SPK closing dari sistem pusat
     * (dicocokkan lewat nama depan). Status beban = chat masuk agen dibanding rata-rata agen aktif pada rentang yang sama:
     * Overload ≥ 130%, Sibuk ≥ 100%, Optimal di bawahnya.
     */
    public function agents(string $start, string $end): array
    {
        $rows = $this->contactQuery($start, $end)
            ->selectRaw('contact_owner_name, COUNT(*) total, SUM(CASE WHEN status_chat IN (?, ?, ?) THEN 1 ELSE 0 END) konsul', self::KONSUL)
            ->whereNotNull('contact_owner_name')->where('contact_owner_name', '<>', '')
            ->groupBy('contact_owner_name')
            ->get()
            // Akun non-CS (Finance, Ai Jahit) disembunyikan, sama dengan peringkat di Dashboard CS
            ->reject(fn ($r) => CsSalesReportService::isHiddenAccount($r->contact_owner_name))
            ->values();

        // SPK closing per CS dari sistem pusat (per_cs_kamus), dicocokkan dengan nama depan
        $spk = [];
        try {
            foreach ($this->metrics->csBreakdown($start, $end)['per_cs'] ?? [] as $cs) {
                $spk[$this->firstName($cs['cs_name'] ?? '')] = (int) ($cs['spk_closing'] ?? 0);
            }
        } catch (Throwable) {
            // tanpa data pusat: kolom SPK closing tampil "–"
        }

        // Rata-rata hanya dari agen yang memegang ≥ 2% chat, supaya akun yang hampir tidak dipakai (mis. akun Finance)
        // tidak menurunkan rata-rata dan membuat semua agen tampak Overload. Batas ini sementara (belum dari Tim Data).
        $total = $rows->sum('total');
        $active = $rows->filter(fn ($r) => $total > 0 && $r->total / $total >= 0.02);
        $mean = $active->count() ? $active->avg('total') : 0;

        return $rows->map(function ($r) use ($spk, $mean) {
            $ratio = $mean > 0 ? $r->total / $mean : 0;
            $key = $this->firstName($r->contact_owner_name);

            return [
                'nama' => $r->contact_owner_name,
                'chat' => (int) $r->total,
                'konsul' => (int) $r->konsul,
                'spk' => $spk[$key] ?? null,
                'beban' => $ratio >= 1.3 ? 'Overload' : ($ratio >= 1.0 ? 'Sibuk' : 'Optimal'),
            ];
        })->sortByDesc('chat')->values()->all();
    }

    /**
     * Alur 3 kiri: status lifecycle percakapan (kontak masuk pada rentang) dan closing pada rentang.
     */
    public function lifecycle(string $start, string $end): array
    {
        $row = $this->contactQuery($start, $end)->selectRaw(
            'SUM(CASE WHEN status_chat = ? THEN 1 ELSE 0 END) greeting, SUM(CASE WHEN status_chat IN (?, ?, ?) THEN 1 ELSE 0 END) konsul',
            array_merge(['Greeting'], self::KONSUL)
        )->first();

        $closing = SleekflowContact::query()
            ->where('assigned_team', self::CS_TEAM)
            ->where(fn ($q) => $q->whereBetween('closing_at', ["{$start} 00:00:00", "{$end} 23:59:59"])
                ->orWhereBetween('penerimaan_at', ["{$start} 00:00:00", "{$end} 23:59:59"]))
            ->count();

        return ['greeting' => (int) ($row->greeting ?? 0), 'konsul' => (int) ($row->konsul ?? 0), 'closing' => $closing];
    }

    protected function contactQuery(string $start, string $end)
    {
        return SleekflowContact::query()
            ->where('assigned_team', self::CS_TEAM)
            ->whereBetween('created_at_sleekflow', ["{$start} 00:00:00", "{$end} 23:59:59"]);
    }

    /** "Ferdian Franlin" / "Defy CS" → "ferdian" / "defy". */
    protected function firstName(string $name): string
    {
        return Str::lower(Str::before(trim($name), ' ') ?: trim($name));
    }
}
