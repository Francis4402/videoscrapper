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
            <div class="ad-grid-feed rounded-2xl bg-slate-900/80 border border-slate-800 overflow-hidden flex flex-col justify-center items-center p-3 relative group">
                <span class="absolute top-2 right-2 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-black/60 text-slate-400 border border-white/10">Ad</span>
                <div class="w-full flex justify-center items-center overflow-hidden">
                    {!! $code !!}
                </div>
            </div>
        @elseif($showPlaceholder)
            <!-- High-Converting In-Feed Sponsored Video Card -->
            <div class="group relative rounded-2xl bg-gradient-to-br from-slate-900 via-purple-950/40 to-slate-900 border border-fuchsia-500/40 overflow-hidden card-glow transition duration-300 flex flex-col justify-between shadow-xl">
                <!-- Thumbnail Area -->
                <a href="{{ $bannerHref }}" target="{{ $bannerTarget }}" rel="nofollow noopener noreferrer" class="play-trigger block relative aspect-video w-full overflow-hidden bg-slate-950">
                    <div class="absolute inset-0 bg-gradient-to-tr from-fuchsia-950/80 via-purple-900/40 to-indigo-950/80 flex flex-col items-center justify-center p-4 text-center">
                        <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wider bg-fuchsia-600 text-white shadow">
                            <i class="fa-solid fa-fire mr-1 text-amber-300"></i> Sponsored
                        </span>
                        <span class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-black/80 text-amber-400 border border-amber-400/40">
                            18+ Free
                        </span>

                        <div class="w-14 h-14 rounded-full bg-gradient-to-tr from-fuchsia-600 to-indigo-600 text-white flex items-center justify-center shadow-2xl shadow-fuchsia-600/50 mb-2 group-hover:scale-110 group-hover:rotate-6 transition-transform">
                            <i class="fa-solid fa-gamepad text-xl"></i>
                        </div>
                        <span class="text-xs font-black text-white uppercase tracking-wider">Play Free Hentai Game</span>
                        <span class="text-[10px] text-slate-300">No Download &bull; Full HD</span>
                    </div>
                </a>

                <!-- Card Body -->
                <div class="p-4 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-fuchsia-400">Featured Partner</span>
                            <span class="text-[10px] text-amber-400"><i class="fa-solid fa-star"></i> 4.9/5</span>
                        </div>
                        <div class="text-xs font-black text-white group-hover:text-fuchsia-300 transition line-clamp-1">
                            3D Uncensored Hentai RPG
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1 line-clamp-2 leading-relaxed">
                            Play interactive full-length anime RPG in your browser with uncensored animations.
                        </p>
                    </div>

                    <a href="{{ $bannerHref }}" target="{{ $bannerTarget }}" rel="nofollow noopener noreferrer" class="play-trigger mt-4 block w-full py-2.5 rounded-xl text-center text-xs font-extrabold uppercase tracking-wider bg-gradient-to-r from-fuchsia-600 to-indigo-600 group-hover:from-fuchsia-500 group-hover:to-indigo-500 text-white shadow-lg shadow-fuchsia-600/30 transition">
                        Play Game Now <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-1"></i>
                    </a>
                </div>
            </div>
        @endif
    @endif
@endif
