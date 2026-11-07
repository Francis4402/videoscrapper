<?php

namespace App\Services;

use App\Models\ScrapedVideo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class VideoScraperService
{
    protected string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';

    /**
     * Scrape videos from the provided URL.
     *
     * @param string $url
     * @return array<ScrapedVideo>
     */
    public function scrape(string $url): array
    {
        $url = trim($url);
        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . $url;
        }

        $parsedUrl = parse_url($url);
        $domain = $parsedUrl['host'] ?? 'unknown';

        // 1. Check if the URL is directly a video file
        if ($this->isDirectVideoUrl($url)) {
            $video = $this->createFromDirectLink($url, $domain);
            return [$video];
        }

        // 2. Fetch the webpage content
        try {
            $response = Http::withHeaders([
                'User-Agent' => $this->userAgent,
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Referer' => $url,
            ])
            ->timeout(15)
            ->withoutVerifying()
            ->get($url);

            if (!$response->successful()) {
                throw new \Exception("Failed to fetch page. HTTP status code: " . $response->status());
            }

            $html = $response->body();
        } catch (\Exception $e) {
            Log::error("Scraper fetch error for {$url}: " . $e->getMessage());
            throw new \Exception("Unable to access website: " . $e->getMessage());
        }

        $crawler = new Crawler($html, $url);
        $foundVideos = [];
        $seenSources = [];

        // Extract global page metadata for fallback
        $pageTitle = $this->extractPageTitle($crawler, $html);
        $pagePoster = $this->extractMetaPoster($crawler);
        $pageDescription = $this->extractMetaContent($crawler, ['description', 'og:description', 'twitter:description']);

        // A. Platform-specific clip listings (e.g. Vlipsy search/listing)
        $platformVideos = $this->extractPlatformClips($crawler, $html, $url, $domain);
        foreach ($platformVideos as $v) {
            $key = $v['embed_url'] ?: $v['video_url'];
            if ($key && !isset($seenSources[$key])) {
                $seenSources[$key] = true;
                $foundVideos[] = $v;
            }
        }

        // B. Recursive JSON-LD Schema.org VideoObject extraction
        $jsonLdVideos = $this->extractJsonLdVideos($crawler, $url, $domain, $pageTitle, $pagePoster);
        foreach ($jsonLdVideos as $v) {
            $key = $v['video_url'] ?: $v['embed_url'];
            if ($key && !isset($seenSources[$key])) {
                $seenSources[$key] = true;
                $foundVideos[] = $v;
            }
        }

        // C. Check HTML5 <video> elements with ancestor context search
        $crawler->filter('video')->each(function (Crawler $node) use ($url, $domain, $pageTitle, $pagePoster, &$foundVideos, &$seenSources) {
            $src = $node->attr('src');
            if (!$src) {
                $node->filter('source')->each(function (Crawler $sourceNode) use (&$src) {
                    if (!$src && $sourceNode->attr('src')) {
                        $src = $sourceNode->attr('src');
                    }
                });
            }

            if ($src) {
                $cleanSrc = $this->cleanVideoUrl($this->resolveUrl($src, $url));
                if (!isset($seenSources[$cleanSrc])) {
                    $seenSources[$cleanSrc] = true;

                    $title = $node->attr('title') ?: $node->attr('aria-label');
                    $poster = $node->attr('poster') ? $this->resolveUrl($node->attr('poster'), $url) : null;
                    $duration = null;

                    // Traverse up ancestor DOM nodes to find associated <img> thumbnail, title, and duration
                    if (!$poster || !$title) {
                        try {
                            $domElement = $node->getNode(0);
                            $curr = $domElement ? $domElement->parentNode : null;
                            $depth = 0;
                            while ($curr && $curr instanceof \DOMElement && $curr->nodeName !== 'body' && $depth < 6) {
                                $parentCrawler = new Crawler($curr, $url);

                                if (!$poster) {
                                    $img = $parentCrawler->filter('img');
                                    if ($img->count()) {
                                        $rawImg = $img->attr('src') ?: $img->attr('data-src') ?: $img->attr('data-original');
                                        if ($rawImg && $this->isValidThumbnail($rawImg)) {
                                            $poster = $this->resolveUrl($rawImg, $url);
                                            if (!$title && $img->attr('alt')) {
                                                $title = trim($img->attr('alt'));
                                            }
                                        }
                                    }
                                }

                                if (!$title) {
                                    $titleNode = $parentCrawler->filter('.video-title, .title, a[title], h2, h3');
                                    if ($titleNode->count()) {
                                        $t = trim($titleNode->first()->text() ?: $titleNode->first()->attr('title'));
                                        if ($t && $t !== $pageTitle) {
                                            $title = $t;
                                        }
                                    }
                                }

                                if (!$duration) {
                                    $timeNode = $parentCrawler->filter('.time, .duration, span[class*="time"]');
                                    if ($timeNode->count()) {
                                        $duration = trim($timeNode->first()->text());
                                    }
                                }

                                if ($poster && $title && $duration) break;
                                $curr = $curr->parentNode;
                                $depth++;
                            }
                        } catch (\Exception $e) {
                            // Continue gracefully
                        }
                    }

                    $trailerId = $node->attr('id');
                    $embedUrl = null;
                    $videoType = str_contains(strtolower($cleanSrc), '.m3u8') ? 'hls' : 'direct';

                    // If this is hentaicity.com, extract the full-length progressive direct MP4
                    if (str_contains($domain, 'hentaicity.com')) {
                        $searchStr = ($cleanSrc ?? '') . ' ' . ($poster ?? '');
                        if (preg_match('#(?:videos/|hentaicity\.com/)([0-9\-]{3,})/([0-9]+)/#', $searchStr, $m)) {
                            $cleanSrc = "https://www.hentaicity.com/flv/{$m[1]}/{$m[2]}/mobile.mp4";
                            $videoType = 'direct';
                        }
                        if ($trailerId) {
                            $embedUrl = "https://www.hentaicity.com/embed/{$trailerId}";
                        }
                    }

                    $foundVideos[] = [
                        'source_url' => $url,
                        'source_domain' => $domain,
                        'title' => $title ?: $pageTitle,
                        'thumbnail_url' => $this->isValidThumbnail($poster) ? $poster : ($this->isValidThumbnail($pagePoster) ? $pagePoster : null),
                        'video_url' => $cleanSrc,
                        'embed_url' => $embedUrl,
                        'video_type' => $videoType,
                        'duration' => $duration,
                    ];
                }
            }
        });

        // D. Check Open Graph / Twitter Player Meta Tags
        $ogVideo = $this->extractOgVideos($crawler, $url, $domain, $pageTitle, $pagePoster, $pageDescription);
        if ($ogVideo) {
            $key = $ogVideo['video_url'] ?: $ogVideo['embed_url'];
            if ($key && !isset($seenSources[$key])) {
                $seenSources[$key] = true;
                $foundVideos[] = $ogVideo;
            }
        }

        // E. Check <iframe> embeds (YouTube, Vimeo, Dailymotion, custom embed players)
        $crawler->filter('iframe')->each(function (Crawler $node) use ($url, $domain, $pageTitle, $pagePoster, &$foundVideos, &$seenSources) {
            $src = $node->attr('src') ?: $node->attr('data-src');
            if (!$src) return;

            $absoluteSrc = $this->resolveUrl($src, $url);
            if ($this->isLikelyVideoIframe($absoluteSrc)) {
                if (!isset($seenSources[$absoluteSrc])) {
                    $seenSources[$absoluteSrc] = true;
                    $title = $node->attr('title') ?: $pageTitle;
                    $thumb = $this->extractIframeThumbnail($absoluteSrc) ?: $pagePoster;

                    $foundVideos[] = [
                        'source_url' => $url,
                        'source_domain' => $domain,
                        'title' => $title . ($title === $pageTitle && count($foundVideos) > 0 ? ' (Embed ' . (count($foundVideos) + 1) . ')' : ''),
                        'thumbnail_url' => $this->isValidThumbnail($thumb) ? $thumb : null,
                        'video_url' => null,
                        'embed_url' => $this->sanitizeEmbedUrl($absoluteSrc),
                        'video_type' => 'embed',
                        'duration' => null,
                    ];
                }
            }
        });

        // F. Regex search in inline scripts and HTML for video URLs (.m3u8, .mp4, .webm)
        if (empty($foundVideos)) {
            $scriptVideos = $this->extractScriptVideoUrls($html, $url, $domain, $pageTitle, $pagePoster);
            foreach ($scriptVideos as $v) {
                $key = $v['video_url'];
                if ($key && !isset($seenSources[$key])) {
                    $seenSources[$key] = true;
                    $foundVideos[] = $v;
                }
            }
        }

        // G. Look for video card links (galleries / listings on the page)
        $cardVideos = $this->extractCardListingVideos($crawler, $url, $domain);
        foreach ($cardVideos as $v) {
            $key = $v['video_url'] ?: $v['embed_url'] ?: $v['source_url'];
            if ($key && !isset($seenSources[$key])) {
                $seenSources[$key] = true;
                $foundVideos[] = $v;
            }
        }

        // H. Watch URL conversion (e.g. YouTube watch?v=...)
        if (empty($foundVideos)) {
            $embedUrl = $this->convertWatchUrlToEmbed($url);
            if ($embedUrl) {
                $thumb = $this->extractIframeThumbnail($embedUrl) ?: $pagePoster;
                $foundVideos[] = [
                    'source_url' => $url,
                    'source_domain' => $domain,
                    'title' => $pageTitle ?: 'Online Video',
                    'thumbnail_url' => $this->isValidThumbnail($thumb) ? $thumb : null,
                    'video_url' => null,
                    'embed_url' => $embedUrl,
                    'video_type' => 'embed',
                    'duration' => null,
                ];
            }
        }

        if (empty($foundVideos)) {
            throw new \Exception("No playable video or embed could be found on the provided page.");
        }

        // Persist to database
        $models = [];
        foreach ($foundVideos as $item) {
            $cleanTitle = Str::limit(trim($item['title'] ?? 'Scraped Video'), 200);
            $cleanThumb = $item['thumbnail_url'] ?? null;
            if ($cleanThumb && !$this->isValidThumbnail($cleanThumb)) {
                $cleanThumb = null;
            }

            // Look for existing record by video_url or embed_url to update its thumbnail and title
            $lookup = [];
            if (!empty($item['video_url'])) {
                $lookup['video_url'] = $item['video_url'];
            } elseif (!empty($item['embed_url'])) {
                $lookup['embed_url'] = $item['embed_url'];
            } else {
                $lookup['source_url'] = $item['source_url'];
            }

            $model = ScrapedVideo::updateOrCreate(
                $lookup,
                [
                    'source_url' => $item['source_url'],
                    'source_domain' => $item['source_domain'] ?? $domain,
                    'title' => $cleanTitle ?: 'Scraped Video',
                    'description' => $item['description'] ?? $pageDescription,
                    'thumbnail_url' => $cleanThumb,
                    'video_type' => $item['video_type'] ?? 'direct',
                    'duration' => $item['duration'] ?? null,
                ]
            );
            $models[] = $model;
        }

        return $models;
    }

    protected function isDirectVideoUrl(string $url): bool
    {
        $path = strtolower(parse_url($url, PHP_URL_PATH) ?? '');
        return Str::endsWith($path, ['.mp4', '.webm', '.m3u8', '.ogv', '.mov']);
    }

    protected function createFromDirectLink(string $url, string $domain): ScrapedVideo
    {
        $filename = basename(parse_url($url, PHP_URL_PATH) ?? 'video');
        $title = ucwords(str_replace(['-', '_', '.'], ' ', pathinfo($filename, PATHINFO_FILENAME)));

        return ScrapedVideo::create([
            'source_url' => $url,
            'source_domain' => $domain,
            'title' => $title ?: 'Direct Video Stream',
            'thumbnail_url' => null,
            'video_url' => $url,
            'embed_url' => null,
            'video_type' => str_contains(strtolower($url), '.m3u8') ? 'hls' : 'direct',
            'duration' => null,
        ]);
    }

    /**
     * Platform-specific extractor (e.g. Vlipsy clip cards, search listings)
     */
    protected function extractPlatformClips(Crawler $crawler, string $html, string $url, string $domain): array
    {
        $clips = [];

        // Vlipsy.com clip listing detection
        if (str_contains($domain, 'vlipsy.com')) {
            $crawler->filter('img')->each(function (Crawler $node) use ($url, $domain, &$clips) {
                $src = $node->attr('src') ?: $node->attr('data-src');
                if ($src && preg_match('#clips/([a-zA-Z0-9]+)/preview#', $src, $matches)) {
                    $clipId = $matches[1];
                    $title = trim($node->attr('alt') ?: 'Funny Clip');

                    if (!isset($clips[$clipId])) {
                        $clips[$clipId] = [
                            'source_url' => $url,
                            'source_domain' => $domain,
                            'title' => $title,
                            'description' => null,
                            'thumbnail_url' => "https://cdn.vlipsy.com/clips/{$clipId}/preview-small.jpg",
                            'video_url' => null,
                            'embed_url' => "https://vlipsy.com/embed/{$clipId}",
                            'video_type' => 'embed',
                            'duration' => null,
                        ];
                    }
                }
            });
        }

        return array_values($clips);
    }

    protected function extractPageTitle(Crawler $crawler, string $html): string
    {
        $og = $crawler->filter('meta[property="og:title"], meta[name="og:title"]');
        if ($og->count() && $og->attr('content')) {
            return trim($og->attr('content'));
        }

        $titleTag = $crawler->filter('title');
        if ($titleTag->count() && $titleTag->text()) {
            return trim($titleTag->text());
        }

        $h1Tag = $crawler->filter('h1');
        if ($h1Tag->count() && $h1Tag->text()) {
            return trim($h1Tag->text());
        }

        return 'Scraped Video';
    }

    protected function extractMetaPoster(Crawler $crawler): ?string
    {
        $poster = $this->extractMetaContent($crawler, ['og:image', 'twitter:image', 'image']);
        return $this->isValidThumbnail($poster) ? $poster : null;
    }

    protected function isValidThumbnail(?string $url): bool
    {
        if (empty($url)) return false;
        $lower = strtolower($url);

        $invalidPatterns = ['logo', 'avatar', 'pixel', 'icon', 'favicon', 'spacer.gif', 'blank.png'];
        foreach ($invalidPatterns as $pat) {
            if (str_contains($lower, $pat)) {
                return false;
            }
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    protected function extractMetaContent(Crawler $crawler, array $names): ?string
    {
        foreach ($names as $name) {
            $nodes = $crawler->filter("meta[name='{$name}'], meta[property='{$name}']");
            if ($nodes->count() && $nodes->attr('content')) {
                return trim($nodes->attr('content'));
            }
        }
        return null;
    }

    protected function extractOgVideos(Crawler $crawler, string $url, string $domain, string $pageTitle, ?string $pagePoster, ?string $description): ?array
    {
        $videoUrl = $this->extractMetaContent($crawler, ['og:video', 'og:video:url', 'og:video:secure_url', 'twitter:player:stream']);
        $embedUrl = $this->extractMetaContent($crawler, ['twitter:player']);

        if (!$videoUrl && !$embedUrl) {
            return null;
        }

        $isDirect = false;
        if ($videoUrl) {
            $isDirect = $this->isDirectVideoUrl($videoUrl);
        }

        return [
            'source_url' => $url,
            'source_domain' => $domain,
            'title' => $pageTitle,
            'description' => $description,
            'thumbnail_url' => $pagePoster,
            'video_url' => $isDirect ? $this->cleanVideoUrl($videoUrl) : null,
            'embed_url' => !$isDirect ? ($videoUrl ?: $embedUrl) : null,
            'video_type' => $isDirect ? (str_contains(strtolower($videoUrl), '.m3u8') ? 'hls' : 'direct') : 'embed',
            'duration' => null,
        ];
    }

    protected function extractJsonLdVideos(Crawler $crawler, string $url, string $domain, string $pageTitle, ?string $pagePoster): array
    {
        $videos = [];

        $crawler->filter('script[type="application/ld+json"]')->each(function (Crawler $node) use ($url, $domain, $pageTitle, $pagePoster, &$videos) {
            $jsonText = trim($node->text());
            $data = json_decode($jsonText, true);
            if (!$data) return;

            $walk = function ($item) use (&$walk, &$videos, $url, $domain, $pageTitle, $pagePoster) {
                if (!is_array($item)) return;

                $type = $item['@type'] ?? null;
                $isMatch = false;

                if (is_string($type) && strtolower($type) === 'videoobject') {
                    $isMatch = true;
                } elseif (is_array($type) && in_array('VideoObject', $type)) {
                    $isMatch = true;
                }

                if ($isMatch) {
                    $contentUrl = $item['contentUrl'] ?? null;
                    $embedUrl = $item['embedUrl'] ?? null;
                    $thumb = is_array($item['thumbnailUrl'] ?? null) ? ($item['thumbnailUrl'][0] ?? null) : ($item['thumbnailUrl'] ?? null);
                    $name = $item['name'] ?? $pageTitle;

                    if ($contentUrl || $embedUrl) {
                        $isDirect = $contentUrl && $this->isDirectVideoUrl($contentUrl);
                        $videos[] = [
                            'source_url' => $url,
                            'source_domain' => $domain,
                            'title' => $name,
                            'description' => $item['description'] ?? null,
                            'thumbnail_url' => $this->isValidThumbnail($thumb) ? $thumb : $pagePoster,
                            'video_url' => $isDirect ? $this->cleanVideoUrl($contentUrl) : null,
                            'embed_url' => !$isDirect ? ($embedUrl ?: $contentUrl) : null,
                            'video_type' => $isDirect ? (str_contains(strtolower($contentUrl), '.m3u8') ? 'hls' : 'direct') : 'embed',
                            'duration' => $this->formatDuration($item['duration'] ?? null),
                        ];
                    }
                }

                foreach ($item as $val) {
                    if (is_array($val)) {
                        $walk($val);
                    }
                }
            };

            $walk($data);
        });

        return $videos;
    }

    protected function extractScriptVideoUrls(string $html, string $url, string $domain, string $pageTitle, ?string $pagePoster): array
    {
        $videos = [];
        
        $patterns = [
            '/(?:file|source|video_url|videoUrl|stream_url|contentUrl)[\'"]?\s*[:=]\s*[\'"](https?:\/\/[^\'"\s]+\.(?:mp4|m3u8|webm)(?:\?[^\'"\s]*)?)[\'"]/i',
            '/(https?:\/\/[^\s\'"<>]+?\.(?:mp4|m3u8|webm)(?:\?[^\s\'"<>]*)?)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $html, $matches)) {
                foreach ($matches[1] as $matchedUrl) {
                    $cleanUrl = $this->cleanVideoUrl($matchedUrl);
                    $lower = strtolower($cleanUrl);

                    if (str_contains($lower, 'sample') && !str_contains($url, 'sample')) continue;
                    if (str_contains($lower, 'analytics') || str_contains($lower, 'pixel')) continue;

                    $isHls = str_contains($lower, '.m3u8');

                    $videos[] = [
                        'source_url' => $url,
                        'source_domain' => $domain,
                        'title' => $pageTitle,
                        'thumbnail_url' => $pagePoster,
                        'video_url' => $cleanUrl,
                        'embed_url' => null,
                        'video_type' => $isHls ? 'hls' : 'direct',
                        'duration' => null,
                    ];
                    if (count($videos) >= 4) break 2;
                }
            }
        }

        return $videos;
    }

    protected function extractCardListingVideos(Crawler $crawler, string $baseUrl, string $domain): array
    {
        $cards = [];

        $crawler->filter('a')->each(function (Crawler $node) use ($baseUrl, $domain, &$cards) {
            if (count($cards) >= 12) return;
            $href = $node->attr('href');
            if (!$href) return;

            $absoluteHref = $this->resolveUrl($href, $baseUrl);
            if ($this->isDirectVideoUrl($absoluteHref)) {
                $text = trim($node->text());
                $img = $node->filter('img');
                $thumb = $img->count() ? $this->resolveUrl($img->attr('src') ?: $img->attr('data-src') ?: '', $baseUrl) : null;

                $cards[] = [
                    'source_url' => $baseUrl,
                    'source_domain' => $domain,
                    'title' => $text ?: pathinfo(parse_url($absoluteHref, PHP_URL_PATH), PATHINFO_FILENAME),
                    'thumbnail_url' => $this->isValidThumbnail($thumb) ? $thumb : null,
                    'video_url' => $this->cleanVideoUrl($absoluteHref),
                    'embed_url' => null,
                    'video_type' => str_contains(strtolower($absoluteHref), '.m3u8') ? 'hls' : 'direct',
                    'duration' => null,
                ];
            }
        });

        return $cards;
    }

    protected function isLikelyVideoIframe(string $url): bool
    {
        $lower = strtolower($url);
        $videoKeywords = [
            'youtube.com/embed', 'player.vimeo.com', 'dailymotion.com/embed', 
            'streamtape.com', 'streamwish.to', 'doodstream.com', 'dood.',
            'mp4upload.com', 'ok.ru/videoembed', 'bilibili.com/player',
            'vlipsy.com/embed', 'embed', 'player', 'playvideo', 'video'
        ];

        foreach ($videoKeywords as $kw) {
            if (str_contains($lower, $kw)) return true;
        }

        return false;
    }

    protected function extractIframeThumbnail(string $url): ?string
    {
        if (preg_match('#youtube(?:-nocookie)?\.com/embed/([a-zA-Z0-9_-]{11})#', $url, $m)) {
            return "https://img.youtube.com/vi/{$m[1]}/hqdefault.jpg";
        }
        return null;
    }

    protected function convertWatchUrlToEmbed(string $url): ?string
    {
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $url, $matches)) {
            return 'https://www.youtube-nocookie.com/embed/' . $matches[1] . '?autoplay=1';
        }

        if (preg_match('/vimeo\.com\/([0-9]+)/i', $url, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[1] . '?autoplay=1';
        }

        if (preg_match('/dailymotion\.com\/video\/([a-zA-Z0-9]+)/i', $url, $matches)) {
            return 'https://www.dailymotion.com/embed/video/' . $matches[1] . '?autoplay=1';
        }

        return null;
    }

    protected function sanitizeEmbedUrl(string $url): string
    {
        if (str_contains($url, 'youtube.com/embed/')) {
            $url = str_replace('youtube.com/embed/', 'youtube-nocookie.com/embed/', $url);
        }
        return $url;
    }

    protected function cleanVideoUrl(string $url): string
    {
        $clean = html_entity_decode($url);
        $clean = str_replace('\\', '', $clean);
        return trim($clean, "'\" \t\n\r\0\x0B");
    }

    protected function formatDuration(?string $duration): ?string
    {
        if (!$duration) return null;
        if (preg_match('/^PT(?:(\d+)M)?(?:(\d+)S)?$/i', $duration, $m)) {
            $min = isset($m[1]) && $m[1] !== '' ? (int)$m[1] : 0;
            $sec = isset($m[2]) && $m[2] !== '' ? (int)$m[2] : 0;
            return sprintf('%02d:%02d', $min, $sec);
        }
        return $duration;
    }

    protected function resolveUrl(string $relativeUrl, string $baseUrl): string
    {
        $relativeUrl = trim($relativeUrl);
        if (empty($relativeUrl)) return '';

        if (preg_match('/^https?:\/\//i', $relativeUrl)) {
            return $relativeUrl;
        }

        if (str_starts_with($relativeUrl, '//')) {
            $scheme = parse_url($baseUrl, PHP_URL_SCHEME) ?? 'https';
            return $scheme . ':' . $relativeUrl;
        }

        $baseParts = parse_url($baseUrl);
        $scheme = $baseParts['scheme'] ?? 'https';
        $host = $baseParts['host'] ?? '';
        $port = isset($baseParts['port']) ? ':' . $baseParts['port'] : '';
        $baseHost = "{$scheme}://{$host}{$port}";

        if (str_starts_with($relativeUrl, '/')) {
            return $baseHost . $relativeUrl;
        }

        $path = $baseParts['path'] ?? '/';
        $dir = dirname($path);
        if ($dir === '\\' || $dir === '.') $dir = '';
        if ($dir && !str_ends_with($dir, '/')) $dir .= '/';

        return $baseHost . ($dir ? '/' . ltrim($dir, '/') : '/') . ltrim($relativeUrl, '/');
    }
}
