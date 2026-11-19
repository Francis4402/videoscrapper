@if(config('ads.enabled', true))
    @php
        $slotName = $slot ?? 'header';
        $slotConfig = config("ads.slots.{$slotName}", []);
        $isEnabled = $slotConfig['enabled'] ?? true;
        $code = trim($slotConfig['code'] ?? '');
        $showPlaceholder = config('ads.show_placeholders', true);
        $size = $slotConfig['size'] ?? 'Responsive';
        $customClass = $class ?? '';
    @endphp

    @if($isEnabled)
        @if(!empty($code))
            <div class="ad-slot ad-slot-{{ $slotName }} my-4 flex flex-col items-center justify-center {{ $customClass }}">
                <span class="text-[9px] tracking-widest uppercase text-slate-500 mb-1 font-semibold select-none">Advertisement</span>
                <div class="ad-content w-full flex justify-center overflow-hidden">
                    {!! $code !!}
                </div>
            </div>
        @elseif($showPlaceholder)
            <div class="ad-placeholder ad-placeholder-{{ $slotName }} my-4 w-full flex flex-col items-center justify-center p-4 rounded-xl border border-dashed border-slate-700/80 bg-slate-900/40 text-slate-400 select-none {{ $customClass }}">
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-fuchsia-400 mb-1">
                    <i class="fa-solid fa-rectangle-ad text-sm"></i>
                    <span>Advertisement ({{ $size }})</span>
                </div>
                <p class="text-[11px] text-slate-500 text-center">
                    Slot: <code class="text-slate-300 bg-slate-800 px-1.5 py-0.5 rounded text-[10px] font-mono">{{ $slotName }}</code> &bull; 
                    Paste ad code into <code class="text-fuchsia-300 bg-slate-800 px-1.5 py-0.5 rounded text-[10px] font-mono">ADS_{{ strtoupper(str_replace('-', '_', $slotName)) }}_CODE</code> in <code class="text-slate-300 font-mono">.env</code>
                </p>
            </div>
        @endif
    @endif
@endif
