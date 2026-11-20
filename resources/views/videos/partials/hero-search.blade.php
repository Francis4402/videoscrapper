<!-- Hero / Search Section -->
<div class="relative rounded-3xl overflow-hidden bg-gradient-to-b from-slate-900/90 via-slate-900/60 to-slate-950 border border-slate-800 p-6 sm:p-10 mb-10 shadow-2xl">
    <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-fuchsia-600/15 via-transparent to-transparent pointer-events-none"></div>

    <div class="relative z-10 max-w-2xl mx-auto text-center">
        <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white mb-3">
            @if($currentCategory && isset($categories[$currentCategory]))
                {{ $categories[$currentCategory]['name'] }} Videos
            @elseif(request('q'))
                {{ e(request('q')) }} Hentai Videos
            @else
                Hentai - Watch Free HD Videos & Anime
            @endif
        </h1>
        <p class="text-slate-400 text-sm sm:text-base mb-6">
            Stream uncensored hentai, gangbang hentai, cartoon hentai, and 3D anime series with ultra-fast direct playback and zero buffering.
        </p>

        <!-- Search Form -->
        <form action="{{ route('home') }}" method="GET" class="relative">
            @if($currentCategory)
                <input type="hidden" name="category" value="{{ $currentCategory }}">
            @endif
            <div class="flex items-center p-2 rounded-2xl bg-slate-950/90 border border-slate-700/80 shadow-inner focus-within:border-fuchsia-500 focus-within:ring-2 focus-within:ring-fuchsia-500/20 transition-all">
                <i class="fa-solid fa-magnifying-glass text-slate-500 ml-3 mr-2 text-base"></i>
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="{{ $currentCategory && isset($categories[$currentCategory]) ? 'Search in ' . $categories[$currentCategory]['name'] . '...' : 'Search videos by title or keyword...' }}"
                    class="w-full bg-transparent border-0 text-white placeholder-slate-500 text-sm sm:text-base focus:ring-0 focus:outline-none"
                >
                @if(request('q') || $currentCategory)
                    <a href="{{ route('home') }}" class="p-2 text-slate-400 hover:text-white mr-1" title="Clear all filters">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                @endif
                <button
                    type="submit"
                    class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-fuchsia-600 to-indigo-600 hover:from-fuchsia-500 hover:to-indigo-500 text-white font-semibold text-sm shadow-lg shadow-fuchsia-600/30 transition whitespace-nowrap"
                >
                    Search
                </button>
            </div>
        </form>
    </div>
</div>
