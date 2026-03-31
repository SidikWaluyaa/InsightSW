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
        $startDate = $startDate ?: Carbon::today()->toDateString();
        $endDate = $endDate ?: Carbon::today()->toDateString();

        $baseQuery = SleekflowContact::query()
            ->whereBetween('updated_at_sleekflow', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        $totalContacts = (clone $baseQuery)->count();
        $totalGreeting = (clone $baseQuery)->where('status_chat', 'Greeting')->count();
        $totalClosing = (clone $baseQuery)->where('status_chat', 'Closing')->count();
        $totalKonsul = (clone $baseQuery)->where('status_chat', 'Konsultasi')->count();
        
        $unhandledCount = (clone $baseQuery)->where(function($q) {
            $q->whereNull('status_chat')->orWhere('status_chat', '');
        })->count();

        $greetingToKonsulRate = $totalGreeting > 0 ? round(($totalKonsul / $totalGreeting) * 100, 1) : 0;
        $unhandledRate = $totalContacts > 0 ? round(($unhandledCount / $totalContacts) * 100, 1) : 0;

        // Leaderboard stats (per owner)
        $ownerStats = (clone $baseQuery)
            ->selectRaw("
                contact_owner_name,
                COUNT(*) as total_contacts,
                SUM(CASE WHEN status_chat = 'Greeting' THEN 1 ELSE 0 END) as total_greeting,
                SUM(CASE WHEN status_chat = 'Closing' THEN 1 ELSE 0 END) as total_closing,
                SUM(CASE WHEN status_chat = 'Konsultasi' THEN 1 ELSE 0 END) as total_konsul
            ")
            ->groupBy('contact_owner_name')
            ->orderByDesc('total_closing')
            ->get()
            ->map(function($stat) {
                $stat->conversion_rate = $stat->total_greeting > 0 ? round(($stat->total_konsul / $stat->total_greeting) * 100, 1) : 0;
                $stat->display_name = $stat->contact_owner_name ?: 'Belum Ditentukan';
                return $stat;
            });

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
        $allContacts = [];
        
        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::parse('2026-03-31')->startOfDay();
        $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::parse('2026-03-31')->endOfDay();
        
        $maxRecordsToScan = 1000; // Force scan at least 1000 to be absolutely sure
        $scannedCount = 0;

        while ($scannedCount < $maxRecordsToScan) {
            $response = Http::withHeaders([
                'X-Sleekflow-Api-Key' => $this->apiKey
            ])->get("{$this->baseUrl}/contact", [
                'limit' => $limit,
                'offset' => $offset,
                'sort' => 'updatedAt desc',      // Try standard sort
                '$orderby' => 'updatedAt desc', // Try OData sort
                'include' => 'custom_fields'
            ]);
            
            if (!$response->successful()) {
                Log::error('Sleekflow API Error: ' . $response->body());
                break;
            }

            $data = $response->json();
            
            if (empty($data) || count($data) === 0) {
                break;
            }

            foreach ($data as $contact) {
                if (empty($contact['CreatedAt'])) continue;

                $createdAt = Carbon::parse($contact['CreatedAt'])->timezone('Asia/Jakarta');
                $updatedAt = Carbon::parse($contact['UpdatedAt'] ?? $contact['CreatedAt'])->timezone('Asia/Jakarta');
                
                // We DONT stop anymore. We scan ALL 1000 records.
                
                // Append to collection if within range
                // We use UpdatedAt for the dashboard range logic now
                if ($updatedAt->gte(Carbon::parse($startDate)->startOfDay()) && $updatedAt->lte(Carbon::parse($endDate)->endOfday())) {
                    $allContacts[] = [
                        'sleekflow_id' => $contact['id'],
                        'first_name' => $contact['FirstName'] ?? null,
                        'last_name' => $contact['LastName'] ?? null,
                        'phone_number' => $contact['PhoneNumber'] ?? null,
                        'email' => $contact['Email'] ?? null,
                        
                        'contact_owner_name' => $contact['ContactOwnerName'] ?? null,
                        'contact_owner_email' => $contact['ContactOwnerEmail'] ?? null,
                        'contact_owner_id' => $contact['ContactOwner'] ?? null,
                        'assigned_team' => $contact['AssignedTeam'] ?? null,
                        
                        'status_chat' => $contact['status_chat'] ?? $contact['custom_fields']['status_chat'] ?? null,
                        'lifecycle_stage' => $contact['lifecycleStage']['name'] ?? $contact['custom_fields']['lifecycle_stage'] ?? null,
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
                        
                        'created_at_sleekflow' => $this->parseToWib($contact['CreatedAt'] ?? null)?->toDateTimeString(),
                        'updated_at_sleekflow' => $this->parseToWib($contact['UpdatedAt'] ?? null)?->toDateTimeString(),
                        'waktu_awal' => $this->parseToWib($contact['CreatedAt'] ?? null)?->toDateString(),
                        'created_at' => now()->toDateTimeString(),
                        'updated_at' => now()->toDateTimeString(),
                    ];
                }
            }

            $offset += $limit;
            $scannedCount += count($data);
        }

        if (!empty($allContacts)) {
            $chunks = array_chunk($allContacts, 100);
            foreach ($chunks as $chunk) {
                SleekflowContact::upsert(
                    $chunk,
                    ['sleekflow_id'],
                    array_diff(array_keys($allContacts[0]), ['sleekflow_id', 'created_at'])
                );
            }
        }

        return ['synced' => count($allContacts)];
    }

    /**
     * Upsert a single contact into the database.
     */
    protected function upsertContact(array $data): void
    {
        $flattened = $this->flattenObject($data);

        SleekflowContact::updateOrCreate(
            ['sleekflow_id' => $data['id']],
            [
                'first_name' => $data['FirstName'] ?? null,
                'last_name' => $data['LastName'] ?? null,
                'phone_number' => $data['PhoneNumber'] ?? null,
                'email' => $data['Email'] ?? null,
                
                'contact_owner_name' => $data['ContactOwnerName'] ?? null,
                'contact_owner_email' => $data['ContactOwnerEmail'] ?? null,
                'contact_owner_id' => $data['ContactOwner'] ?? null,
                'assigned_team' => $data['AssignedTeam'] ?? null,
                
                'status_chat' => $data['status_chat'] ?? $data['custom_fields']['status_chat'] ?? null,
                'lifecycle_stage' => $data['lifecycleStage']['name'] ?? $data['custom_fields']['lifecycle_stage'] ?? null,
                'lead_stage' => $data['LeadStage'] ?? null,
                'priority' => $data['Priority'] ?? null,
                
                'last_contact' => $this->parseToWib($data['LastContact'] ?? null),
                'last_contact_from_customers' => $this->parseToWib($data['LastContactFromCustomers'] ?? null),
                'last_contacted_from_company' => $this->parseToWib($data['LastContactedFromCompany'] ?? null),
                'last_contacted_from_user' => $this->parseToWib($data['LastContactedFromUser'] ?? null),
                'last_channel' => $data['LastChannel'] ?? null,
                
                'lead_source' => $data['LeadSource'] ?? null,
                'facebook_form_id' => $data['Facebook Form ID'] ?? null,
                'labels' => $data['Labels'] ?? null,
                'lists' => $data['Lists'] ?? null,
                
                'company_name' => $data['CompanyName'] ?? null,
                'job_title' => $data['JobTitle'] ?? null,
                'country' => $data['Country'] ?? null,
                'subscriber' => filter_var($data['Subscriber'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'ai_agent_session' => $data['AI Agent Session'] ?? null,
                'collaborators' => $data['Collaborators'] ?? null,
                
                'facebook_psid' => $data['FacebookPSId'] ?? null,
                'wechat_openid' => $data['WeChatOpenId'] ?? null,
                'line_chatid' => $data['LineChatId'] ?? null,
                
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
