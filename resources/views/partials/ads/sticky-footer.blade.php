@if(config('ads.enabled', true))
    @php
        $slotConfig = config('ads.slots.footer_sticky', []);
        $isEnabled = $slotConfig['enabled'] ?? true;
        $code = trim($slotConfig['code'] ?? '');
        $showPlaceholder = config('ads.show_placeholders', true);
    @endphp

    @if($isEnabled && (!empty($code) || $showPlaceholder))
        <div id="sticky-footer-ad" class="fixed bottom-0 inset-x-0 z-40 bg-slate-950/95 backdrop-blur-md border-t border-slate-800/90 shadow-2xl transition-all duration-300">
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
                        <div class="py-1 px-4 text-center text-xs text-slate-400 flex items-center gap-2">
                            <i class="fa-solid fa-rectangle-ad text-fuchsia-400"></i>
                            <span>Sticky Bottom Banner (728x90 / 320x50) &bull; Set <code class="text-fuchsia-300 font-mono">ADS_FOOTER_STICKY_CODE</code> in .env</span>
                        </div>
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
