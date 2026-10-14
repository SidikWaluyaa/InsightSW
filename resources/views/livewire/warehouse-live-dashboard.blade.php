<div>
    <div class="space-y-6" wire:init="loadData">
        <x-ui.page-header title="Dashboard Gudang" subtitle="Ringkasan alur sepatu masuk, proses, dan keluar gudang." badge="Live API">
            <x-ui.range-toolbar :start="$startDate" :end="$endDate" :presets="$this->datePresets()" refresh="refreshData" />
        </x-ui.page-header>

        {{-- Di luar blok @if agar tidak ikut di-morph saat data selesai dimuat (state Alpine-nya harus tetap) --}}
        <div wire:ignore><x-ui.formula-modal /></div>

        @if (!$isLoaded)
            {{-- Skeleton Loaders --}}
            <div class="h-32 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl animate-pulse"></div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @for ($i = 0; $i < 9; $i++)
                    <div class="h-28 bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl animate-pulse"></div>
                @endfor
            </div>

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
                    <p class="text-sm text-rose-600 dark:text-rose-500 mt-1">Sistem gagal berkomunikasi dengan API Gudang: {{ $errorMessage }}</p>
                    <button wire:click="refreshData" class="mt-3 px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl transition-all">
                        Coba Lagi
                    </button>
                </div>
            </div>
        @else
            <x-ui.range-info :start="$startDate" :end="$endDate"
                :updated="!empty($metadata['last_updated']) ? \Carbon\Carbon::parse($metadata['last_updated'])->translatedFormat('j M Y H:i') : null" />

            @php
                // Definisi tiap angka mengikuti kode API pusat (WarehouseDashboardApiService::getHeroMetrics).
                // Satuan: SPK (1 SPK = 1 unit), kecuali isi rak yang berasal dari penghitung rak.
                $n = fn ($key) => number_format((float) ($summary[$key] ?? 0), 0, ',', '.');

                // Kelancaran: API mengembalikan 100 bila pembaginya 0, yang menyesatkan. Tampilkan "–" saja.
                $rate = function (string $rateKey, string $baseKey) use ($summary) {
                    if ((float) ($summary[$baseKey] ?? 0) == 0.0) {
                        return ['value' => '–', 'state' => null];
                    }
                    $v = (float) ($summary[$rateKey] ?? 0);

                    return [
                        'value' => ($v > 0 ? '+' : '') . str_replace('.', ',', (string) $v) . '%',
                        'state' => $v < 0 ? 'Menumpuk' : 'Lancar',
                    ];
                };
                // Keterisian rak: slot terpakai dibanding kapasitas (rak penerimaan + rak selesai, tanpa rak aksesoris)
                $rackCapacity = (float) ($storage['status']['total_capacity'] ?? 0);
                $rackUsed = (float) ($summary['stored_items'] ?? 0);
                $rackPercent = $rackCapacity > 0 ? round($rackUsed / $rackCapacity * 100) : null;

                $rateIn = $rate('clearance_rate_before', 'incoming_day');
                $rateOut = $rate('clearance_rate_after', 'after_masuk');

                // ---- Info rumus per kartu (tombol "i", hanya Admin) + cek silang dengan data yang sedang tampil ----
                $raw = fn ($key) => (float) ($summary[$key] ?? 0);
                $heat = collect($storage['heatmap'] ?? []);
                $heatBefore = (float) $heat->where('category', 'before')->sum('count');
                $heatShoes = (float) $heat->where('category', 'shoes')->sum('count');
                $heatManual = (float) $heat->filter(fn ($r) => str_starts_with((string) ($r['category'] ?? ''), 'manual'))->sum('count');
                $calcRate = fn ($a, $b) => $raw($b) == 0.0 ? null : round(($raw($a) - $raw($b)) / $raw($b) * 100, 1);
                $same = fn ($x, $y) => abs((float) $x - (float) $y) < 0.05;

                $checks = [
                    'inbound_inventory' => [
                        ['ok' => $same($heatBefore, $raw('inbound_inventory')), 'teks' => "heatmap before = {$heatBefore} ; inbound_inventory = {$raw('inbound_inventory')}"],
                    ],
                    'finish_inventory' => [
                        ['ok' => $same($heatShoes + $heatManual, $raw('finish_inventory')), 'teks' => "heatmap shoes {$heatShoes} + manual {$heatManual} = " . ($heatShoes + $heatManual) . " ; finish_inventory = {$raw('finish_inventory')}"],
                    ],
                    'stored_items' => [
                        ['ok' => $same($raw('inbound_inventory') + $raw('finish_inventory'), $raw('stored_items')), 'teks' => "inbound {$raw('inbound_inventory')} + finish {$raw('finish_inventory')} = " . ($raw('inbound_inventory') + $raw('finish_inventory')) . " ; stored_items = {$raw('stored_items')}"],
                        ['ok' => $same($storage['status']['current_usage'] ?? -1, $raw('stored_items')), 'teks' => 'storage.status.current_usage = ' . ($storage['status']['current_usage'] ?? '?') . " ; total_inventory = {$raw('total_inventory')}"],
                    ],
                    'qc_reject' => [
                        ['ok' => $same($qcAnalytics['trends']['total_reject'] ?? -1, $raw('qc_reject')), 'teks' => 'qc_analytics.trends.total_reject = ' . ($qcAnalytics['trends']['total_reject'] ?? '?') . " ; qc_reject = {$raw('qc_reject')}"],
                    ],
                    'shoes_in_rack_count' => [
                        ['ok' => $same($heatShoes, $raw('shoes_in_rack_count')), 'teks' => "penghitung rak sepatu (heatmap shoes) = {$heatShoes} ; shoes_in_rack_count = {$raw('shoes_in_rack_count')}"],
                    ],
                    'finished_day' => [
                        ['ok' => $same($raw('total_finished'), $raw('finished_day')), 'teks' => "total_finished = {$raw('total_finished')} ; finished_day = {$raw('finished_day')}"],
                    ],
                    'total_qc_processed' => [
                        ['ok' => $same(($qcAnalytics['trends']['total_lolos'] ?? 0) + ($qcAnalytics['trends']['total_reject'] ?? 0), $raw('total_qc_processed')), 'teks' => 'total_lolos ' . ($qcAnalytics['trends']['total_lolos'] ?? '?') . ' + total_reject ' . ($qcAnalytics['trends']['total_reject'] ?? '?') . " ; total_qc_processed = {$raw('total_qc_processed')}"],
                    ],
                    'clearance_rate_before' => is_null($calcRate('spk_print', 'incoming_day')) ? [] : [
                        ['ok' => $same($calcRate('spk_print', 'incoming_day'), $raw('clearance_rate_before')), 'teks' => "({$raw('spk_print')} − {$raw('incoming_day')}) ÷ {$raw('incoming_day')} × 100 = {$calcRate('spk_print', 'incoming_day')} ; API = {$raw('clearance_rate_before')}"],
                    ],
                    'clearance_rate_after' => is_null($calcRate('sepatu_keluar', 'after_masuk')) ? [] : [
                        ['ok' => $same($calcRate('sepatu_keluar', 'after_masuk'), $raw('clearance_rate_after')), 'teks' => "({$raw('sepatu_keluar')} − {$raw('after_masuk')}) ÷ {$raw('after_masuk')} × 100 = {$calcRate('sepatu_keluar', 'after_masuk')} ; API = {$raw('clearance_rate_after')}"],
                    ],
                ];

                $showFormula = auth()->user()?->isAdmin() ?? false;
                $info = fn (string $key, string $judul) => ! $showFormula ? null : array_merge(config("warehouse_formulas.{$key}", []), [
                    'judul' => $judul,
                    'endpoint' => 'GET /warehouse-summary?start_date=' . $startDate . '&end_date=' . $endDate,
                    'field' => 'data.summary.' . $key,
                    'nilai' => (string) ($summary[$key] ?? 'null'),
                    'cek' => $checks[$key] ?? [],
                ]);
            @endphp

            {{-- ================= KONDISI SAAT INI (tidak mengikuti filter tanggal) ================= --}}
            <x-ui.section-note title="Kondisi Gudang Saat Ini" tag="Tidak mengikuti filter tanggal">
                Posisi terkini saat data diambil, bukan jumlah selama periode yang dipilih. Mengganti rentang tanggal tidak mengubah angka di kelompok ini.
            </x-ui.section-note>

            {{-- Hitungan per SPK --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-ui.stat-card label="SPK Pending" :info="$info('pending_reception', 'SPK Pending')" :value="$n('pending_reception')" unit="SPK" icon="inbox-in"
                    note="SPK yang sudah dibuat CS tetapi belum dikonfirmasi diterima gudang." />
                <x-ui.stat-card label="Selesai, Belum di Rak" :info="$info('finished_not_stored', 'Selesai, Belum di Rak')" :value="$n('finished_not_stored')" unit="SPK" icon="sparkles" tone="amber"
                    note="SPK berstatus selesai, belum diambil/dikirim, dan tidak tercatat di rak mana pun. Termasuk SPK lama yang tidak pernah ditandai diambil." />
                <x-ui.stat-card label="Siap Diambil" :info="$info('ready_for_pickup', 'Siap Diambil')" :value="$n('ready_for_pickup')" unit="SPK" icon="check-circle" tone="emerald"
                    note="SPK berstatus selesai, belum diambil/dikirim, dan masih tercatat di rak." />
            </div>

            {{-- Hitungan isi rak (penghitung rak) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-ui.stat-card label="Isi Rak Penerimaan" :info="$info('inbound_inventory', 'Isi Rak Penerimaan')" :value="$n('inbound_inventory')" unit="Isi Rak" icon="inbox-in" tone="amber"
                    note="Sepatu sebelum reparasi: jumlah isi rak penerimaan, menurut penghitung rak." />
                <x-ui.stat-card label="Isi Rak Selesai" :info="$info('finish_inventory', 'Isi Rak Selesai')" :value="$n('finish_inventory')" unit="Isi Rak" icon="cube"
                    note="Sepatu sesudah reparasi: jumlah isi rak sepatu ditambah semua rak manual, menurut penghitung rak." />
                {{-- Bukan tahap pengerjaan: ini ukuran seberapa penuh gudang (gabungan sebelum + sesudah reparasi) --}}
                <x-ui.stat-card label="Keterisian Rak" :info="$info('stored_items', 'Keterisian Rak')" :value="$n('stored_items')" icon="archive" tone="blue"
                    :unit="$rackCapacity > 0 ? 'dari ' . number_format($rackCapacity, 0, ',', '.') . ' slot' : 'slot terpakai'"
                    :badge="is_null($rackPercent) ? null : $rackPercent . '% terisi'" badge-tone="slate"
                    note="Seberapa penuh gudang, bukan tahap pengerjaan: {{ $n('inbound_inventory') }} sebelum reparasi + {{ $n('finish_inventory') }} sesudah reparasi. Rak aksesoris tidak dihitung." />
            </div>

            {{-- Angka pendukung (posisi saat ini) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-ui.stat-card label="Antrean Kirim" :info="$info('shipping_pending', 'Antrean Kirim')" :value="$n('shipping_pending')" unit="Pengiriman" icon="truck" tone="blue"
                    note="Pengiriman yang belum diverifikasi." />
                <x-ui.stat-card label="Sepatu di Rak Sepatu" :info="$info('shoes_in_rack_count', 'Sepatu di Rak Sepatu')" :value="$n('shoes_in_rack_count')" unit="Penempatan" icon="location"
                    note="Penempatan aktif di rak kategori sepatu, dihitung dari datanya langsung (bukan penghitung rak). Sama dengan total di halaman Sepatu di Rak." />
                <x-ui.stat-card label="Calon Donasi (≥ 3 Bulan)" :info="$info('donation_candidates_count', 'Calon Donasi (≥ 3 Bulan)')" :value="$n('donation_candidates_count')" unit="Penempatan" icon="gift"
                    :tone="($summary['donation_candidates_count'] ?? 0) > 0 ? 'rose' : 'slate'"
                    note="Sepatu di rak sepatu yang sudah tersimpan 3 bulan atau lebih." />
            </div>

            <div class="p-4 bg-slate-50 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-800 rounded-2xl text-xs text-slate-600 dark:text-gray-400 leading-relaxed space-y-1.5">
                <p><span class="font-bold text-slate-800 dark:text-gray-200">Kenapa Isi Rak Selesai ({{ $n('finish_inventory') }}) tidak sama dengan Siap Diambil ({{ $n('ready_for_pickup') }})?</span>
                    Keduanya dihitung dengan cara berbeda, dan Siap Diambil bukan bagian dari Isi Rak Selesai.</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li><span class="font-bold">Isi Rak Selesai</span> menjumlah penghitung isi rak, termasuk barang di rak manual yang dicatat tanpa SPK, dan sepatu di rak yang SPK-nya belum berstatus selesai.</li>
                    <li><span class="font-bold">Siap Diambil</span> menghitung SPK: hanya yang berstatus selesai, belum diambil/dikirim, dan masih tercatat di rak.</li>
                    <li>Penghitung rak dinaik-turunkan setiap ada barang masuk/keluar, bukan dihitung ulang dari isi sebenarnya, jadi angkanya bisa sedikit bergeser dari kondisi fisik.</li>
                </ul>
            </div>

            {{-- ================= AKTIVITAS PADA PERIODE (mengikuti filter tanggal) ================= --}}
            <x-ui.section-note class="pt-2" title="Aktivitas pada Periode" tone="emerald"
                tag="{{ \Carbon\Carbon::parse($startDate)->translatedFormat('j M Y') }} – {{ \Carbon\Carbon::parse($endDate)->translatedFormat('j M Y') }}">
                Jumlah kejadian selama rentang tanggal yang dipilih; berubah saat filter diganti. Tiap kartu memakai tanggal kejadiannya sendiri, jadi satu SPK bisa terhitung di lebih dari satu kartu.
            </x-ui.section-note>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <x-ui.stat-card label="1. Sepatu Masuk (Before)" :info="$info('incoming_day', '1. Sepatu Masuk (Before)')" :value="$n('incoming_day')" unit="SPK" icon="inbox-in"
                    note="SPK dengan tanggal masuk pada periode ini yang sudah dikonfirmasi diterima gudang." />
                <x-ui.stat-card label="2. Dikirim ke Workshop" :info="$info('spk_print', '2. Dikirim ke Workshop')" :value="$n('spk_print')" unit="SPK" icon="truck" tone="blue"
                    note="SPK yang berpindah status menjadi dikirim ke workshop (lewat manifest) pada periode ini." />
                <x-ui.stat-card label="3. Ditolak QC Penerimaan" :info="$info('qc_reject', '3. Ditolak QC Penerimaan')" :value="$n('qc_reject')" unit="Kejadian" icon="warning"
                    :tone="($summary['qc_reject'] ?? 0) > 0 ? 'rose' : 'slate'"
                    note="Jumlah kejadian penolakan saat QC penerimaan gudang. SPK yang ditolak lalu diloloskan tetap terhitung." />
                <x-ui.stat-card label="4. Selesai Dikerjakan (After Masuk)" :info="$info('after_masuk', '4. Selesai Dikerjakan (After Masuk)')" :value="$n('after_masuk')" unit="SPK" icon="sparkles"
                    note="SPK yang tanggal selesainya jatuh pada periode ini." />
                <x-ui.stat-card label="5. Sepatu Keluar" :info="$info('sepatu_keluar', '5. Sepatu Keluar')" :value="$n('sepatu_keluar')" unit="SPK" icon="outbox" tone="blue"
                    note="SPK yang diambil pelanggan atau diserahkan untuk dikirim pada periode ini." />

                {{-- (Dikirim ke Workshop - Sepatu Masuk) / Sepatu Masuk x 100 --}}
                <x-ui.stat-card label="6. Kelancaran Arus Masuk" :info="$info('clearance_rate_before', '6. Kelancaran Arus Masuk')" :value="$rateIn['value']" icon="scale"
                    :tone="$rateIn['state'] === 'Menumpuk' ? 'amber' : ($rateIn['state'] ? 'emerald' : 'slate')"
                    :badge="$rateIn['state']" :badge-tone="$rateIn['state'] === 'Menumpuk' ? 'amber' : 'emerald'"
                    :note="$rateIn['state']
                        ? '(Dikirim ke Workshop − Sepatu Masuk) ÷ Sepatu Masuk. Positif: yang dikirim lebih banyak dari yang masuk. Negatif: sepatu masuk lebih cepat dari yang dikirim.'
                        : 'Tidak ada sepatu masuk pada periode ini, jadi tidak bisa dihitung.'" />

                {{-- (Sepatu Keluar - Selesai Dikerjakan) / Selesai Dikerjakan x 100 --}}
                <x-ui.stat-card label="7. Kelancaran Arus Keluar" :info="$info('clearance_rate_after', '7. Kelancaran Arus Keluar')" :value="$rateOut['value']" icon="refresh"
                    :tone="$rateOut['state'] === 'Menumpuk' ? 'amber' : ($rateOut['state'] ? 'emerald' : 'slate')"
                    :badge="$rateOut['state']" :badge-tone="$rateOut['state'] === 'Menumpuk' ? 'amber' : 'emerald'"
                    :note="$rateOut['state']
                        ? '(Sepatu Keluar − Selesai Dikerjakan) ÷ Selesai Dikerjakan. Positif: yang keluar lebih banyak dari yang selesai. Negatif: sepatu selesai menumpuk karena belum diambil.'
                        : 'Tidak ada sepatu selesai pada periode ini, jadi tidak bisa dihitung.'" />
            </div>

            {{-- Angka pendukung (mengikuti periode): tanggal acuannya berbeda dari kartu bernomor di atas --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-ui.stat-card label="SPK Dibuat" :info="$info('total_incoming', 'SPK Dibuat')" :value="$n('total_incoming')" unit="SPK" icon="clipboard-list"
                    note="SPK yang dibuat pada periode ini, semua status. Berbeda dari Sepatu Masuk, yang memakai tanggal masuk dan hanya menghitung yang sudah diterima gudang." />
                <x-ui.stat-card label="Berstatus Selesai (Terakhir Diubah)" :info="$info('finished_day', 'Berstatus Selesai (Terakhir Diubah)')" :value="$n('finished_day')" unit="SPK" icon="check-circle"
                    note="SPK berstatus selesai yang terakhir diubah pada periode ini. Berbeda dari Selesai Dikerjakan, yang memakai tanggal selesai." />
                <x-ui.stat-card label="Diproses QC Gudang" :info="$info('total_qc_processed', 'Diproses QC Gudang')" :value="$n('total_qc_processed')" unit="SPK" icon="tag"
                    note="SPK yang sudah punya hasil QC gudang dengan waktu QC pada periode ini." />
            </div>

        @endif
    </div>
</div>
