<?php

namespace App\Livewire\Sleekflow;

use App\Models\SleekflowDailyAnalytics;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;

class Analytics extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public $month = '';

    public function mount()
    {
        if (empty($this->month)) {
            $this->month = now()->format('Y-m');
        }
    }

    public function updatingMonth()
    {
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $query = SleekflowDailyAnalytics::query();

        if (!empty($this->month)) {
            $query->whereRaw("DATE_FORMAT(date_time, '%Y-%m') = ?", [$this->month]);
        }

        $analytics = $query->orderBy('date_time', 'desc')->paginate(15);

        // Menghitung ringkasan metrik bulan ini
        $summary = [
            'total_contacts' => $query->sum('number_of_contacts'),
            'total_enquiries' => $query->sum('number_of_new_enquires'),
            'total_messages_sent' => $query->sum('number_of_messages_sent'),
            'total_messages_received' => $query->sum('number_of_message_received'),
        ];

        return view('livewire.sleekflow.analytics', [
            'analytics' => $analytics,
            'summary' => $summary,
        ]);
    }
}
