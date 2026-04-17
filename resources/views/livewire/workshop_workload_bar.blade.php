<div class="space-y-3 group p-4 rounded-2xl border border-slate-50 dark:border-gray-800/50 hover:bg-slate-50/50 dark:hover:bg-gray-800/30 transition-all">
    <div class="flex items-center justify-between">
        <div class="flex flex-col">
            <span class="text-[11px] font-black uppercase tracking-[0.1em] text-slate-700 dark:text-gray-200">{{ $label }}</span>
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Operasional</span>
        </div>
        <div class="text-right">
            <span class="text-xs font-black text-{{ $color }}-600 font-mono">{{ $value }}</span>
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest ml-1">Unit</span>
        </div>
    </div>
    <div class="relative w-full h-2.5 bg-slate-100 dark:bg-gray-800 rounded-full overflow-hidden">
        <div class="h-full bg-gradient-to-r from-{{ $color }}-400 to-{{ $color }}-600 rounded-full transition-all duration-1000 relative group-hover:shadow-[0_0_12px_rgba(16,185,129,0.3)]" 
             style="width: {{ $percentage }}%">
            @if($percentage > 10)
                <div class="absolute inset-0 bg-[linear-gradient(45deg,rgba(255,255,255,0.1)_25%,transparent_25%,transparent_50%,rgba(255,255,255,0.1)_50%,rgba(255,255,255,0.1)_75%,transparent_75%,transparent)] bg-[length:20px_20px] animate-[slide_1.5s_linear_infinite]"></div>
            @endif
        </div>
    </div>
</div>
