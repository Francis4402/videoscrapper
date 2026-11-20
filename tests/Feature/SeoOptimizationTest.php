<?php

namespace Tests\Feature;

use App\Models\ScrapedVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoOptimizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_contains_target_seo_keywords_and_title(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // First keyword in title and H1 is 'Hentai'
        $response->assertSee('<title>Hentai: Watch Free HD Hentai Videos &amp; Anime Online | HentaiFlex</title>', false);
        $response->assertSee('Hentai - Watch Free HD Videos & Anime', false);

        // Core target keywords
        $response->assertSee('hentai', false);
        $response->assertSee('gangbang hentai', false);
        $response->assertSee('cartoon hentai', false);
        $response->assertSee('hentaiflex', false);
        $response->assertSee('3d hentai', false);
        $response->assertSee('uncensored hentai', false);

        // Schema.org FAQPage & BreadcrumbList structured data
        $response->assertSee('FAQPage', false);
        $response->assertSee('BreadcrumbList', false);
    }

    public function test_category_pages_render_dynamic_seo_titles(): void
    {
        // Gangbang category page
        $resGangbang = $this->get('/?category=gangbang');
        $resGangbang->assertStatus(200);
        $resGangbang->assertSee('Gangbang Hentai Videos - Watch Free HD Gangbang Hentai Online | HentaiFlex', false);

        // Cartoon category page
        $resCartoon = $this->get('/?category=cartoon');
        $resCartoon->assertStatus(200);
        $resCartoon->assertSee('Cartoon Hentai Videos - Watch Free HD Cartoon Hentai Online | HentaiFlex', false);
    }

    public function test_video_sitemap_includes_google_video_seo_tags(): void
    {
        config(['scraper.target_url' => 'https://example.com/source/101']);

        $slug = 'top-gangbang-cartoon-hentai-adventure';
        ScrapedVideo::create([
            'title' => 'Top Gangbang Cartoon Hentai Adventure',
            'slug' => $slug,
            'source_url' => 'https://example.com/source/101',
            'video_url' => 'https://example.com/video101.mp4',
            'thumbnail_url' => 'https://example.com/thumb101.jpg',
            'duration' => '15:30',
        ]);

        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $response->assertSee('<video:tag>hentai</video:tag>', false);
        $response->assertSee('<video:tag>watch hentai</video:tag>', false);
        $response->assertSee('<video:tag>free hentai</video:tag>', false);
        $response->assertSee('<video:tag>gangbang hentai</video:tag>', false);
        $response->assertSee('<video:tag>cartoon hentai</video:tag>', false);
        $response->assertSee('<video:tag>hentaiflex</video:tag>', false);
        $response->assertSee('<video:category>Anime &amp; Hentai</video:category>', false);

        // Verify Google Video Sitemap rule: player_loc must NOT match loc
        $response->assertSee('<loc>' . route('videos.watch', $slug) . '</loc>', false);
        $response->assertSee('<video:player_loc allow_embed="yes">' . route('videos.embed', $slug) . '</video:player_loc>', false);
        $response->assertSee('<video:content_loc>', false);
    }

    public function test_embed_player_loads_successfully_without_xframe_restrictions(): void
    {
        $video = ScrapedVideo::create([
            'title' => 'Embed Test Hentai Video',
            'slug' => 'embed-test-hentai-video',
            'source_url' => 'https://example.com/source/102',
            'video_url' => 'https://example.com/video102.mp4',
            'thumbnail_url' => 'https://example.com/thumb102.jpg',
            'duration' => '10:00',
        ]);

        $response = $this->get('/embed/' . $video->slug);
        $response->assertStatus(200);
        $response->assertHeader('Content-Security-Policy', 'frame-ancestors *');
        $response->assertHeaderMissing('X-Frame-Options');
        $response->assertSee('video-player', false);
        $response->assertSee('startPlayback', false);
    }
}
