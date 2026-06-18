<?php

use App\Livewire\BudgetTransferManager;
use App\Livewire\DailyReportForm;
use App\Livewire\Dashboard;
use App\Livewire\MetaAds\Index as MetaAdsIndex;
use App\Livewire\MonthlySettingForm;
use App\Livewire\WeeklyReportTable;
use App\Livewire\FinanceDashboard;
use App\Livewire\FinanceSyncHistory;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Marketing Group (Admin, Editor, Viewer)
    Route::middleware(['role:Admin,Editor,Viewer'])->group(function () {
        Route::get('dashboard', Dashboard::class)->name('dashboard');
        Route::get('meta-ads', MetaAdsIndex::class)->name('meta-ads');
    });

    // Reports & Finance Group (Admin, Editor, Finance, Viewer)
    Route::middleware(['role:Admin,Editor,Finance,Viewer'])->group(function () {
        Route::get('daily-report', DailyReportForm::class)->name('daily-report');
        Route::get('budget-transfer', BudgetTransferManager::class)->name('budget-transfer');
        Route::get('weekly-report', WeeklyReportTable::class)->name('weekly-report');
        Route::get('finance-live', \App\Livewire\FinanceLiveDashboard::class)->name('finance-live-dashboard');
        Route::get('finance-live/export-pdf', [\App\Http\Controllers\FinanceLiveExportController::class, 'printPdf'])->name('finance-live.export-pdf');
        Route::get('finance-sync', FinanceDashboard::class)->name('finance-sync');
        Route::get('finance-history', FinanceSyncHistory::class)->name('finance-history');
        Route::get('finance/payment-insights', \App\Livewire\PaymentInsights::class)->name('finance-payment-insights');
        Route::get('finance/piutang-before', \App\Livewire\FinancePiutangBefore::class)->name('finance-piutang-before');
        Route::get('finance/piutang-before/export-pdf', [\App\Http\Controllers\PiutangBeforeExportController::class, 'printPdf'])->name('finance-piutang-before.export-pdf');
        Route::get('finance/piutang-after', \App\Livewire\FinancePiutangAfter::class)->name('finance-piutang-after');
        Route::get('finance/piutang-after/export-pdf', [\App\Http\Controllers\PiutangAfterExportController::class, 'printPdf'])->name('finance-piutang-after.export-pdf');
        Route::get('finance/export-pdf', [\App\Http\Controllers\FinanceExportController::class, 'printPdf'])->name('finance.export-pdf');
    });

    // CX Group (Admin, Editor, CX, Viewer)
    Route::middleware(['role:Admin,Editor,CX,Viewer'])->group(function () {
        Route::get('customer-service/cx-upsell', \App\Livewire\CxUpsellReport::class)->name('cx-upsell');
        Route::get('customer-service/quality-control', \App\Livewire\QualityControlIndex::class)->name('quality-control');
        Route::get('cx/konfirmasi-after', \App\Livewire\CxKonfirmasiAfter::class)->name('cx-konfirmasi-after');
        Route::get('cx/konfirmasi-api', \App\Livewire\CxKonfirmasiApi::class)->name('cx-konfirmasi-api');
    });

    // Admin Only
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('monthly-settings', MonthlySettingForm::class)->name('monthly-settings');
        Route::get('users', \App\Livewire\UserManager::class)->name('users');
    });

    // Customer Service Group (Admin, Editor, CS, Leader CS, Viewer)
    Route::middleware(['role:Admin,Editor,CS,Leader CS,Viewer'])->group(function () {
        Route::get('customer-service/dashboard', \App\Livewire\CsDashboard::class)->name('cs-dashboard');
        Route::get('customer-service/chat-masuk', \App\Livewire\SleekflowManager::class)->name('chat-masuk');
        Route::get('customer-service/tracking', \App\Livewire\CsTracking::class)->name('cs-tracking');
        Route::get('customer-service/kpi', \App\Livewire\CsKpi::class)->name('cs-kpi');

        // Followup restricted to Leader CS, Admin, Editor
        Route::middleware(['role:Admin,Editor,Leader CS'])->group(function () {
            Route::get('customer-service/followup', \App\Livewire\CsFollowup::class)->name('cs-followup');
        });
    });

    // Gudang Group (Admin, Editor, Gudang, Viewer)
    Route::middleware(['role:Admin,Editor,Gudang,Viewer'])->group(function () {
        Route::get('gudang/dashboard', \App\Livewire\WarehouseLiveDashboard::class)->name('warehouse-live-dashboard');
        Route::get('gudang/antrian-manifest', \App\Livewire\WarehouseManifestQueue::class)->name('warehouse-manifest-queue');
        Route::get('gudang/sepatu-di-rak', \App\Livewire\WarehouseShoesInRack::class)->name('warehouse-shoes-in-rack');
        Route::get('gudang/sepatu-di-rak/export-pdf', [\App\Http\Controllers\WarehouseShoerackExportController::class, 'printPdf'])->name('warehouse-shoes-in-rack.export-pdf');
        Route::get('gudang', \App\Livewire\WarehouseCommandCenter::class)->name('warehouse-command-center');
        Route::get('gudang/inventory', \App\Livewire\WarehouseDashboard::class)->name('warehouse-dashboard');
        Route::get('gudang/requests', \App\Livewire\WarehouseRequests::class)->name('warehouse-requests');
        Route::get('gudang/transactions', \App\Livewire\WarehouseTransactions::class)->name('warehouse-transactions');
        Route::get('gudang/intelligence', \App\Livewire\WarehouseIntelligence::class)->name('warehouse-intelligence');
    });

    // Workshop Group (Admin, Editor, Viewer)
    Route::middleware(['role:Admin,Editor,Viewer'])->group(function () {
        Route::get('workshop/intelligence-v2', \App\Livewire\WorkshopDashboard::class)->name('workshop-intelligence-v2');
        Route::get('workshop/data-sortir', \App\Livewire\WorkshopDataSortir::class)->name('workshop-data-sortir');
        Route::get('workshop/data-sortir/export-pdf', [\App\Http\Controllers\WorkshopSortirExportController::class, 'printPdf'])->name('workshop-data-sortir.export-pdf');
        Route::get('workshop/data-produksi', \App\Livewire\WorkshopDataProduksi::class)->name('workshop-data-produksi');
        Route::get('workshop/data-produksi/export-pdf', [\App\Http\Controllers\WorkshopProductionExportController::class, 'printPdf'])->name('workshop-data-produksi.export-pdf');
        Route::get('workshop/data-qc', \App\Livewire\WorkshopDataQc::class)->name('workshop-data-qc');
        Route::get('workshop/data-qc/export-pdf', [\App\Http\Controllers\WorkshopQcExportController::class, 'printPdf'])->name('workshop-data-qc.export-pdf');
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
