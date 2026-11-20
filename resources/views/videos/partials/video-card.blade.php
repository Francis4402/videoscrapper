<!-- Video Card Component -->
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
