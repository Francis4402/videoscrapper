@if(config('ads.enabled', true))
    @php
        $slotName = $slot ?? 'header';
        $slotConfig = config("ads.slots.{$slotName}", []);
        $isEnabled = $slotConfig['enabled'] ?? true;
        $code = trim($slotConfig['code'] ?? '');
        $showPlaceholder = config('ads.show_placeholders', true);
        $size = $slotConfig['size'] ?? 'Responsive';
        $customClass = $class ?? '';
        $adUrl = $slotConfig['url'] ?? config('ads.onclick.url', 'https://syndication.exoclick.com/splash.php?idzone=default');
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
            <div class="ad-placeholder ad-placeholder-{{ $slotName }} my-4 w-full flex flex-col items-center justify-center {{ $customClass }}">
                <div class="w-full flex items-center justify-between text-[9px] text-slate-500 uppercase tracking-wider font-semibold mb-1 px-1">
                    <span><i class="fa-solid fa-rectangle-ad text-fuchsia-400 mr-1"></i> Sponsored ({{ $size }})</span>
                    <span class="opacity-70 font-mono text-[8px]">ADS_{{ strtoupper(str_replace('-', '_', $slotName)) }}_CODE</span>
                </div>

                @if($slotName === 'watch_sidebar')
                    <!-- 300x250 Medium Rectangle High-CTR Working Banner -->
                    <a href="{{ $adUrl }}" target="_blank" rel="nofollow noopener noreferrer" class="group relative block w-full rounded-2xl overflow-hidden bg-gradient-to-br from-purple-950 via-slate-900 to-indigo-950 border border-fuchsia-500/40 p-5 shadow-2xl hover:border-fuchsia-400 hover:scale-[1.02] transition-all duration-300">
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-fuchsia-950 border border-fuchsia-500/50 text-fuchsia-300">
                                <i class="fa-solid fa-fire text-amber-400"></i> Hot Game
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-rose-950 border border-rose-500/50 text-rose-300">18+ Uncensored</span>
                        </div>
                        <div class="text-center py-3">
                            <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-tr from-fuchsia-600 to-indigo-600 flex items-center justify-center text-white text-2xl shadow-xl shadow-fuchsia-600/50 mb-3 group-hover:scale-110 group-hover:rotate-3 transition-transform">
                                <i class="fa-solid fa-gamepad"></i>
                            </div>
                            <h4 class="text-sm font-black text-white group-hover:text-fuchsia-300 transition">Hentai Heroes: RPG</h4>
                            <p class="text-[11px] text-slate-300 mt-1 leading-snug">Play in browser &bull; Unlock uncensored harem scenes &bull; Free instant play</p>
                        </div>
                        <div class="mt-2 w-full py-2.5 rounded-xl bg-gradient-to-r from-fuchsia-600 to-indigo-600 group-hover:from-fuchsia-500 group-hover:to-indigo-500 text-white font-extrabold text-xs uppercase tracking-wider text-center shadow-lg shadow-fuchsia-600/40 transition">
                            <i class="fa-solid fa-play mr-1.5 text-[10px]"></i> Play Free Now
                        </div>
                    </a>
                @else
                    <!-- 728x90 Leaderboard High-CTR Working Banner -->
                    <a href="{{ $adUrl }}" target="_blank" rel="nofollow noopener noreferrer" class="group relative block w-full rounded-2xl overflow-hidden bg-gradient-to-r from-slate-900 via-purple-950/80 to-slate-900 border border-fuchsia-500/40 p-3 sm:px-6 shadow-xl hover:border-fuchsia-400 hover:shadow-fuchsia-600/20 transition-all duration-300">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-fuchsia-600 to-indigo-600 flex items-center justify-center text-white text-xl shadow-lg shadow-fuchsia-600/40 flex-shrink-0 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-rose-600 text-white">18+</span>
                                        <h4 class="text-xs sm:text-sm font-black text-white group-hover:text-fuchsia-300 transition truncate">
                                            Uncensored Hentai RPG - Play in Browser
                                        </h4>
                                    </div>
                                    <p class="text-[11px] text-slate-300 mt-0.5 hidden sm:block">
                                        Collect heroines, interactive animations, 100% free with zero downloads required!
                                    </p>
                                </div>
                            </div>
                            <div class="flex-shrink-0 w-full sm:w-auto text-center">
                                <span class="inline-flex items-center justify-center gap-2 w-full sm:w-auto px-5 py-2 rounded-xl bg-gradient-to-r from-fuchsia-600 to-indigo-600 group-hover:from-fuchsia-500 group-hover:to-indigo-500 text-white font-bold text-xs uppercase tracking-wider shadow-lg shadow-fuchsia-600/30 group-hover:scale-105 transition-transform">
                                    <span>Play Now</span>
                                    <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                @endif
            </div>
        @endif
    @endif
@endif
