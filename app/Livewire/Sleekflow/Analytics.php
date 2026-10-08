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

        // Mengambil rata-rata waktu respon (dari format time)
        $avgTimeRow = (clone $query)->selectRaw('
            SEC_TO_TIME(AVG(TIME_TO_SEC(response_time_all_messages))) as avg_time,
            SEC_TO_TIME(AVG(TIME_TO_SEC(response_time_first_messages))) as avg_first_time
        ')->first();
        
        $avgResponseTime = $avgTimeRow && $avgTimeRow->avg_time ? $avgTimeRow->avg_time : '00:00:00';
        $avgFirstResponseTime = $avgTimeRow && $avgTimeRow->avg_first_time ? $avgTimeRow->avg_first_time : '00:00:00';
        
        // Format string agar lebih rapi (misal dari "01:28:21" menjadi format jam/menit/detik jika perlu, atau biarkan asli)
        // Kita buang milidetik jika ada
        $avgResponseTime = explode('.', $avgResponseTime)[0];
        $avgFirstResponseTime = explode('.', $avgFirstResponseTime)[0];

        // Menghitung ringkasan metrik bulan ini
        $summary = [
            'total_contacts' => $query->sum('number_of_contacts'),
            'total_enquiries' => $query->sum('number_of_new_enquires'),
            'total_messages_sent' => $query->sum('number_of_messages_sent'),
            'total_messages_received' => $query->sum('number_of_message_received'),
            'avg_response_time' => $avgResponseTime,
            'avg_first_response_time' => $avgFirstResponseTime,
        ];

        return view('livewire.sleekflow.analytics', [
            'analytics' => $analytics,
            'summary' => $summary,
        ]);
    }
}
