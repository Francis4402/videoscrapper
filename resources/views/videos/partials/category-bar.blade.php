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
                href="{{ request('q') ? route('home', ['category' => $slug, 'q' => request('q')]) : route('videos.category', $slug) }}"
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
