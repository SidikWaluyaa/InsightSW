<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class FinanceLiveExportController extends Controller
{
    public function printPdf(Request $request)
    {
        $type = $request->input('type', 'invoices'); // invoices or payments
        $startDate = $request->input('startDate', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', now()->format('Y-m-d'));
        $search = $request->input('search', '');
        $statusFilter = $request->input('statusFilter', 'all');
        $paymentTypeFilter = $request->input('paymentTypeFilter', 'all');

        $apiKey = config('services.dashboard.key', 'sws_live_6f8g9h0j1k2l3m4n5o6p7q8r9s0');
        $baseUrl = config('services.dashboard.base_url', 'https://info.shoeworkshop.id/api/v1');

        $data = [];

        if ($type === 'invoices') {
            // Fetch live invoices
            $invoicesUrl = rtrim($baseUrl, '/') . '/finance-sync';
            $response = Http::timeout(25)
                ->withHeaders([
                    'X-API-KEY' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($invoicesUrl, [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);

            if ($response->successful()) {
                $rawInvoices = $response->json('data') ?? [];
                
                // Filter Invoices in memory
                $filtered = collect($rawInvoices);
                if ($search) {
                    $searchLower = strtolower($search);
                    $filtered = $filtered->filter(function ($invoice) use ($searchLower) {
                        return str_contains(strtolower($invoice['spk_number'] ?? ''), $searchLower) ||
                               str_contains(strtolower($invoice['customer_name'] ?? ''), $searchLower);
                    });
                }
                if ($statusFilter !== 'all') {
                    $filtered = $filtered->filter(function ($invoice) use ($statusFilter) {
                        $status = $invoice['status_pembayaran'] ?? '';
                        if ($statusFilter === 'L') {
                            return $status === 'L';
                        } elseif ($statusFilter === 'BL') {
                            return in_array($status, ['BL', 'C', 'DP']);
                        } elseif ($statusFilter === 'BB') {
                            return !in_array($status, ['L', 'BL', 'C', 'DP']);
                        }
                        return true;
                    });
                }
                $data = $filtered->values()->all();
            }
        } else {
            // Fetch live payments
            $paymentUrl = rtrim($baseUrl, '/') . '/payment-sync';
            $response = Http::timeout(25)
                ->withHeaders([
                    'X-API-KEY' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($paymentUrl, [
                    'limit' => 4000,
                ]);

            if ($response->successful()) {
                $rawPayments = $response->json('data') ?? [];
                
                // Filter by date range first
                $filtered = collect($rawPayments)->filter(function ($item) use ($startDate, $endDate) {
                    if (isset($item['paid_at'])) {
                        $paidDate = substr($item['paid_at'], 0, 10);
                        return $paidDate >= $startDate && $paidDate <= $endDate;
                    }
                    return false;
                });

                // Apply search
                if ($search) {
                    $searchLower = strtolower($search);
                    $filtered = $filtered->filter(function ($payment) use ($searchLower) {
                        return str_contains(strtolower($payment['invoice_number'] ?? ''), $searchLower) ||
                               str_contains(strtolower($payment['customer_name'] ?? ''), $searchLower);
                    });
                }

                // Apply payment type filter
                if ($paymentTypeFilter !== 'all') {
                    $filtered = $filtered->filter(function ($payment) use ($paymentTypeFilter) {
                        return strtoupper($payment['payment_type'] ?? '') === strtoupper($paymentTypeFilter);
                    });
                }

                // Sort by date descending
                $data = $filtered->sortByDesc('paid_at')->values()->all();
            }
        }

        $pdf = Pdf::loadView('exports.finance-live-pdf', [
            'type' => $type,
            'data' => $data,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
            'statusFilter' => $statusFilter,
            'paymentTypeFilter' => $paymentTypeFilter,
        ])->setPaper('a4', 'landscape');

        $filename = 'Finance_Live_' . ucfirst($type) . '_' . now()->format('YmdHis') . '.pdf';
        return $pdf->stream($filename);
    }
}
