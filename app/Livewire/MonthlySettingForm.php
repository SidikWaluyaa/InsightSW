<?php

namespace App\Livewire;

use App\Models\MonthlySetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MonthlySettingForm extends Component
{
    public string $month;
    public string $target_revenue = '';
    public string $budget = ''; // Match blade property name
    public string $target_roas = ''; // New property
    public string $target_chat_consul = ''; // New property
    public string $total_days = '';
    public string $total_holidays = '';

    public $settings;

    protected $rules = [
        'month' => 'required|date',
        'target_revenue' => 'required|numeric|min:0',
        'budget' => 'required|numeric|min:0',
        'target_roas' => 'required|numeric|min:0',
        'target_chat_consul' => 'required|integer|min:0',
        'total_days' => 'required|integer|min:1|max:31',
        'total_holidays' => 'required|integer|min:0|max:15',
    ];

    public function mount(): void
    {
        $this->month = Carbon::now()->format('Y-m-01');
        $this->loadSettings();
        $this->loadExisting();
    }

    public function loadSettings(): void
    {
        $this->settings = MonthlySetting::orderBy('month', 'desc')->limit(12)->get();
    }

    public function loadExisting(): void
    {
        $monthDate = Carbon::parse($this->month)->startOfMonth();
        $setting = MonthlySetting::where('month', $monthDate->format('Y-m-d'))
            ->first();

        // Also load weekly target for ROAS and Chat
        $weekly = \App\Models\WeeklyTarget::where('month', $monthDate->format('Y-m-d'))
            ->first();

        if ($setting) {
            $this->target_revenue = $setting->target_revenue;
            $this->budget = $setting->total_budget;
            $this->total_days = $setting->total_days;
            $this->total_holidays = $setting->total_holidays;
        } else {
            $this->target_revenue = '';
            $this->budget = '';
            $this->total_days = Carbon::parse($this->month)->daysInMonth;
            $this->total_holidays = '0';
        }

        if ($weekly) {
            $this->target_roas = $weekly->target_roas;
            $this->target_chat_consul = $weekly->target_chat_consul;
        } else {
            $this->target_roas = '';
            $this->target_chat_consul = '';
        }
    }

    public function updatedMonth(): void
    {
        $this->loadExisting();
    }

    public function save(): void
    {
        $this->validate();

        $service = app(\App\Services\MonthlySettingService::class);
        
        $service->saveMonthlySetting([
            'month' => $this->month,
            'target_revenue' => $this->target_revenue,
            'budget' => $this->budget,
            'target_roas' => $this->target_roas,
            'target_chat_consul' => $this->target_chat_consul,
            'total_days' => $this->total_days,
            'total_holidays' => $this->total_holidays,
        ]);

        $this->loadSettings();
        $this->dispatch('swal', [
            'title' => 'Berhasil!',
            'text' => 'Pengaturan bulanan berhasil disimpan!',
            'icon' => 'success',
            'timer' => 3000,
        ]);
    }

    public function render()
    {
        return view('livewire.monthly-setting-form');
    }
}
