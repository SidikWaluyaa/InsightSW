@php
    $num = fn ($v) => $v === null ? '–' : number_format($v, 0, ',', '.');
    $tones = ['hijau' => 'emerald', 'kuning' => 'amber', 'merah' => 'rose'];
    $mom = fn ($c) => $c['perubahan'] === null ? null : ($c['perubahan'] >= 0 ? '▲ ' : '▼ ') . number_format(abs($c['perubahan']), 1, ',', '.') . '% vs bulan lalu';
    $frt = $kpis['frt'];
    $slaOk = $frt['nilai'] !== null && $frt['nilai'] < \App\Services\SleekflowActivityService::SLA_SECONDS;
    $bot = $kpis['bot'];
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
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <x-ui.stat-card label="Total Chat Masuk" :value="$kpis['chat']['teks']" unit="chat" icon="chat"
                :badge="$mom($kpis['chat'])" :badge-tone="$tones[$kpis['chat']['status']] ?? 'slate'"
                :info-call="$showFormula ? 'showFormula(15)' : null"
                :note="'± ' . $num(($kpis['chat']['nilai'] ?? 0) / max($kpis['days'], 1)) . ' chat/hari (kontak baru). Bulan lalu: ' . $kpis['chat']['teks_lalu'] . '.'" />
            <x-ui.stat-card label="First Response Time" :value="$frt['teks']" icon="clock"
                :badge="$frt['nilai'] === null ? null : ($slaOk ? 'Sesuai SLA (< 3 menit)' : 'Di atas SLA (< 3 menit)')" :badge-tone="$slaOk ? 'emerald' : 'rose'"
                :info-call="$showFormula ? 'showFormula(17)' : null"
                :note="'Rata-rata harian waktu balas pertama, hari libur CS tidak dihitung. Bulan lalu: ' . $frt['teks_lalu'] . ($mom($frt) ? ' (' . $mom($frt) . ').' : '.')" />
            <x-ui.stat-card label="Rasio Otomasi Bot" :value="$bot['rasio'] === null ? '–' : number_format($bot['rasio'], 1, ',', '.') . '%'" icon="sparkles"
                :note="'Pesan otomatis ÷ semua pesan terkirim (' . $num($bot['automated']) . ' dari ' . $num($bot['sent']) . '). Human CS: ' . ($bot['rasio'] === null ? '–' : number_format(100 - $bot['rasio'], 1, ',', '.') . '%') . '.'"
                warning="AI greeting sedang dimatikan, jadi rasio ini mendekati 0%." />
            <x-ui.stat-card label="Biaya Bubble Chat" value="Menyusul" icon="cash" compact
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

            <section class="bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl p-6 shadow-sm space-y-3">
                <div>
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Estimasi Kendala & Reason Drop-off</h3>
                    <p class="text-xs text-slate-500 dark:text-gray-400">Diperoleh dari survei / sampling manual CS.</p>
                </div>
                <p class="text-sm text-amber-600 dark:text-amber-400">Belum ada data. Hasil survei alasan drop-off (mis. harga, sepatu rusak parah, ongkir, tidak merespons) perlu diinput Tim Data.</p>
            </section>
        </div>
    </div>
</div>
