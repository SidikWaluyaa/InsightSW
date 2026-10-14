{{-- Modal info rumus (untuk developer). Letakkan sekali per halaman; dibuka oleh event "show-formula" (mis. tombol "i" di <x-ui.stat-card :info>).
     Teks bawaan untuk Gudang (API pusat); halaman lain bisa menimpanya lewat label_nilai, label_rumus, sumber_rumus. --}}
<div x-data="{ open: false, d: {} }"
    x-on:show-formula.window="d = $event.detail; open = true"
    x-on:keydown.escape.window="open = false"
    x-show="open" x-cloak
    class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center p-4"
    role="dialog" aria-modal="true" :aria-label="'Info rumus ' + (d.judul || '')">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" x-on:click="open = false" x-show="open" x-transition.opacity></div>

    <div x-show="open" x-transition
        class="relative w-full max-w-xl max-h-[85vh] overflow-y-auto bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-2xl">
        <div class="sticky top-0 flex items-start justify-between gap-4 px-6 pt-5 pb-4 bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800">
            <div class="min-w-0">
                <p class="text-[9px] font-black text-emerald-500 uppercase tracking-[0.25em]">Info Rumus · Developer</p>
                <h3 class="text-lg font-black text-slate-800 dark:text-white tracking-tight" x-text="d.judul"></h3>
                <span class="inline-block mt-1 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-widest"
                    :class="d.sifat === 'periode' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-slate-500/10 text-slate-500 dark:text-gray-400'"
                    x-text="d.sifat === 'periode' ? 'Mengikuti filter tanggal' : 'Posisi saat ini (tidak ikut filter tanggal)'"></span>
            </div>
            <button type="button" x-on:click="open = false" aria-label="Tutup"
                class="p-2 -mr-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-gray-200 hover:bg-slate-100 dark:hover:bg-gray-800 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <dl class="px-6 py-5 space-y-5 text-sm">
            <div>
                <dt class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1">Sumber Data</dt>
                <dd class="font-mono text-xs text-slate-700 dark:text-gray-300 break-all">
                    <span x-text="d.endpoint"></span><br>
                    field: <span class="font-bold text-emerald-600 dark:text-emerald-400" x-text="d.field"></span>
                </dd>
            </div>

            <div>
                <dt class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1"><span x-text="d.label_nilai || 'Nilai Mentah dari API'"></span></dt>
                <dd class="font-mono text-xs text-slate-700 dark:text-gray-300">
                    <span class="font-bold" x-text="d.nilai"></span>
                    <span class="text-slate-400" x-show="d.satuan" x-text="'(' + d.satuan + ')'"></span>
                </dd>
            </div>

            <div>
                <dt class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1">Definisi</dt>
                <dd class="text-slate-700 dark:text-gray-300 leading-relaxed" x-text="d.definisi"></dd>
            </div>

            <div>
                <dt class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1"><span x-text="d.label_rumus || 'Rumus Database (Sistem Pusat)'"></span></dt>
                <dd>
                    <pre x-show="d.rumus_db" class="p-3 rounded-xl bg-slate-900 text-emerald-300 text-[11px] leading-relaxed whitespace-pre-wrap break-words" x-text="d.rumus_db"></pre>
                    <p x-show="d.rumus_db" class="mt-1.5 text-[10px] text-slate-400 dark:text-gray-500" x-text="d.sumber_rumus || 'Sumber: WarehouseDashboardApiService (sistem pusat). SQL diturunkan dari kode, belum dijalankan ulang ke database.'"></p>
                    <p x-show="!d.rumus_db" class="p-3 rounded-xl border border-dashed border-amber-300 dark:border-amber-900/60 bg-amber-50 dark:bg-amber-950/20 text-xs text-amber-800 dark:text-amber-300 leading-relaxed">
                        Belum dikonfirmasi. Tabel dan kolom persisnya ada di kode sistem pusat (info.shoeworkshop.id) dan belum dicatat di sini.
                        Isi <span class="font-mono font-bold">rumus_db</span> pada <span class="font-mono font-bold">config/warehouse_formulas.php</span>.
                    </p>
                </dd>
            </div>

            <div x-show="d.hitung">
                <dt class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-2">Rincian Hitungan (Angka Periode Ini)</dt>
                <dd>
                    <ol class="space-y-2">
                        <template x-for="(l, i) in (d.hitung?.langkah || [])" :key="i">
                            <li class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-gray-800/60">
                                <span class="shrink-0 w-7 h-7 rounded-lg inline-flex items-center justify-center text-base font-black"
                                    :class="l.op === '=' ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-gray-700 text-slate-700 dark:text-gray-200'"
                                    x-text="l.op" :title="{'+': 'Tambah', '−': 'Kurang', '÷': 'Bagi', '×': 'Kali', '=': 'Hasil'}[l.op]"></span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs text-slate-600 dark:text-gray-300" x-text="(i + 1) + '. ' + l.teks"></p>
                                    <p x-show="l.rumus" class="mt-1 font-mono text-[11px] text-slate-500 dark:text-gray-400 break-words" x-text="l.rumus"></p>
                                    <p class="mt-1 font-mono text-sm font-black text-slate-800 dark:text-white" x-text="'= ' + l.hasil"></p>
                                </div>
                            </li>
                        </template>
                    </ol>

                    <details x-show="d.hitung?.rincian?.length" class="mt-3 group">
                        <summary class="cursor-pointer text-xs font-bold text-emerald-600 dark:text-emerald-400 select-none"
                            x-text="'Lihat nilai per hari (' + (d.hitung?.rincian?.length || 0) + ' hari)'"></summary>
                        <div class="mt-2 max-h-64 overflow-y-auto rounded-xl border border-gray-100 dark:border-gray-800">
                            <table class="w-full text-[11px] font-mono">
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    <template x-for="r in (d.hitung?.rincian || [])" :key="r.tanggal">
                                        <tr :class="r.keluar ? 'text-slate-400 dark:text-gray-600' : 'text-slate-700 dark:text-gray-300'">
                                            <td class="px-3 py-1.5 whitespace-nowrap" x-text="r.tanggal"></td>
                                            <td class="px-3 py-1.5 text-right whitespace-nowrap" :class="r.keluar && 'line-through'" x-text="r.nilai"></td>
                                            <td class="px-3 py-1.5 text-right text-[10px] font-sans" x-text="r.ket || ''"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </details>
                </dd>
            </div>

            <div x-show="d.turunan">
                <dt class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1"><span x-text="d.label_turunan || 'Rumus Turunan (dari field API)'"></span></dt>
                <dd class="font-mono text-xs text-slate-700 dark:text-gray-300 leading-relaxed" x-text="d.turunan"></dd>
            </div>

            <div x-show="d.cek && d.cek.length">
                <dt class="text-[10px] font-black text-slate-400 dark:text-gray-500 uppercase tracking-widest mb-1">Cek Silang dengan Data Saat Ini</dt>
                <dd class="space-y-1.5">
                    <template x-for="c in (d.cek || [])" :key="c.teks">
                        <p class="flex items-start gap-2 text-xs font-mono leading-relaxed">
                            <span class="shrink-0 mt-px px-1.5 rounded text-[9px] font-black uppercase tracking-wider"
                                :class="c.ok ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400'"
                                x-text="c.ok ? 'Cocok' : 'Beda'"></span>
                            <span class="text-slate-700 dark:text-gray-300" x-text="c.teks"></span>
                        </p>
                    </template>
                </dd>
            </div>
        </dl>
    </div>
</div>
