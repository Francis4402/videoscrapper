<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $video->clean_title }} - HentaiFlex Player</title>
    <meta name="robots" content="noindex, follow">
    <meta http-equiv="Delegate-CH" content="Sec-CH-UA https://s.magsrv.com; Sec-CH-UA-Mobile https://s.magsrv.com; Sec-CH-UA-Arch https://s.magsrv.com; Sec-CH-UA-Model https://s.magsrv.com; Sec-CH-UA-Platform https://s.magsrv.com; Sec-CH-UA-Platform-Version https://s.magsrv.com; Sec-CH-UA-Bitness https://s.magsrv.com; Sec-CH-UA-Full-Version-List https://s.magsrv.com; Sec-CH-UA-Full-Version https://s.magsrv.com;">
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
        .instream-overlay { position: absolute; inset: 0; z-index: 22; display: none; flex-direction: column; justify-content: space-between; background: #000; }
        .instream-video { width: 100%; height: 100%; object-fit: contain; background: #000; cursor: pointer; }
        .instream-top { position: absolute; top: 12px; left: 12px; right: 12px; display: flex; justify-content: space-between; align-items: center; z-index: 30; pointer-events: none; }
        .instream-badge { background: rgba(0,0,0,0.8); color: #fff; font-size: 12px; padding: 5px 10px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.2); font-weight: 600; }
        .instream-link { pointer-events: auto; background: rgba(0,0,0,0.8); color: #f43f5e; text-decoration: none; font-size: 12px; padding: 5px 12px; border-radius: 6px; border: 1px solid rgba(244,63,94,0.4); font-weight: 600; }
        .instream-skip-box { position: absolute; bottom: 16px; right: 16px; z-index: 30; }
        .instream-skip-btn { padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: bold; border: 1px solid rgba(255,255,255,0.2); background: rgba(0,0,0,0.8); color: #888; cursor: not-allowed; }
        .instream-skip-btn.active { background: linear-gradient(135deg, #ec4899, #8b5cf6); color: #fff; border: none; cursor: pointer; }
        .instream-progress-bar { position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: rgba(255,255,255,0.2); z-index: 30; }
        .instream-progress { height: 100%; background: #ec4899; width: 0%; transition: width 0.1s linear; }
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

        <!-- IN-STREAM VAST VIDEO AD OVERLAY -->
        <div id="instream-ad-overlay" class="instream-overlay">
            <video id="instream-ad-video" playsinline class="instream-video"></video>
            <div class="instream-top">
                <span class="instream-badge">Ad <span id="instream-ad-duration" style="color: #aaa; margin-left: 4px;"></span></span>
                <a id="instream-ad-link" href="#" target="_blank" rel="noopener" class="instream-link">Visit Advertiser ↗</a>
            </div>
            <div class="instream-skip-box">
                <button type="button" id="instream-ad-skip" disabled class="instream-skip-btn">Skip in <span id="instream-ad-countdown">5</span>s</button>
            </div>
            <div class="instream-progress-bar">
                <div id="instream-ad-progress" class="instream-progress"></div>
            </div>
        </div>

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

        const instreamConfig = {
            enabled: {{ (config('ads.enabled', true) && config('ads.instream.enabled', true) && !empty(config('ads.instream.vast_url'))) ? 'true' : 'false' }},
            vastUrl: @json(config('ads.instream.vast_url', 'https://s.magsrv.com/v1/vast.php?idz=6109862')),
            skipOffset: {{ (int) config('ads.instream.skip_offset', 5) }}
        };

        let adPlayed = false;

        function playMainVideo() {
            const adOverlay = document.getElementById('instream-ad-overlay');
            const adVideo = document.getElementById('instream-ad-video');
            if (adVideo) {
                adVideo.pause();
                adVideo.removeAttribute('src');
                adVideo.load();
            }
            if (adOverlay) {
                adOverlay.style.display = 'none';
            }

            const video = document.getElementById('video-player');
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

        async function parseVastTag(url, depth = 0) {
            if (depth > 4 || !url) return null;
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 4000);
                const resp = await fetch(url, {
                    signal: controller.signal,
                    headers: { 'Accept': 'application/xml, text/xml, */*' }
                });
                clearTimeout(timeoutId);
                if (!resp.ok) return null;
                const xmlText = await resp.text();
                const doc = new DOMParser().parseFromString(xmlText, 'text/xml');

                const impressions = [];
                const clickTrackings = [];

                doc.querySelectorAll('Impression').forEach(el => {
                    const u = el.textContent.trim();
                    if (u) impressions.push(u);
                });
                doc.querySelectorAll('ClickTracking').forEach(el => {
                    const u = el.textContent.trim();
                    if (u) clickTrackings.push(u);
                });

                const wrapperTag = doc.querySelector('VASTAdTagURI');
                if (wrapperTag && wrapperTag.textContent.trim()) {
                    const sub = await parseVastTag(wrapperTag.textContent.trim(), depth + 1);
                    if (sub) {
                        return {
                            ...sub,
                            impressions: [...impressions, ...(sub.impressions || [])],
                            clickTrackings: [...clickTrackings, ...(sub.clickTrackings || [])]
                        };
                    }
                }

                const mediaList = Array.from(doc.querySelectorAll('MediaFile'));
                const selected = mediaList.find(m => (m.getAttribute('type') || '').includes('mp4')) || mediaList[0];
                if (!selected || !selected.textContent.trim()) return null;

                let skipSec = instreamConfig.skipOffset || 5;
                const linear = doc.querySelector('Linear');
                if (linear && linear.getAttribute('skipoffset')) {
                    const raw = linear.getAttribute('skipoffset').trim();
                    if (raw.includes(':')) {
                        const parts = raw.split(':').map(Number);
                        skipSec = (parts[0] * 3600) + (parts[1] * 60) + (parts[2] || 0);
                    } else if (!raw.endsWith('%')) {
                        const parsed = parseInt(raw, 10);
                        if (!isNaN(parsed) && parsed > 0) skipSec = parsed;
                    }
                }

                const clickThroughEl = doc.querySelector('ClickThrough');
                const clickThrough = clickThroughEl ? clickThroughEl.textContent.trim() : '';

                return {
                    mediaUrl: selected.textContent.trim(),
                    clickThrough: clickThrough,
                    skipOffset: Math.max(1, skipSec),
                    impressions: impressions,
                    clickTrackings: clickTrackings
                };
            } catch (e) {
                console.warn('VAST parse error:', e);
                return null;
            }
        }

        function pingTrackers(urls) {
            if (!Array.isArray(urls)) return;
            urls.forEach(u => {
                if (!u) return;
                try {
                    const img = new Image();
                    img.src = u;
                } catch (e) {}
            });
        }

        async function startPlayback() {
            const cover = document.getElementById('player-cover');
            if (cover) {
                cover.style.pointerEvents = 'none';
                cover.style.opacity = '0';
                setTimeout(() => { cover.style.display = 'none'; }, 300);
            }

            if (instreamConfig.enabled && instreamConfig.vastUrl && !adPlayed) {
                adPlayed = true;
                const adOverlay = document.getElementById('instream-ad-overlay');
                const adVideo = document.getElementById('instream-ad-video');
                const adLink = document.getElementById('instream-ad-link');
                const adSkip = document.getElementById('instream-ad-skip');
                const adCountdown = document.getElementById('instream-ad-countdown');
                const adDuration = document.getElementById('instream-ad-duration');
                const adProgress = document.getElementById('instream-ad-progress');

                if (adOverlay && adVideo) {
                    adOverlay.style.display = 'flex';

                    let timeoutHandle = setTimeout(() => {
                        console.warn('In-stream ad load timed out, starting main video.');
                        playMainVideo();
                    }, 4000);

                    const vastData = await parseVastTag(instreamConfig.vastUrl);
                    clearTimeout(timeoutHandle);

                    if (!vastData || !vastData.mediaUrl) {
                        playMainVideo();
                        return;
                    }

                    pingTrackers(vastData.impressions);
                    adVideo.src = vastData.mediaUrl;
                    adVideo.currentTime = 0;

                    const targetUrl = vastData.clickThrough || '#';
                    if (adLink) {
                        adLink.href = targetUrl;
                        adLink.onclick = function() { pingTrackers(vastData.clickTrackings); };
                    }
                    adVideo.onclick = function() {
                        if (targetUrl && targetUrl !== '#') {
                            pingTrackers(vastData.clickTrackings);
                            window.open(targetUrl, '_blank');
                        }
                    };

                    const skipSeconds = vastData.skipOffset;
                    let isSkipped = false;
                    function triggerSkip() {
                        if (isSkipped) return;
                        isSkipped = true;
                        playMainVideo();
                    }

                    if (adSkip) {
                        adSkip.disabled = true;
                        adSkip.onclick = triggerSkip;
                    }

                    adVideo.ontimeupdate = function() {
                        const cur = adVideo.currentTime || 0;
                        const dur = adVideo.duration || 0;

                        if (adProgress && dur > 0) {
                            adProgress.style.width = ((cur / dur) * 100) + '%';
                        }
                        if (adDuration && dur > 0) {
                            adDuration.textContent = Math.max(0, Math.ceil(dur - cur)) + 's';
                        }

                        if (cur < skipSeconds) {
                            if (adCountdown) adCountdown.textContent = Math.ceil(skipSeconds - cur);
                        } else if (adSkip && adSkip.disabled) {
                            adSkip.disabled = false;
                            adSkip.textContent = 'Skip Ad ▶';
                            adSkip.classList.add('active');
                        }
                    };

                    adVideo.onended = function() { playMainVideo(); };
                    adVideo.onerror = function() { playMainVideo(); };

                    const playPromise = adVideo.play();
                    if (playPromise !== undefined) {
                        playPromise.catch(function(e) {
                            console.warn('Ad autoplay blocked, playing main video:', e);
                            playMainVideo();
                        });
                    }
                    return;
                }
            }

            playMainVideo();
        }
    </script>
</body>
</html>