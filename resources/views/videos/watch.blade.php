@extends('layouts.app')

@section('title', $video->title . ' - Watch Video')

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
        <div class="flex items-center gap-2">
            <button
                type="button"
                id="btn-theater"
                onclick="toggleTheaterMode()"
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center gap-1.5 transition"
            >
                <i class="fa-solid fa-expand text-fuchsia-400"></i>
                <span id="theater-text">Theater Mode</span>
            </button>

            <button
                type="button"
                onclick="copyWatchLink()"
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 flex items-center gap-1.5 transition"
            >
                <i class="fa-solid fa-share-nodes text-indigo-400"></i>
                <span id="copy-text">Share</span>
            </button>

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

            @if($video->description)
                <div class="p-5 rounded-2xl bg-slate-900/60 border border-slate-800 text-slate-300 text-sm leading-relaxed">
                    <p class="whitespace-pre-line">{{ $video->description }}</p>
                </div>
            @endif
        </div>

        <!-- Right: Related Videos -->
        <div class="space-y-4">
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
        const cover = document.getElementById('player-cover');
        const video = document.getElementById('video-player');

        // Instantly disable pointer events and hide cover
        if (cover) {
            cover.style.pointerEvents = 'none';
            cover.style.opacity = '0';
            setTimeout(() => {
                cover.style.display = 'none';
            }, 300);
        }

        // Trigger native HTML5 video playback
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

    // Share link copy
    function copyWatchLink() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            const btn = document.getElementById('copy-text');
            const original = btn.textContent;
            btn.textContent = 'Copied!';
            setTimeout(() => { btn.textContent = original; }, 2000);
        });
    }
</script>
@endpush
