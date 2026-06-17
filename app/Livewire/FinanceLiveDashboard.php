<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

#[Layout('layouts.app')]
class FinanceLiveDashboard extends Component
{
    public string $search = '';
    public string $statusFilter = 'all';
    public string $paymentTypeFilter = 'all';
    public string $startDate = '';
    public string $endDate = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
        'paymentTypeFilter' => ['except' => 'all'],
        'startDate' => ['except' => ''],
        'endDate' => ['except' => ''],
    ];

    public string $activeTab = 'invoices'; // invoices, payments
    public bool $isLoaded = false;
    public ?string $errorMessage = null;

    // Pagination pages
    public int $invoicePage = 1;
    public int $paymentPage = 1;
    public int $perPage = 50;

    // Cache TTL in seconds (5 minutes)
    protected int $cacheTtl = 300;

    public function mount()
    {
        $this->startDate = request()->query('startDate') ?? '';
        $this->endDate = request()->query('endDate') ?? '';

        // Default to current month
        if (empty($this->startDate)) {
            $this->startDate = now()->startOfMonth()->format('Y-m-d');
        }
        if (empty($this->endDate)) {
            $this->endDate = now()->format('Y-m-d');
        }
    }

    public function updatedStartDate()
    {
        $this->resetPagination();
    }

    public function updatedEndDate()
    {
        $this->resetPagination();
    }

    public function updatedSearch()
    {
        $this->resetPagination();
    }

    public function updatedStatusFilter()
    {
        $this->resetPagination();
    }

    public function updatedPaymentTypeFilter()
    {
        $this->resetPagination();
    }

    public function updatedActiveTab()
    {
        $this->resetPagination();
    }

    protected function resetPagination()
    {
        $this->invoicePage = 1;
        $this->paymentPage = 1;
    }

    public function nextInvoicePage()
    {
        $this->invoicePage++;
    }

    public function prevInvoicePage()
    {
        if ($this->invoicePage > 1) {
            $this->invoicePage--;
        }
    }

    public function nextPaymentPage()
    {
        $this->paymentPage++;
    }

    public function prevPaymentPage()
    {
        if ($this->paymentPage > 1) {
            $this->paymentPage--;
        }
    }

    public function loadData()
    {
        $this->isLoaded = true;
    }

    public function refreshData()
    {
        $cacheKey = "finance_live_dashboard_{$this->startDate}_{$this->endDate}";
        Cache::forget($cacheKey);
        $this->dispatch('swal', [
            'title' => 'Data Diperbarui',
            'text' => 'Mengambil data keuangan terbaru dari API.',
            'icon' => 'success',
            'timer' => 2000,
            'toast' => true,
            'position' => 'top-end'
        ]);
    }

    /**
     * Fetch the dashboard data from API with cache.
     */
    protected function getDashboardData(): array
    {
        try {
            $cacheKey = "finance_live_dashboard_{$this->startDate}_{$this->endDate}";
            
            return Cache::remember($cacheKey, $this->cacheTtl, function () {
                $apiKey = config('services.dashboard.key', 'sws_live_6f8g9h0j1k2l3m4n5o6p7q8r9s0');
                $baseUrl = config('services.dashboard.base_url', 'https://info.shoeworkshop.id/api/v1');
                
                $dashboardUrl = rtrim($baseUrl, '/') . '/finance/dashboard';
                $invoicesUrl = rtrim($baseUrl, '/') . '/finance-sync';
                $paymentUrl = rtrim($baseUrl, '/') . '/payment-sync';

                // 1. Fetch dashboard stats
                $response = Http::timeout(15)
                    ->get($dashboardUrl, [
                        'api_key' => $apiKey,
                        'start_date' => $this->startDate,
                        'end_date' => $this->endDate,
                    ]);

                if ($response->failed()) {
                    $error = $response->json('message') ?? $response->body() ?? 'API Request Failed';
                    Log::error('Finance Live API Failed: ' . $error);
                    throw new \Exception($error);
                }

                $json = $response->json();
                if (($json['status'] ?? '') !== 'success' || !isset($json['data'])) {
                    throw new \Exception($json['message'] ?? 'Invalid response format');
                }

                $dashboardData = $json['data'];
                $period = $dashboardData['period'] ?? ['start' => $this->startDate, 'end' => $this->endDate];

                // 2. Fetch all invoices (finance-sync) for full data list
                $invoicesResponse = Http::timeout(15)
                    ->withHeaders([
                        'X-API-KEY' => $apiKey,
                        'Accept' => 'application/json',
                    ])
                    ->get($invoicesUrl, [
                        'start_date' => $this->startDate,
                        'end_date' => $this->endDate,
                    ]);

                $invoicesList = [];
                if ($invoicesResponse->successful()) {
                    $invoicesList = $invoicesResponse->json('data') ?? [];
                }

                // 3. Fetch payment-sync for active period distribution and list
                $paymentResponse = Http::timeout(15)
                    ->withHeaders([
                        'X-API-KEY' => $apiKey,
                        'Accept' => 'application/json',
                    ])
                    ->get($paymentUrl, [
                        'limit' => 4000,
                    ]);

                $paymentList = [];
                $distribution = [
                    'BEFORE' => ['count' => 0, 'total' => 0, 'label' => 'DP AWAL', 'icon' => 'clock', 'badge_text' => 'BEFORE', 'badge' => 'bg-blue-500/10 text-blue-500 border border-blue-500/20', 'icon_class' => 'bg-blue-500/10 text-blue-500', 'color' => 'bg-blue-500'],
                    'AFTER' => ['count' => 0, 'total' => 0, 'label' => 'PELUNASAN', 'icon' => 'check-circle', 'badge_text' => 'AFTER', 'badge' => 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20', 'icon_class' => 'bg-emerald-500/10 text-emerald-500', 'color' => 'bg-emerald-500'],
                    'TAMBAH_JASA' => ['count' => 0, 'total' => 0, 'label' => 'TAMBAH JASA', 'icon' => 'plus', 'badge_text' => 'TAMBAH JASA', 'badge' => 'bg-indigo-500/10 text-indigo-500 border border-indigo-500/20', 'icon_class' => 'bg-indigo-500/10 text-indigo-500', 'color' => 'bg-indigo-500'],
                    'LUNAS_AWAL' => ['count' => 0, 'total' => 0, 'label' => 'LUNAS AWAL', 'icon' => 'lightning-bolt', 'badge_text' => 'LUNAS AWAL', 'badge' => 'bg-amber-500/10 text-amber-500 border border-amber-500/20', 'icon_class' => 'bg-amber-500/10 text-amber-500', 'color' => 'bg-amber-500'],
                    'ONGKIR' => ['count' => 0, 'total' => 0, 'label' => 'ONGKOS KIRIM', 'icon' => 'gift', 'badge_text' => 'ONGKIR', 'badge' => 'bg-rose-500/10 text-rose-500 border border-rose-500/20', 'icon_class' => 'bg-rose-500/10 text-rose-500', 'color' => 'bg-rose-500'],
                ];

                if ($paymentResponse->successful()) {
                    $rawPayments = $paymentResponse->json('data') ?? [];
                    foreach ($rawPayments as $item) {
                        if (isset($item['paid_at'])) {
                            $paidDate = substr($item['paid_at'], 0, 10);
                            if ($paidDate >= $period['start'] && $paidDate <= $period['end']) {
                                $paymentList[] = $item;
                                $type = strtoupper($item['payment_type'] ?? '');
                                if (isset($distribution[$type])) {
                                    $distribution[$type]['count']++;
                                    $distribution[$type]['total'] += (float)($item['amount_paid'] ?? 0);
                                }
                            }
                        }
                    }
                }

                $dashboardData['all_invoices'] = $invoicesList;
                $dashboardData['all_payments'] = $paymentList;
                $dashboardData['payment_type_distribution'] = $distribution;

                return $dashboardData;
            });
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
            Log::error('Finance Live Component Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Render the Livewire component.
     */
    public function render()
    {
        $metrics = [];
        $statusBreakdown = [];
        $metadata = [];
        $period = [];
        $paymentTypeDistribution = [];
        $allInvoices = [];
        $allPayments = [];

        if ($this->isLoaded) {
            $apiData = $this->getDashboardData();
            if (!empty($apiData)) {
                $metrics = $apiData['metrics'] ?? [];
                $statusBreakdown = $apiData['status_breakdown'] ?? [];
                $metadata = $apiData['metadata'] ?? [];
                $period = $apiData['period'] ?? [];
                $paymentTypeDistribution = $apiData['payment_type_distribution'] ?? [];
                $allInvoices = $apiData['all_invoices'] ?? [];
                $allPayments = $apiData['all_payments'] ?? [];
            }
        }

        // 1. Filter Invoices
        $filteredInvoices = collect($allInvoices);
        if ($this->search) {
            $searchLower = strtolower($this->search);
            $filteredInvoices = $filteredInvoices->filter(function ($invoice) use ($searchLower) {
                return str_contains(strtolower($invoice['spk_number'] ?? ''), $searchLower) ||
                       str_contains(strtolower($invoice['customer_name'] ?? ''), $searchLower);
            });
        }
        if ($this->statusFilter !== 'all') {
            $filteredInvoices = $filteredInvoices->filter(function ($invoice) {
                $status = $invoice['status_pembayaran'] ?? '';
                if ($this->statusFilter === 'L') {
                    return $status === 'L';
                } elseif ($this->statusFilter === 'BL') {
                    return in_array($status, ['BL', 'C', 'DP']);
                } elseif ($this->statusFilter === 'BB') {
                    return !in_array($status, ['L', 'BL', 'C', 'DP']);
                }
                return true;
            });
        }

        $totalInvoicesCount = $filteredInvoices->count();
        // Paginate Invoices
        $paginatedInvoices = $filteredInvoices->slice(($this->invoicePage - 1) * $this->perPage, $this->perPage);
        $totalInvoicePages = (int) ceil($totalInvoicesCount / $this->perPage);

        // 2. Filter Payments
        $filteredPayments = collect($allPayments);
        if ($this->search) {
            $searchLower = strtolower($this->search);
            $filteredPayments = $filteredPayments->filter(function ($payment) use ($searchLower) {
                return str_contains(strtolower($payment['invoice_number'] ?? ''), $searchLower) ||
                       str_contains(strtolower($payment['customer_name'] ?? ''), $searchLower);
            });
        }
        if ($this->paymentTypeFilter !== 'all') {
            $filteredPayments = $filteredPayments->filter(function ($payment) {
                return strtoupper($payment['payment_type'] ?? '') === strtoupper($this->paymentTypeFilter);
            });
        }

        $totalPaymentsCount = $filteredPayments->count();
        // Paginate Payments
        $paginatedPayments = $filteredPayments->slice(($this->paymentPage - 1) * $this->perPage, $this->perPage);
        $totalPaymentPages = (int) ceil($totalPaymentsCount / $this->perPage);

        return view('livewire.finance-live-dashboard', [
            'metrics' => $metrics,
            'statusBreakdown' => $statusBreakdown,
            'invoices' => $paginatedInvoices,
            'payments' => $paginatedPayments,
            'metadata' => $metadata,
            'period' => $period,
            'paymentTypeDistribution' => $paymentTypeDistribution,
            'totalInvoices' => $totalInvoicesCount,
            'totalPayments' => $totalPaymentsCount,
            'totalInvoicePages' => $totalInvoicePages,
            'totalPaymentPages' => $totalPaymentPages,
        ]);
    }
}
