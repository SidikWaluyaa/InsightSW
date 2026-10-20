@php
    $num = fn ($v) => $v === null ? '–' : number_format($v, 0, ',', '.');
    $tones = ['hijau' => 'emerald', 'kuning' => 'amber', 'merah' => 'rose'];
    $mom = fn ($c) => $c['perubahan'] === null ? null : ($c['perubahan'] >= 0 ? '▲ ' : '▼ ') . number_format(abs($c['perubahan']), 1, ',', '.') . '% vs bulan lalu';
    $frt = $kpis['frt'];
    $slaOk = $frt['nilai'] !== null && $frt['nilai'] < \App\Services\SleekflowActivityService::SLA_SECONDS;
    $bot = $kpis['bot'];
    // Detik → teks pendek: "24 detik" / "12 mnt 5 dtk" / "1 jam 13 mnt"
    $dur = function ($sec) {
        if ($sec === null) return '–';
        $sec = (int) round($sec);
        if ($sec < 60) return $sec . ' detik';
        if ($sec < 3600) return intdiv($sec, 60) . ' mnt ' . ($sec % 60) . ' dtk';
        return intdiv($sec, 3600) . ' jam ' . intdiv($sec % 3600, 60) . ' mnt';
    };
    // Median dipakai sebagai angka utama: rata-rata mudah terseret chat yang masuk sebelum AI aktif
    $aiOk = $replies && $replies['ai_median'] !== null && $replies['ai_median'] < \App\Services\SleekflowActivityService::AI_TARGET_SECONDS;
    $th = 'px-4 py-3 whitespace-nowrap';
    $bebanTone = ['Optimal' => 'bg-emerald-500', 'Sibuk' => 'bg-amber-500', 'Overload' => 'bg-rose-500'];
@endphp

{{-- CS → SleekFlow Activity: layout PRD halaman 2 (beban kerja & SLA) --}}
<div class="space-y-6" x-data x-on:tarik-kontak-lanjut.window="$wire.tarikBagian()">
    <div wire:ignore><x-ui.formula-modal /></div>

    <x-ui.page-header title="SleekFlow Activity" subtitle="Beban kerja & SLA tim CS: chat masuk, kecepatan balas, dan status percakapan.">
        <x-ui.range-toolbar :start="$startDate" :end="$endDate" :max="$maxDate" :presets="$presets" refresh="tarikKontak" refresh-label="Tarik Kontak Sleekflow" />
    </x-ui.page-header>

    <x-ui.range-info :start="$startDate" :end="$endDate">
        <span>Dibanding {{ \Carbon\Carbon::parse($kpis['prevRange'][0])->translatedFormat('j M') }} – {{ \Carbon\Carbon::parse($kpis['prevRange'][1])->translatedFormat('j M Y') }}</span>
    </x-ui.range-info>

    @if($menarik)
        {{-- Tarik kontak bertahap sedang berjalan --}}
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 text-sm text-indigo-700 dark:text-indigo-300">
            <span class="w-4 h-4 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></span>
            Menarik kontak Sleekflow… {{ number_format($tarikJumlah, 0, ',', '.') }} kontak tersimpan (halaman API ke-{{ intdiv($tarikOffset, 100) + 1 }}). Jangan tutup halaman ini.
        </div>
    @endif

    @foreach($kpis['errors'] as $error)
        <div class="px-4 py-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-sm text-rose-700 dark:text-rose-400">{{ $error }}</div>
    @endforeach

    <div wire:loading.class="opacity-30" wire:target="setDateRange, tarikKontak, tarikBagian" class="space-y-6 transition-opacity">
        {{-- Alur 1: KPI beban kerja & respon --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            <x-ui.stat-card label="Total Chat Masuk" :value="$kpis['chat']['teks']" unit="chat" icon="chat"
                :badge="$mom($kpis['chat'])" :badge-tone="$tones[$kpis['chat']['status']] ?? 'slate'"
                :info-call="$showFormula ? 'showFormula(15)' : null"
                :note="'± ' . $num(($kpis['chat']['nilai'] ?? 0) / max($kpis['days'], 1)) . ' chat/hari (kontak baru). Bulan lalu: ' . $kpis['chat']['teks_lalu'] . '.'" />

            {{-- Balasan AI greeting: dari API pesan Sleekflow (analitik harian Sleekflow tidak menghitungnya) --}}
            <x-ui.stat-card label="First Response AI Greeting (Median)" :value="$replies ? $dur($replies['ai_median']) : 'Menyusul'" icon="sparkles" compact
                :badge="$replies && $replies['ai_median'] !== null ? ($aiOk ? 'Sesuai target (< 30 detik)' : 'Di atas target (< 30 detik)') : null" :badge-tone="$aiOk ? 'emerald' : 'rose'"
                :info-call="$showFormula ? 'showFormula(\'ai_median\')' : null"
                :note="$replies
                    ? 'Median waktu dari chat masuk ke balasan AI, untuk chat yang dibalas AI lebih dulu dari staf (rata-rata ' . $dur($replies['ai_rata']) . ', terseret chat yang masuk sebelum AI aktif). Data mulai ' . \Carbon\Carbon::parse($replies['dari'])->translatedFormat('j M Y') . '.'
                    : 'Belum ada data balasan per percakapan untuk rentang ini (sinkron sync:sleekflow-replies). AI greeting aktif sejak 19 Okt 2026.'" />

            <x-ui.stat-card label="Chat Dibalas AI Greeting" :value="$replies && $replies['persen_ai'] !== null ? number_format($replies['persen_ai'], 1, ',', '.') . '%' : 'Menyusul'" icon="chat" compact
                :info-call="$showFormula ? 'showFormula(\'persen_ai\')' : null"
                :note="$replies
                    ? $num($replies['dibalas_ai']) . ' dari ' . $num($replies['whatsapp']) . ' chat WhatsApp dibalas AI lebih dulu dari staf. AI greeting hanya aktif di WhatsApp; sisanya langsung dibalas staf CS.'
                    : 'Porsi chat WhatsApp yang dibalas AI greeting lebih dulu. Menunggu data balasan per percakapan.'" />

            <x-ui.stat-card label="First Response Staf CS" :value="$frt['teks']" icon="clock"
                :badge="$frt['nilai'] === null ? null : ($slaOk ? 'Sesuai SLA (< 3 menit)' : 'Di atas SLA (< 3 menit)')" :badge-tone="$slaOk ? 'emerald' : 'rose'"
                :info-call="$showFormula ? 'showFormula(17)' : null"
                :note="'Rata-rata harian waktu balas pertama staf CS dari analitik Sleekflow (balasan AI & pesan otomatis tidak dihitung), hari libur CS tidak dihitung. Bulan lalu: ' . $frt['teks_lalu'] . ($mom($frt) ? ' (' . $mom($frt) . ').' : '.') . ($replies && $replies['staf_median'] !== null ? ' Median per percakapan dari API pesan: ' . $dur($replies['staf_median']) . '.' : '')" />

            @if($replies && $replies['rasio_bot'] !== null)
                <x-ui.stat-card label="Rasio Otomasi Bot" :value="number_format($replies['rasio_bot'], 1, ',', '.') . '%'" icon="sparkles"
                    :info-call="$showFormula ? 'showFormula(\'rasio_bot\')' : null"
                    :note="'Pesan AI + otomatis ÷ semua pesan keluar (AI ' . $num($replies['msg_ai']) . ', otomatis ' . $num($replies['msg_auto']) . ', staf ' . $num($replies['msg_staff']) . '). Human CS: ' . number_format(100 - $replies['rasio_bot'], 1, ',', '.') . '%.'" />
            @else
                <x-ui.stat-card label="Rasio Otomasi Bot" :value="$bot['rasio'] === null ? '–' : number_format($bot['rasio'], 1, ',', '.') . '%'" icon="sparkles"
                    :info-call="$showFormula ? 'showFormula(\'rasio_bot\')' : null"
                    :note="'Pesan otomatis ÷ semua pesan terkirim (' . $num($bot['automated']) . ' dari ' . $num($bot['sent']) . ') dari analitik harian Sleekflow.'"
                    warning="Analitik harian Sleekflow tidak menghitung balasan AI & pesan otomatis. Angka akurat muncul setelah data balasan per percakapan tersedia." />
            @endif

            <x-ui.stat-card label="Biaya Bubble Chat" value="Menyusul" icon="cash" compact
                :info-call="$showFormula ? 'showFormula(\'bubble\')' : null"
                note="Tarif PRD: Rp 356,65 per bubble chat. Jumlah bubble yang ditagih Sleekflow/WhatsApp belum ada sumber datanya." />
        </div>

        @if($coverage['persen'] !== null && $coverage['persen'] < 90)
            {{-- Data kontak per agen hanya lengkap untuk tanggal yang sudah ditarik --}}
            <div class="px-4 py-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 text-sm text-amber-800 dark:text-amber-300">
                Data kontak Sleekflow untuk rentang ini baru {{ $num($coverage['kontak']) }} dari {{ $num($coverage['chat']) }} chat masuk ({{ number_format($coverage['persen'], 0, ',', '.') }}%).
                Tabel per agen dan status lifecycle di bawah belum lengkap. Klik <strong>Tarik Kontak Sleekflow</strong> (bisa beberapa menit).
            </div>
        @endif

        {{-- Alur 2: distribusi beban kerja & aktivitas per agen CS --}}
        <section class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">Distribusi Beban Kerja & Aktivitas per Agen CS</h3>
                <p class="text-xs text-slate-500 dark:text-gray-400">
                    Chat masuk & konsultasi dari kontak Sleekflow (pemilik kontak). SPK closing dari sistem pusat, dicocokkan lewat nama depan agen.
                    Status beban: chat masuk agen dibanding rata-rata agen aktif (porsi ≥ 2%) — Overload ≥ 130%, Sibuk ≥ 100%, Optimal di bawahnya. Batas ini sementara.
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800/50 text-[10px] uppercase tracking-widest font-bold text-slate-400 dark:text-gray-500">
                            <th class="{{ $th }}">Agen CS</th>
                            <th class="{{ $th }} text-right">Total Chat Masuk</th>
                            <th class="{{ $th }} text-right">Chat Konsultasi</th>
                            <th class="{{ $th }} text-right">Avg Response Time</th>
                            <th class="{{ $th }} text-right">SPK Closing</th>
                            <th class="{{ $th }}">Status Beban Kerja</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($agents as $a)
                            <tr wire:key="agen-{{ md5($a['nama']) }}" class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                <td class="px-4 py-3 font-bold text-slate-800 dark:text-white whitespace-nowrap">{{ $a['nama'] }}</td>
                                <td class="px-4 py-3 text-right text-slate-700 dark:text-gray-200">{{ $num($a['chat']) }} chat</td>
                                <td class="px-4 py-3 text-right text-slate-600 dark:text-gray-300">{{ $num($a['konsul']) }} chat</td>
                                <td class="px-4 py-3 text-right text-slate-400" title="Waktu balas per agen belum tersedia dari Sleekflow">–</td>
                                <td class="px-4 py-3 text-right font-bold text-slate-800 dark:text-gray-100">{{ $a['spk'] === null ? '–' : $num($a['spk']) . ' SPK' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-gray-200">
                                        <span class="w-2.5 h-2.5 rounded-full {{ $bebanTone[$a['beban']] }}"></span>{{ $a['beban'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-10 text-center text-sm italic text-slate-400">Belum ada kontak Sleekflow tim CS pada rentang ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Alur 3: status lifecycle & evaluasi kendala chat --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <section class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm space-y-4">
                <div>
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Status Lifecycle Percakapan</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400">Greeting & konsultasi = status chat kontak yang masuk pada rentang ini; terbit SPK = kontak yang closing pada rentang ini.</p>
                </div>
                @foreach([['Greeting (chat baru / menunggu CS)', $lifecycle['greeting'], 'bg-violet-500'], ['Konsultasi sepatu (aktif berjalan)', $lifecycle['konsul'], 'bg-sky-500'], ['Terbit SPK / closing selesai', $lifecycle['closing'], 'bg-emerald-500']] as [$label, $value, $dot])
                    <div class="flex items-center justify-between text-sm">
                        <span class="flex items-center gap-2 text-slate-600 dark:text-gray-300"><span class="w-2.5 h-2.5 rounded-full {{ $dot }}"></span>{{ $label }}</span>
                        <span class="font-black text-slate-800 dark:text-white">{{ $num($value) }} chat</span>
                    </div>
                @endforeach
                <p class="text-[11px] text-slate-400 dark:text-gray-500">Tagging Hot / Warm / Cold belum ada.</p>
            </section>

            <section class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm space-y-4">
                <div>
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Estimasi Kendala & Reason Drop-off</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400">Diperoleh dari survei / sampling manual CS. Menampilkan survei yang periodenya beririsan dengan rentang ini.</p>
                </div>

                @if(empty($dropoff['rekap']))
                    <p class="text-sm text-amber-600 dark:text-amber-400">Belum ada hasil survei untuk rentang ini.</p>
                @else
                    <ol class="space-y-2">
                        @foreach($dropoff['rekap'] as $i => $r)
                            <li wire:key="rekap-{{ md5($r['alasan']) }}">
                                <div class="flex justify-between gap-3 text-sm">
                                    <span class="text-slate-600 dark:text-gray-300">{{ $i + 1 }}. {{ $r['alasan'] }}</span>
                                    <span class="font-bold text-slate-800 dark:text-white whitespace-nowrap">~{{ number_format($r['persen'], 0, ',', '.') }}% <span class="text-xs font-medium text-slate-400">({{ number_format($r['jumlah'], 0, ',', '.') }} chat)</span></span>
                                </div>
                                <div class="h-1.5 mt-1 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden"><div class="h-full bg-rose-400" style="width: {{ min($r['persen'], 100) }}%"></div></div>
                            </li>
                        @endforeach
                    </ol>
                    <p class="text-[11px] text-slate-400 dark:text-gray-500">Total sampel {{ number_format($dropoff['total'], 0, ',', '.') }} chat dari {{ $dropoff['baris']->count() }} baris survei.</p>
                @endif

                @if($canInputDropoff)
                    {{-- Input hasil survei (Admin, Editor, Leader CS) --}}
                    <details class="group rounded-2xl border border-gray-100 dark:border-gray-800" @if($errors->any()) open @endif>
                        <summary class="cursor-pointer select-none px-4 py-3 text-sm font-bold text-emerald-600 dark:text-emerald-400">+ Input hasil survei</summary>
                        <div class="px-4 pb-4 space-y-3">
                            <div class="grid grid-cols-2 gap-2">
                                <label class="text-xs text-slate-500 dark:text-gray-400">Awal periode sampel
                                    <input type="date" wire:model="dropMulai" class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-slate-700 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 mt-1">
                                </label>
                                <label class="text-xs text-slate-500 dark:text-gray-400">Akhir periode sampel
                                    <input type="date" wire:model="dropSelesai" class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-slate-700 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 mt-1">
                                </label>
                            </div>
                            <div class="grid grid-cols-3 gap-2">
                                <label class="col-span-2 text-xs text-slate-500 dark:text-gray-400">Alasan
                                    <select wire:model="dropAlasan" class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-slate-700 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 mt-1">
                                        <option value="">Pilih alasan…</option>
                                        @foreach($alasanList as $a)
                                            <option value="{{ $a }}">{{ $a }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="text-xs text-slate-500 dark:text-gray-400">Jumlah chat
                                    <input type="number" min="1" wire:model="dropJumlah" class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-slate-700 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 mt-1">
                                </label>
                            </div>
                            <label class="block text-xs text-slate-500 dark:text-gray-400">Catatan (opsional)
                                <input type="text" maxlength="255" wire:model="dropCatatan" placeholder="mis. sampel 100 chat tidak lanjut" class="w-full h-10 px-3 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl text-sm text-slate-700 dark:text-gray-200 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 mt-1">
                            </label>
                            @if($errors->any())
                                <ul class="text-xs text-rose-600 dark:text-rose-400 space-y-0.5">
                                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                                </ul>
                            @endif
                            <x-ui.btn wire:click="simpanAlasan" target="simpanAlasan" class="w-full">Simpan</x-ui.btn>
                        </div>

                        @if($dropoff['baris']->isNotEmpty())
                            <div class="px-4 pb-4">
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Baris survei di rentang ini</p>
                                <ul class="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                                    @foreach($dropoff['baris'] as $b)
                                        <li wire:key="drop-{{ $b->id }}" class="py-2 flex items-start justify-between gap-3">
                                            <span class="text-slate-600 dark:text-gray-300">
                                                {{ $b->periode_mulai->translatedFormat('j M') }} – {{ $b->periode_selesai->translatedFormat('j M Y') }} · {{ $b->alasan }} · <strong>{{ number_format($b->jumlah, 0, ',', '.') }}</strong> chat
                                                @if($b->catatan)<span class="block text-slate-400">{{ $b->catatan }}</span>@endif
                                                <span class="block text-slate-400">oleh {{ $b->creator?->name ?? '-' }}</span>
                                            </span>
                                            <button type="button" wire:click="hapusAlasan({{ $b->id }})" wire:confirm="Hapus baris survei ini?" class="shrink-0 text-rose-500 hover:underline">Hapus</button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </details>
                @endif
            </section>
        </div>
    </div>
</div>
