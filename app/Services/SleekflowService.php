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

        $baseQuery = SleekflowContact::query()
            ->whereBetween('created_at_sleekflow', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

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
                SUM(CASE WHEN status_chat = 'Konsultasi' THEN 1 ELSE 0 END) as total_konsul,
                SUM(CASE WHEN (status_chat IS NULL OR status_chat = '') THEN 1 ELSE 0 END) as total_unhandled
            ")
            ->groupBy('contact_owner_name')
            ->orderByDesc('total_closing')
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
                    'conversion_rate' => $stat->total_greeting > 0 ? round(($stat->total_konsul / $stat->total_greeting) * 100, 1) : 0,
                    'display_name' => $stat->contact_owner_name ?: 'Belum Ditentukan'
                ];
            })
            ->sortByDesc('consultation_rate')
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
        $allContacts = [];
        
        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay() : Carbon::today()->startOfDay();
        $endDate = $endDate ? Carbon::parse($endDate)->endOfDay() : Carbon::today()->endOfDay();
        
        $stop = false;

        while (!$stop) {
            $response = Http::withHeaders([
                'X-Sleekflow-Api-Key' => $this->apiKey
            ])->get("{$this->baseUrl}/contact", [
                'limit' => $limit,
                'offset' => $offset,
                // 'sort' => 'updatedAt desc', // AppScript doesn't use sort, we match its default
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

                $createdAtWib = $this->parseToWib($contact['CreatedAt']);
                $createdDateWibString = $createdAtWib->toDateString();
                $updatedAtWib = $this->parseToWib($contact['UpdatedAt'] ?? $contact['CreatedAt']);
                
                // 🔥 APP SCRIPT STOP LOGIC: if (createdWIB < startDate) stop = true;
                if ($createdDateWibString < $startDate->toDateString()) {
                    $stop = true;
                    break;
                }

                // 🔥 APP SCRIPT FILTER: if (createdWIB >= startDate && createdWIB <= endDate)
                if ($createdDateWibString >= $startDate->toDateString() && $createdDateWibString <= $endDate->toDateString()) {
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
                        
                        // 🔥 APP SCRIPT MAPPING for lifecycle_stage
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

            $offset += $limit;
        }

        if (!empty($allContacts)) {
            foreach ($allContacts as $data) {
                // Determine which timestamp to set based on current status
                $status = $data['status_chat'];
                $now = now()->toDateTimeString();
                
                $updateData = $data;
                unset($updateData['sleekflow_id']); // Don't update the ID

                // Check and set timestamps only if they are NULL in the DB
                $contact = SleekflowContact::where('sleekflow_id', $data['sleekflow_id'])->first();
                
                if ($contact) {
                    if ($status === 'Greeting' && !$contact->greeting_at) $updateData['greeting_at'] = $now;
                    if ($status === 'Konsultasi' && !$contact->konsul_at) $updateData['konsul_at'] = $now;
                    if ($status === 'Follow Up Konsultasi' && !$contact->followed_up_at) $updateData['followed_up_at'] = $now;
                    if ($status === 'Closing' && !$contact->closing_at) $updateData['closing_at'] = $now;
                    if ($status === 'Before Penerimaan' && !$contact->penerimaan_at) $updateData['penerimaan_at'] = $now;
                    
                    // Don't overwrite existing timestamps if we already have them in the DB
                    if ($contact->greeting_at) unset($updateData['greeting_at']);
                    if ($contact->konsul_at) unset($updateData['konsul_at']);
                    if ($contact->followed_up_at) unset($updateData['followed_up_at']);
                    if ($contact->closing_at) unset($updateData['closing_at']);
                    if ($contact->penerimaan_at) unset($updateData['penerimaan_at']);

                    $contact->update($updateData);
                } else {
                    // New contact - set the first timestamp if status matches
                    if ($status === 'Greeting') $updateData['greeting_at'] = $now;
                    if ($status === 'Konsultasi') $updateData['konsul_at'] = $now;
                    if ($status === 'Follow Up Konsultasi') $updateData['followed_up_at'] = $now;
                    if ($status === 'Closing') $updateData['closing_at'] = $now;
                    if ($status === 'Before Penerimaan') $updateData['penerimaan_at'] = $now;
                    
                    SleekflowContact::create(array_merge($updateData, ['sleekflow_id' => $data['sleekflow_id']]));
                }
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
