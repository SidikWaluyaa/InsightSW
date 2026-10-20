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

    /** Target balasan AI greeting (Kamus Metrik): di bawah 30 detik. */
    public const AI_TARGET_SECONDS = 30;

    /**
     * Balasan pertama dari API pesan Sleekflow (tabel sleekflow_conversation_replies, sync:sleekflow-replies):
     * waktu balas AI greeting, % percakapan yang dibalas AI, dan porsi pesan AI/otomatis vs staf.
     * null = belum ada data untuk rentang ini.
     */
    public function replies(string $start, string $end): ?array
    {
        $rows = \App\Models\SleekflowConversationReply::whereBetween('tanggal', [$start, $end])
            ->get(['tanggal', 'channel', 'first_ai_at', 'first_staff_at', 'ai_seconds', 'staff_seconds', 'msg_ai', 'msg_auto', 'msg_staff']);

        if ($rows->isEmpty()) {
            return null;
        }

        $median = function ($values) {
            $v = $values->filter(fn ($x) => $x !== null)->sort()->values();
            return $v->isEmpty() ? null : (float) $v[intdiv($v->count() - 1, 2)];
        };

        // AI greeting = AI yang menjadi pembalas pertama (sebelum staf). Balasan AI yang muncul setelah staf membalas
        // (mis. chat masuk sebelum AI aktif, atau AI menimpali di tengah percakapan) bukan greeting.
        $greeting = $rows->filter(fn ($r) => $r->first_ai_at && (! $r->first_staff_at || $r->first_ai_at->lte($r->first_staff_at)));
        // AI greeting hanya aktif di WhatsApp, jadi persen dihitung dari chat WhatsApp
        $whatsapp = $rows->where('channel', 'whatsappcloudapi');
        $outbound = $rows->sum('msg_ai') + $rows->sum('msg_auto') + $rows->sum('msg_staff');

        return [
            'percakapan' => $rows->count(),
            'whatsapp' => $whatsapp->count(),
            'dibalas_ai' => $greeting->count(),
            'persen_ai' => $whatsapp->count() ? $greeting->where('channel', 'whatsappcloudapi')->count() / $whatsapp->count() * 100 : null,
            'ai_rata' => $greeting->isEmpty() ? null : (float) $greeting->avg('ai_seconds'),
            'ai_median' => $median($greeting->pluck('ai_seconds')),
            'staf_median' => $median($rows->pluck('staff_seconds')),
            'msg_ai' => (int) $rows->sum('msg_ai'),
            'msg_auto' => (int) $rows->sum('msg_auto'),
            'msg_staff' => (int) $rows->sum('msg_staff'),
            'rasio_bot' => $outbound ? ($rows->sum('msg_ai') + $rows->sum('msg_auto')) / $outbound * 100 : null,
            'hari' => $rows->pluck('tanggal')->map(fn ($d) => $d->toDateString())->unique()->count(),
            'dari' => $rows->min('tanggal')->toDateString(),
        ];
    }

    /**
     * Isi modal rumus (tombol "i") untuk kartu dari tabel balasan percakapan: ai_median, persen_ai, rasio_bot.
     * Angka dihitung ulang dari baris yang sama dengan replies(), lengkap dengan rincian per hari.
     */
    public function explainReplies(string $key, string $start, string $end): ?array
    {
        $r = $this->replies($start, $end);
        $n = fn ($v, $dec = 0) => number_format((float) $v, $dec, ',', '.');
        $target = self::AI_TARGET_SECONDS;

        $base = [
            'sifat' => 'periode',
            'endpoint' => 'Sleekflow GET /conversation/all (urut updatedTime) + GET /conversation/message/{id} → tabel sleekflow_conversation_replies (php artisan sync:sleekflow-replies)',
            'label_nilai' => 'Nilai di Kartu',
            'label_rumus' => 'Rumus Database (InsightSW)',
            'label_turunan' => 'Olahan Setelah Query',
            'sumber_rumus' => "Dijalankan di App\\Services\\SleekflowActivityService::replies() dengan :start = {$start} dan :end = {$end}.",
        ];

        $info = [
            'ai_median' => [
                'judul' => 'Balas Pertama AI Greeting (Median)',
                'satuan' => 'detik',
                'field' => 'Pesan dengan deliveryType = AiAgentAction → first_ai_at; pesan masuk pertama → first_in_at; ai_seconds = first_ai_at − first_in_at',
                'definisi' => 'Lama pelanggan menunggu balasan pertama dari AI greeting, dihitung per percakapan lalu diambil nilai tengahnya (median). '
                    . 'Hanya percakapan yang dibalas AI lebih dulu dari staf CS (first_ai_at ≤ first_staff_at, atau staf belum membalas); '
                    . 'balasan AI yang muncul setelah staf bukan greeting. Median dipakai karena rata-rata terseret chat yang masuk sebelum AI aktif. '
                    . "Target Kamus Metrik: di bawah {$target} detik. Analitik harian Sleekflow tidak menghitung balasan AI, jadi angka ini diambil dari API pesan.",
                'rumus_db' => <<<'SQL'
SELECT ai_seconds
FROM sleekflow_conversation_replies
WHERE tanggal BETWEEN :start AND :end
  AND first_ai_at IS NOT NULL
  AND (first_staff_at IS NULL OR first_ai_at <= first_staff_at)
ORDER BY ai_seconds;
SQL,
                'turunan' => 'median = nilai ke-⌊(jumlah − 1) ÷ 2⌋ + 1 dari deret ai_seconds yang sudah diurutkan (median bawah bila jumlahnya genap).',
            ],
            'persen_ai' => [
                'judul' => 'Chat Dibalas AI Greeting',
                'satuan' => '%',
                'field' => 'channel = whatsappcloudapi; first_ai_at, first_staff_at',
                'definisi' => 'Porsi chat WhatsApp yang dibalas AI greeting lebih dulu dari staf CS. AI greeting hanya aktif di WhatsApp, '
                    . 'jadi pembaginya chat WhatsApp saja, bukan semua kanal. Chat yang langsung dibalas staf (AI tidak aktif / jam tertentu) tidak dihitung.',
                'rumus_db' => <<<'SQL'
SELECT
  SUM(CASE WHEN first_ai_at IS NOT NULL
            AND (first_staff_at IS NULL OR first_ai_at <= first_staff_at) THEN 1 ELSE 0 END) AS dibalas_ai,
  COUNT(*) AS chat_whatsapp
FROM sleekflow_conversation_replies
WHERE tanggal BETWEEN :start AND :end
  AND channel = 'whatsappcloudapi';
SQL,
                'turunan' => 'persen = dibalas_ai ÷ chat_whatsapp × 100',
            ],
            'rasio_bot' => [
                'judul' => 'Rasio Otomasi Bot',
                'satuan' => '%',
                'field' => 'deliveryType: AiAgentAction → msg_ai; AutomatedMessage → msg_auto; Normal / QuickReply (dikirim staf) → msg_staff. Catatan internal (channel note) tidak dihitung.',
                'definisi' => 'Porsi pesan keluar yang dikirim mesin (AI greeting + pesan otomatis Sleekflow) dibanding semua pesan keluar ke pelanggan. Sisanya = Human CS.',
                'rumus_db' => <<<'SQL'
SELECT SUM(msg_ai) AS ai, SUM(msg_auto) AS otomatis, SUM(msg_staff) AS staf
FROM sleekflow_conversation_replies
WHERE tanggal BETWEEN :start AND :end;
SQL,
                'turunan' => 'rasio = (ai + otomatis) ÷ (ai + otomatis + staf) × 100',
            ],
        ];

        if (! isset($info[$key])) {
            return null;
        }

        if (! $r) {
            return $base + $info[$key] + [
                'nilai' => 'Menyusul',
                'cek' => [['ok' => false, 'teks' => "Belum ada baris sleekflow_conversation_replies untuk {$start} s/d {$end}. Jalankan sync:sleekflow-replies (data tersedia sejak 19 Okt 2026)."]],
            ];
        }

        $rows = \App\Models\SleekflowConversationReply::whereBetween('tanggal', [$start, $end])
            ->get(['tanggal', 'channel', 'first_ai_at', 'first_staff_at', 'ai_seconds', 'msg_ai', 'msg_auto', 'msg_staff']);
        $isGreeting = fn ($x) => $x->first_ai_at && (! $x->first_staff_at || $x->first_ai_at->lte($x->first_staff_at));
        $perDay = $rows->groupBy(fn ($x) => $x->tanggal->toDateString())->sortKeys();
        $day = fn ($d) => \Carbon\Carbon::parse($d)->translatedFormat('D, j M Y');
        $coverage = ['ok' => true, 'teks' => 'Percakapan tersimpan: ' . $n($r['percakapan']) . ' (' . $n($r['whatsapp']) . ' WhatsApp) dari ' . $r['hari'] . ' hari, data mulai ' . $r['dari']];

        if ($key === 'ai_median') {
            $values = $rows->filter($isGreeting)->pluck('ai_seconds')->filter(fn ($v) => $v !== null)->sort()->values();
            $count = $values->count();
            $pos = intdiv(max($count - 1, 0), 2);

            return $base + $info[$key] + [
                'nilai' => $r['ai_median'] === null ? '–' : $n($r['ai_median']) . ' detik',
                'hitung' => [
                    'langkah' => [
                        ['op' => '=', 'teks' => 'Percakapan yang dibalas AI lebih dulu dari staf', 'rumus' => null, 'hasil' => $n($count) . ' percakapan'],
                        ['op' => '÷', 'teks' => 'Urutkan ai_seconds, ambil posisi tengah', 'rumus' => '(' . $n($count) . ' − 1) ÷ 2, dibulatkan ke bawah, +1', 'hasil' => 'urutan ke-' . $n($pos + 1)],
                        ['op' => '=', 'teks' => 'Median (nilai di kartu)', 'rumus' => null, 'hasil' => $r['ai_median'] === null ? '–' : $n($r['ai_median']) . ' detik'],
                    ],
                    'rincian' => $perDay->map(function ($g, $d) use ($isGreeting, $n, $day) {
                        $v = $g->filter($isGreeting)->pluck('ai_seconds')->filter(fn ($x) => $x !== null)->sort()->values();

                        return ['tanggal' => $day($d), 'nilai' => $v->isEmpty() ? '–' : $n($v[intdiv($v->count() - 1, 2)]) . ' dtk', 'ket' => $n($v->count()) . ' chat dibalas AI'];
                    })->values()->all(),
                ],
                'cek' => [
                    $coverage,
                    ['ok' => $r['ai_median'] !== null && $r['ai_median'] < $target, 'teks' => "Target < {$target} detik: median " . ($r['ai_median'] === null ? '–' : $n($r['ai_median']) . ' detik')],
                    ['ok' => true, 'teks' => 'Rata-rata (tidak dipakai di kartu): ' . ($r['ai_rata'] === null ? '–' : $n($r['ai_rata'], 1) . ' detik')],
                ],
            ];
        }

        if ($key === 'persen_ai') {
            return $base + $info[$key] + [
                'nilai' => $r['persen_ai'] === null ? '–' : $n($r['persen_ai'], 1) . '%',
                'hitung' => [
                    'langkah' => [
                        ['op' => '÷', 'teks' => 'Chat WhatsApp dibalas AI lebih dulu ÷ semua chat WhatsApp', 'rumus' => $n($r['dibalas_ai']) . ' ÷ ' . $n($r['whatsapp']), 'hasil' => $r['whatsapp'] ? $n($r['dibalas_ai'] / $r['whatsapp'], 6) : '–'],
                        ['op' => '×', 'teks' => 'Kali 100 supaya jadi persen', 'rumus' => null, 'hasil' => $r['persen_ai'] === null ? '–' : $n($r['persen_ai'], 4)],
                        ['op' => '=', 'teks' => 'Nilai di kartu (dibulatkan)', 'rumus' => null, 'hasil' => $r['persen_ai'] === null ? '–' : $n($r['persen_ai'], 1) . '%'],
                    ],
                    'rincian' => $perDay->map(function ($g, $d) use ($isGreeting, $n, $day) {
                        $wa = $g->where('channel', 'whatsappcloudapi');
                        $ai = $wa->filter($isGreeting)->count();

                        return ['tanggal' => $day($d), 'nilai' => $wa->count() ? $n($ai / $wa->count() * 100, 1) . '%' : '–', 'ket' => $n($ai) . ' dari ' . $n($wa->count()) . ' chat WA'];
                    })->values()->all(),
                ],
                'cek' => [
                    $coverage,
                    ['ok' => $r['dibalas_ai'] <= $r['whatsapp'], 'teks' => 'Semua percakapan yang dibalas AI lebih dulu: ' . $n($r['dibalas_ai']) . ' (AI hanya aktif di WhatsApp)'],
                ],
            ];
        }

        $outbound = $r['msg_ai'] + $r['msg_auto'] + $r['msg_staff'];

        return $base + $info[$key] + [
            'nilai' => $r['rasio_bot'] === null ? '–' : $n($r['rasio_bot'], 1) . '%',
            'hitung' => [
                'langkah' => [
                    ['op' => '+', 'teks' => 'Pesan AI + pesan otomatis', 'rumus' => $n($r['msg_ai']) . ' + ' . $n($r['msg_auto']), 'hasil' => $n($r['msg_ai'] + $r['msg_auto'])],
                    ['op' => '+', 'teks' => 'Semua pesan keluar (AI + otomatis + staf)', 'rumus' => $n($r['msg_ai']) . ' + ' . $n($r['msg_auto']) . ' + ' . $n($r['msg_staff']), 'hasil' => $n($outbound)],
                    ['op' => '÷', 'teks' => 'Pesan mesin ÷ semua pesan keluar, × 100', 'rumus' => $n($r['msg_ai'] + $r['msg_auto']) . ' ÷ ' . $n($outbound) . ' × 100', 'hasil' => $r['rasio_bot'] === null ? '–' : $n($r['rasio_bot'], 4)],
                    ['op' => '=', 'teks' => 'Nilai di kartu (dibulatkan)', 'rumus' => null, 'hasil' => $r['rasio_bot'] === null ? '–' : $n($r['rasio_bot'], 1) . '%'],
                ],
                'rincian' => $perDay->map(function ($g, $d) use ($n, $day) {
                    $mesin = $g->sum('msg_ai') + $g->sum('msg_auto');
                    $all = $mesin + $g->sum('msg_staff');

                    return ['tanggal' => $day($d), 'nilai' => $all ? $n($mesin / $all * 100, 1) . '%' : '–', 'ket' => 'AI ' . $n($g->sum('msg_ai')) . ', otomatis ' . $n($g->sum('msg_auto')) . ', staf ' . $n($g->sum('msg_staff'))];
                })->values()->all(),
            ],
            'cek' => [$coverage],
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
