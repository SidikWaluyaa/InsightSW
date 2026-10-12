<?php

namespace App\Services;

use App\Models\SleekflowContact;
use App\Models\SleekflowDailyAnalytics;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SleekflowService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.sleekflow.key', '');
        $this->baseUrl = (string) config('services.sleekflow.base_url', 'https://sleekflow-core-app-seas-production.azurewebsites.net/api');
    }

    /**
     * Get consolidated analytics data for a specific date range.
     */
    public function getAnalyticsData(?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ? Carbon::parse($startDate, 'Asia/Jakarta')->toDateString() : Carbon::today('Asia/Jakarta')->toDateString();
        $endDate = $endDate ? Carbon::parse($endDate, 'Asia/Jakarta')->toDateString() : Carbon::today('Asia/Jakarta')->toDateString();
        $start = $startDate . ' 00:00:00';
        $end = $endDate . ' 23:59:59';
        // Calculate Inbound Totals (berbasis cohort / lead masuk hari ini)
        $totals = SleekflowContact::query()
            ->whereBetween('created_at_sleekflow', [$start, $end])
            ->selectRaw("
                COUNT(*) as total_contacts,
                COUNT(CASE WHEN status_chat = 'Greeting' THEN 1 END) as total_greeting,
                COUNT(CASE WHEN status_chat IN ('Konsultasi', 'Follow Up Konsultasi', 'Progress') THEN 1 END) as total_konsul
            ")
            ->first();

        // Calculate Closing Performance (Time-of-event: Hitung semua deal hari ini terlepas kapan lead masuk)
        $totalClosing = SleekflowContact::query()
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('closing_at', [$start, $end])
                  ->orWhereBetween('penerimaan_at', [$start, $end]);
            })
            ->count();

        // Calculate Unhandled (mengikuti filter utama)
        $unhandledCount = SleekflowContact::query()
            ->whereBetween('created_at_sleekflow', [$start, $end])
            ->where(function ($q) {
                $q->whereNull('status_chat')
                  ->orWhere('status_chat', '')
                  ->orWhere('status_chat', 'Contact Filtered');
            })
            ->count();

        $totalContacts = (int)$totals->total_contacts;
        $totalGreeting = (int)$totals->total_greeting;
        // $totalClosing sudah didapatkan dari count() query terpisah di atas
        $totalKonsul = (int)$totals->total_konsul;

        $greetingToKonsulRate = $totalGreeting > 0 ? round(($totalKonsul / $totalGreeting) * 100, 1) : 0;
        // unhandledRate uses totalContacts today? We can keep it or use total global contacts. We'll use totalContacts today for relative comparison, or just 0 if totalContacts is 0.
        $unhandledRate = $totalContacts > 0 ? round(($unhandledCount / $totalContacts) * 100, 1) : 0;

        // Leaderboard stats (per owner) using Mutually Exclusive logic
        $todayOwnerStats = SleekflowContact::query()
            ->whereBetween('created_at_sleekflow', [$start, $end])
            ->selectRaw("
                contact_owner_name,
                COUNT(*) as total_contacts,
                COUNT(CASE WHEN status_chat = 'Greeting' THEN 1 END) as total_greeting,
                COUNT(CASE WHEN status_chat IN ('Konsultasi', 'Follow Up Konsultasi', 'Progress') THEN 1 END) as total_konsul
            ")
            ->where('assigned_team', '5000000659')
            ->groupBy('contact_owner_name')
            ->get()
            ->keyBy('contact_owner_name');

        $closingOwnerStats = SleekflowContact::query()
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('closing_at', [$start, $end])
                  ->orWhereBetween('penerimaan_at', [$start, $end]);
            })
            ->where('assigned_team', '5000000659')
            ->selectRaw("contact_owner_name, COUNT(*) as total_closing")
            ->groupBy('contact_owner_name')
            ->get()
            ->keyBy('contact_owner_name');

        $unhandledOwnerStats = SleekflowContact::query()
            ->whereBetween('created_at_sleekflow', [$start, $end])
            ->where(function($q) {
                $q->whereNull('status_chat')
                  ->orWhere('status_chat', '')
                  ->orWhere('status_chat', 'Contact Filtered');
            })
            ->where('assigned_team', '5000000659')
            ->selectRaw("contact_owner_name, COUNT(*) as total_unhandled")
            ->groupBy('contact_owner_name')
            ->get()
            ->keyBy('contact_owner_name');

        $allOwners = collect($todayOwnerStats->keys())
            ->merge($closingOwnerStats->keys())
            ->merge($unhandledOwnerStats->keys())
            ->unique();

        $ownerStats = $allOwners->map(function($ownerName) use ($todayOwnerStats, $closingOwnerStats, $unhandledOwnerStats) {
            $todayStat = $todayOwnerStats->get($ownerName);
            $closingStat = $closingOwnerStats->get($ownerName);
            $unhandledStat = $unhandledOwnerStats->get($ownerName);

            $t_contacts = $todayStat ? (int)$todayStat->total_contacts : 0;
            $t_greeting = $todayStat ? (int)$todayStat->total_greeting : 0;
            $t_closing = $closingStat ? (int)$closingStat->total_closing : 0;
            $t_konsul = $todayStat ? (int)$todayStat->total_konsul : 0;
            $t_unhandled = $unhandledStat ? (int)$unhandledStat->total_unhandled : 0;

            return [
                'contact_owner_name' => $ownerName,
                'total_contacts' => $t_contacts,
                'total_greeting' => $t_greeting,
                'total_closing' => $t_closing,
                'total_konsul' => $t_konsul,
                'total_unhandled' => $t_unhandled,
                'consultation_rate' => ($t_konsul + $t_greeting) > 0 
                    ? round(($t_konsul / ($t_konsul + $t_greeting)) * 100, 1) 
                    : 0,
                'conversion_rate' => $t_greeting > 0 ? round(($t_closing / $t_greeting) * 100, 1) : 0,
                'display_name' => $ownerName ?: 'Belum Ditentukan'
            ];
        })
        ->filter(fn($s) => !empty($s['contact_owner_name']) && ($s['total_contacts'] + $s['total_greeting'] + $s['total_closing'] + $s['total_konsul'] + $s['total_unhandled']) > 0)
        ->sortByDesc('total_unhandled')
        ->values()
        ->toArray();

        return [
            'totalContacts' => $totalContacts,
            'totalGreeting' => $totalGreeting,
            'totalClosing' => $totalClosing,
            'totalKonsul' => $totalKonsul,
            'unhandledCount' => $unhandledCount,
            'greetingToKonsulRate' => $greetingToKonsulRate,
            'unhandledRate' => $unhandledRate,
            'ownerStats' => $ownerStats,
        ];
    }

    /**
     * Sync contacts from Sleekflow API to local database.
     */
    public function syncContacts(?string $startDate = null, ?string $endDate = null): array
    {
        $limit = 100;
        $offset = 0;
        
        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::today()->endOfDay();
        
        $stop = false;
        $totalSynced = 0;

        while (!$stop) {
            try {
                $response = Http::withHeaders([
                    'X-Sleekflow-Api-Key' => $this->apiKey
                ])->timeout(45)->retry(5, 3000)->get("{$this->baseUrl}/contact", [
                    'limit' => $limit,
                    'offset' => $offset,
                    'sort' => 'updatedAt desc',
                    'include' => 'custom_fields'
                ]);
                
                if (!$response->successful()) {
                    $errorMsg = 'Sleekflow API Error: ' . ($response->json('message') ?? $response->body());
                    Log::error($errorMsg);
                    break; // Safely stop and keep what we have
                }
            } catch (\Exception $e) {
                Log::error('Sleekflow API Sync Exception: ' . $e->getMessage());
                break; // Stop syncing if server is busy after retries, but don't crash the app
            }

            $data = $response->json();
            
            if (empty($data) || count($data) === 0) {
                break;
            }

            $batchData = [];
            $batchIds = [];

            foreach ($data as $contact) {
                if (empty($contact['CreatedAt'])) continue;

                $createdAtWib = $this->parseToWib($contact['CreatedAt']);
                $createdDateWibString = $createdAtWib->toDateString();
                
                $updatedAtWib = $this->parseToWib($contact['UpdatedAt'] ?? $contact['CreatedAt']);
                $updatedDateWibString = $updatedAtWib->toDateString();
                
                // 🔥 LOGIK STOP BARU: Berhenti jika UPDATE terakhir sudah melewati batas tanggal awal
                if ($updatedDateWibString < $startDate->toDateString()) {
                    $stop = true;
                    // We don't break yet, finish the current batch in case it's unsorted
                }

                // 🔥 FILTER: Tarik semua kontak yang di-update sejak tanggal awal (tanpa membatasi batas atas)
                // Hal ini menjamin kontak yang dibuat dalam rentang tanggal tetapi mengalami update di luar rentang tanggal tidak akan hilang.
                if ($updatedDateWibString >= $startDate->toDateString()) {
                    $contactId = (string) $contact['id'];
                    $batchIds[] = $contactId;
                    
                    // $updatedAtWib already initialized above
                    
                    $batchData[$contactId] = [
                        'sleekflow_id' => $contactId,
                        'first_name' => $contact['FirstName'] ?? null,
                        'last_name' => $contact['LastName'] ?? null,
                        'phone_number' => $contact['PhoneNumber'] ?? null,
                        'email' => $contact['Email'] ?? null,
                        
                        'contact_owner_name' => $contact['ContactOwnerName'] ?? null,
                        'contact_owner_email' => $contact['ContactOwnerEmail'] ?? null,
                        'contact_owner_id' => $contact['ContactOwner'] ?? null,
                        'assigned_team' => $contact['AssignedTeam'] ?? null,
                        
                        'status_chat' => $contact['status_chat'] ?? $contact['customFields']['status_chat'] ?? $contact['custom_fields']['status_chat'] ?? null,
                        
                        'lifecycle_stage' => $contact['lifecycleStage']['name'] ?? 
                                             (is_string($contact['lifecycleStage'] ?? null) ? $contact['lifecycleStage'] : null) ?? 
                                             ($contact['customFields']['lifecycle_stage'] ?? $contact['custom_fields']['lifecycle_stage'] ?? null),
                        
                        'lead_stage' => $contact['LeadStage'] ?? null,
                        'priority' => $contact['Priority'] ?? null,
                        
                        'last_contact' => $this->parseToWib($contact['LastContact'] ?? null)?->toDateTimeString(),
                        'last_contact_from_customers' => $this->parseToWib($contact['LastContactFromCustomers'] ?? null)?->toDateTimeString(),
                        'last_contacted_from_company' => $this->parseToWib($contact['LastContactedFromCompany'] ?? null)?->toDateTimeString(),
                        'last_contacted_from_user' => $this->parseToWib($contact['LastContactedFromUser'] ?? null)?->toDateTimeString(),
                        'last_channel' => $contact['LastChannel'] ?? null,
                        
                        'lead_source' => $contact['LeadSource'] ?? null,
                        'facebook_form_id' => $contact['Facebook Form ID'] ?? null,
                        'labels' => isset($contact['Labels']) ? json_encode($contact['Labels']) : null,
                        'lists' => isset($contact['Lists']) ? json_encode($contact['Lists']) : null,
                        
                        'company_name' => $contact['CompanyName'] ?? null,
                        'job_title' => $contact['JobTitle'] ?? null,
                        'country' => $contact['Country'] ?? null,
                        'subscriber' => filter_var($contact['Subscriber'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                        'ai_agent_session' => $contact['AI Agent Session'] ?? null,
                        'collaborators' => isset($contact['Collaborators']) ? json_encode($contact['Collaborators']) : null,
                        
                        'facebook_psid' => $contact['FacebookPSId'] ?? null,
                        'wechat_openid' => $contact['WeChatOpenId'] ?? null,
                        'line_chatid' => $contact['LineChatId'] ?? null,
                        
                        'created_at_sleekflow' => $createdAtWib->toDateTimeString(),
                        'updated_at_sleekflow' => $updatedAtWib->toDateTimeString(),
                        'waktu_awal' => $createdDateWibString,
                        'created_at' => now()->toDateTimeString(),
                        'updated_at' => now()->toDateTimeString(),
                    ];
                }
            }

            // Process Batch
            if (!empty($batchIds)) {
                $existingContacts = SleekflowContact::whereIn('sleekflow_id', $batchIds)
                    ->get()
                    ->keyBy('sleekflow_id');

                $finalUpsertData = [];
                $now = now()->toDateTimeString();

                foreach ($batchData as $id => $data) {
                    $existing = $existingContacts->get($id);
                    $status = $data['status_chat'];
                    $record = $data;

                    // 🔥 CRITICAL: Initialize all tracking columns to null to ensure 
                    // consistent column count for the batch upsert (avoids SQL Column Count error)
                    $record['greeting_at'] = $existing ? $existing->greeting_at : null;
                    $record['konsul_at'] = $existing ? $existing->konsul_at : null;
                    $record['followed_up_at'] = $existing ? $existing->followed_up_at : null;
                    $record['closing_at'] = $existing ? $existing->closing_at : null;
                    $record['penerimaan_at'] = $existing ? $existing->penerimaan_at : null;

                    if ($status === 'Greeting' && empty($record['greeting_at'])) $record['greeting_at'] = $now;
                    if ($status === 'Konsultasi' && empty($record['konsul_at'])) $record['konsul_at'] = $now;
                    if ($status === 'Follow Up Konsultasi' && empty($record['followed_up_at'])) $record['followed_up_at'] = $now;
                    if ($status === 'Closing' && empty($record['closing_at'])) $record['closing_at'] = $now;
                    if ($status === 'Before Penerimaan' && empty($record['penerimaan_at'])) $record['penerimaan_at'] = $now;

                    $finalUpsertData[] = $record;
                }

                if (!empty($finalUpsertData)) {
                    // Laravel upsert: records, unique columns, columns to update
                    SleekflowContact::upsert($finalUpsertData, ['sleekflow_id'], [
                        'first_name', 'last_name', 'phone_number', 'email', 
                        'contact_owner_name', 'contact_owner_email', 'contact_owner_id', 'assigned_team',
                        'status_chat', 'lifecycle_stage', 'lead_stage', 'priority',
                        'last_contact', 'last_contact_from_customers', 'last_contacted_from_company', 'last_contacted_from_user',
                        'last_channel', 'lead_source', 'facebook_form_id', 'labels', 'lists',
                        'company_name', 'job_title', 'country', 'subscriber', 'ai_agent_session', 'collaborators',
                        'facebook_psid', 'wechat_openid', 'line_chatid',
                        'created_at_sleekflow', 'updated_at_sleekflow', 'waktu_awal', 'updated_at',
                        'greeting_at', 'konsul_at', 'followed_up_at', 'closing_at', 'penerimaan_at'
                    ]);
                    $totalSynced += count($finalUpsertData);
                }
            }

            $offset += $limit;
            
            // Sleep for 500ms to prevent HTTP 400 "Server busy" rate limit errors
            usleep(500000);
            
            // Safety break to prevent infinite loops (Sleekflow max contacts safety)
            if ($offset > 50000) break; 
        }

        return ['synced' => $totalSynced];
    }

    /**
     * Mapping field API Sleekflow Daily Analytics (camelCase) -> kolom tabel sleekflow_daily_analytics (snake_case).
     * Format: 'apiField' => ['db_column', 'tipe']
     */
    protected const DAILY_ANALYTICS_MAP = [
        'responseTimeForFirstMessages'            => ['response_time_first_messages', 'time'],
        'responseTimeForAllMessages'              => ['response_time_all_messages', 'time'],
        'numberOfContacts'                        => ['number_of_contacts', 'int'],
        'numberOfContactsAverage'                 => ['number_of_contacts_average', 'decimal'],
        'numberOfAllConversations'                => ['number_of_all_conversations', 'int'],
        'numberOfAllConversationsAverage'         => ['number_of_all_conversations_average', 'decimal'],
        'numberOfActiveConversations'             => ['number_of_active_conversations', 'int'],
        'numberOfActiveConversationsAverage'      => ['number_of_active_conversations_average', 'decimal'],
        'numberOfUniqueConversations'             => ['number_of_unique_conversations', 'int'],
        'numberOfUniqueConversationAverage'       => ['number_of_unique_conversation_average', 'decimal'],
        'numberOfUniqueActiveConversations'       => ['number_of_unique_active_conversations', 'int'],
        'numberOfUniqueActiveConversationAverage' => ['number_of_unique_active_conversation_average', 'decimal'],
        'numberOfNewEnquires'                     => ['number_of_new_enquires', 'int'],
        'numberOfNewEnquiresAverage'              => ['number_of_new_enquires_average', 'decimal'],
        'numberOfMessagesSent'                    => ['number_of_messages_sent', 'int'],
        'numberOfMessagesSentAverage'             => ['number_of_messages_sent_average', 'decimal'],
        'numberOfMessageReceived'                 => ['number_of_message_received', 'int'],
        'numberOfMessageReceivedAverage'          => ['number_of_message_received_average', 'decimal'],
        'numberOfAutomatedMessages'               => ['number_of_automated_messages', 'int'],
        'numberOfBroadcastSent'                   => ['number_of_broadcast_sent', 'int'],
        'numberOfBroadcastBounced'                => ['number_of_broadcast_bounced', 'int'],
        'numberOfBroadcastDelivered'              => ['number_of_broadcast_delivered', 'int'],
        'numberOfBroadcastRead'                   => ['number_of_broadcast_read', 'int'],
        'numberOfBroadcastReplied'                => ['number_of_broadcast_replied', 'int'],
        'activeAgents'                            => ['active_agents', 'int'],
    ];

    /**
     * Tarik Daily Analytics dari Sleekflow API lalu simpan ke tabel sleekflow_daily_analytics.
     * Idempotent: baris di-update berdasarkan date_time, jadi aman dijalankan berulang kali.
     *
     * @param string $startDate  Y-m-d (inklusif)
     * @param string $endDate    Y-m-d (inklusif)
     */
    public function syncDailyAnalytics(string $startDate, string $endDate): array
    {
        $url = (string) config('services.sleekflow.analytics_url', '');

        if ($url === '') {
            return $this->analyticsResult(false, 0, 0, 'SLEEKFLOW_ANALYTICS_URL belum diisi di .env. Sync dibatalkan.');
        }
        if ($this->apiKey === '') {
            return $this->analyticsResult(false, 0, 0, 'SLEEKFLOW_API_KEY belum diisi di .env. Sync dibatalkan.');
        }

        try {
            $start = Carbon::createFromFormat('Y-m-d', $startDate, 'UTC')->startOfDay();
            $end = Carbon::createFromFormat('Y-m-d', $endDate, 'UTC')->startOfDay();
        } catch (\Throwable $e) {
            return $this->analyticsResult(false, 0, 0, 'Format tanggal tidak valid (harus Y-m-d).');
        }

        if ($start->gt($end)) {
            return $this->analyticsResult(false, 0, 0, "Tanggal awal ({$startDate}) lebih besar dari tanggal akhir ({$endDate}).");
        }

        // Bucket harian Sleekflow memakai format "YYYY-MM-DDT00:00:00Z".
        // 'to' dikirim H+1 agar hari terakhir pasti ikut (aman jika API memperlakukan 'to' eksklusif);
        // data di luar rentang yang diminta difilter lagi di bawah.
        $params = [
            'from' => $start->format('Y-m-d\TH:i:s\Z'),
            'to'   => $end->copy()->addDay()->format('Y-m-d\TH:i:s\Z'),
        ];

        try {
            $response = Http::withHeaders([
                    (string) config('services.sleekflow.analytics_auth_header', 'X-Sleekflow-Api-Key') => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->timeout(60)
                ->retry(3, 3000, throw: false)
                ->get($url, $params);
        } catch (\Throwable $e) {
            Log::error('Sleekflow Daily Analytics Exception: ' . $e->getMessage());
            return $this->analyticsResult(false, 0, 0, 'Gagal menghubungi Sleekflow API: ' . $e->getMessage());
        }

        if (!$response->successful()) {
            $msg = "Sleekflow Daily Analytics API Error [HTTP {$response->status()}]: " . mb_substr($response->body(), 0, 500);
            Log::error($msg);
            return $this->analyticsResult(false, 0, 0, $msg);
        }

        $payload = $response->json();
        // Dukung payload langsung { dailyAnalytics: [...] } maupun dibungkus { data: { dailyAnalytics: [...] } }
        $rows = $payload['dailyAnalytics'] ?? $payload['data']['dailyAnalytics'] ?? null;

        if (!is_array($rows)) {
            $msg = 'Payload Sleekflow tidak valid: field "dailyAnalytics" tidak ditemukan.';
            Log::error($msg, ['body' => mb_substr($response->body(), 0, 500)]);
            return $this->analyticsResult(false, 0, 0, $msg);
        }

        $synced = 0;
        $skipped = 0;
        $rangeStart = $start->toDateString();
        $rangeEnd = $end->toDateString();

        DB::transaction(function () use ($rows, $rangeStart, $rangeEnd, &$synced, &$skipped) {
            foreach ($rows as $row) {
                $mapped = is_array($row) ? $this->mapDailyAnalyticsRow($row) : null;

                if ($mapped === null) {
                    $skipped++;
                    Log::warning('Sleekflow Daily Analytics: baris dilewati (dateTime tidak valid).', ['row' => $row]);
                    continue;
                }

                $date = $mapped['date_time'];
                if ($date < $rangeStart || $date > $rangeEnd) {
                    continue; // di luar rentang yang diminta
                }

                $record = SleekflowDailyAnalytics::firstOrNew(['date_time' => $date]);
                $record->fill($mapped);
                $record->save();
                $synced++;
            }
        });

        $msg = "Sleekflow Daily Analytics {$rangeStart} s/d {$rangeEnd}: {$synced} hari tersimpan" . ($skipped ? ", {$skipped} baris dilewati" : '') . '.';
        Log::info($msg);

        return $this->analyticsResult(true, $synced, $skipped, $msg);
    }

    /**
     * Ubah satu baris payload API menjadi atribut tabel. Return null jika dateTime tidak valid.
     */
    protected function mapDailyAnalyticsRow(array $row): ?array
    {
        $rawDate = $row['dateTime'] ?? null;
        if (!is_string($rawDate) || !preg_match('/^\d{4}-\d{2}-\d{2}/', $rawDate)) {
            return null;
        }

        // dateTime adalah bucket tanggal (00:00:00Z) — ambil bagian tanggalnya saja,
        // TIDAK dikonversi +7 agar tanggal tidak bergeser.
        $attributes = ['date_time' => substr($rawDate, 0, 10)];

        foreach (self::DAILY_ANALYTICS_MAP as $apiField => [$column, $type]) {
            $value = $row[$apiField] ?? null;

            $attributes[$column] = match ($type) {
                'time'    => $this->normalizeDuration($value),
                'int'     => is_numeric($value) ? (int) round((float) $value) : 0,
                'decimal' => is_numeric($value) ? round((float) $value, 2) : 0,
            };
        }

        return $attributes;
    }

    /**
     * Normalisasi durasi ke format MySQL TIME (HH:MM:SS).
     * Menangani format .NET TimeSpan seperti "1.02:03:04" (lebih dari 24 jam) dan milidetik "00:16:21.123".
     */
    protected function normalizeDuration(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        if (!preg_match('/^(?:(\d+)\.)?(\d{1,3}):(\d{2}):(\d{2})(?:\.\d+)?$/', trim($value), $m)) {
            Log::warning("Sleekflow Daily Analytics: format durasi tidak dikenali '{$value}'.");
            return null;
        }

        $hours = ((int) ($m[1] ?? 0)) * 24 + (int) $m[2];
        $hours = min($hours, 838); // batas maksimum tipe TIME MySQL (838:59:59)

        return sprintf('%02d:%s:%s', $hours, $m[3], $m[4]);
    }

    protected function analyticsResult(bool $success, int $synced, int $skipped, string $message): array
    {
        return compact('success', 'synced', 'skipped', 'message');
    }

    /**
     * Get bulk daily stats for a date range (Optimized for monthly sync).
     */
    public function getDailyStatsInRange(string $startDate, string $endDate): array
    {
        return SleekflowContact::query()
            ->whereBetween('created_at_sleekflow', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw("
                DATE(created_at_sleekflow) as date,
                COUNT(*) as chat_in,
                SUM(CASE WHEN status_chat = 'Konsultasi' THEN 1 ELSE 0 END) as chat_consul
            ")
            ->groupBy('date')
            ->get()
            ->keyBy(function($item) {
                // Ensure key is Y-m-d string even if MySQL/Eloquent returns as object
                return is_string($item->date) ? $item->date : Carbon::parse($item->date)->toDateString();
            })
            ->toArray();
    }

    /**
     * Upsert a single contact into the database.
     */
    protected function upsertContact(array $data): void
    {
        // ... (This can be kept for single triggers, but syncContacts is optimized)
        SleekflowContact::updateOrCreate(
            ['sleekflow_id' => $data['id']],
            [
                'first_name' => $data['FirstName'] ?? null,
                'last_name' => $data['LastName'] ?? null,
                'phone_number' => $data['PhoneNumber'] ?? null,
                'email' => $data['Email'] ?? null,
                'status_chat' => $data['status_chat'] ?? $data['custom_fields']['status_chat'] ?? null,
                'created_at_sleekflow' => $this->parseToWib($data['CreatedAt'] ?? null),
                'updated_at_sleekflow' => $this->parseToWib($data['UpdatedAt'] ?? null),
                'waktu_awal' => $this->parseToWib($data['CreatedAt'] ?? null)?->toDateString(),
            ]
        );
    }

    /**
     * Helper to parse and convert timestamp to WIB.
     */
    protected function parseToWib(?string $val): ?Carbon
    {
        if (!$val) return null;
        try {
            // Brute force +7 as requested to ensure WIB conversion
            return Carbon::parse($val)->addHours(7);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Flatten nested object (similar to AppScript logic).
     */
    protected function flattenObject(array $obj, string $prefix = ""): array
    {
        $res = [];
        foreach ($obj as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value) && !array_is_list($value)) {
                $res = array_merge($res, $this->flattenObject($value, $newKey));
            } else {
                $res[$newKey] = $value;
            }
        }
        return $res;
    }
}
