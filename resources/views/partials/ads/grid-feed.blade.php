@if(config('ads.enabled', true))
    @php
        $slotConfig = config('ads.slots.grid_feed', []);
        $isEnabled = $slotConfig['enabled'] ?? true;
        $code = trim($slotConfig['code'] ?? '');
        $showPlaceholder = config('ads.show_placeholders', true);
        $adUrl = $slotConfig['url'] ?? config('ads.onclick.url', '');
        $isScriptUrl = !empty($adUrl) && (str_contains($adUrl, 'splash.php') || str_ends_with($adUrl, '.js') || (str_contains($adUrl, 'exoclick.com') && str_contains($adUrl, 'idzone=')));
        $bannerHref = ($isScriptUrl || empty($adUrl)) ? 'javascript:void(0)' : $adUrl;
        $bannerTarget = ($isScriptUrl || empty($adUrl)) ? '_self' : '_blank';
    @endphp

    @if($isEnabled)
        @if(!empty($code))
            <!-- Separated Full-Width In-Feed Banner Ad Break -->
            <div class="col-span-full w-full my-4">
                <div class="w-full max-w-full rounded-2xl bg-[#090c16]/90 border border-slate-800/80 hover:border-fuchsia-500/30 p-2.5 sm:p-4 shadow-2xl backdrop-blur-sm transition-all duration-300">
                    <div class="w-full flex items-center justify-between text-[10px] text-slate-400 font-semibold mb-2 px-1">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-fuchsia-400 animate-pulse"></span>
                            <span class="tracking-wider uppercase text-slate-300 font-bold text-[9px]">SPONSORED</span>
                        </span>
                        <span class="text-[9px] uppercase tracking-wider text-slate-500 font-medium">Advertisement</span>
                    </div>
                    <!-- Responsive Separated Ad Content -->
                    <div class="ad-content w-full flex justify-center items-center overflow-hidden max-w-full">
                        <div class="w-full max-w-full flex justify-center">
                            {!! $code !!}
                        </div>
                    </div>
                </div>
            </div>
        @elseif($showPlaceholder)
            <!-- Separated Full-Width In-Feed Sponsored Banner -->
            <div class="col-span-full w-full my-4">
                <a href="{{ $bannerHref }}" target="{{ $bannerTarget }}" rel="nofollow noopener noreferrer" class="play-trigger group relative block w-full rounded-2xl overflow-hidden bg-gradient-to-r from-purple-950/90 via-slate-900 to-indigo-950/90 border border-fuchsia-500/40 hover:border-fuchsia-400 p-4 sm:p-5 shadow-2xl transition duration-300">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-tr from-fuchsia-600 to-indigo-600 flex items-center justify-center text-white text-xl sm:text-2xl shadow-xl shadow-fuchsia-600/50 group-hover:scale-110 transition-transform shrink-0">
                                <i class="fa-solid fa-gamepad"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-fuchsia-600 text-white shadow">
                                        <i class="fa-solid fa-fire mr-1 text-amber-300"></i> Sponsored
                                    </span>
                                    <span class="text-xs text-amber-400 font-bold"><i class="fa-solid fa-star"></i> 4.9/5 Partner</span>
                                </div>
                                <h4 class="text-sm sm:text-base font-black text-white group-hover:text-fuchsia-300 transition line-clamp-1">
                                    3D Uncensored Hentai RPG &bull; Play Free in Browser
                                </h4>
                                <p class="text-xs text-slate-400 mt-0.5 hidden sm:block">
                                    Interactive full-length anime RPG &bull; No downloads &bull; Instant HD streaming.
                                </p>
                            </div>
                        </div>
                        <span class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-fuchsia-600 to-indigo-600 text-white font-bold text-xs uppercase tracking-wider shadow-lg group-hover:opacity-90 transition shrink-0">
                            Play Game Now <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-1"></i>
                        </span>
                    </div>
                </a>
            </div>
        @endif
    @endif
@endif
