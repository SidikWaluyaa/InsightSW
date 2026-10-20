<div>
    <div class="space-y-6" wire:init="loadData">
        <x-ui.page-header title="Antrian Manifest" subtitle="Riwayat pengiriman manifest antara gudang dan workshop." badge="Live API">
            <x-ui.range-toolbar :start="$startDate" :end="$endDate" :presets="$this->datePresets()" refresh="refreshData" />
        </x-ui.page-header>

        @if (!$isLoaded)
            {{-- Skeleton Loaders --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="h-32 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl animate-pulse"></div>
                <div class="h-32 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl animate-pulse"></div>
                <div class="h-32 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl animate-pulse"></div>
            </div>
            <div class="h-64 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl animate-pulse"></div>

        @elseif ($errorMessage)
            {{-- Alert error --}}
            <div class="p-6 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900 rounded-3xl flex items-start gap-4">
                <div class="p-3 bg-rose-500/10 text-rose-500 rounded-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-lg text-rose-800 dark:text-rose-400">Gagal Memuat Data</h3>
                    <p class="text-sm text-rose-600 dark:text-rose-500 mt-1">Sistem gagal berkomunikasi dengan API Manifest: {{ $errorMessage }}</p>
                    <button wire:click="refreshData" class="mt-3 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl transition-all">
                        Coba Lagi
                    </button>
                </div>
            </div>
        @else
            <x-ui.range-info :start="$startDate" :end="$endDate"
                :updated="!empty($metadata['last_updated']) ? \Carbon\Carbon::parse($metadata['last_updated'])->translatedFormat('j M Y H:i') : null" />

            {{-- FIRST ROW: 3 KPI Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1: TOTAL MANIFEST TERKIRIM -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm relative overflow-hidden group hover:scale-[1.01] transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest leading-none mb-3">TOTAL MANIFEST TERKIRIM</p>
                            <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">
                                {{ number_format($summary['total_manifests_sent'] ?? 0, 0, ',', '.') }} <span class="text-xs text-slate-400 font-bold uppercase ml-1">Manifest</span>
                            </h3>
                            <p class="text-[9px] text-slate-400 font-black uppercase tracking-wider mt-2">
                                DITERIMA: {{ number_format($summary['total_manifests_received'] ?? 0, 0, ',', '.') }}
                            </p>
                        </div>
                        <span class="p-3 bg-slate-50 dark:bg-gray-800 rounded-2xl text-slate-500 group-hover:scale-110 transition-transform inline-flex"><x-ui.icon name="cube" /></span>
                    </div>
                </div>

                <!-- Card 2: TOTAL SPK / SEPATU TERKIRIM -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm relative overflow-hidden group hover:scale-[1.01] transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest leading-none mb-3">TOTAL SPK DI MANIFEST</p>
                            <h3 class="text-3xl font-black text-emerald-500 tracking-tight">
                                {{ number_format($summary['total_shoes_sent'] ?? 0, 0, ',', '.') }} <span class="text-xs text-emerald-500 font-bold uppercase ml-1">SPK</span>
                            </h3>
                            @php
                                $manifestCount = $summary['total_manifests_sent'] ?? 0;
                                $avgShoes = $manifestCount > 0 ? round(($summary['total_shoes_sent'] ?? 0) / $manifestCount, 1) : 0;
                            @endphp
                            <p class="text-[9px] text-slate-400 font-black uppercase tracking-wider mt-2">
                                RERATA: {{ number_format($avgShoes, 1, ',', '.') }} SPK / MANIFEST
                            </p>
                        </div>
                        <span class="p-3 bg-emerald-500/10 text-emerald-500 rounded-2xl group-hover:scale-110 transition-transform inline-flex"><x-ui.icon name="tag" /></span>
                    </div>
                </div>

                <!-- Card 3: TOTAL JASA LOGISTIK -->
                <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm relative overflow-hidden group hover:scale-[1.01] transition-all">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest leading-none mb-3">TOTAL JASA LOGISTIK</p>
                            <h3 class="text-3xl font-black text-amber-500 tracking-tight">
                                {{ number_format($summary['total_services_count'] ?? 0, 0, ',', '.') }} <span class="text-xs text-amber-500 font-bold uppercase ml-1">Jasa</span>
                            </h3>
                            <p class="text-[9px] text-slate-400 font-black uppercase tracking-wider mt-2">
                                RERATA JASA: {{ number_format($summary['average_services_per_shoe'] ?? 0, 1, ',', '.') }} JASA / SPK
                            </p>
                        </div>
                        <span class="p-3 bg-amber-500/10 text-amber-500 rounded-2xl group-hover:scale-110 transition-transform inline-flex"><x-ui.icon name="clipboard-list" /></span>
                    </div>
                </div>
            </div>



            <div class="p-4 bg-slate-50 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800 rounded-2xl text-xs text-slate-600 dark:text-gray-400 leading-relaxed">
                <span class="font-bold text-slate-800 dark:text-gray-200">Catatan:</span>
                Daftar ini memuat dua jenis manifest: <span class="font-mono font-bold">MFST-</span> (gudang ke workshop) dan <span class="font-mono font-bold">MNF-OUT-</span> (workshop ke gudang).
                Satu SPK hanya tertaut ke satu manifest pada satu waktu, jadi manifest lama bisa menampilkan <span class="font-bold">0 SPK</span> setelah SPK-nya masuk ke manifest berikutnya.
                Angka total di atas menghitung SPK yang saat ini tertaut ke manifest pada periode ini.
            </div>

            {{-- THIRD ROW: Manifest Log History Table --}}
            <div class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4 mb-6">
                    <div>
                        <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wider">
                            RIWAYAT MANIFEST LOGISTIK (PERIODE INI)
                        </h4>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">DAFTAR MANIFEST DAN VALUASI JASA DI DALAMNYA</p>
                    </div>

                    <span class="text-[9px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-wider">
                        TOTAL: {{ count($recentManifests) }} MANIFEST
                    </span>
                </div>

                {{-- Table container --}}
                <div class="overflow-x-auto border border-gray-100 dark:border-gray-800 rounded-2xl">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-100 dark:border-gray-800 uppercase text-[9px] font-black text-slate-400 dark:text-gray-500 tracking-wider">
                                <th class="px-5 py-4">NO. MANIFEST</th>
                                <th class="px-5 py-4">ARAH</th>
                                <th class="px-5 py-4">PENGIRIM / TANGGAL</th>
                                <th class="px-5 py-4">PENERIMA</th>
                                <th class="px-5 py-4 text-center">JUMLAH SPK</th>
                                <th class="px-5 py-4 text-center">JUMLAH JASA</th>
                                <th class="px-5 py-4 text-center">STATUS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 uppercase font-bold text-slate-700 dark:text-gray-300 text-[10px]">
                            @forelse($recentManifests as $manifest)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-800/20 transition-colors">
                                    <td class="px-5 py-4 font-mono font-black text-emerald-600 dark:text-emerald-400">
                                        {{ $manifest['manifest_number'] ?? '-' }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-slate-600 dark:text-gray-400">
                                        {{-- Prefix nomor menentukan arah: MFST- gudang ke workshop, MNF-OUT- workshop ke gudang --}}
                                        {{ str_starts_with($manifest['manifest_number'] ?? '', 'MNF-OUT') ? 'Workshop → Gudang' : 'Gudang → Workshop' }}
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-col">
                                            <span class="text-slate-800 dark:text-white font-black">{{ ($manifest['dispatcher_name'] ?? 'N/A') === 'N/A' ? 'Tidak tercatat' : $manifest['dispatcher_name'] }}</span>
                                            <span class="text-[9px] text-slate-400 font-normal font-mono mt-0.5">
                                                {{ !empty($manifest['dispatched_at']) ? \Carbon\Carbon::parse($manifest['dispatched_at'])->translatedFormat('d M Y H:i') : '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600 dark:text-gray-400">
                                        @php $receiver = $manifest['receiver_name'] ?? ''; @endphp
                                        @if($receiver !== '' && $receiver !== 'N/A')
                                            {{ $receiver }}
                                        @else
                                            <span class="text-slate-400">{{ strtoupper($manifest['status'] ?? '') === 'RECEIVED' ? 'Tidak tercatat' : 'Belum diterima' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center text-slate-800 dark:text-white font-black">
                                        {{ number_format($manifest['work_orders_count'] ?? 0, 0, ',', '.') }} SPK
                                    </td>
                                    <td class="px-5 py-4 text-center text-slate-800 dark:text-white font-black">
                                        {{ number_format($manifest['total_services_count'] ?? 0, 0, ',', '.') }} Jasa
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @php
                                            $status = strtoupper($manifest['status'] ?? 'SENT');
                                        @endphp
                                        @if($status === 'RECEIVED')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[8px] font-black tracking-widest bg-emerald-50 text-emerald-700 border border-emerald-200/50 dark:bg-emerald-950/20 dark:text-emerald-400 dark:border-emerald-900/30">
                                                DITERIMA
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[8px] font-black tracking-widest bg-blue-50 text-blue-700 border border-blue-200/50 dark:bg-blue-950/20 dark:text-blue-400 dark:border-blue-900/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                                TRANSIT
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-8 py-20 text-center">
                                        <div class="flex flex-col items-center gap-3 opacity-30">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <p class="text-xs">Tidak ada data manifest ditemukan untuk periode ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
