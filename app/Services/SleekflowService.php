<?php

namespace App\Services;

use App\Models\SleekflowContact;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SleekflowService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.sleekflow.key');
        $this->baseUrl = config('services.sleekflow.base_url', 'https://sleekflow-core-app-seas-production.azurewebsites.net/api');
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

        // Calculate all totals in one optimized query
        $totals = SleekflowContact::query()
            ->selectRaw("
                COUNT(CASE WHEN created_at_sleekflow BETWEEN ? AND ? THEN 1 END) as total_contacts,
                COUNT(CASE WHEN greeting_at BETWEEN ? AND ? THEN 1 END) as total_greeting,
                COUNT(CASE WHEN konsul_at BETWEEN ? AND ? THEN 1 END) as total_konsul,
                COUNT(CASE WHEN closing_at BETWEEN ? AND ? THEN 1 END) as total_closing,
                COUNT(CASE WHEN (status_chat IS NULL OR status_chat = '') AND created_at_sleekflow BETWEEN ? AND ? THEN 1 END) as total_unhandled
            ", [
                $start, $end, // total contacts
                $start, $end, // greeting
                $start, $end, // konsul
                $start, $end, // closing
                $start, $end  // unhandled (based on chat entry)
            ])
            ->first();

        $totalContacts = (int)$totals->total_contacts;
        $totalGreeting = (int)$totals->total_greeting;
        $totalClosing = (int)$totals->total_closing;
        $totalKonsul = (int)$totals->total_konsul;
        $unhandledCount = (int)$totals->total_unhandled;

        $greetingToKonsulRate = $totalGreeting > 0 ? round(($totalKonsul / $totalGreeting) * 100, 1) : 0;
        $unhandledRate = $totalContacts > 0 ? round(($unhandledCount / $totalContacts) * 100, 1) : 0;

        // Leaderboard stats (per owner) 
        // We filter the base query to owners who had ANY activity in the range to keep it efficient
        $ownerStats = SleekflowContact::query()
            ->selectRaw("
                contact_owner_name,
                COUNT(CASE WHEN created_at_sleekflow BETWEEN ? AND ? THEN 1 END) as total_contacts,
                COUNT(CASE WHEN greeting_at BETWEEN ? AND ? THEN 1 END) as total_greeting,
                COUNT(CASE WHEN closing_at BETWEEN ? AND ? THEN 1 END) as total_closing,
                COUNT(CASE WHEN konsul_at BETWEEN ? AND ? THEN 1 END) as total_konsul,
                COUNT(CASE WHEN (status_chat IS NULL OR status_chat = '') AND created_at_sleekflow BETWEEN ? AND ? THEN 1 END) as total_unhandled
            ", [
                $start, $end, // total contacts
                $start, $end, // greeting
                $start, $end, // closing
                $start, $end, // konsul
                $start, $end  // unhandled
            ])
            ->groupBy('contact_owner_name')
            ->get()
            ->map(function($stat) {
                return [
                    'contact_owner_name' => $stat->contact_owner_name,
                    'total_contacts' => (int)$stat->total_contacts,
                    'total_greeting' => (int)$stat->total_greeting,
                    'total_closing' => (int)$stat->total_closing,
                    'total_konsul' => (int)$stat->total_konsul,
                    'total_unhandled' => (int)$stat->total_unhandled,
                    'consultation_rate' => ($stat->total_konsul + $stat->total_greeting) > 0 
                        ? round(($stat->total_konsul / ($stat->total_konsul + $stat->total_greeting)) * 100, 1) 
                        : 0,
                    'conversion_rate' => $stat->total_greeting > 0 ? round(($stat->total_closing / $stat->total_greeting) * 100, 1) : 0,
                    'display_name' => $stat->contact_owner_name ?: 'Belum Ditentukan'
                ];
            })
            ->filter(fn($s) => ($s['total_contacts'] + $s['total_greeting'] + $s['total_closing'] + $s['total_konsul']) > 0)
            ->sortByDesc('total_closing')
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
            $response = Http::withHeaders([
                'X-Sleekflow-Api-Key' => $this->apiKey
            ])->timeout(45)->retry(3, 2000)->get("{$this->baseUrl}/contact", [
                'limit' => $limit,
                'offset' => $offset,
                'sort' => 'createdAt desc',
                'include' => 'custom_fields'
            ]);
            
            if (!$response->successful()) {
                $errorMsg = 'Sleekflow API Error: ' . ($response->json('message') ?? $response->body());
                Log::error($errorMsg);
                throw new \Exception($errorMsg);
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
                
                // 🔥 APP SCRIPT STOP LOGIC: only stop if we are past the date range 
                // and we've already synced the relevant ones.
                if ($createdDateWibString < $startDate->toDateString()) {
                    $stop = true;
                    // We don't break yet, finish the current batch in case it's unsorted
                }

                // 🔥 APP SCRIPT FILTER: if (createdWIB >= startDate && createdWIB <= endDate)
                if ($createdDateWibString >= $startDate->toDateString() && $createdDateWibString <= $endDate->toDateString()) {
                    $contactId = (string) $contact['id'];
                    $batchIds[] = $contactId;
                    
                    $updatedAtWib = $this->parseToWib($contact['UpdatedAt'] ?? $contact['CreatedAt']);
                    
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
                        
                        'status_chat' => $contact['status_chat'] ?? $contact['custom_fields']['status_chat'] ?? null,
                        
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
            
            // Safety break to prevent infinite loops (Sleekflow max contacts safety)
            if ($offset > 5000) break; 
        }

        return ['synced' => $totalSynced];
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
            return Carbon::parse($val)->timezone('Asia/Jakarta');
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
