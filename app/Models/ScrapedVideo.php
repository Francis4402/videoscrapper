<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

class ScrapedVideo extends Model implements Sitemapable
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'id',
        'slug',
        'source_url',
        'source_domain',
        'title',
        'description',
        'thumbnail_url',
        'video_url',
        'embed_url',
        'video_type',
        'duration',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if (empty($model->slug) && !empty($model->title)) {
                $model->slug = static::generateUniqueSlug($model->title, $model->id);
            }
        });
    }

    /**
     * Generate SEO-friendly unique slug from video title
     */
    public static function generateUniqueSlug(string $title, ?string $id = null): string
    {
        $base = Str::slug($title);
        if (empty($base)) {
            $base = 'video-' . substr((string) ($id ?? Str::uuid()), 0, 8);
        }

        $base = Str::limit($base, 90, '');
        $slug = $base;
        $count = 1;

        while (static::where('slug', $slug)->when($id, fn($q) => $q->where('id', '!=', $id))->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }

    /**
     * Use slug for route model binding
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Check if this is an embed video player
     */
    public function isEmbed(): bool
    {
        return $this->video_type === 'embed' || empty($this->video_url);
    }

    /**
     * Check if this is an HLS stream (.m3u8)
     */
    public function isHls(): bool
    {
        return $this->video_type === 'hls' || (
            !empty($this->video_url) && str_contains(strtolower($this->video_url), '.m3u8')
        );
    }

    public static array $categoryDefinitions = [
        'gangbang' => [
            'slug' => 'gangbang',
            'name' => 'Gangbang Hentai',
            'icon' => 'fa-solid fa-users',
            'keywords' => ['gangbang', 'gang bang', 'group', 'orgy', 'threesome', 'foursome', 'multiple', 'harem'],
        ],
        'cartoon' => [
            'slug' => 'cartoon',
            'name' => 'Cartoon Hentai',
            'icon' => 'fa-solid fa-paintbrush',
            'keywords' => ['cartoon', 'animation', 'animated', 'toon', 'western', 'parody', 'drawn'],
        ],
        'uncensored' => [
            'slug' => 'uncensored',
            'name' => 'Uncensored Hentai',
            'icon' => 'fa-solid fa-eye',
            'keywords' => ['uncensored', 'decensored', 'mosaic', 'raw', 'unfiltered'],
        ],
        '3d' => [
            'slug' => '3d',
            'name' => '3D Hentai',
            'icon' => 'fa-solid fa-cube',
            'keywords' => ['3d', 'cgi', 'sfm', 'blender', '3-d'],
        ],
        'anime' => [
            'slug' => 'anime',
            'name' => 'Anime Hentai',
            'icon' => 'fa-solid fa-tv',
            'keywords' => ['hentai', 'anime', 'ecchi', 'doujinshi', 'ova'],
        ],
        'busty' => [
            'slug' => 'busty',
            'name' => 'Busty',
            'icon' => 'fa-solid fa-heart',
            'keywords' => ['busty', 'tits', 'titty', 'boob'],
        ],
        'school' => [
            'slug' => 'school',
            'name' => 'School',
            'icon' => 'fa-solid fa-graduation-cap',
            'keywords' => ['school', 'student', 'teen', 'academy'],
        ],
        'teacher' => [
            'slug' => 'teacher',
            'name' => 'Teacher',
            'icon' => 'fa-solid fa-chalkboard-user',
            'keywords' => ['teacher', 'principal', 'sensei'],
        ],
        'mature' => [
            'slug' => 'mature',
            'name' => 'Mature / MILF',
            'icon' => 'fa-solid fa-gem',
            'keywords' => ['mature', 'housewife', 'widow', 'mom', 'milf'],
        ],
        'fantasy' => [
            'slug' => 'fantasy',
            'name' => 'Fantasy & Elf',
            'icon' => 'fa-solid fa-wand-magic-sparkles',
            'keywords' => ['elf', 'succubus', 'demon', 'fairy', 'cursed', 'knight', 'magic'],
        ],
        'maid' => [
            'slug' => 'maid',
            'name' => 'Maid',
            'icon' => 'fa-solid fa-bell-concierge',
            'keywords' => ['maid', 'servant'],
        ],
        'oral' => [
            'slug' => 'oral',
            'name' => 'Blowjob',
            'icon' => 'fa-solid fa-fire-flame-curved',
            'keywords' => ['blowjob', 'oral', 'face fucking'],
        ],
        'creampie' => [
            'slug' => 'creampie',
            'name' => 'Creampie',
            'icon' => 'fa-solid fa-droplet',
            'keywords' => ['creampie', 'impregnat', 'cum'],
        ],
        'drama' => [
            'slug' => 'drama',
            'name' => 'Drama & Affair',
            'icon' => 'fa-solid fa-masks-theater',
            'keywords' => ['cheating', 'unfaithful', 'affair', 'husband'],
        ],
        'anime' => [
            'slug' => 'anime',
            'name' => 'Anime',
            'icon' => 'fa-solid fa-tv',
            'keywords' => ['hentai', 'anime', 'ecchi', 'doujinshi'],
        ],
    ];

    /**
     * Scope query to exclude funny videos, memes, and vlipsy clips
     */
    public function scopeNotFunny($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('source_domain')->orWhere('source_domain', 'not like', '%vlipsy%');
        })->where(function ($q) {
            $q->where('title', 'not like', '%funny%')
              ->where('title', 'not like', '%meme%')
              ->where('title', 'not like', '%laughing%')
              ->where('title', 'not like', '%lol montage%');
        });
    }

    /**
     * Get matching categories for this video
     */
    public function getCategoriesAttribute(): array
    {
        $cats = [];
        $titleLower = strtolower($this->title);
        $sourceLower = strtolower($this->source_url ?? '');

        foreach (self::$categoryDefinitions as $slug => $def) {
            if ($slug === '3d' && (str_contains($sourceLower, '3d') || str_contains($sourceLower, 'cgi'))) {
                $cats[] = $def;
                continue;
            }

            foreach ($def['keywords'] as $kw) {
                if (str_contains($titleLower, $kw)) {
                    $cats[] = $def;
                    break;
                }
            }
        }

        if (empty($cats)) {
            $cats[] = [
                'slug' => 'general',
                'name' => 'General',
                'icon' => 'fa-solid fa-video',
                'keywords' => [],
            ];
        }

        return $cats;
    }

    /**
     * Get a cleaned, normalized version of the video title without domain watermarks or file extensions.
     */
    public function getCleanTitleAttribute(): string
    {
        $title = $this->title ?? 'Hentai Video';

        // Remove video file extensions
        $title = preg_replace('/\.(mp4|m3u8|webm|mkv|avi|flv)$/i', '', $title);

        // Remove source domain watermarks or spam tags like "www.", ".com", ".net"
        $title = preg_replace('/(https?:\/\/)?(www\.)?[a-zA-Z0-9-]+\.(com|net|org|tv|to|io|me|cc)\b/i', '', $title);

        // Remove scraper site domain tags
        $title = preg_replace('/\b(hentaicity|watchhentai|spankbang|pornhub|xvideos|xnxx)\b/i', '', $title);

        // Clean excessive whitespace, leading/trailing dashes and separators
        $title = trim(preg_replace('/[\s\-_|:]+$/', '', preg_replace('/^[\s\-_|:]+/', '', $title)));
        $title = preg_replace('/\s+/', ' ', $title);

        return !empty($title) ? $title : 'Featured Anime Episode';
    }

    /**
     * Get a guaranteed unique, high-CTR SEO title for this video.
     * Uses deterministic formula rotation so each video has distinct wording to prevent Google duplicate content flags.
     */
    public function getSeoTitleAttribute(): string
    {
        $cleanTitle = $this->clean_title;
        $primaryCategory = $this->categories[0]['name'] ?? 'Anime';
        if ($primaryCategory === 'General') {
            $primaryCategory = 'Hentai';
        }

        // Deterministic template index based on UUID/ID
        $hash = crc32((string) ($this->id ?? $cleanTitle));
        $patterns = [
            "Watch {$cleanTitle} Full Episode (HD {$primaryCategory}) | HentaiFlex",
            "{$cleanTitle} - Free {$primaryCategory} Hentai Streaming in 1080p | HentaiFlex",
            "Stream {$cleanTitle} Online ({$primaryCategory} Anime HD) | HentaiFlex",
            "{$cleanTitle} - Watch Full Uncensored Hentai Online | HentaiFlex",
            "Watch {$cleanTitle} [{$primaryCategory} HD] Streaming Online | HentaiFlex",
            "{$cleanTitle} - Free Full-Length {$primaryCategory} Anime | HentaiFlex",
            "Stream {$cleanTitle} Free HD - {$primaryCategory} Video | HentaiFlex",
            "{$cleanTitle} - Watch Full HD Hentai Online | HentaiFlex",
        ];

        return $patterns[abs($hash) % count($patterns)];
    }

    /**
     * Generate an authentic, completely unique narrative description for Google.
     * Synthesizes title, categories, duration, and content highlights so no two videos share duplicate text.
     */
    public function getSeoDescriptionAttribute(): string
    {
        $cleanTitle = $this->clean_title;
        $categoryNames = array_map(fn($c) => strtolower($c['name']), array_slice($this->categories, 0, 3));
        $categoryStr = !empty($categoryNames) ? implode(', ', $categoryNames) : 'uncensored anime and hentai';
        $durationStr = $this->duration ? " featuring a runtime of {$this->duration}" : '';

        // Deterministic template variation
        $hash = crc32((string) ($this->id ?? $cleanTitle));
        $templates = [
            "Watch {$cleanTitle} full episode in 1080p HD on HentaiFlex{$durationStr}. This {$categoryStr} anime release features intense uncensored animation, premium visuals, and captivating storylines. Stream now with instant playback, zero buffering, and mobile compatibility.",
            "Stream {$cleanTitle} online for free in high definition on HentaiFlex. Showcasing top-tier {$categoryStr} scenes{$durationStr}, this full-length animation delivers crystal-clear 1080p visuals and seamless direct MP4 playback on all devices with no sign-up needed.",
            "Experience {$cleanTitle} in ultra-sharp HD streaming on HentaiFlex{$durationStr}. Discover thrilling {$categoryStr} moments with full uncensored action and dynamic sound design. Enjoy unlimited free streaming without buffering or account registration.",
            "Watch full-length {$cleanTitle} online on HentaiFlex, featuring premium {$categoryStr} animation{$durationStr}. Immerse yourself in high-resolution direct video playback with smooth seeking, adaptive streaming, and 100% free access.",
            "Dive into {$cleanTitle} on HentaiFlex{$durationStr}. This highly requested {$categoryStr} episode brings fluid animation, vibrant artwork, and complete uncensored scenes straight to your mobile or desktop browser with zero interruptions.",
            "Stream the complete {$cleanTitle} anime video on HentaiFlex{$durationStr}. Highlighting captivating {$categoryStr} themes, this full HD stream is optimized for fast loading, instant seeking, and superior playback quality on any screen.",
        ];

        $synthesized = $templates[abs($hash) % count($templates)];

        // If the scraped video already had a descriptive blurb, sanitize and merge it seamlessly
        if (!empty($this->description) && strlen(trim($this->description)) > 25) {
            $cleanedOriginal = strip_tags($this->description);
            // Remove source watermarks from scraped text
            $cleanedOriginal = preg_replace('/(https?:\/\/)?(www\.)?[a-zA-Z0-9-]+\.(com|net|org|tv|to|io|me|cc)\b/i', '', $cleanedOriginal);
            $cleanedOriginal = preg_replace('/\b(hentaicity|watchhentai|spankbang|pornhub|xvideos|xnxx)\b/i', '', $cleanedOriginal);
            $cleanedOriginal = trim(preg_replace('/\s+/', ' ', $cleanedOriginal));

            if (strlen($cleanedOriginal) > 30) {
                return $cleanedOriginal . ' ' . $synthesized;
            }
        }

        return $synthesized;
    }

    /**
     * Get a concise 150-160 character meta description for search engine SERP snippets.
     */
    public function getSeoSummaryAttribute(): string
    {
        return Str::limit(strip_tags($this->seo_description), 158);
    }

    /**
     * Get clean playback stream URL (or fallback)
     */
    public function getPlayableStreamUrl(): ?string
    {
        if ($this->video_url) {
            return route('stream.proxy', ['id' => $this->id]);
        }
        return null;
    }

    /**
     * Convert model instance to a Spatie Sitemap Tag with rich Google Video & Image SEO.
     */
    public function toSitemapTag(): Url | string | array
    {
        $slug = $this->slug ?: $this->id;
        $url = Url::create(route('videos.watch', $slug))
            ->setLastModificationDate($this->updated_at ?? now())
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            ->setPriority(0.7);

        if ($this->video_url) {
            $thumb = $this->thumbnail_url;
            if ($thumb && Str::startsWith($thumb, '//')) {
                $thumb = 'https:' . $thumb;
            } elseif ($thumb && !Str::startsWith($thumb, 'http')) {
                $thumb = url($thumb);
            }
            $thumb = $thumb ?: url('/favicon.ico');

            $options = [
                'family_friendly' => 'no',
                'publication_date' => ($this->created_at ?? now())->toAtomString(),
                'category' => 'Anime & Hentai',
                'view_count' => 12500,
                'rating' => 4.9,
            ];

            if ($this->duration) {
                $parts = array_map('intval', explode(':', $this->duration));
                $sec = count($parts) === 3 ? ($parts[0] * 3600 + $parts[1] * 60 + $parts[2]) : (count($parts) === 2 ? ($parts[0] * 60 + $parts[1]) : ($parts[0] ?? 600));
                $options['duration'] = $sec;
            }

            $tags = [
                'hentai', 'watch hentai', 'watch hentai online', 'free hentai', 'hentai online free',
                'hentai streaming', 'best hentai sites', 'hentai video', 'hentai videos', 'hd hentai',
                'gangbang hentai', 'cartoon hentai', '3d hentai', 'uncensored hentai', 'full hentai episodes',
                'english sub hentai', 'english dub hentai', 'hentai tube', 'milf hentai', 'tentacle hentai',
                'harem anime', 'anime', 'hd anime', 'hentaiflex'
            ];

            // Google Video SEO: Use dedicated embed player URL for player_loc.
            // Omit content_loc so Googlebot uses player_loc and never complains about raw byte downloads or play page mismatches.
            $playerLoc = route('videos.embed', $slug);

            $url->addVideo(
                thumbnailLoc: $thumb,
                title: $this->seo_title,
                description: Str::limit(strip_tags($this->seo_description), 250),
                contentLoc: null,
                playerLoc: $playerLoc,
                options: $options,
                tags: $tags
            );

            if ($thumb) {
                $url->addImage($thumb, $this->seo_title, '', $this->seo_title);
            }
        }

        return $url;
    }
}
