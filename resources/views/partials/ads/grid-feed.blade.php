@if(config('ads.enabled', true))
    @php
        $slotConfig = config('ads.slots.grid_feed', []);
        $isEnabled = $slotConfig['enabled'] ?? true;
        $code = trim($slotConfig['code'] ?? '');
        $showPlaceholder = config('ads.show_placeholders', true);
    @endphp

    @if($isEnabled)
        @if(!empty($code))
            <div class="ad-grid-feed rounded-2xl bg-slate-900/80 border border-slate-800 overflow-hidden flex flex-col justify-center items-center p-3 relative group">
                <span class="absolute top-2 right-2 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-black/60 text-slate-400 border border-white/10">Ad</span>
                <div class="w-full flex justify-center items-center overflow-hidden">
                    {!! $code !!}
                </div>
            </div>
        @elseif($showPlaceholder)
            <div class="rounded-2xl bg-gradient-to-br from-slate-900 via-fuchsia-950/20 to-slate-900 border border-dashed border-fuchsia-500/40 p-5 flex flex-col justify-between select-none min-h-[240px] text-center">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-fuchsia-950/80 text-fuchsia-400 border border-fuchsia-500/30">
                        <i class="fa-solid fa-rectangle-ad"></i> Sponsored
                    </span>
                    <span class="text-[10px] text-slate-500 font-mono">In-Feed</span>
                </div>
                
                <div class="py-4">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-fuchsia-600/30 to-indigo-600/30 border border-fuchsia-500/30 flex items-center justify-center mx-auto mb-2 text-fuchsia-400">
                        <i class="fa-solid fa-bullhorn text-lg"></i>
                    </div>
                    <h4 class="text-xs font-bold text-slate-200">Native In-Grid Ad</h4>
                    <p class="text-[11px] text-slate-400 mt-1">Blends directly between video cards for high CTR.</p>
                </div>

                <div class="text-[10px] text-slate-400 bg-slate-950/60 p-2 rounded-lg border border-slate-800">
                    Set <code class="text-fuchsia-300 font-mono">ADS_GRID_FEED_CODE</code> in .env
                </div>
            </div>
        @endif
    @endif
@endif
