<?php

namespace App\Http\Controllers;

use App\Services\CsSalesReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CsSalesExportController extends Controller
{
    /**
     * PDF Dashboard CS (PRD halaman 3): 4 kartu, peringkat CS, dan rincian harian untuk rentang ?dari=&sampai=.
     * Rentang dibatasi sampai kemarin (H-1), sama dengan halamannya.
     */
    public function printPdf(Request $request, CsSalesReportService $report)
    {
        $max = now()->subDay()->startOfDay();

        try {
            $start = Carbon::createFromFormat('Y-m-d', (string) $request->query('dari'))->startOfDay()->min($max);
            $end = Carbon::createFromFormat('Y-m-d', (string) $request->query('sampai'))->startOfDay()->min($max);
        } catch (\Throwable) {
            [$start, $end] = [$max->copy()->startOfMonth(), $max];
        }

        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        [$s, $e] = [$start->toDateString(), $end->toDateString()];

        $pdf = Pdf::loadView('exports.cs-sales-pdf', $report->summary($s, $e) + [
            'leaderboard' => $report->leaderboard($s, $e),
            'daily' => $report->daily($s, $e),
            'start' => $start,
            'end' => $end,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("Dashboard_CS_{$s}_{$e}.pdf");
    }
}
