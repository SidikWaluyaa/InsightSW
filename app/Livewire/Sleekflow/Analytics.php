<?php

namespace App\Livewire\Sleekflow;

use App\Livewire\Concerns\WithDateRange;
use App\Models\SleekflowDailyAnalytics;
use App\Services\SleekflowActivityService;
use App\Services\SleekflowService;
use App\Services\SyncService;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

class Analytics extends Component
{
    use WithPagination, WithDateRange;

    #[Url(as: 'dari', except: '')]
    public string $startDate = '';

    #[Url(as: 'sampai', except: '')]
    public string $endDate = '';

    public ?string $syncMessage = null;
    public bool $syncOk = true;

    public function mount()
    {
        // Default: tanggal 1 s/d kemarin (H-1), karena Sleekflow belum menyediakan data hari berjalan
        $latest = $this->maxSelectableDate();

        [$start, $end] = $this->normalizeDateRange(
            $this->startDate ?: $latest->copy()->startOfMonth()->toDateString(),
            $this->endDate ?: $latest->toDateString()
        );

        $this->startDate = $start;
        $this->endDate = $end;
    }

    protected function dateRangeChanged(): void
    {
        $this->syncMessage = null;

        $this->resetPage();
        $this->dispatchChart();
    }

    /**
     * Tarik ulang data rentang terpilih langsung dari Sleekflow API (dibatasi 1x per menit).
     */
    public function syncData(SleekflowService $service, SyncService $syncService): void
    {
        $result = null;

        // Nama modul disamakan dengan lock di sync:sleekflow-analytics agar tidak tabrakan dengan cron
        $ran = $syncService->syncIfAllowed('sleekflow_analytics', function () use ($service, &$result) {
            $result = $service->syncDailyAnalytics($this->startDate, $this->endDate);
        });

        if (!$ran) {
            $wait = $syncService->getSecondsToNextSync('sleekflow_analytics');
            $this->syncOk = false;
            $this->syncMessage = $wait > 0
                ? "Data baru saja diperbarui. Coba lagi dalam {$wait} detik."
                : 'Pembaruan data lain sedang berjalan. Coba lagi sebentar.';
        } else {
            $this->syncOk = (bool) ($result['success'] ?? false);
            $synced = (int) ($result['synced'] ?? 0);

            // Pesan untuk pengguna dibuat sederhana; detail teknis tetap tercatat di log
            if (!$this->syncOk) {
                $this->syncMessage = 'Gagal memperbarui data dari Sleekflow. Silakan coba lagi nanti.';
            } elseif ($synced > 0) {
                $this->syncMessage = "Data berhasil diperbarui ({$synced} hari).";
            } else {
                $this->syncMessage = 'Belum ada data baru. Data Sleekflow tersedia sampai kemarin.';
            }
        }

        $this->dispatchChart();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        // Simpan query dasar sebelum di-order agar agregasi (AVG/SUM) tidak error di MySQL Strict Mode
        $baseQuery = $this->rangeQuery();

        $analytics = (clone $baseQuery)->orderBy('date_time', 'desc')->paginate(15);

        $summary = $this->summary($baseQuery);

        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);
        $rangeDays = $start->diffInDays($end) + 1;
        $daysWithData = (clone $baseQuery)->count();

        return view('livewire.sleekflow.analytics', [
            'analytics' => $analytics,
            'summary' => $summary,
            'cards' => $this->cards($summary),
            'chart' => $this->chartData(),
            'rangeDays' => $rangeDays,
            'daysWithData' => $daysWithData,
            'presets' => $this->datePresets(),
            'maxDate' => $this->maxSelectableDate()->toDateString(),
        ]);
    }

    /**
     * Isi kartu KPI: label, nilai, keterangan singkat, dan info rumus (tombol "i", hanya Admin).
     */
    protected function cards(array $summary): array
    {
        $n = fn ($v) => number_format((int) $v, 0, ',', '.');

        $cards = [
            'total_contacts' => [
                'label' => 'Total Kontak', 'value' => $n($summary['total_contacts']), 'sub' => null, 'tone' => 'blue', 'icon' => 'users',
                'note' => 'Kontak yang tercatat per hari di Sleekflow, dijumlahkan.',
            ],
            'total_enquiries' => [
                'label' => 'Prospek Baru', 'value' => $n($summary['total_enquiries']), 'sub' => null, 'tone' => 'emerald', 'icon' => 'user-plus',
                'note' => 'Percakapan baru yang dimulai pelanggan.',
            ],
            'total_messages_sent' => [
                'label' => 'Pesan Terkirim', 'value' => $n($summary['total_messages_sent']), 'sub' => null, 'tone' => 'purple', 'icon' => 'send',
                'note' => 'Pesan yang dikirim tim CS ke pelanggan.',
            ],
            'total_messages_received' => [
                'label' => 'Pesan Diterima', 'value' => $n($summary['total_messages_received']), 'sub' => null, 'tone' => 'pink', 'icon' => 'chat',
                'note' => 'Pesan yang masuk dari pelanggan.',
            ],
            'avg_first_response_time' => [
                'label' => 'Waktu Balas Pertama (Staf CS)', 'value' => $summary['avg_first_response_time'], 'sub' => $summary['avg_first_response_text'], 'tone' => 'rose', 'icon' => 'clock',
                'note' => 'Lama pelanggan menunggu sampai dibalas pertama kali oleh staf CS. Balasan AI greeting tidak dihitung Sleekflow. Hari libur CS tidak dihitung.',
            ],
            'ai_greeting' => $this->aiGreetingCard(),
            'avg_response_time' => [
                'label' => 'Rata-rata Waktu Balas', 'value' => $summary['avg_response_time'], 'sub' => $summary['avg_response_text'], 'tone' => 'amber', 'icon' => 'clock',
                'note' => 'Rata-rata lama tiap pesan pelanggan menunggu balasan tim CS. Hari libur CS tidak dihitung.',
            ],
        ];

        // Tombol "i" hanya untuk Admin; isinya baru dihitung saat diklik (showFormula), supaya halaman tetap ringan
        $isAdmin = auth()->user()?->isAdmin() ?? false;

        foreach ($cards as $key => &$card) {
            // Kartu AI greeting dari tabel balasan percakapan, bukan analitik harian: belum punya rincian rumus di modal
            $card['info'] = $isAdmin && $key !== 'ai_greeting';
        }

        return $cards;
    }

    /**
     * Kartu AI greeting: median waktu balas AI sebagai pembalas pertama, dari API pesan Sleekflow
     * (tabel sleekflow_conversation_replies, sync:sleekflow-replies). Data tersedia sejak 19 Okt 2026.
     */
    protected function aiGreetingCard(): array
    {
        $r = app(SleekflowActivityService::class)->replies($this->startDate, $this->endDate);
        $target = SleekflowActivityService::AI_TARGET_SECONDS;

        if (! $r || $r['ai_median'] === null) {
            return [
                'label' => 'Balas Pertama AI Greeting', 'value' => 'Menyusul', 'sub' => $r ? 'Belum ada chat yang dibalas AI lebih dulu' : 'Data balasan AI belum ditarik untuk rentang ini',
                'tone' => 'cyan', 'icon' => 'sparkles',
                'note' => "Median waktu balas AI greeting (target < {$target} detik). Data tersedia sejak 19 Okt 2026.",
            ];
        }

        $detik = (int) round($r['ai_median']);
        $persen = $r['persen_ai'] === null ? '–' : number_format($r['persen_ai'], 1, ',', '.') . '%';

        $waktu = sprintf('%02d:%02d:%02d', intdiv($detik, 3600), intdiv($detik % 3600, 60), $detik % 60);

        return [
            'label' => 'Balas Pertama AI Greeting',
            'value' => $waktu,
            'sub' => $this->durationText($waktu) . ' (median) · ' . ($detik < $target ? 'sesuai target' : 'di atas target'),
            'tone' => 'cyan', 'icon' => 'sparkles',
            'note' => number_format($r['dibalas_ai'], 0, ',', '.') . ' dari ' . number_format($r['whatsapp'], 0, ',', '.') . " chat WhatsApp ({$persen}) dibalas AI lebih dulu. Target < {$target} detik. Data sejak "
                . Carbon::parse($r['dari'])->translatedFormat('j M Y') . '.',
        ];
    }

    /**
     * Ringkasan angka kartu pada rentang terpilih.
     */
    protected function summary($baseQuery): array
    {
        // Rata-rata waktu balas tanpa orderBy. Hari libur CS (tidak ada balasan staf) tidak ikut dirata-rata;
        // AVG mengabaikan NULL dari CASE. Total chat/pesan di hari itu tetap dihitung di kartu jumlah.
        $all = $this->countedResponseDay('response_time_all_messages');
        $first = $this->countedResponseDay('response_time_first_messages');
        $avgTimeRow = (clone $baseQuery)->selectRaw("
            SEC_TO_TIME(AVG(CASE WHEN {$all} THEN TIME_TO_SEC(response_time_all_messages) END)) as avg_time,
            SEC_TO_TIME(AVG(CASE WHEN {$first} THEN TIME_TO_SEC(response_time_first_messages) END)) as avg_first_time
        ")->first();

        $avgResponseTime = $avgTimeRow && $avgTimeRow->avg_time ? $avgTimeRow->avg_time : '00:00:00';
        $avgFirstResponseTime = $avgTimeRow && $avgTimeRow->avg_first_time ? $avgTimeRow->avg_first_time : '00:00:00';

        // Kita buang milidetik jika ada
        $avgResponseTime = explode('.', $avgResponseTime)[0];
        $avgFirstResponseTime = explode('.', $avgFirstResponseTime)[0];

        // Menghitung ringkasan metrik pada rentang terpilih
        $summary = [
            'total_contacts' => (clone $baseQuery)->sum('number_of_contacts'),
            'total_enquiries' => (clone $baseQuery)->sum('number_of_new_enquires'),
            'total_messages_sent' => (clone $baseQuery)->sum('number_of_messages_sent'),
            'total_messages_received' => (clone $baseQuery)->sum('number_of_message_received'),
            'avg_response_time' => $avgResponseTime,
            'avg_first_response_time' => $avgFirstResponseTime,
            'avg_response_text' => $this->durationText($avgResponseTime),
            'avg_first_response_text' => $this->durationText($avgFirstResponseTime),
        ];

        return $summary;
    }

    /**
     * Isi modal info rumus satu kartu (tombol "i", hanya Admin): rumus, rincian hitungan, dan cek silang.
     * Dikirim lewat event browser "show-formula" yang didengar <x-ui.formula-modal>.
     */
    public function showFormula(string $key): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $baseQuery = $this->rangeQuery();
        $summary = $this->summary($baseQuery);
        $cards = $this->cards($summary);

        if (! isset($cards[$key])) {
            return;
        }

        $rangeDays = Carbon::parse($this->startDate)->diffInDays(Carbon::parse($this->endDate)) + 1;
        $checks = $this->formulaChecks($baseQuery, $rangeDays, (clone $baseQuery)->count());
        $breakdown = $this->formulaBreakdown($baseQuery, $summary)[$key];

        $info = array_merge(config("sleekflow_formulas.{$key}", []), [
            'judul' => $cards[$key]['label'],
            'sifat' => 'periode',
            'endpoint' => 'Sleekflow GET /analytic?startDate=' . $this->startDate . '&endDate=' . $this->endDate . ' → tabel sleekflow_daily_analytics',
            'nilai' => (string) $summary[$key],
            'cek' => array_merge($checks[$key] ?? [], $breakdown['cek']),
            'hitung' => $breakdown['hitung'],
            'label_nilai' => 'Nilai di Kartu',
            'label_rumus' => 'Rumus Database (InsightSW)',
            'label_turunan' => 'Olahan Setelah Query',
            'sumber_rumus' => 'Dijalankan di App\\Livewire\\Sleekflow\\Analytics ke tabel lokal, dengan :start = ' . $this->startDate . ' dan :end = ' . $this->endDate . '.',
        ]);

        $this->dispatch('show-formula', ...$info);
    }

    /**
     * Rincian aritmatika tiap kartu untuk modal rumus: nilai per hari, lalu langkah tambah/kurang/bagi
     * dengan angka sebenarnya, dihitung ulang di PHP dari baris yang sama dengan query kartu.
     */
    protected function formulaBreakdown($baseQuery, array $summary): array
    {
        $rows = (clone $baseQuery)->orderBy('date_time')->get([
            'date_time', 'number_of_contacts', 'number_of_new_enquires', 'number_of_messages_sent',
            'number_of_message_received', 'response_time_first_messages', 'response_time_all_messages',
        ]);

        $n = fn ($v, $dec = 0) => number_format($v, $dec, ',', '.');
        $day = fn ($row) => Carbon::parse($row->date_time)->translatedFormat('D, j M Y');
        // Deret angka untuk ditampilkan: dipotong di tengah jika terlalu panjang (daftar lengkap ada di tabel per hari)
        $series = function (array $terms) {
            $count = count($terms);

            return $count > 12
                ? implode(' + ', array_slice($terms, 0, 5)) . ' + … + ' . implode(' + ', array_slice($terms, -3)) . " ({$count} angka)"
                : (implode(' + ', $terms) ?: '0');
        };

        $result = [];

        $sums = [
            'total_contacts' => 'number_of_contacts',
            'total_enquiries' => 'number_of_new_enquires',
            'total_messages_sent' => 'number_of_messages_sent',
            'total_messages_received' => 'number_of_message_received',
        ];

        foreach ($sums as $key => $column) {
            $values = $rows->pluck($column)->map(fn ($v) => (int) $v)->all();
            $total = array_sum($values);

            $result[$key] = [
                'hitung' => [
                    'langkah' => [
                        ['op' => '+', 'teks' => 'Jumlahkan nilai harian dari ' . count($values) . ' hari (semua hari, termasuk hari libur CS)',
                            'rumus' => $series(array_map($n, $values)), 'hasil' => $n($total)],
                        ['op' => '=', 'teks' => 'Nilai di kartu', 'rumus' => null, 'hasil' => $n($total)],
                    ],
                    'rincian' => $rows->map(fn ($r) => [
                        'tanggal' => $day($r), 'nilai' => $n((int) $r->{$column}), 'keluar' => false, 'ket' => null,
                    ])->all(),
                ],
                'cek' => [[
                    'ok' => $total === (int) $summary[$key],
                    'teks' => 'Hasil rincian ' . $n($total) . ' = nilai kartu ' . $n((int) $summary[$key]),
                ]],
            ];
        }

        $times = [
            'avg_first_response_time' => 'response_time_first_messages',
            'avg_response_time' => 'response_time_all_messages',
        ];

        foreach ($times as $key => $column) {
            $counted = [];
            $rincian = [];

            foreach ($rows as $r) {
                // Syarat sama dengan countedResponseDay(): bukan hari libur CS dan kolomnya terisi
                $offDay = static::isOffDay($r);
                $empty = ! $r->{$column} || $r->{$column} === '00:00:00';
                $sec = $this->timeToSeconds($r->{$column});

                if (! $offDay && ! $empty) {
                    $counted[] = $sec;
                }

                $rincian[] = [
                    'tanggal' => $day($r),
                    'nilai' => ($r->{$column} ?: '00:00:00') . ' = ' . $n($sec) . ' dtk',
                    'keluar' => $offDay || $empty,
                    'ket' => $offDay ? 'libur CS, tidak dihitung' : ($empty ? 'kosong, tidak dihitung' : null),
                ];
            }

            $days = count($counted);
            $total = array_sum($counted);
            $avg = $days > 0 ? $total / $days : 0;
            $avgText = gmdate('H:i:s', (int) floor($avg));
            $skipped = $rows->count() - $days;

            $result[$key] = [
                'hitung' => [
                    'langkah' => [
                        ['op' => '−', 'teks' => 'Buang hari libur CS (tidak ada balasan pertama dari staf) dan hari yang kosong',
                            'rumus' => $rows->count() . ' hari − ' . $skipped . ' hari', 'hasil' => $days . ' hari dihitung'],
                        ['op' => '+', 'teks' => 'Ubah tiap nilai harian ke detik, lalu jumlahkan',
                            'rumus' => $series(array_map($n, $counted)), 'hasil' => $n($total) . ' detik'],
                        ['op' => '÷', 'teks' => 'Bagi dengan jumlah hari yang dihitung (rata-rata)',
                            'rumus' => $n($total) . ' ÷ ' . $days, 'hasil' => $n($avg, 1) . ' detik'],
                        ['op' => '=', 'teks' => 'Ubah kembali ke jam:menit:detik (sisa pecahan detik dibuang)',
                            'rumus' => $n((int) floor($avg)) . ' detik', 'hasil' => $avgText],
                    ],
                    'rincian' => $rincian,
                ],
                'cek' => [[
                    'ok' => $avgText === $summary[$key],
                    'teks' => "Hasil rincian {$avgText} = nilai kartu {$summary[$key]}",
                ]],
            ];
        }

        return $result;
    }

    protected function timeToSeconds(?string $time): int
    {
        $parts = array_map('intval', explode(':', explode('.', (string) $time)[0]));

        return count($parts) === 3 ? $parts[0] * 3600 + $parts[1] * 60 + $parts[2] : 0;
    }

    /**
     * Cek silang untuk modal rumus: kelengkapan data harian dan dampak cara merata-ratakan waktu balas.
     */
    protected function formulaChecks($baseQuery, int $rangeDays, int $daysWithData): array
    {
        $coverage = [
            'ok' => $daysWithData === $rangeDays,
            'teks' => "Hari yang punya data: {$daysWithData} dari {$rangeDays} hari pada periode",
        ];

        $automated = (int) (clone $baseQuery)->sum('number_of_automated_messages');
        $noAgentDays = (clone $baseQuery)->where('number_of_messages_sent', 0)->count();

        $checks = [
            'total_contacts' => [$coverage],
            'total_enquiries' => [$coverage],
            'total_messages_sent' => [
                $coverage,
                ['ok' => $automated === 0, 'teks' => 'Pesan otomatis (number_of_automated_messages) pada periode: ' . number_format($automated, 0, ',', '.')],
                ['ok' => $noAgentDays === 0, 'teks' => "Hari tanpa pesan terkirim dari tim CS: {$noAgentDays} hari"],
            ],
            'total_messages_received' => [$coverage],
        ];

        $toTime = fn ($sec) => is_null($sec) ? '-' : gmdate('H:i:s', (int) floor($sec));

        foreach (['avg_first_response_time' => 'response_time_first_messages', 'avg_response_time' => 'response_time_all_messages'] as $key => $column) {
            $counted = $this->countedResponseDay($column);

            $row = (clone $baseQuery)->selectRaw("
                AVG(CASE WHEN {$counted} THEN TIME_TO_SEC({$column}) END) as avg_card,
                SUM(CASE WHEN response_time_first_messages = '00:00:00' THEN 1 ELSE 0 END) as off_days,
                SUM(CASE WHEN response_time_first_messages = '00:00:00' AND DAYOFWEEK(date_time) = 1 THEN 1 ELSE 0 END) as off_sundays,
                AVG(TIME_TO_SEC({$column})) as avg_all_days,
                SUM(CASE WHEN {$counted} THEN TIME_TO_SEC({$column}) * number_of_messages_sent END)
                    / NULLIF(SUM(CASE WHEN {$counted} THEN number_of_messages_sent END), 0) as avg_weighted
            ")->first();

            $offDays = (int) ($row->off_days ?? 0);
            $offSundays = (int) ($row->off_sundays ?? 0);
            // Selisih di bawah 1 menit dianggap sama dengan angka di kartu
            $same = fn ($sec) => ! is_null($sec) && ! is_null($row->avg_card) && abs($sec - $row->avg_card) < 60;

            $checks[$key] = [
                $coverage,
                ['ok' => true, 'teks' => "Hari libur CS (tidak ada balasan pertama dari staf) yang dikecualikan: {$offDays} hari, {$offSundays} di antaranya Minggu"],
                ['ok' => $same($row->avg_all_days), 'teks' => 'Cara lama, semua hari ikut dihitung: ' . $toTime($row->avg_all_days) . ' (kartu: ' . $toTime($row->avg_card) . ')'],
                ['ok' => $same($row->avg_weighted), 'teks' => 'Rata-rata berbobot pesan terkirim, hari ramai lebih berpengaruh: ' . $toTime($row->avg_weighted) . ' (kartu: ' . $toTime($row->avg_card) . ')'],
            ];
        }

        return $checks;
    }

    /**
     * Syarat SQL hari yang ikut rata-rata waktu balas: bukan hari libur CS dan kolomnya terisi.
     *
     * Hari libur dibaca dari data, bukan dari nama hari: tidak ada balasan pertama dari staf sama sekali
     * (response_time_first_messages = 00:00:00). Minggu tidak dipatok libur karena Feb–Sep 2026 CS
     * tetap masuk tiap Minggu; Minggu 4 & 11 Okt 2026 kosong dan otomatis terkecualikan.
     */
    protected function countedResponseDay(string $column): string
    {
        return "(response_time_first_messages <> '00:00:00' AND {$column} <> '00:00:00')";
    }

    /** Hari libur CS: tidak ada balasan pertama dari staf. */
    public static function isOffDay($row): bool
    {
        return ! $row->response_time_first_messages || $row->response_time_first_messages === '00:00:00';
    }

    protected function rangeQuery()
    {
        return SleekflowDailyAnalytics::query()
            ->whereDate('date_time', '>=', $this->startDate)
            ->whereDate('date_time', '<=', $this->endDate);
    }

    /**
     * Data untuk combo chart: semua hari pada rentang terpilih, urut Ascending.
     */
    protected function chartData(): array
    {
        $records = $this->rangeQuery()->orderBy('date_time', 'asc')
            ->get(['date_time', 'number_of_new_enquires', 'response_time_first_messages']);

        // Tampilkan tahun di label jika rentang melewati pergantian tahun
        $format = substr($this->startDate, 0, 4) === substr($this->endDate, 0, 4) ? 'd M' : 'd M y';

        $dates = [];
        $enquiries = [];
        $responseTimes = [];
        $sundays = [];
        $offDays = [];

        foreach ($records as $row) {
            $dates[] = Carbon::parse($row->date_time)->translatedFormat($format);
            $enquiries[] = (int) $row->number_of_new_enquires;
            $sundays[] = Carbon::parse($row->date_time)->isSunday();
            $offDays[] = static::isOffDay($row);

            // Ubah "HH:mm:ss" jadi Total Menit (karena Y-axis grafik butuh angka Decimal/Integer)
            $timeStr = $row->response_time_first_messages;
            $minutes = 0;

            // Hari libur CS: tidak ada nilai (null); di grafik garisnya disambung lurus ke hari kerja berikutnya
            if (static::isOffDay($row)) {
                $responseTimes[] = null;

                continue;
            }
            if ($timeStr) {
                $parts = explode(':', $timeStr);
                if (count($parts) === 3) {
                    $minutes = ($parts[0] * 60) + $parts[1] + ($parts[2] / 60);
                }
            }
            $responseTimes[] = round($minutes, 2);
        }

        return [
            'dates' => $dates,
            'enquiries' => $enquiries,
            'responseTimes' => $responseTimes,
            'sundays' => $sundays,
            'offDays' => $offDays,
        ];
    }

    /**
     * Ubah "HH:mm:ss" menjadi teks yang mudah dibaca, mis. "1 jam 36 menit".
     */
    protected function durationText(string $time): string
    {
        $parts = array_map('intval', explode(':', $time));
        if (count($parts) !== 3) {
            return '';
        }

        [$h, $m, $sec] = $parts;

        if ($h > 0) {
            return "{$h} jam {$m} menit";
        }

        return $m > 0 ? "{$m} menit {$sec} detik" : "{$sec} detik";
    }

    protected function dispatchChart(): void
    {
        $this->dispatch('sleekflow-chart-update', chart: $this->chartData());
    }

    /**
     * Sleekflow belum menyediakan data hari berjalan: batas atas filter adalah kemarin (H-1).
     */
    protected function maxSelectableDate(): Carbon
    {
        return now()->subDay()->startOfDay();
    }
}
