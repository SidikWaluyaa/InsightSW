<?php

namespace App\Livewire\Concerns;

use Carbon\Carbon;

/**
 * Perilaku filter rentang tanggal yang seragam untuk halaman yang memakai <x-ui.range-toolbar>.
 * Komponen pemakai wajib punya properti publik string $startDate dan $endDate.
 */
trait WithDateRange
{
    /**
     * Dipanggil dari date range picker & tombol preset (satu request untuk kedua tanggal).
     */
    public function setDateRange(string $start, string $end): void
    {
        [$this->startDate, $this->endDate] = $this->normalizeDateRange($start, $end);

        // Sinkronkan tampilan picker (penting saat rentang diubah lewat preset atau dikoreksi server)
        $this->dispatch('date-range-updated', start: $this->startDate, end: $this->endDate);

        $this->dateRangeChanged();
    }

    /**
     * Hook: dijalankan setelah rentang berubah (muat ulang data, reset halaman, dll).
     */
    protected function dateRangeChanged(): void
    {
        //
    }

    /**
     * Tanggal paling akhir yang boleh dipilih. Default: hari ini.
     */
    protected function maxSelectableDate(): Carbon
    {
        return now()->startOfDay();
    }

    /**
     * Pastikan format Y-m-d valid, urutan benar, dan tidak melewati tanggal maksimum.
     */
    protected function normalizeDateRange(?string $start, ?string $end): array
    {
        $max = $this->maxSelectableDate();

        try {
            $s = Carbon::createFromFormat('Y-m-d', (string) $start)->startOfDay();
            $e = Carbon::createFromFormat('Y-m-d', (string) $end)->startOfDay();
        } catch (\Throwable $ex) {
            return [$max->copy()->startOfMonth()->toDateString(), $max->toDateString()];
        }

        if ($s->gt($e)) {
            [$s, $e] = [$e, $s];
        }

        return [$s->min($max)->toDateString(), $e->min($max)->toDateString()];
    }

    /**
     * Preset rentang cepat, sama di semua halaman.
     */
    public function datePresets(): array
    {
        $max = $this->maxSelectableDate();
        $end = $max->toDateString();
        $lastMonth = $max->copy()->subMonthNoOverflow();

        $presets = [];

        if ($max->isToday()) {
            $presets[] = ['label' => 'Hari Ini', 'start' => $end, 'end' => $end];
        }

        $presets[] = ['label' => '7 Hari', 'start' => $max->copy()->subDays(6)->toDateString(), 'end' => $end];
        $presets[] = ['label' => '30 Hari', 'start' => $max->copy()->subDays(29)->toDateString(), 'end' => $end];
        $presets[] = ['label' => 'Bulan Ini', 'start' => $max->copy()->startOfMonth()->toDateString(), 'end' => $end];
        $presets[] = ['label' => 'Bulan Lalu', 'start' => $lastMonth->copy()->startOfMonth()->toDateString(), 'end' => $lastMonth->copy()->endOfMonth()->toDateString()];

        return $presets;
    }
}
