<!-- Main Content Leaderboard Banner -->
@include('partials.ads.slot', ['slot' => 'header', 'class' => 'mb-8'])

<!-- Header & Stats Bar -->
<div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-800/80">
    <div class="flex items-center gap-3">
        <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-fire text-fuchsia-400"></i>
            <span>
                @if($currentCategory && isset($categories[$currentCategory]))
                    {{ $categories[$currentCategory]['name'] }} Videos
                @elseif(request('q'))
                    Search Results: "{{ request('q') }}"
                @else
                    Featured Videos
                @endif
            </span>
        </h2>
        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-slate-300 border border-slate-700">
            {{ $videos->total() }}
        </span>
    </div>

    @if($totalCount > 0)
        <form action="{{ route('videos.clearAll') }}" method="POST" onsubmit="return confirm('Clear all videos?');">
            @csrf
            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-500 hover:text-rose-400 hover:bg-rose-950/30 transition">
                <i class="fa-solid fa-trash-can mr-1"></i> Clear List
            </button>
        </form>
    @endif
</div>

<!-- Video Cards Grid -->
@if($videos->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($videos as $video)
            @include('videos.partials.video-card', ['video' => $video])

            {{-- Separated Full-Width Banner Ad Break between rows of cards --}}
            @if($loop->iteration === 8)
                @include('partials.ads.grid-feed')
            @endif
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-10">
        {{ $videos->links() }}
    </div>

    <!-- Bottom Homepage Leaderboard Banner Ad -->
    @include('partials.ads.slot', ['slot' => 'home_bottom', 'class' => 'mt-10 mb-4'])

@else
    <!-- Empty State -->
    <div class="rounded-3xl border border-dashed border-slate-800 bg-slate-900/30 p-12 text-center max-w-lg mx-auto">
        <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-fuchsia-600/10 text-fuchsia-400 flex items-center justify-center text-2xl border border-fuchsia-500/20">
            <i class="fa-solid fa-film"></i>
        </div>
        <h3 class="text-lg font-bold text-white mb-2">No Videos Available</h3>
        <p class="text-slate-400 text-sm">
            Add a link to <code class="px-2 py-0.5 rounded bg-slate-800 text-fuchsia-300 font-mono text-xs">SCRAPE_TARGET_URL=...</code> in your <code class="text-white font-mono">.env</code> file. Videos will sync automatically when you refresh.
        </p>
    </div>
@endif
