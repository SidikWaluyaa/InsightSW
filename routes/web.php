<?php

use App\Livewire\BudgetTransferManager;
use App\Livewire\DailyReportForm;
use App\Livewire\Dashboard;
use App\Livewire\MetaAds\Index as MetaAdsIndex;
use App\Livewire\MonthlySettingForm;
use App\Livewire\WeeklyReportTable;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('meta-ads', MetaAdsIndex::class)->name('meta-ads');

    // Admin & Editor
    Route::middleware(['role:Admin,Editor'])->group(function () {
        Route::get('daily-report', DailyReportForm::class)->name('daily-report');
        Route::get('budget-transfer', BudgetTransferManager::class)->name('budget-transfer');
        Route::get('weekly-report', WeeklyReportTable::class)->name('weekly-report');
    });

    // Admin Only
    Route::middleware(['role:Admin'])->group(function () {
        Route::get('monthly-settings', MonthlySettingForm::class)->name('monthly-settings');
        Route::get('users', \App\Livewire\UserManager::class)->name('users');
    });

    // Customer Service
    Route::get('customer-service/dashboard', \App\Livewire\CsDashboard::class)->name('cs-dashboard');
    Route::get('customer-service/chat-masuk', \App\Livewire\SleekflowManager::class)->name('chat-masuk');
    Route::get('customer-service/tracking', \App\Livewire\CsTracking::class)->name('cs-tracking');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
