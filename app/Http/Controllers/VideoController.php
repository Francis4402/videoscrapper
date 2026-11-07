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
     * Display front page with video cards.
     * Automatically syncs from .env whenever SCRAPE_TARGET_URL is added or changed.
     */
    public function index(Request $request)
    {
        $envTarget = trim((string) config('scraper.target_url'));
        $cacheKey = 'synced_env_target_hash';
        $currentHash = !empty($envTarget) ? md5($envTarget) : null;
        $syncedHash = Cache::get($cacheKey);

        // Auto-sync whenever link in .env is added, modified, or if database is empty
        if (!empty($envTarget) && ($currentHash !== $syncedHash || ScrapedVideo::count() === 0)) {
            try {
                $urls = array_filter(array_map('trim', explode(',', $envTarget)));
                foreach ($urls as $url) {
                    $this->scraperService->scrape($url);
                }
                Cache::forever($cacheKey, $currentHash);
            } catch (\Exception $e) {
                Log::warning("Auto-sync failed for target [{$envTarget}]: " . $e->getMessage());
            }
        }

        $search = $request->query('q');
        $currentCategory = $request->query('category');

        $query = ScrapedVideo::latest();

        // Onclick category filtering
        if ($currentCategory && isset(ScrapedVideo::$categoryDefinitions[$currentCategory])) {
            $catDef = ScrapedVideo::$categoryDefinitions[$currentCategory];
            $query->where(function ($q) use ($catDef) {
                foreach ($catDef['keywords'] as $k) {
                    $q->orWhere('title', 'like', "%{$k}%")
                      ->orWhere('description', 'like', "%{$k}%");
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
        $totalCount = ScrapedVideo::count();

        // Calculate counts for each category
        $categoryCounts = Cache::remember('video_category_counts', 120, function () {
            $counts = [];
            foreach (ScrapedVideo::$categoryDefinitions as $slug => $def) {
                $counts[$slug] = ScrapedVideo::where(function ($q) use ($def) {
                    foreach ($def['keywords'] as $k) {
                        $q->orWhere('title', 'like', "%{$k}%");
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
        $video = ScrapedVideo::where('slug', $slug)
            ->orWhere('id', $slug)
            ->firstOrFail();

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

        $relatedVideos = ScrapedVideo::where('id', '!=', $video->id)
            ->latest()
            ->take(10)
            ->get();

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
     * Clear all videos.
     */
    public function clearAll()
    {
        ScrapedVideo::truncate();
        Cache::forget('synced_env_target_hash');

        return redirect()->route('home')->with('success', 'All video cards have been cleared.');
    }
}
