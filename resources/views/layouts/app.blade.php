<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="referrer" content="strict-origin-when-cross-origin">

    <!-- Google Search Console Verification -->
    @if(env('GOOGLE_SITE_VERIFICATION'))
    <meta name="google-site-verification" content="{{ env('GOOGLE_SITE_VERIFICATION') }}">
    @endif

    <title>@yield('title', 'Watch Free Hentai Online - HD Hentai Streaming, Gangbang & Cartoon Anime | HentaiFlex')</title>
    <meta name="description" content="@yield('meta_description', 'Watch free HD hentai online with zero buffering. Stream thousands of uncensored full-length hentai episodes, gangbang hentai, cartoon hentai, 3D SFM animations, and English sub anime on HentaiFlex.')">
    <meta name="keywords" content="@yield('meta_keywords', 'watch hentai, free hentai, hentai online, hentai streaming, best hentai sites, watch hentai online free, uncensored hentai, gangbang hentai, cartoon hentai, 3d hentai, full hentai episodes, english sub hentai, hentai tube, hd anime, hentai videos, hentaiflex')">
    <link rel="canonical" href="@yield('canonical_url', url()->current())">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">

    <!-- Open Graph / Social Sharing -->
    <meta property="og:site_name" content="HentaiFlex">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="@yield('canonical_url', url()->current())">
    <meta property="og:title" content="@yield('title', 'Watch Free Hentai Online - HD Hentai Streaming, Gangbang & Cartoon Anime | HentaiFlex')">
    <meta property="og:description" content="@yield('meta_description', 'Watch free HD hentai online with zero buffering. Stream thousands of uncensored full-length hentai episodes, gangbang hentai, cartoon hentai, 3D SFM animations, and English sub anime on HentaiFlex.')">
    <meta property="og:image" content="@yield('og_image', asset('images/og-cover.jpg'))">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="@yield('canonical_url', url()->current())">
    <meta name="twitter:title" content="@yield('title', 'Watch Free Hentai Online - HD Hentai Streaming, Gangbang & Cartoon Anime | HentaiFlex')">
    <meta name="twitter:description" content="@yield('meta_description', 'Watch free HD hentai online with zero buffering. Stream thousands of uncensored full-length hentai episodes, gangbang hentai, cartoon hentai, 3D SFM animations, and English sub anime on HentaiFlex.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-cover.jpg'))">

    <!-- Structured Data (JSON-LD) for Google Search -->
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "WebSite",
        "name": "HentaiFlex",
        "alternateName": ["Hentai Flex", "HentaiFlex HD", "HentaiFlex Gangbang & Cartoon Hentai"],
        "url": "{{ url('/') }}",
        "description": "Watch free HD hentai, gangbang hentai, cartoon hentai, and 3D anime videos streaming online.",
        "potentialAction": {
            "@type": "SearchAction",
            "target": "{{ url('/') }}?q={search_term_string}",
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    @stack('schema')

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fdf4ff',
                            100: '#fae8ff',
                            500: '#d946ef',
                            600: '#c026d3',
                            700: '#a21caf',
                        },
                        darkbg: '#0b0f19',
                        cardbg: '#1e293b',
                    }
                }
            }
        }
    </script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Plyr Player CSS -->
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />

    <style>
        body {
            background-color: #0b0f19;
            color: #f1f5f9;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        }
        .glass-header {
            background: rgba(11, 15, 25, 0.9);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .card-glow:hover {
            box-shadow: 0 12px 30px -5px rgba(217, 70, 239, 0.3), 0 8px 10px -6px rgba(99, 102, 241, 0.2);
            border-color: rgba(217, 70, 239, 0.6);
            transform: translateY(-2px);
        }
        .play-pulse {
            animation: pulse-glow 2s infinite;
        }
        @keyframes pulse-glow {
            0% {
                box-shadow: 0 0 0 0 rgba(217, 70, 239, 0.7);
            }
            70% {
                box-shadow: 0 0 0 20px rgba(217, 70, 239, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(217, 70, 239, 0);
            }
        }
        /* Mobile-Responsive Ad Enforcements */
        .ad-content, .ad-slot, .ad-placeholder {
            max-width: 100% !important;
        }
        .ad-content ins,
        .ad-content iframe,
        .ad-slot ins,
        .ad-slot iframe {
            max-width: 100% !important;
            margin-left: auto !important;
            margin-right: auto !important;
            display: block !important;
        }
    </style>
    @stack('styles')

    <!-- ExoClick Site Verification -->
    <meta name="6a97888e-site-verification" content="d2f41d24add6224e9e65403281a4372f">

    <!-- Custom Ad Network Head Scripts (Verification / Header Tag) -->
    @if(config('ads.enabled', true) && config('ads.head_script'))
        {!! config('ads.head_script') !!}
    @endif
</head>
<body class="min-h-screen flex flex-col bg-[#0b0f19]">

    <!-- Navigation Header -->
    <header class="glass-header sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-fuchsia-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-fuchsia-500/25 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-play text-white text-base ml-0.5"></i>
                </div>
                <div>
                    <span class="text-xl font-black tracking-tight bg-gradient-to-r from-fuchsia-400 via-pink-400 to-indigo-400 bg-clip-text text-transparent">HentaiFlex</span>
                    <span class="text-[10px] text-slate-400 block -mt-1 tracking-wider uppercase font-semibold">Ultra HD Streaming</span>
                </div>
            </a>

            <!-- Search / Quick Nav -->
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="px-3.5 py-1.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition">
                    <i class="fa-solid fa-film mr-1.5 text-xs text-fuchsia-400"></i> Browse Videos
                </a>
            </div>
        </div>
    </header>

    <!-- Alerts / Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if (session('success'))
            <div class="flex items-center gap-3 p-4 mb-4 rounded-xl bg-emerald-950/60 border border-emerald-500/30 text-emerald-300">
                <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                <div class="flex-1 text-sm">{{ session('success') }}</div>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 p-4 mb-4 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-300">
                <i class="fa-solid fa-triangle-exclamation text-rose-400 text-lg"></i>
                <div class="flex-1 text-sm">{{ session('error') }}</div>
                <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-200">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="p-4 mb-4 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-300 text-sm">
                <div class="font-medium mb-1"><i class="fa-solid fa-circle-exclamation mr-1.5"></i> Notification:</div>
                <ul class="list-disc list-inside text-rose-300/90 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 bg-[#070b13] py-10 mt-16 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex items-center justify-center gap-2 mb-2">
                <div class="w-6 h-6 rounded-lg bg-gradient-to-tr from-fuchsia-600 to-indigo-600 flex items-center justify-center text-white text-[10px]">
                    <i class="fa-solid fa-play"></i>
                </div>
                <span class="font-extrabold text-sm text-white tracking-wide">HentaiFlex</span>
            </div>
            <p class="text-slate-400 font-medium">HentaiFlex &copy; {{ date('Y') }} &bull; Watch Free HD Anime & Hentai Streaming Online</p>
            <p class="mt-1 text-slate-500 max-w-xl mx-auto leading-relaxed">
                Stream thousands of full-length videos in high definition with ultra-fast direct MP4 playback, instant seeking, and zero buffering.
            </p>
        </div>
    </footer>

    <!-- Sticky Bottom Banner Ad -->
    @include('partials.ads.sticky-footer')

    <!-- Scripts -->
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <script>
        // Anti-Scraping / Right-Click Media Protection
        document.addEventListener('contextmenu', function(e) {
            if (e.target.closest('video') || e.target.closest('#player-container-wrapper') || e.target.closest('.plyr')) {
                e.preventDefault();
                return false;
            }
        });
        document.addEventListener('dragstart', function(e) {
            if (e.target.tagName === 'VIDEO' || e.target.closest('video')) {
                e.preventDefault();
                return false;
            }
        });
    </script>

    <!-- OnClick Popunder Monetization Engine -->
    @if(config('ads.enabled', true) && config('ads.onclick.enabled', true))
    <script>
        (function() {
            const adUrl = "{{ config('ads.onclick.url', 'https://syndication.exoclick.com/splash.php?idzone=default') }}";
            const freqMinutes = {{ (int) config('ads.onclick.frequency_minutes', 3) }};

            window.triggerOnClickAd = function(source) {
                if (!adUrl) return;
                const now = Date.now();

                // Video play click: Highest earning placement, triggers pop ad while video plays
                if (source === 'video_play') {
                    const lastPlayTime = sessionStorage.getItem('hentaiflex_last_video_play_pop');
                    if (lastPlayTime && (now - parseInt(lastPlayTime, 10)) < 15000) {
                        return; // 15-second debounce on repeated play button clicks
                    }
                    try {
                        const win = window.open(adUrl, '_blank');
                        if (win) {
                            sessionStorage.setItem('hentaiflex_last_video_play_pop', now.toString());
                            localStorage.setItem('hentaiflex_last_ad_pop', now.toString());
                            window.focus();
                        }
                    } catch (e) {
                        console.warn('Popup suppressed:', e);
                    }
                    return;
                }

                // General page click throttle
                const lastTime = localStorage.getItem('hentaiflex_last_ad_pop');
                if (lastTime && (now - parseInt(lastTime, 10)) < (freqMinutes * 60 * 1000)) {
                    return;
                }

                try {
                    const win = window.open(adUrl, '_blank');
                    if (win) {
                        localStorage.setItem('hentaiflex_last_ad_pop', now.toString());
                        window.focus();
                    }
                } catch (e) {
                    // Suppressed by browser pop blocker
                }
            };

            @if(config('ads.onclick.trigger_on_first_click', true))
            let pageClicked = false;
            document.addEventListener('click', function(e) {
                if (pageClicked) return;
                if (e.target.closest('a[target="_blank"]') || e.target.closest('#sticky-footer-ad') || e.target.closest('button[onclick*="dismiss"]')) {
                    return;
                }
                pageClicked = true;
                window.triggerOnClickAd('page_click');
            }, { capture: true, once: true });
            @endif
        })();
    </script>
    @endif

    @stack('scripts')

    <!-- Popunder / On-Click Ad Script -->
    @if(config('ads.enabled', true) && config('ads.popunder.enabled', true) && config('ads.popunder.code'))
        {!! config('ads.popunder.code') !!}
    @endif
</body>
</html>
