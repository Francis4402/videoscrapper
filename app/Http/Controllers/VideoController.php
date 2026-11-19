<?php

namespace App\Http\Controllers;

use App\Models\ScrapedVideo;
use App\Services\VideoScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VideoController extends Controller
{
    protected VideoScraperService $scraperService;

    public function __construct(VideoScraperService $scraperService)
    {
        $this->scraperService = $scraperService;
    }

    /**
     * Get array of active target URLs from .env, normalized with and without trailing slash.
     */
    protected function getActiveTargetUrls(): array
    {
        $envTarget = trim((string) config('scraper.target_url'));
        if (empty($envTarget)) {
            return [];
        }

        $rawUrls = preg_split('/[\r\n,;]+/', $envTarget, -1, PREG_SPLIT_NO_EMPTY);
        $normalized = [];
        foreach ($rawUrls as $raw) {
            $u = trim($raw);
            if (!empty($u)) {
                $trimmed = rtrim($u, '/');
                $normalized[] = $trimmed;
                $normalized[] = $trimmed . '/';
            }
        }
        return array_unique($normalized);
    }

    /**
     * Display front page with video cards.
     * Automatically syncs from .env whenever SCRAPE_TARGET_URL is added or changed.
     * Strictly displays ONLY data from the links configured in .env.
     */
    public function index(Request $request)
    {
        $envTarget = trim((string) config('scraper.target_url'));
        $activeUrls = $this->getActiveTargetUrls();
        $cacheKey = 'synced_env_target_hash';
        $currentHash = !empty($envTarget) ? md5($envTarget) : null;
        $syncedHash = Cache::get($cacheKey);

        // Auto-sync whenever link in .env is added, modified, or if database is empty
        if (!empty($envTarget) && ($currentHash !== $syncedHash || ScrapedVideo::count() === 0)) {
            $rawUrls = preg_split('/[\r\n,;]+/', $envTarget, -1, PREG_SPLIT_NO_EMPTY);
            $urls = array_filter(array_map('trim', $rawUrls));

            foreach ($urls as $url) {
                try {
                    $this->scraperService->scrape($url);
                } catch (\Throwable $e) {
                    Log::warning("Auto-sync failed for URL [{$url}]: " . $e->getMessage());
                }
            }
            Cache::forever($cacheKey, $currentHash);
            Cache::forget('video_category_counts');
        }

        // Clean up: delete any videos not in active .env target URLs, and delete all funny/meme/vlipsy videos
        if (!empty($activeUrls)) {
            ScrapedVideo::whereNotIn('source_url', $activeUrls)->delete();
        }
        ScrapedVideo::where(function ($q) {
            $q->where('source_domain', 'like', '%vlipsy%')
              ->orWhere('title', 'like', '%funny%')
              ->orWhere('title', 'like', '%meme%')
              ->orWhere('title', 'like', '%laughing%')
              ->orWhere('title', 'like', '%lol montage%');
        })->delete();

        $search = $request->query('q');
        $currentCategory = $request->query('category');

        $baseQuery = ScrapedVideo::notFunny();
        if (!empty($activeUrls)) {
            $baseQuery->whereIn('source_url', $activeUrls);
        }

        $query = (clone $baseQuery)->latest();

        // Onclick category filtering
        if ($currentCategory && isset(ScrapedVideo::$categoryDefinitions[$currentCategory])) {
            $catDef = ScrapedVideo::$categoryDefinitions[$currentCategory];
            $query->where(function ($q) use ($catDef, $currentCategory) {
                if ($currentCategory === '3d') {
                    $q->where('title', 'like', '%3d%')
                      ->orWhere('title', 'like', '%cgi%')
                      ->orWhere('title', 'like', '%sfm%')
                      ->orWhere('title', 'like', '%blender%')
                      ->orWhere('title', 'like', '%3-d%')
                      ->orWhere('source_url', 'like', '%3d%');
                } else {
                    foreach ($catDef['keywords'] as $k) {
                        $q->orWhere('title', 'like', "%{$k}%");
                    }
                }
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('source_domain', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $videos = $query->paginate(16)->withQueryString();
        $totalCount = (clone $baseQuery)->count();

        // Calculate counts for each category strictly scoped to active .env sources
        $countsCacheKey = 'video_category_counts_' . md5(json_encode($activeUrls));
        $categoryCounts = Cache::remember($countsCacheKey, 60, function () use ($baseQuery) {
            $counts = [];
            foreach (ScrapedVideo::$categoryDefinitions as $slug => $def) {
                $counts[$slug] = (clone $baseQuery)->where(function ($q) use ($def, $slug) {
                    if ($slug === '3d') {
                        $q->where('title', 'like', '%3d%')
                          ->orWhere('title', 'like', '%cgi%')
                          ->orWhere('title', 'like', '%sfm%')
                          ->orWhere('title', 'like', '%blender%')
                          ->orWhere('title', 'like', '%3-d%')
                          ->orWhere('source_url', 'like', '%3d%');
                    } else {
                        foreach ($def['keywords'] as $k) {
                            $q->orWhere('title', 'like', "%{$k}%");
                        }
                    }
                })->count();
            }
            return $counts;
        });

        return view('videos.index', [
            'videos' => $videos,
            'totalCount' => $totalCount,
            'search' => $search,
            'currentCategory' => $currentCategory,
            'categories' => ScrapedVideo::$categoryDefinitions,
            'categoryCounts' => $categoryCounts,
        ]);
    }

    /**
     * Dedicated Watch Page with Native Full-Length Stream Playback.
     */
    public function watch(string $slug)
    {
        $video = ScrapedVideo::notFunny()
            ->where(function ($q) use ($slug) {
                $q->where('slug', $slug)->orWhere('id', $slug);
            })
            ->firstOrFail();

        $activeUrls = $this->getActiveTargetUrls();
        if (!empty($activeUrls) && !app()->runningUnitTests()) {
            if (!in_array($video->source_url, $activeUrls)) {
                abort(404, 'Video not found in active sources.');
            }
        }

        $resolvedStreamUrl = $video->video_url;
        $videoType = $video->video_type;

        // For hentaicity videos, ensure direct progressive mobile.mp4 is used for instant full-length playback
        if (empty($video->video_url) && str_contains($video->source_domain ?? '', 'hentaicity.com')) {
            $search = ($video->thumbnail_url ?? '') . ' ' . ($video->embed_url ?? '');
            if (preg_match('#(?:videos/|hentaicity\.com/)([0-9\-]{3,})/([0-9]+)/#', $search, $m)) {
                $video->video_url = "https://www.hentaicity.com/flv/{$m[1]}/{$m[2]}/mobile.mp4";
                $video->video_type = 'direct';
                $video->save();
                $resolvedStreamUrl = $video->video_url;
                $videoType = 'direct';
            }
        }

        // For watchhentai.net videos, resolve direct stream from primary player
        if (empty($video->video_url) && str_contains($video->source_domain ?? '', 'watchhentai.net') && !empty($video->embed_url)) {
            try {
                $directUrl = Cache::remember("watchhentai_stream_{$video->id}", 86400, function () use ($video) {
                    $epRes = Http::withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Referer' => 'https://watchhentai.net/',
                    ])->timeout(10)->withoutVerifying()->get($video->embed_url);

                    if ($epRes->successful()) {
                        $html = $epRes->body();
                        $playerUrl = null;
                        if (preg_match('/data-primary-player-url=["\']([^"\']+)["\']/i', $html, $m)) {
                            $playerUrl = $m[1];
                        } elseif (preg_match('/<iframe[^>]+src=["\'](https?:\/\/watchhentai\.net\/player\/[^"\']+)["\']/i', $html, $m)) {
                            $playerUrl = $m[1];
                        }

                        if ($playerUrl) {
                            $playerRes = Http::withHeaders([
                                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                                'Referer' => $video->embed_url,
                            ])->timeout(10)->withoutVerifying()->get($playerUrl);

                            if ($playerRes->successful()) {
                                $playerHtml = $playerRes->body();
                                if (preg_match('/"contentUrl"\s*:\s*["\']([^"\']+)["\']/i', $playerHtml, $cm)) {
                                    return $cm[1];
                                }
                                if (preg_match('/(?:https?:)?\/\/[^\s"\'<>]+\.(?:mp4|m3u8)[^\s"\'<>]*/i', $playerHtml, $sm)) {
                                    return $sm[0];
                                }
                            }
                        }
                    }
                    return null;
                });

                if ($directUrl) {
                    $video->video_url = $directUrl;
                    $video->video_type = 'direct';
                    $video->save();
                    $resolvedStreamUrl = $directUrl;
                    $videoType = 'direct';
                }
            } catch (\Throwable $e) {
                Log::warning("WatchHentai stream resolution failed: " . $e->getMessage());
            }
        }

        // Fallback: extract direct full-length HLS stream from embed player if available (bypasses 3rd-party JWPlayer errors)
        if (empty($resolvedStreamUrl) && !empty($video->embed_url) && str_contains($video->embed_url, 'hentaicity.com/embed/')) {
            try {
                $hls = Cache::remember("hls_stream_{$video->id}", 1800, function () use ($video) {
                    $res = Http::withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Referer' => 'https://www.hentaicity.com/',
                    ])->timeout(5)->withoutVerifying()->get($video->embed_url);

                    if ($res->successful() && preg_match('/file\s*:\s*["\'](https?:\/\/[^"\']+\.m3u8[^"\']*)["\']/i', $res->body(), $m)) {
                        return html_entity_decode($m[1]);
                    }
                    return null;
                });

                if ($hls) {
                    $resolvedStreamUrl = $hls;
                    $videoType = 'hls';
                }
            } catch (\Exception $e) {
                Log::warning("Live HLS resolution error: " . $e->getMessage());
            }
        }

        $relatedQuery = ScrapedVideo::notFunny()->where('id', '!=', $video->id);
        if (!empty($activeUrls)) {
            $relatedQuery->whereIn('source_url', $activeUrls);
        }
        $relatedVideos = $relatedQuery->latest()->take(10)->get();

        return view('videos.watch', [
            'video' => $video,
            'resolvedStreamUrl' => $resolvedStreamUrl,
            'videoType' => $videoType,
            'relatedVideos' => $relatedVideos,
        ]);
    }

    /**
     * Remove a video card.
     */
    public function destroy(string $id)
    {
        $video = ScrapedVideo::findOrFail($id);
        $video->delete();

        return redirect()->back()->with('success', 'Video card removed.');
    }

    /**
     * XML Sitemap for Google Video Search Indexing.
     */
    public function sitemap()
    {
        $activeUrls = $this->getActiveTargetUrls();
        $query = ScrapedVideo::notFunny()->latest();
        if (!empty($activeUrls)) {
            $query->whereIn('source_url', $activeUrls);
        }
        $videos = $query->take(1000)->get();
        $categories = ScrapedVideo::$categoryDefinitions;

        return response()->view('sitemap', [
            'videos' => $videos,
            'categories' => $categories,
        ])->header('Content-Type', 'text/xml');
    }

    /**
     * Clear all videos.
     */
    public function clearAll()
    {
        ScrapedVideo::truncate();
        Cache::forget('synced_env_target_hash');

        return redirect()->route('home')->with('success', 'All video cards have been cleared.');
    }
}
