@if(config('ads.enabled', true))
    @php
        $slotName = $slot ?? 'header';
        $slotConfig = config("ads.slots.{$slotName}", []);
        $isEnabled = $slotConfig['enabled'] ?? true;
        $code = trim($slotConfig['code'] ?? '');
        $showPlaceholder = config('ads.show_placeholders', true);
        $size = $slotConfig['size'] ?? 'Responsive';
        $customClass = $class ?? '';
        $adUrl = $slotConfig['url'] ?? config('ads.onclick.url', 'https://syndication.exoclick.com/splash.php?idzone=6096676');
        $slotConstName = 'ADS_' . strtoupper(str_replace('-', '_', $slotName)) . '_CODE';
    @endphp

    @if($isEnabled)
        @if(!empty($code))
            <div id="ad-slot-{{ $slotName }}" class="ad-slot ad-slot-{{ $slotName }} w-full my-3 flex flex-col items-center justify-center {{ $customClass }}">
                <div class="w-full flex items-center justify-between text-[9px] text-slate-500 uppercase tracking-widest font-semibold mb-1 px-1 max-w-full">
                    <span><i class="fa-solid fa-rectangle-ad text-fuchsia-400 mr-1"></i> Advertisement</span>
                    @if($slotName === 'header')
                        <button type="button" onclick="this.closest('.ad-slot').remove()" class="text-slate-500 hover:text-slate-300 transition text-[10px]" title="Dismiss ad">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    @endif
                </div>
                <!-- Responsive iframe / script wrapper to prevent mobile overflow -->
                <div class="ad-content w-full flex justify-center items-center overflow-x-auto sm:overflow-hidden max-w-full">
                    <div class="w-full max-w-full flex justify-center">
                        {!! $code !!}
                    </div>
                </div>
            </div>
        @elseif($showPlaceholder)
            <div id="ad-placeholder-{{ $slotName }}" class="ad-placeholder ad-placeholder-{{ $slotName }} w-full my-3 flex flex-col items-center justify-center {{ $customClass }}">
                <div class="w-full flex items-center justify-between text-[9px] text-slate-500 uppercase tracking-widest font-semibold mb-1 px-1 max-w-full">
                    <span class="inline-flex items-center gap-1">
                        <i class="fa-solid fa-rectangle-ad text-fuchsia-400"></i>
                        <span>Sponsored</span>
                    </span>
                    <span class="sr-only">{{ $slotConstName }}</span>
                    @if($slotName === 'header')
                        <button type="button" onclick="this.closest('.ad-placeholder').remove()" class="text-slate-500 hover:text-slate-300 transition text-[10px] px-1" title="Dismiss banner">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    @endif
                </div>

                @if($slotName === 'watch_sidebar')
                    <!-- 300x250 Medium Rectangle High-CTR Responsive Banner -->
                    <a href="{{ $adUrl }}" target="_blank" rel="nofollow noopener noreferrer" class="group relative block w-full rounded-2xl overflow-hidden bg-gradient-to-br from-purple-950 via-slate-900 to-indigo-950 border border-fuchsia-500/40 p-4 sm:p-5 shadow-2xl hover:border-fuchsia-400 hover:scale-[1.01] transition-all duration-300">
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-fuchsia-950 border border-fuchsia-500/50 text-fuchsia-300">
                                <i class="fa-solid fa-fire text-amber-400"></i> Hot Game
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-rose-950 border border-rose-500/50 text-rose-300">18+ Uncensored</span>
                        </div>
                        <div class="text-center py-2 sm:py-3">
                            <div class="w-14 h-14 sm:w-16 sm:h-16 mx-auto rounded-2xl bg-gradient-to-tr from-fuchsia-600 to-indigo-600 flex items-center justify-center text-white text-xl sm:text-2xl shadow-xl shadow-fuchsia-600/50 mb-3 group-hover:scale-110 group-hover:rotate-3 transition-transform">
                                <i class="fa-solid fa-gamepad"></i>
                            </div>
                            <h4 class="text-xs sm:text-sm font-black text-white group-hover:text-fuchsia-300 transition">Hentai Heroes: RPG</h4>
                            <p class="text-[11px] text-slate-300 mt-1 leading-snug">Play in browser &bull; Unlock uncensored harem scenes &bull; Free instant play</p>
                        </div>
                        <div class="mt-2 w-full py-2.5 rounded-xl bg-gradient-to-r from-fuchsia-600 to-indigo-600 group-hover:from-fuchsia-500 group-hover:to-indigo-500 text-white font-extrabold text-xs uppercase tracking-wider text-center shadow-lg shadow-fuchsia-600/40 transition">
                            <i class="fa-solid fa-play mr-1.5 text-[10px]"></i> Play Free Now
                        </div>
                    </a>
                @else
                    <!-- 728x90 Leaderboard / Mobile-Optimized Responsive Banner -->
                    <a href="{{ $adUrl }}" target="_blank" rel="nofollow noopener noreferrer" class="group relative block w-full rounded-2xl overflow-hidden bg-gradient-to-r from-slate-900 via-purple-950/80 to-slate-900 border border-fuchsia-500/40 p-2.5 sm:p-3.5 sm:px-6 shadow-xl hover:border-fuchsia-400 hover:shadow-fuchsia-600/20 transition-all duration-300">
                        <div class="flex items-center justify-between gap-2.5 sm:gap-4">
                            <!-- Left: Icon & Text -->
                            <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0">
                                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-tr from-fuchsia-600 to-indigo-600 flex items-center justify-center text-white text-sm sm:text-xl shadow-lg shadow-fuchsia-600/40 flex-shrink-0 group-hover:scale-105 transition-transform">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 sm:gap-2">
                                        <span class="px-1.5 py-0.5 rounded text-[8px] sm:text-[9px] font-black uppercase tracking-wider bg-rose-600 text-white flex-shrink-0">18+</span>
                                        <h4 class="text-xs sm:text-sm font-black text-white group-hover:text-fuchsia-300 transition truncate">
                                            Uncensored Hentai RPG
                                        </h4>
                                    </div>
                                    <p class="text-[10px] sm:text-[11px] text-slate-300 mt-0.5 truncate hidden xs:block">
                                        Interactive browser anime game &bull; 100% Free &bull; No Download
                                    </p>
                                </div>
                            </div>

                            <!-- Right: CTA Button -->
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 sm:px-5 sm:py-2 rounded-xl bg-gradient-to-r from-fuchsia-600 to-indigo-600 group-hover:from-fuchsia-500 group-hover:to-indigo-500 text-white font-bold text-[11px] sm:text-xs uppercase tracking-wider shadow-lg shadow-fuchsia-600/30 group-hover:scale-105 transition-transform">
                                    <span>Play Now</span>
                                    <i class="fa-solid fa-chevron-right text-[9px] hidden sm:inline"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                @endif
            </div>
        @endif
    @endif
@endif
