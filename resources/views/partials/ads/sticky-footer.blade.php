@if(config('ads.enabled', true))
    @php
        $slotConfig = config('ads.slots.footer_sticky', []);
        $isEnabled = $slotConfig['enabled'] ?? true;
        $code = trim($slotConfig['code'] ?? '');
        $showPlaceholder = config('ads.show_placeholders', true);
        $adUrl = $slotConfig['url'] ?? config('ads.onclick.url', '');
        $isScriptUrl = !empty($adUrl) && (str_contains($adUrl, 'splash.php') || str_ends_with($adUrl, '.js') || (str_contains($adUrl, 'exoclick.com') && str_contains($adUrl, 'idzone=')));
        $bannerHref = ($isScriptUrl || empty($adUrl)) ? 'javascript:void(0)' : $adUrl;
        $bannerTarget = ($isScriptUrl || empty($adUrl)) ? '_self' : '_blank';
    @endphp

    @if($isEnabled && (!empty($code) || $showPlaceholder))
        <div id="sticky-footer-ad" class="fixed bottom-0 inset-x-0 z-40 bg-slate-950/95 backdrop-blur-md border-t border-slate-800/80 shadow-2xl transition-all duration-300">
            <div class="max-w-7xl mx-auto px-3 sm:px-6 py-1.5 sm:py-2 relative flex items-center justify-center">
                <!-- Close Button -->
                <button
                    type="button"
                    onclick="dismissStickyAd()"
                    class="absolute -top-3.5 right-3 sm:right-6 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-full w-7 h-7 flex items-center justify-center text-xs shadow-lg border border-slate-700 transition"
                    title="Close advertisement"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>

                <div class="w-full flex flex-col items-center justify-center max-w-full">
                    <span class="text-[8px] uppercase tracking-wider text-slate-500 font-semibold select-none">Advertisement</span>
                    
                    @if(!empty($code))
                        <div class="ad-content flex justify-center items-center max-h-[90px] w-full overflow-hidden">
                            {!! $code !!}
                        </div>
                    @else
                        <!-- Working Clickable Responsive Sticky Footer Banner -->
                        <a href="{{ $bannerHref }}" target="{{ $bannerTarget }}" rel="nofollow noopener noreferrer" class="play-trigger group flex items-center justify-between gap-2.5 sm:gap-4 w-full max-w-4xl px-2.5 py-1.5 sm:px-4 sm:py-2 rounded-xl bg-gradient-to-r from-purple-950/90 via-slate-900 to-indigo-950/90 border border-fuchsia-500/40 hover:border-fuchsia-400 shadow-lg transition">
                            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-gradient-to-tr from-fuchsia-600 to-indigo-600 text-white flex items-center justify-center text-xs shadow group-hover:scale-110 transition-transform flex-shrink-0">
                                    <i class="fa-solid fa-gamepad"></i>
                                </div>
                                <div class="text-left min-w-0">
                                    <p class="text-[11px] sm:text-xs font-black text-white group-hover:text-fuchsia-300 transition truncate">
                                        <span class="text-amber-400">🔥 18+ Hentai RPG</span> &bull; Free Play
                                    </p>
                                    <p class="text-[10px] text-slate-400 hidden sm:block truncate">Full HD animations &bull; Zero download &bull; Instant browser play.</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 sm:px-4 sm:py-1.5 rounded-lg bg-gradient-to-r from-fuchsia-600 to-indigo-600 text-white font-bold text-[11px] sm:text-xs shadow group-hover:scale-105 transition-transform flex-shrink-0">
                                Play Now <i class="fa-solid fa-chevron-right text-[8px] ml-0.5"></i>
                            </span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <script>
            function dismissStickyAd() {
                const el = document.getElementById('sticky-footer-ad');
                if (el) {
                    el.style.transform = 'translateY(100%)';
                    setTimeout(() => { el.remove(); }, 300);
                    sessionStorage.setItem('dismiss_sticky_ad', '1');
                }
            }
            if (sessionStorage.getItem('dismiss_sticky_ad') === '1') {
                const el = document.getElementById('sticky-footer-ad');
                if (el) el.remove();
            }
        </script>
    @endif
@endif
