<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FinanceSync;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class FinanceExportController extends Controller
{
    public function printPdf(Request $request)
    {
        $startDate = $request->input('startDate', now()->format('Y-m-d'));
        $endDate = $request->input('endDate', now()->format('Y-m-d'));
        $search = $request->input('search', '');
        $statusFilter = $request->input('statusFilter', '');

        $start = $startDate . ' 00:00:00';
        $end = $endDate . ' 23:59:59';

        $query = FinanceSync::query()
            ->whereBetween('source_created_at', [$start, $end]);

        if ($statusFilter) {
            if ($statusFilter === 'BB' || $statusFilter === 'B') {
                $query->where('status_pembayaran', 'BB');
            } elseif ($statusFilter === 'BL' || $statusFilter === 'C') {
                $query->where('status_pembayaran', 'BL');
            } elseif ($statusFilter === 'PL') {
                $query->whereIn('status_pembayaran', ['BB', 'BL']);
            } elseif ($statusFilter === 'L') {
                $query->where('status_pembayaran', 'L');
            }
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('spk_number', 'like', '%' . $search . '%')
                  ->orWhere('customer_name', 'like', '%' . $search . '%')
                  ->orWhere('customer_phone', 'like', '%' . $search . '%');
            });
        }

        $transactions = $query->orderBy('source_created_at', 'DESC')->get();

        $pdf = Pdf::loadView('exports.finance-pdf', [
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'search' => $search,
            'statusFilter' => $statusFilter,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Finance_Report_' . now()->format('YmdHis') . '.pdf');
    }
}
