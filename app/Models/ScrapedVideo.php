<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ScrapedVideo extends Model
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
        '3d' => [
            'slug' => '3d',
            'name' => '3D',
            'icon' => 'fa-solid fa-cube',
            'keywords' => ['3d', 'cgi'],
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
        'clips' => [
            'slug' => 'clips',
            'name' => 'Clips & Memes',
            'icon' => 'fa-solid fa-face-laugh-squint',
            'keywords' => ['funny', 'laugh', 'meme', 'joke'],
        ],
    ];

    /**
     * Get matching categories for this video
     */
    public function getCategoriesAttribute(): array
    {
        $cats = [];
        $text = strtolower($this->title . ' ' . ($this->description ?? ''));

        foreach (self::$categoryDefinitions as $slug => $def) {
            foreach ($def['keywords'] as $kw) {
                if (str_contains($text, $kw)) {
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
     * Get clean playback stream URL (or fallback)
     */
    public function getPlayableStreamUrl(): ?string
    {
        if ($this->video_url) {
            return route('stream.proxy', ['id' => $this->id]);
        }
        return null;
    }
}
