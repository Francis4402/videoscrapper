@extends('layouts.app')

@section('title', $video->seo_title)
@section('meta_description', $video->seo_summary)
@section('meta_keywords', addslashes($video->clean_title) . ', watch ' . addslashes($video->clean_title) . ', watch hentai, watch hentai online, free hentai, hentai streaming, best hentai sites, uncensored hentai, gangbang hentai, cartoon hentai, 3d hentai, full hentai episode, english sub hentai, hentai tube, hd anime, hentaiflex')
@section('og_type', 'video.other')
@section('og_image', $video->thumbnail_url ?: '')
@section('canonical_url', route('videos.watch', $video->slug ?: $video->id))

@push('schema')
@php
    $parts = array_map('intval', explode(':', $video->duration ?? ''));
    $totalSeconds = count($parts) === 3 ? ($parts[0] * 3600 + $parts[1] * 60 + $parts[2]) : (count($parts) === 2 ? ($parts[0] * 60 + $parts[1]) : ($parts[0] ?? null));
    $thumb = $video->thumbnail_url;
    if ($thumb && Str::startsWith($thumb, '//')) {
        $thumb = 'https:' . $thumb;
    } elseif ($thumb && !Str::startsWith($thumb, 'http')) {
        $thumb = url($thumb);
    }
    $thumb = $thumb ?: url('/favicon.ico');
@endphp
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "VideoObject",
    "name": {!! json_encode($video->seo_title) !!},
    "description": {!! json_encode($video->seo_summary) !!},
    "keywords": "watch hentai, free hentai, watch hentai online, hentai streaming, best hentai sites, uncensored hentai, gangbang hentai, cartoon hentai, 3d hentai, english sub hentai, full hentai episodes, hentaiflex",
    "genre": ["Hentai", "Anime", "Animation"],
    "isFamilyFriendly": "false",
    "thumbnailUrl": [
        {!! json_encode($thumb) !!}
    ],
    "uploadDate": "{{ $video->created_at->toIso8601String() }}",
    @if($totalSeconds)
    "duration": "PT{{ $totalSeconds }}S",
    @endif
    "contentUrl": "{{ route('stream.proxy', $video->id) }}",
    "embedUrl": "{{ route('videos.embed', $video->slug ?: $video->id) }}"
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {
            "@type": "ListItem",
            "position": 1,
            "name": "Hentai Videos",
            "item": "{{ route('home') }}"
        },
        {
            "@type": "ListItem",
            "position": 2,
            "name": "{{ addslashes($video->title) }}",
            "item": "{{ route('videos.watch', $video->slug ?: $video->id) }}"
        }
    ]
}
</script>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <!-- Top Navigation Breadcrumb -->
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white transition">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            <span>Back to Videos</span>
        </a>

        @if($video->duration)
            <span class="px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 text-xs border border-slate-700">
                <i class="fa-regular fa-clock mr-1 text-fuchsia-400"></i> {{ $video->duration }}
            </span>
        @endif
    </div>

    <!-- Main Player Area -->
    <div id="player-container-wrapper" class="w-full transition-all duration-300">
        <div class="relative w-full rounded-2xl overflow-hidden bg-black shadow-2xl border border-slate-800/80 aspect-video">
            
            @if($videoType === 'embed' && empty($resolvedStreamUrl))
                <!-- 3rd-Party Embed Player (e.g. YouTube) -->
                <iframe
                    id="embed-player"
                    src="{{ $video->embed_url }}"
                    title="{{ $video->title }}"
                    class="w-full h-full border-0 absolute inset-0 z-10"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share; fullscreen"
                    allowfullscreen
                ></iframe>

            @else
                <!-- Native Full-Length HTML5 / HLS Video Player -->
                <video
                    id="video-player"
                    playsinline
                    controls
                    preload="metadata"
                    poster="{{ $video->thumbnail_url ?: '' }}"
                    class="w-full h-full object-contain absolute inset-0 z-10 bg-black"
                    @if($videoType !== 'hls' && !str_contains($resolvedStreamUrl, '.m3u8'))
                        src="{{ route('stream.proxy', $video->id) }}"
                    @endif
                >
                    @if($videoType !== 'hls' && !str_contains($resolvedStreamUrl, '.m3u8'))
                        <source src="{{ route('stream.proxy', $video->id) }}" type="video/mp4">
                        <source src="{{ $resolvedStreamUrl ?: $video->video_url }}" type="video/mp4">
                    @endif
                    Your browser does not support HTML5 video.
                </video>
            @endif

            <!-- CENTER COVER OVERLAY: Thumbnail Image & Centered Play Button -->
            <div
                id="player-cover"
                onclick="startPlayback()"
                class="absolute inset-0 z-30 flex flex-col items-center justify-center bg-black cursor-pointer group/cover transition-all duration-300 select-none"
            >
                <!-- Thumbnail Backdrop -->
                @if($video->thumbnail_url)
                    <img
                        src="{{ $video->thumbnail_url }}"
                        alt="{{ $video->title }}"
                        referrerpolicy="no-referrer"
                        class="absolute inset-0 w-full h-full object-cover opacity-50 group-hover/cover:scale-105 transition-transform duration-700"
                        onerror="if(!this.dataset.proxied){this.dataset.proxied='1'; this.src='{{ route('image.proxy') }}?url=' + encodeURIComponent('{{ $video->thumbnail_url }}');}"
                    >
                @endif

                <!-- Dark Vignette Gradient -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-black/60 pointer-events-none"></div>

                <!-- Big Centered Play Button -->
                <div class="relative z-10 flex flex-col items-center justify-center pointer-events-none">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gradient-to-tr from-fuchsia-600 to-indigo-600 text-white flex items-center justify-center shadow-2xl shadow-fuchsia-600/50 play-pulse group-hover/cover:scale-110 active:scale-95 transition-transform duration-300">
                        <i class="fa-solid fa-play text-2xl sm:text-3xl ml-1 text-white"></i>
                    </div>
                    <span class="mt-4 px-4 py-1.5 rounded-full bg-black/70 backdrop-blur border border-white/10 text-xs sm:text-sm font-semibold tracking-wider uppercase text-white/90 shadow">
                        Click to Watch Full Video
                    </span>
                </div>
            </div>

        </div>
    </div>

    <!-- Video Controls & Action Bar -->
    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 p-4 rounded-xl bg-slate-900/80 border border-slate-800">
        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                id="btn-watch-favorite"
                onclick="toggleCurrentFavorite()"
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center gap-1.5 transition"
            >
                <i id="fav-icon" class="fa-regular fa-heart text-rose-400"></i>
                <span id="fav-text">Favorite</span>
            </button>

            <button
                type="button"
                id="btn-theater"
                onclick="toggleTheaterMode()"
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center gap-1.5 transition"
            >
                <i class="fa-solid fa-expand text-fuchsia-400"></i>
                <span id="theater-text">Theater</span>
            </button>

            <button
                type="button"
                onclick="openShareModal()"
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center gap-1.5 transition"
            >
                <i class="fa-solid fa-share-nodes text-indigo-400"></i>
                <span>Share &amp; Embed</span>
            </button>

            <a
                href="{{ route('videos.random') }}"
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-gradient-to-r from-fuchsia-600/30 to-pink-600/30 text-fuchsia-300 border border-fuchsia-500/40 hover:border-fuchsia-400 hover:text-white flex items-center gap-1.5 transition shadow-sm"
                title="Watch another random video"
            >
                <i class="fa-solid fa-shuffle text-pink-400 text-xs"></i>
                <span>Next Random</span>
            </a>

            @if($video->video_url)
                <a
                    href="{{ route('stream.proxy', $video->id) }}"
                    download="{{ Str::slug($video->title) }}.mp4"
                    class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center gap-1.5 transition"
                >
                    <i class="fa-solid fa-download text-emerald-400"></i>
                    <span>Download</span>
                </a>
            @endif
        </div>

        <div class="text-xs text-slate-400">
            <span><i class="fa-regular fa-calendar mr-1"></i> {{ $video->created_at->format('M d, Y') }}</span>
        </div>
    </div>

    <!-- Below Player Ad Slot (728x90 / 300x250) -->
    @include('partials.ads.slot', ['slot' => 'watch_below_player', 'class' => 'mt-6'])

    <!-- Video Details & Related Grid -->
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Video Details -->
        <div class="lg:col-span-2 space-y-4">
            <!-- Category Badges for Onclick Search -->
            <div class="flex flex-wrap items-center gap-2">
                @foreach($video->categories as $vcat)
                    <a
                        href="{{ route('home', ['category' => $vcat['slug']]) }}"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-slate-800/80 hover:bg-fuchsia-950/60 text-slate-300 hover:text-fuchsia-300 border border-slate-700/80 hover:border-fuchsia-600/50 transition"
                    >
                        <i class="{{ $vcat['icon'] }} text-xs text-fuchsia-400"></i>
                        <span>{{ $vcat['name'] }}</span>
                    </a>
                @endforeach
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                {{ $video->title }}
            </h1>

            <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 text-slate-300 text-sm leading-relaxed">
                <p class="whitespace-pre-line">{{ $video->seo_description }}</p>
            </div>
        </div>

        <!-- Right: Related Videos -->
        <div class="space-y-4">
            <!-- Sidebar Ad Slot (300x250) -->
            @include('partials.ads.slot', ['slot' => 'watch_sidebar'])

            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-fire text-fuchsia-400"></i> More Videos
            </h3>

            @if($relatedVideos->count() > 0)
                <div class="space-y-3">
                    @foreach($relatedVideos as $rel)
                        <a href="{{ route('videos.watch', $rel->slug ?: $rel->id) }}" class="flex items-center gap-3 p-2 rounded-xl bg-slate-900/60 hover:bg-slate-800/80 border border-slate-800 group transition">
                            <div class="relative w-28 aspect-video rounded-lg overflow-hidden bg-slate-950 flex-shrink-0">
                                @if($rel->thumbnail_url)
                                    <img
                                        src="{{ $rel->thumbnail_url }}"
                                        alt="{{ $rel->title }}"
                                        referrerpolicy="no-referrer"
                                        onerror="if(!this.dataset.proxied){this.dataset.proxied='1'; this.src='{{ route('image.proxy') }}?url=' + encodeURIComponent('{{ $rel->thumbnail_url }}');} else {this.src='https://placehold.co/640x360/1e293b/cbd5e1?text=Video';}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition"
                                    >
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-slate-950 text-slate-600">
                                        <i class="fa-solid fa-play text-xs text-fuchsia-500/50"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-semibold text-slate-200 group-hover:text-fuchsia-300 line-clamp-2 transition">
                                    {{ $rel->title }}
                                </h4>
                                @if($rel->duration)
                                    <span class="text-[10px] text-slate-400 mt-1 block">
                                        {{ $rel->duration }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            <a href="{{ route('home') }}" class="block w-full py-2.5 rounded-xl text-center text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">
                <i class="fa-solid fa-house mr-1 text-fuchsia-400"></i> Back to All Videos
            </a>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
    const streamUrl = "{{ $resolvedStreamUrl }}";
    const videoType = "{{ $videoType }}";
    let hlsInstance = null;
    let isHlsReady = false;

    // Initialize Hls.js for full-length .m3u8 streaming
    document.addEventListener('DOMContentLoaded', function() {
        const video = document.getElementById('video-player');

        if (video && (videoType === 'hls' || streamUrl.includes('.m3u8'))) {
            if (window.Hls && Hls.isSupported()) {
                hlsInstance = new Hls({
                    enableWorker: true,
                    lowLatencyMode: false,
                });
                hlsInstance.loadSource(streamUrl);
                hlsInstance.attachMedia(video);
                hlsInstance.on(Hls.Events.MANIFEST_PARSED, function() {
                    isHlsReady = true;
                    console.log('Full-length HLS stream loaded and ready.');
                });
                hlsInstance.on(Hls.Events.ERROR, function(event, data) {
                    if (data.fatal) {
                        console.warn('HLS stream fatal error:', data);
                    }
                });
            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                video.src = streamUrl;
            }
        }
    });

    // Start playback when centered cover or play button is clicked
    function startPlayback() {
        // 1. Immediately trigger popup ad on trusted user click gesture
        if (typeof window.triggerOnClickAd === 'function') {
            window.triggerOnClickAd('video_play');
        }

        const cover = document.getElementById('player-cover');
        const video = document.getElementById('video-player');

        // 2. Instantly disable pointer events and hide cover overlay
        if (cover) {
            cover.style.pointerEvents = 'none';
            cover.style.opacity = '0';
            setTimeout(() => {
                cover.style.display = 'none';
            }, 300);
        }

        // 3. Immediately start video stream playback
        if (video) {
            video.controls = true;
            if (!video.src && video.querySelector('source')) {
                video.src = video.querySelector('source').src;
            }
            const playPromise = video.play();
            if (playPromise !== undefined) {
                playPromise.catch(function(err) {
                    console.warn('Direct play error:', err);
                });
            }
        }
    }

    // Theater Mode Toggle
    let isTheater = false;
    function toggleTheaterMode() {
        const wrapper = document.getElementById('player-container-wrapper');
        const theaterText = document.getElementById('theater-text');
        isTheater = !isTheater;

        if (isTheater) {
            wrapper.classList.remove('max-w-7xl');
            wrapper.classList.add('w-full', 'max-w-none');
            theaterText.textContent = 'Exit Theater';
        } else {
            wrapper.classList.remove('w-full', 'max-w-none');
            theaterText.textContent = 'Theater Mode';
        }
    }

    // Share Modal
    function openShareModal() {
        const modal = document.getElementById('share-modal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeShareModal() {
        const modal = document.getElementById('share-modal');
        if (modal) modal.classList.add('hidden');
    }

    // Share link copy
    function copyWatchLink() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            const btn = document.getElementById('copy-share-btn');
            if (btn) {
                const orig = btn.innerText;
                btn.innerText = 'Copied!';
                setTimeout(() => { btn.innerText = orig; }, 2000);
            }
        });
    }

    // Embed code copy
    function copyEmbedCode() {
        const embedText = document.getElementById('embed-code-text');
        if (embedText) {
            navigator.clipboard.writeText(embedText.value).then(() => {
                const btn = document.getElementById('copy-embed-btn');
                if (btn) {
                    const orig = btn.innerText;
                    btn.innerText = 'Copied!';
                    setTimeout(() => { btn.innerText = orig; }, 2000);
                }
            });
        }
    }

    // Watch Page Favorite Toggle
    const currentVideo = {
        id: "{{ $video->id }}",
        slug: "{{ $video->slug }}",
        title: {!! json_encode($video->clean_title) !!},
        thumb: "{{ $video->thumbnail_url }}",
        duration: "{{ $video->duration }}",
        url: "{{ route('videos.watch', $video->slug ?: $video->id) }}"
    };

    function updateWatchFavoriteBtn() {
        const favs = getFavorites();
        const isFav = favs.some(f => f.id === currentVideo.id);
        const icon = document.getElementById('fav-icon');
        const text = document.getElementById('fav-text');
        const btn = document.getElementById('btn-watch-favorite');
        if (!icon || !text || !btn) return;

        if (isFav) {
            icon.className = 'fa-solid fa-heart text-rose-500';
            text.innerText = 'Saved';
            btn.classList.add('border-rose-500/40', 'bg-rose-950/30', 'text-rose-200');
        } else {
            icon.className = 'fa-regular fa-heart text-rose-400';
            text.innerText = 'Favorite';
            btn.classList.remove('border-rose-500/40', 'bg-rose-950/30', 'text-rose-200');
        }
    }

    function toggleCurrentFavorite() {
        let favs = getFavorites();
        const idx = favs.findIndex(f => f.id === currentVideo.id);
        if (idx >= 0) {
            favs.splice(idx, 1);
        } else {
            favs.unshift(currentVideo);
        }
        saveFavorites(favs);
        updateWatchFavoriteBtn();
    }

    document.addEventListener('DOMContentLoaded', updateWatchFavoriteBtn);
</script>

<!-- Share & Embed Modal -->
<div id="share-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
    <div class="relative w-full max-w-lg bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden p-6 space-y-5">
        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
            <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-share-nodes text-fuchsia-400"></i> Share &amp; Embed Video
            </h3>
            <button type="button" onclick="closeShareModal()" class="text-slate-400 hover:text-white text-lg">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Direct Link -->
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Direct Page Link</label>
            <div class="flex items-center gap-2">
                <input type="text" readonly value="{{ route('videos.watch', $video->slug ?: $video->id) }}" class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-300 select-all outline-none">
                <button type="button" id="copy-share-btn" onclick="copyWatchLink()" class="px-4 py-2 bg-gradient-to-r from-fuchsia-600 to-indigo-600 text-white rounded-xl text-xs font-bold shrink-0 hover:opacity-90 transition">
                    Copy
                </button>
            </div>
        </div>

        <!-- Social Share Buttons -->
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Share on Social &amp; Communities</label>
            <div class="grid grid-cols-4 gap-2">
                <a href="https://t.me/share/url?url={{ urlencode(route('videos.watch', $video->slug ?: $video->id)) }}&text={{ urlencode($video->clean_title) }}" target="_blank" rel="noopener" class="p-2.5 rounded-xl bg-[#229ED9]/15 border border-[#229ED9]/30 text-[#229ED9] hover:bg-[#229ED9]/30 flex flex-col items-center gap-1 transition text-center">
                    <i class="fa-brands fa-telegram text-base"></i>
                    <span class="text-[10px] font-semibold">Telegram</span>
                </a>
                <a href="https://twitter.com/intent/tweet?text={{ urlencode($video->clean_title) }}&url={{ urlencode(route('videos.watch', $video->slug ?: $video->id)) }}" target="_blank" rel="noopener" class="p-2.5 rounded-xl bg-sky-500/15 border border-sky-500/30 text-sky-400 hover:bg-sky-500/30 flex flex-col items-center gap-1 transition text-center">
                    <i class="fa-brands fa-x-twitter text-base"></i>
                    <span class="text-[10px] font-semibold">Twitter / X</span>
                </a>
                <a href="https://reddit.com/submit?url={{ urlencode(route('videos.watch', $video->slug ?: $video->id)) }}&title={{ urlencode($video->clean_title) }}" target="_blank" rel="noopener" class="p-2.5 rounded-xl bg-[#FF4500]/15 border border-[#FF4500]/30 text-[#FF4500] hover:bg-[#FF4500]/30 flex flex-col items-center gap-1 transition text-center">
                    <i class="fa-brands fa-reddit-alien text-base"></i>
                    <span class="text-[10px] font-semibold">Reddit</span>
                </a>
                <a href="https://api.whatsapp.com/send?text={{ urlencode($video->clean_title . ' ' . route('videos.watch', $video->slug ?: $video->id)) }}" target="_blank" rel="noopener" class="p-2.5 rounded-xl bg-[#25D366]/15 border border-[#25D366]/30 text-[#25D366] hover:bg-[#25D366]/30 flex flex-col items-center gap-1 transition text-center">
                    <i class="fa-brands fa-whatsapp text-base"></i>
                    <span class="text-[10px] font-semibold">WhatsApp</span>
                </a>
            </div>
        </div>

        <!-- iFrame Embed Code -->
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">Embed on Website / Blog</label>
            <div class="flex items-center gap-2">
                <input id="embed-code-text" type="text" readonly value='&lt;iframe src="{{ route('videos.embed', $video->slug ?: $video->id) }}" width="100%" height="450" frameborder="0" allowfullscreen&gt;&lt;/iframe&gt;' class="flex-1 bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-400 font-mono select-all outline-none">
                <button type="button" id="copy-embed-btn" onclick="copyEmbedCode()" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 rounded-xl text-xs font-semibold shrink-0 transition">
                    Copy Code
                </button>
            </div>
        </div>
    </div>
</div>
</script>
@endpush
