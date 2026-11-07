@extends('layouts.app')

@section('title', 'StreamFlix - Watch HD Videos')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Hero / Search Section -->
    <div class="relative rounded-3xl overflow-hidden bg-gradient-to-b from-slate-900/90 via-slate-900/60 to-slate-950 border border-slate-800 p-6 sm:p-10 mb-10 shadow-2xl">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-fuchsia-600/15 via-transparent to-transparent pointer-events-none"></div>

        <div class="relative z-10 max-w-2xl mx-auto text-center">
            <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-white mb-3">
                Watch HD Videos
            </h1>
            <p class="text-slate-400 text-sm sm:text-base mb-6">
                Stream movies, trailers, and clips instantly with full playback controls.
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

    <!-- CATEGORIES FILTER BAR -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-3 px-1">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-fuchsia-400"></i>
                <span>Categories</span>
            </h3>
            @if($currentCategory || request('q'))
                <a href="{{ route('home') }}" class="text-xs font-medium text-fuchsia-400 hover:text-fuchsia-300 flex items-center gap-1 transition">
                    <i class="fa-solid fa-rotate-left text-[10px]"></i> View All Videos
                </a>
            @endif
        </div>

        <!-- Scrollable / Flex Category Pills -->
        <div class="flex items-center gap-2.5 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">
            <!-- All Category Pill -->
            <a
                href="{{ route('home', request('q') ? ['q' => request('q')] : []) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all duration-200 border {{ empty($currentCategory) ? 'bg-gradient-to-r from-fuchsia-600 to-indigo-600 text-white shadow-lg shadow-fuchsia-600/30 border-fuchsia-400/50 scale-105' : 'bg-slate-900/90 text-slate-300 hover:text-white hover:bg-slate-800 border-slate-800 hover:border-slate-700' }}"
            >
                <i class="fa-solid fa-compass text-xs"></i>
                <span>All</span>
                <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] {{ empty($currentCategory) ? 'bg-black/30 text-white' : 'bg-slate-800 text-slate-400' }}">
                    {{ $totalCount }}
                </span>
            </a>

            @foreach($categories as $slug => $cat)
                @php
                    $isActive = ($currentCategory === $slug);
                    $count = $categoryCounts[$slug] ?? 0;
                @endphp
                <a
                    href="{{ route('home', array_filter(['category' => $slug, 'q' => request('q')])) }}"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all duration-200 border {{ $isActive ? 'bg-gradient-to-r from-fuchsia-600 to-indigo-600 text-white shadow-lg shadow-fuchsia-600/30 border-fuchsia-400/50 scale-105 font-bold' : 'bg-slate-900/90 text-slate-300 hover:text-white hover:bg-slate-800 border-slate-800 hover:border-slate-700' }}"
                >
                    <i class="{{ $cat['icon'] }} text-xs {{ $isActive ? 'text-white' : 'text-fuchsia-400' }}"></i>
                    <span>{{ $cat['name'] }}</span>
                    @if($count > 0)
                        <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] {{ $isActive ? 'bg-black/30 text-white' : 'bg-slate-800 text-slate-400' }}">
                            {{ $count }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    </div>

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
                <div class="group relative rounded-2xl bg-slate-900/80 border border-slate-800 overflow-hidden card-glow transition duration-300 flex flex-col">
                    
                    <!-- Thumbnail container with Centered Play Button on hover -->
                    <a href="{{ route('videos.watch', $video->slug ?: $video->id) }}" class="relative aspect-video w-full overflow-hidden bg-slate-950 block">
                        @if($video->thumbnail_url)
                            <img
                                src="{{ $video->thumbnail_url }}"
                                alt="{{ $video->title }}"
                                loading="lazy"
                                referrerpolicy="no-referrer"
                                onerror="if(!this.dataset.proxied){this.dataset.proxied='1'; this.src='{{ route('image.proxy') }}?url=' + encodeURIComponent('{{ $video->thumbnail_url }}');} else {this.src='https://placehold.co/640x360/1e293b/cbd5e1?text=Video';}"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                            >
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-slate-900 via-indigo-950/40 to-slate-900 text-slate-600 group-hover:scale-105 transition duration-500">
                                <i class="fa-solid fa-play text-3xl mb-2 text-fuchsia-500/40"></i>
                            </div>
                        @endif

                        <!-- Duration Badge -->
                        @if($video->duration)
                            <div class="absolute bottom-2.5 right-2.5">
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold tracking-wider bg-black/80 text-white backdrop-blur border border-white/10 shadow">
                                    {{ $video->duration }}
                                </span>
                            </div>
                        @endif

                        <!-- Centered Play Button Overlay -->
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition duration-300">
                            <div class="w-14 h-14 rounded-full bg-gradient-to-tr from-fuchsia-600 to-indigo-600 text-white flex items-center justify-center shadow-xl shadow-fuchsia-600/50 transform scale-75 group-hover:scale-100 transition duration-300">
                                <i class="fa-solid fa-play text-lg ml-0.5"></i>
                            </div>
                        </div>
                    </a>

                    <!-- Card Body -->
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <!-- Onclick Category Badges on Card -->
                            <div class="flex flex-wrap items-center gap-1.5 mb-2">
                                @foreach(array_slice($video->categories, 0, 2) as $cardCat)
                                    <a
                                        href="{{ route('home', ['category' => $cardCat['slug']]) }}"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-800/90 hover:bg-fuchsia-950/60 text-slate-300 hover:text-fuchsia-300 border border-slate-700/60 hover:border-fuchsia-700/50 transition"
                                    >
                                        <i class="{{ $cardCat['icon'] }} text-[9px] text-fuchsia-400"></i>
                                        <span>{{ $cardCat['name'] }}</span>
                                    </a>
                                @endforeach
                            </div>

                            <!-- Title -->
                            <h3 class="font-semibold text-slate-100 text-sm line-clamp-2 hover:text-fuchsia-300 transition">
                                <a href="{{ route('videos.watch', $video->slug ?: $video->id) }}">
                                    {{ $video->title }}
                                </a>
                            </h3>
                        </div>

                        <!-- Card Footer -->
                        <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between gap-2">
                            <a
                                href="{{ route('videos.watch', $video->slug ?: $video->id) }}"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-gradient-to-r from-fuchsia-600/20 to-indigo-600/20 hover:from-fuchsia-600 hover:to-indigo-600 text-fuchsia-300 hover:text-white font-semibold text-xs border border-fuchsia-500/30 hover:border-transparent transition"
                            >
                                <i class="fa-solid fa-play text-[10px]"></i> Watch Video
                            </a>

                            <!-- Delete single card button -->
                            <form action="{{ route('videos.destroy', $video->id) }}" method="POST" onsubmit="return confirm('Remove this video?');">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    title="Delete"
                                    class="p-2 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-950/40 transition"
                                >
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-10">
            {{ $videos->links() }}
        </div>

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

</div>
@endsection
