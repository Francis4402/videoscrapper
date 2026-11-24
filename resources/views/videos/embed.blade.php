<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $video->clean_title }} - HentaiFlex Player</title>
    <meta name="robots" content="noindex, follow">
    <script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.7/dist/hls.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { width: 100%; height: 100%; overflow: hidden; background-color: #0b0d14; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .player-container { position: relative; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #000; }
        video { width: 100%; height: 100%; object-fit: contain; background: #000; }
        .cover-overlay { position: absolute; inset: 0; background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; cursor: pointer; z-index: 20; transition: opacity 0.25s ease; }
        .cover-overlay::after { content: ''; position: absolute; inset: 0; background: rgba(0, 0, 0, 0.45); }
        .play-btn { position: relative; z-index: 30; width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #ec4899, #8b5cf6); display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 25px rgba(236, 72, 153, 0.5); transition: transform 0.2s ease; }
        .cover-overlay:hover .play-btn { transform: scale(1.1); }
        .watermark { position: absolute; top: 12px; right: 12px; z-index: 25; font-size: 13px; font-weight: 700; color: #fff; text-decoration: none; opacity: 0.8; text-shadow: 0 1px 3px rgba(0,0,0,0.8); background: rgba(0,0,0,0.6); padding: 5px 12px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.15); transition: all 0.2s; }
        .watermark:hover { opacity: 1; color: #f43f5e; border-color: rgba(244,63,94,0.4); }
    </style>
    @if(config('ads.enabled') && config('ads.onclick.enabled'))
    {!! config('ads.popunder.code') !!}
    @endif
</head>
<body>
    <div class="player-container">
        <video id="video-player" playsinline preload="metadata">
            @if($resolvedStreamUrl)
            <source src="{{ $resolvedStreamUrl }}" type="{{ (str_contains($resolvedStreamUrl, '.m3u8') || $videoType === 'hls') ? 'application/x-mpegURL' : 'video/mp4' }}">
            @else
            <source src="{{ route('stream.proxy', $video->id) }}" type="video/mp4">
            @endif
        </video>

        <a href="{{ route('videos.watch', $video->slug ?: $video->id) }}" target="_blank" rel="noopener" class="watermark">
            HentaiFlex
        </a>

        @php
            $thumb = $video->thumbnail_url;
            if ($thumb && Str::startsWith($thumb, '//')) {
                $thumb = 'https:' . $thumb;
            } elseif ($thumb && !Str::startsWith($thumb, 'http')) {
                $thumb = url($thumb);
            }
        @endphp
        <a id="player-cover" href="javascript:void(0)" role="button" aria-label="Play video" class="cover-overlay play-trigger" style="background-image: url('{{ $thumb }}');" onclick="startPlayback()">
            <div class="play-btn">
                <svg style="width: 32px; height: 32px; fill: white; margin-left: 4px;" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
            </div>
        </a>
    </div>

    <script>
        const streamUrl = "{{ $resolvedStreamUrl ?: route('stream.proxy', $video->id) }}";
        const videoType = "{{ $videoType }}";
        let hlsInstance = null;

        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('video-player');
            if (video && (videoType === 'hls' || streamUrl.includes('.m3u8'))) {
                if (window.Hls && Hls.isSupported()) {
                    hlsInstance = new Hls({ enableWorker: true, lowLatencyMode: false });
                    hlsInstance.loadSource(streamUrl);
                    hlsInstance.attachMedia(video);
                } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                    video.src = streamUrl;
                }
            }
        });

        function startPlayback() {
            // Trigger popunder ad if available
            if (typeof window.triggerOnClickAd === 'function') {
                window.triggerOnClickAd('video_play');
            }

            const cover = document.getElementById('player-cover');
            const video = document.getElementById('video-player');
            if (cover) {
                cover.style.pointerEvents = 'none';
                cover.style.opacity = '0';
                setTimeout(() => { cover.style.display = 'none'; }, 300);
            }
            if (video) {
                video.controls = true;
                if (!video.src && video.querySelector('source')) {
                    video.src = video.querySelector('source').src;
                }
                const playPromise = video.play();
                if (playPromise !== undefined) {
                    playPromise.catch(e => console.warn('Playback error:', e));
                }
            }
        }
    </script>
</body>
</html>