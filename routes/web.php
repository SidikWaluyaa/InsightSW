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
        Route::get('finance-sync', FinanceDashboard::class)->name('finance-sync');
        Route::get('finance-history', FinanceSyncHistory::class)->name('finance-history');
        Route::get('finance/payment-insights', \App\Livewire\PaymentInsights::class)->name('finance-payment-insights');
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

        // Followup restricted to Leader CS, Admin, Editor
        Route::middleware(['role:Admin,Editor,Leader CS'])->group(function () {
            Route::get('customer-service/followup', \App\Livewire\CsFollowup::class)->name('cs-followup');
        });
    });

    // Gudang Group (Admin, Editor, Gudang, Viewer)
    Route::middleware(['role:Admin,Editor,Gudang,Viewer'])->group(function () {
        Route::get('gudang', \App\Livewire\WarehouseCommandCenter::class)->name('warehouse-command-center');
        Route::get('gudang/inventory', \App\Livewire\WarehouseDashboard::class)->name('warehouse-dashboard');
        Route::get('gudang/requests', \App\Livewire\WarehouseRequests::class)->name('warehouse-requests');
        Route::get('gudang/transactions', \App\Livewire\WarehouseTransactions::class)->name('warehouse-transactions');
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
