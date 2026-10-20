@props(['hierarchy'])

{{-- Tabel hierarki berjenjang (PRD halaman 4): tanggal hanya di baris awal tanggal, materi iklan hanya di baris awal
     materi; nama kampanye dan angka selalu terisi. Data: MetaAdsReportService::hierarchy(). --}}
@php $th = 'px-3 py-3 whitespace-nowrap'; @endphp
<div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                    <th class="{{ $th }}">Tanggal</th>
                    <th class="{{ $th }}">Materi Iklan</th>
                    <th class="{{ $th }}">Nama Kampanye</th>
                    <th class="{{ $th }} text-right">Ad Spend (Rp)</th>
                    <th class="{{ $th }} text-right">Impr</th>
                    <th class="{{ $th }} text-right">Link Clicks</th>
                    <th class="{{ $th }} text-right">Chat</th>
                    <th class="{{ $th }} text-right">CPR (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($hierarchy->items() as $date => $ads)
                    @foreach($ads as $adName => $campaignRows)
                        @foreach($campaignRows as $row)
                            @php $firstOfDay = $loop->parent->first && $loop->first; @endphp
                            <tr wire:key="h-{{ $date }}-{{ md5($adName . $row->campaign_name) }}"
                                class="{{ $firstOfDay ? 'border-t-2 border-gray-200 dark:border-gray-700' : ($loop->first ? 'border-t border-gray-100 dark:border-gray-800' : '') }} hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                <td class="px-3 py-2.5 align-top whitespace-nowrap font-bold text-slate-700 dark:text-gray-200">{{ $firstOfDay ? \Carbon\Carbon::parse($date)->translatedFormat('d M Y') : '' }}</td>
                                <td class="px-3 py-2.5 align-top whitespace-nowrap font-semibold text-slate-800 dark:text-white">{{ $loop->first ? $adName : '' }}</td>
                                <td class="px-3 py-2.5 text-slate-500 dark:text-gray-400">{{ $row->campaign_name }}</td>
                                <td class="px-3 py-2.5 text-right whitespace-nowrap font-bold text-slate-800 dark:text-gray-100">Rp {{ number_format($row->spend, 0, ',', '.') }}</td>
                                <td class="px-3 py-2.5 text-right text-slate-600 dark:text-gray-300">{{ number_format($row->impressions, 0, ',', '.') }}</td>
                                <td class="px-3 py-2.5 text-right text-slate-600 dark:text-gray-300">{{ number_format($row->link_click, 0, ',', '.') }}</td>
                                <td class="px-3 py-2.5 text-right font-bold text-blue-600 dark:text-blue-400">{{ number_format($row->results, 0, ',', '.') }}</td>
                                <td class="px-3 py-2.5 text-right whitespace-nowrap text-slate-600 dark:text-gray-300">{{ $row->results > 0 ? 'Rp ' . number_format($row->spend / $row->results, 0, ',', '.') : '–' }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                @empty
                    <tr><td colspan="8" class="px-6 py-16 text-center text-sm italic text-slate-400">Belum ada data Meta Ads untuk rentang ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($hierarchy->hasPages())
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/30 border-t border-gray-100 dark:border-gray-800">
            {{ $hierarchy->links(data: ['scrollTo' => false]) }}
        </div>
    @endif
</div>
