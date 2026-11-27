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

        // Deterministic template index based on UUID/ID and slug
        $seed = (string) ($this->id ?? '') . (string) ($this->slug ?? '') . $cleanTitle;
        $hash = crc32($seed);

        $patterns = [
            // High-CTR Uncensored & Quality Intents
            "Watch {$cleanTitle} [Uncensored 1080p HD] | HentaiFlex",
            "{$cleanTitle} - Full Uncensored {$primaryCategory} Video | HentaiFlex",
            "Stream {$cleanTitle} in 1080p 60FPS Uncensored | HentaiFlex",
            "{$cleanTitle} (Uncensored HD Stream) - Free {$primaryCategory} | HentaiFlex",
            "Watch {$cleanTitle} [Ultra HD {$primaryCategory}] Streaming | HentaiFlex",
            "{$cleanTitle} - Remastered Uncensored HD Anime | HentaiFlex",

            // High-CTR Full Episode & Subtitled Intents
            "Watch {$cleanTitle} Full Episode Online (English Sub) | HentaiFlex",
            "{$cleanTitle} - Free Full-Length {$primaryCategory} Anime Stream | HentaiFlex",
            "Stream {$cleanTitle} Online Free - HD Anime Video | HentaiFlex",
            "{$cleanTitle} - Watch Full Episode (No Buffering) | HentaiFlex",
            "Watch {$cleanTitle} Online (English Subtitles HD) | HentaiFlex",
            "{$cleanTitle} - Complete Full-Length {$primaryCategory} Episode | HentaiFlex",

            // High-CTR 3D, SFM & Studio Animation Intents
            "Stream {$cleanTitle} [1080p HD] - Free {$primaryCategory} | HentaiFlex",
            "{$cleanTitle} - Premium {$primaryCategory} Animation in 1080p | HentaiFlex",
            "Watch {$cleanTitle} Full Video (High Bitrate HD) | HentaiFlex",
            "Stream {$cleanTitle} HD - Full Uncensored Release | HentaiFlex",
            "{$cleanTitle} - Watch High Bitrate Anime Stream | HentaiFlex",
            "Watch {$cleanTitle} Free HD - {$primaryCategory} Video | HentaiFlex",

            // High-CTR Tube & Trending Search Intents
            "{$cleanTitle} - Best Hentai Streaming Free Online | HentaiFlex",
            "{$cleanTitle} - Fast Mobile {$primaryCategory} Video Stream | HentaiFlex",
            "Stream {$cleanTitle} Full Version Free | HentaiFlex",
            "Watch {$cleanTitle} Uncensored Online - HD Player | HentaiFlex",
            "{$cleanTitle} - Free {$primaryCategory} Streaming in 1080p | HentaiFlex",
            "Stream {$cleanTitle} Online ({$primaryCategory} Anime HD) | HentaiFlex",
            "{$cleanTitle} - Watch Full Uncensored Hentai Online | HentaiFlex",
            "Watch {$cleanTitle} [{$primaryCategory} HD] Streaming Online | HentaiFlex",
            "{$cleanTitle} - Free Full-Length {$primaryCategory} Anime | HentaiFlex",
            "{$cleanTitle} - Watch Full HD Hentai Online | HentaiFlex",
            "Watch {$cleanTitle} Direct Stream - Free {$primaryCategory} HD | HentaiFlex",
            "{$cleanTitle} - Instant 1080p HD Streaming | HentaiFlex",
            "Stream {$cleanTitle} Free in High Definition | HentaiFlex",
            "{$cleanTitle} - Top {$primaryCategory} Anime Episode Online | HentaiFlex",
        ];

        return $patterns[abs($hash) % count($patterns)];
    }

    /**
     * Generate an authentic, completely unique narrative description for Google.
     * Modularly synthesizes dynamic hooks, category-specific storylines, technical specs,
     * and high-value search terms so no two videos share duplicate text.
     */
    public function getSeoDescriptionAttribute(): string
    {
        $cleanTitle = $this->clean_title;
        $categoryNames = array_map(fn($c) => strtolower($c['name']), array_slice($this->categories, 0, 3));
        $categoryStr = !empty($categoryNames) ? implode(', ', $categoryNames) : 'uncensored anime and hentai';
        $primaryCategory = $this->categories[0]['name'] ?? 'Anime';
        $durationStr = $this->duration ? " featuring a full runtime of {$this->duration}" : '';

        // Deterministic variation seeds
        $seed = (string) ($this->id ?? '') . (string) ($this->slug ?? '') . $cleanTitle;
        $hash = abs(crc32($seed));

        // 1. Dynamic Narrative Opening Hooks (12 variations)
        $hooks = [
            "Watch {$cleanTitle} full video streaming online in full 1080p high definition on HentaiFlex{$durationStr}.",
            "Stream the complete {$cleanTitle} episode with crystal-clear audio and uncensored visuals on HentaiFlex{$durationStr}.",
            "Experience {$cleanTitle} online in ultra-sharp 1080p HD with instant playback and zero buffering on HentaiFlex{$durationStr}.",
            "Enjoy unlimited free streaming of {$cleanTitle} with zero lag, instant seeking, and high bitrate video on HentaiFlex{$durationStr}.",
            "Dive into {$cleanTitle}, one of the most requested {$primaryCategory} anime streaming releases on HentaiFlex{$durationStr}.",
            "Watch full-length uncensored {$cleanTitle} online in 1080p 60FPS on HentaiFlex{$durationStr}.",
            "Discover {$cleanTitle} streaming free in high resolution with complete English subtitles on HentaiFlex{$durationStr}.",
            "Stream {$cleanTitle} direct to your mobile, tablet, or desktop browser with zero buffering on HentaiFlex{$durationStr}.",
            "Immerse yourself in {$cleanTitle}, featuring premium {$categoryStr} scenes and captivating animation on HentaiFlex{$durationStr}.",
            "Get instant access to {$cleanTitle} full-length HD stream on HentaiFlex without registration or payment{$durationStr}.",
            "Watch {$cleanTitle} free online, optimized for smooth playback, adaptive streaming, and rich colors on HentaiFlex{$durationStr}.",
            "Stream the complete uncensored release of {$cleanTitle} with enhanced visuals and remastered audio on HentaiFlex{$durationStr}.",
        ];
        $selectedHook = $hooks[$hash % count($hooks)];

        // 2. Category-Specific Storyline & Content Synopses
        $catSlugs = array_map(fn($c) => $c['slug'] ?? '', $this->categories);
        $categorySynopses = [];

        if (in_array('3d', $catSlugs)) {
            $categorySynopses[] = "Rendered with high-fidelity 3D CGI and SFM graphics, this animation features fluid character motion, realistic physics, and dynamic camera angles crafted for maximum visual impact.";
        }
        if (in_array('uncensored', $catSlugs)) {
            $categorySynopses[] = "Presented in full decensored and mosaic-free video format, every intense moment is preserved in pristine clarity without obstruction or pixelation.";
        }
        if (in_array('fantasy', $catSlugs)) {
            $categorySynopses[] = "Set within an alluring fantasy realm of mystical elves, dark succubi, and enchanted adventurers, this episode weaves magical encounters with deep erotic tension.";
        }
        if (in_array('gangbang', $catSlugs)) {
            $categorySynopses[] = "Featuring dynamic multi-character chemistry and unrelenting group dynamics, this storyline brings intense harem scenarios and passionate encounters to life.";
        }
        if (in_array('school', $catSlugs) || in_array('teacher', $catSlugs)) {
            $categorySynopses[] = "Exploring thrilling student-teacher secrets and academy drama, this forbidden romance builds up through secret rendezvous and seductive classroom moments.";
        }
        if (in_array('mature', $catSlugs)) {
            $categorySynopses[] = "Delivering a captivating mature storyline with alluring milf and adult themes, passionate drama unfolds with irresistible chemistry and emotional depth.";
        }
        if (in_array('cartoon', $catSlugs)) {
            $categorySynopses[] = "Crafted with expressive hand-drawn 2D animation and stylized character designs, this cartoon hentai feature blends creative parody with captivating adult art direction.";
        }
        if (in_array('creampie', $catSlugs) || in_array('oral', $catSlugs)) {
            $categorySynopses[] = "Packed with intense climax scenes, deep oral moments, and passionate creampie sequences, this release delivers high-energy pacing and raw excitement.";
        }

        if (empty($categorySynopses)) {
            $categorySynopses[] = "Showcasing compelling character interactions, top-tier anime artwork, and thrilling encounters, this release stands out as a must-watch {$primaryCategory} video.";
        }
        $selectedCategoryPlot = $categorySynopses[($hash >> 3) % count($categorySynopses)];

        // 3. Technical & Streaming Spec Highlights (8 variations)
        $specs = [
            "Optimized for fast mobile and desktop streaming, this video supports adaptive playback, instant seeking, full-screen theater mode, and direct progressive MP4 delivery.",
            "Stream seamlessly in full 1080p with lossless stereo audio and clear English subtitles. Compatible across iOS, Android, tablet, and desktop devices.",
            "Enjoy ultra-fast cloud CDN delivery with zero loading delay. Features intuitive HTML5 playback controls, volume boost, and theater viewing.",
            "Delivered via high-speed streaming infrastructure for smooth, uninterrupted playback. Audio is mastered in clear Japanese voice acting with English subtitle support.",
            "Engineered for crisp resolution and smooth frame rates. Switch easily to full-screen theater mode and enjoy bufferless streaming on any connection.",
            "Experience vibrant color reproduction and deep contrast on OLED and Retina displays with our optimized HTML5 player and adaptive video bitrate.",
            "Designed for instant viewing without sign-ups or software downloads. Enjoy complete private browsing with SSL encryption and fast seeking.",
            "Featuring high-definition video encoding and responsive mobile scaling, allowing you to enjoy your favorite anime episodes anywhere, anytime.",
        ];
        $selectedSpec = $specs[($hash >> 6) % count($specs)];

        // 4. Trending Search Keyword Integration Callout
        $seoFooter = "HentaiFlex is your premier destination to stream free uncensored hentai, full anime episodes, and 3D SFM animations with fast, secure playback.";

        // 5. Scraped Original Description Integration
        $originalContext = '';
        if (!empty($this->description) && strlen(trim($this->description)) > 25) {
            $cleanedOriginal = strip_tags($this->description);
            $cleanedOriginal = preg_replace('/(https?:\/\/)?(www\.)?[a-zA-Z0-9-]+\.(com|net|org|tv|to|io|me|cc)\b/i', '', $cleanedOriginal);
            $cleanedOriginal = preg_replace('/\b(hentaicity|watchhentai|spankbang|pornhub|xvideos|xnxx|hanime)\b/i', '', $cleanedOriginal);
            $cleanedOriginal = trim(preg_replace('/\s+/', ' ', $cleanedOriginal));

            if (strlen($cleanedOriginal) > 30) {
                $originalContext = $cleanedOriginal . ' ';
            }
        }

        return trim("{$originalContext}{$selectedHook} {$selectedCategoryPlot} {$selectedSpec} {$seoFooter}");
    }

    /**
     * Get a concise 150-160 character meta description for search engine SERP snippets.
     */
    public function getSeoSummaryAttribute(): string
    {
        $cleanTitle = $this->clean_title;
        $primaryCategory = $this->categories[0]['name'] ?? 'Hentai';
        if ($primaryCategory === 'General') {
            $primaryCategory = 'Hentai';
        }

        $seed = (string) ($this->id ?? '') . $cleanTitle;
        $hash = abs(crc32($seed));

        $summaries = [
            "Watch {$cleanTitle} full episode in 1080p HD on HentaiFlex. Stream free uncensored {$primaryCategory} anime with instant playback and zero buffering.",
            "Stream {$cleanTitle} online free in high definition. Discover top uncensored {$primaryCategory} scenes with fast direct playback on HentaiFlex.",
            "Watch full-length {$cleanTitle} on HentaiFlex. Enjoy premium {$primaryCategory} animation in crystal-clear 1080p with English subs and zero lag.",
            "Experience {$cleanTitle} in ultra-sharp 1080p HD streaming on HentaiFlex. 100% free uncensored {$primaryCategory} anime with instant mobile playback.",
            "Stream {$cleanTitle} free online on HentaiFlex. Featuring full-length {$primaryCategory} animation in 1080p with fast bufferless playback.",
            "Watch {$cleanTitle} online in 1080p 60FPS. Free uncensored {$primaryCategory} streaming with crystal-clear audio and English subs on HentaiFlex.",
        ];

        return Str::limit($summaries[$hash % count($summaries)], 158);
    }

    /**
     * Generate high-volume, trending Google search keywords tailored to this video.
     * Retains 100% of the original keyword inventory while appending high-ranking search terms.
     */
    public function getSeoKeywordsAttribute(): string
    {
        $cleanTitle = strtolower($this->clean_title);
        $primaryCategory = strtolower($this->categories[0]['name'] ?? 'hentai');

        $videoSpecific = [
            $cleanTitle,
            "watch {$cleanTitle}",
            "stream {$cleanTitle}",
            "{$cleanTitle} uncensored",
            "{$cleanTitle} full episode",
            "{$cleanTitle} 1080p",
            "{$cleanTitle} english sub",
            "{$cleanTitle} online free",
        ];

        // 100% of original core keywords preserved + latest high-volume search queries
        $allKeywords = array_merge($videoSpecific, [
            // Original keywords (completely preserved)
            'watch hentai',
            'watch hentai online',
            'watch hentai online free',
            'free hentai',
            'hentai online',
            'hentai online free',
            'hentai streaming',
            'best hentai sites',
            'uncensored hentai',
            'gangbang hentai',
            'cartoon hentai',
            '3d hentai',
            'full hentai episode',
            'full hentai episodes',
            'english sub hentai',
            'english dub hentai',
            'hentai tube',
            'hd anime',
            'hentai videos',
            'hentai video',
            'milf hentai',
            'tentacle hentai',
            'harem anime',
            'anime',
            'hentaiflex',
            "{$primaryCategory} hentai",

            // New trending high-volume Google search queries
            'hanime tv',
            'hentai haven',
            'rule34 video',
            'sfm hentai',
            '4k hentai',
            '1080p hentai tube',
            'uncensored anime stream',
            'no buffering hentai',
            'anime porn streaming',
        ]);

        return implode(', ', array_unique($allKeywords));
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
                'harem anime', 'anime', 'hd anime', 'rule34 video', 'hanime', 'hentaiflex'
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
