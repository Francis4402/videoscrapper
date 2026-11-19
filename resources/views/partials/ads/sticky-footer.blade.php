@if(config('ads.enabled', true))
    @php
        $slotConfig = config('ads.slots.footer_sticky', []);
        $isEnabled = $slotConfig['enabled'] ?? true;
        $code = trim($slotConfig['code'] ?? '');
        $showPlaceholder = config('ads.show_placeholders', true);
        $adUrl = $slotConfig['url'] ?? config('ads.onclick.url', 'https://syndication.exoclick.com/splash.php?idzone=default');
    @endphp

    @if($isEnabled && (!empty($code) || $showPlaceholder))
        <div id="sticky-footer-ad" class="fixed bottom-0 inset-x-0 z-40 bg-slate-950/95 backdrop-blur-md border-t border-slate-800 shadow-2xl transition-all duration-300">
            <div class="max-w-7xl mx-auto px-4 py-2 relative flex items-center justify-center">
                <!-- Close Button -->
                <button
                    type="button"
                    onclick="dismissStickyAd()"
                    class="absolute -top-3.5 right-4 sm:right-6 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white rounded-full w-7 h-7 flex items-center justify-center text-xs shadow-lg border border-slate-700 transition"
                    title="Close advertisement"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>

                <div class="w-full flex flex-col items-center justify-center">
                    <span class="text-[8px] uppercase tracking-wider text-slate-500 mb-0.5 font-semibold">Advertisement</span>
                    
                    @if(!empty($code))
                        <div class="ad-content flex justify-center max-h-[90px] overflow-hidden">
                            {!! $code !!}
                        </div>
                    @else
                        <!-- Working Clickable Sticky Footer Banner -->
                        <a href="{{ $adUrl }}" target="_blank" rel="nofollow noopener noreferrer" class="group flex items-center justify-between gap-3 w-full max-w-4xl px-4 py-2 rounded-xl bg-gradient-to-r from-purple-950/90 via-slate-900 to-indigo-950/90 border border-fuchsia-500/40 hover:border-fuchsia-400 shadow-lg transition">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-fuchsia-600 to-indigo-600 text-white flex items-center justify-center text-xs shadow group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-gamepad"></i>
                                </div>
                                <div class="text-left">
                                    <p class="text-xs font-black text-white group-hover:text-fuchsia-300 transition">
                                        <span class="text-amber-400">🔥 18+ Uncensored Hentai RPG</span> &bull; Play Free Online
                                    </p>
                                    <p class="text-[10px] text-slate-400 hidden sm:block">Full HD animations, zero download, instant browser gameplay.</p>
                                </div>
                            </div>
                            <span class="px-4 py-1.5 rounded-lg bg-gradient-to-r from-fuchsia-600 to-indigo-600 text-white font-bold text-xs shadow group-hover:scale-105 transition-transform flex-shrink-0">
                                Play Now <i class="fa-solid fa-arrow-up-right-from-square text-[9px] ml-1"></i>
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

            document.addEventListener('DOMContentLoaded', function() {
                if (sessionStorage.getItem('dismiss_sticky_ad') === '1') {
                    const el = document.getElementById('sticky-footer-ad');
                    if (el) el.remove();
                }
            });
        </script>
    @endif
@endif
