<div class="relative group p-6 rounded-[2rem] border transition-all duration-500
    {{ $item->is_bottleneck 
        ? 'bg-gradient-to-br from-rose-50/50 to-white dark:from-rose-900/10 dark:to-gray-900 border-rose-500/30 shadow-lg shadow-rose-500/5' 
        : 'bg-white dark:bg-gray-800/40 border-slate-100 dark:border-gray-800' }} 
    hover:shadow-2xl hover:shadow-indigo-500/10 hover:-translate-y-1">
    
    @if($item->is_bottleneck)
        <div class="absolute -top-3 -right-3 px-4 py-1.5 bg-rose-500 text-white text-[10px] font-black uppercase tracking-[0.2em] rounded-xl shadow-lg shadow-rose-500/30 z-10 border border-white/20">
            Hambatan
        </div>
    @endif

    <div class="flex flex-col gap-4">
        <div class="flex items-center justify-between">
            <span class="text-[12px] font-black {{ $item->is_bottleneck ? 'text-rose-600' : 'text-slate-700 dark:text-gray-200' }} uppercase tracking-[0.1em]">
                {{ $item->sub_stage }}
            </span>
            <div class="flex items-center gap-2">
                <span class="text-xl font-black text-slate-900 dark:text-white">{{ $item->count }}</span>
                <div class="w-1.5 h-1.5 rounded-full {{ $item->count > 0 ? 'bg-'.$config['color'].'-500 animate-pulse' : 'bg-slate-200' }}"></div>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <div class="flex items-center justify-between text-[9px] font-bold uppercase tracking-widest text-slate-400">
                <span>Rata-rata Tunggu</span>
                <span class="{{ $item->avg_hours > 24 ? 'text-amber-500' : 'text-emerald-500' }} font-black">{{ max(0, $item->avg_hours) }} jam</span>
            </div>
            
            {{-- Progres Bar --}}
            <div class="w-full h-1.5 bg-slate-100 dark:bg-gray-700/50 rounded-full overflow-hidden">
                <div class="h-full bg-{{ $config['color'] }}-500 rounded-full transition-all duration-1000" 
                     style="width: {{ $total > 0 ? ($item->count / $total) * 100 : 0 }}%"></div>
            </div>
        </div>
    </div>
</div>
