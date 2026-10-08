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

        // Simpan query dasar sebelum di-order agar agregasi (AVG/SUM) tidak error di MySQL Strict Mode
        $baseQuery = clone $query;

        $analytics = $query->orderBy('date_time', 'desc')->paginate(15);

        // Mengambil rata-rata waktu respon (dari format time) tanpa orderBy
        $avgTimeRow = (clone $baseQuery)->selectRaw('
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
            'total_contacts' => (clone $baseQuery)->sum('number_of_contacts'),
            'total_enquiries' => (clone $baseQuery)->sum('number_of_new_enquires'),
            'total_messages_sent' => (clone $baseQuery)->sum('number_of_messages_sent'),
            'total_messages_received' => (clone $baseQuery)->sum('number_of_message_received'),
            'avg_response_time' => $avgResponseTime,
            'avg_first_response_time' => $avgFirstResponseTime,
        ];

        // --- Data untuk Chart (Semua data dalam bulan ini, urut Ascending) ---
        $chartRecords = (clone $baseQuery)->orderBy('date_time', 'asc')->get();
        $chartDates = [];
        $chartEnquiries = [];
        $chartResponseTimes = [];

        foreach ($chartRecords as $row) {
            $chartDates[] = \Carbon\Carbon::parse($row->date_time)->format('d M');
            $chartEnquiries[] = (int) $row->number_of_new_enquires;
            
            // Ubah "HH:mm:ss" jadi Total Menit (karena Y-axis grafik butuh angka Decimal/Integer)
            $timeStr = $row->response_time_first_messages;
            $minutes = 0;
            if ($timeStr) {
                $parts = explode(':', $timeStr);
                if (count($parts) === 3) {
                    $minutes = ($parts[0] * 60) + $parts[1] + ($parts[2] / 60);
                }
            }
            $chartResponseTimes[] = round($minutes, 2);
        }

        return view('livewire.sleekflow.analytics', [
            'analytics' => $analytics,
            'summary' => $summary,
            'chartDates' => $chartDates,
            'chartEnquiries' => $chartEnquiries,
            'chartResponseTimes' => $chartResponseTimes,
        ]);
    }
}
