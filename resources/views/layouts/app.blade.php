<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="referrer" content="no-referrer">

    <title>@yield('title', 'Video Stream Portal')</title>

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
    </style>
    @stack('styles')
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
                    <span class="text-xl font-black tracking-tight bg-gradient-to-r from-fuchsia-400 via-pink-400 to-indigo-400 bg-clip-text text-transparent">StreamFlix</span>
                    <span class="text-[10px] text-slate-400 block -mt-1 tracking-wider uppercase font-semibold">Video Portal</span>
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
    <footer class="border-t border-slate-800/80 bg-[#070b13] py-8 mt-16 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4">
            <p class="text-slate-400 font-medium">StreamFlix &copy; {{ date('Y') }} &bull; Native Video Streaming</p>
            <p class="mt-1 text-slate-600">Stream videos smoothly with native controls and instant playback.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    @stack('scripts')
</body>
</html>
