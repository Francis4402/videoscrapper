<?php

namespace Tests\Feature;

use App\Models\ScrapedVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class VideoScraperWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('HentaiFlex');
        $response->assertSee('Watch Free HD Videos');
    }

    public function test_sitemap_xml_returns_valid_google_video_sitemap(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
        $response->assertSee('xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"', false);
    }

    public function test_auto_syncs_when_link_is_configured_in_env(): void
    {
        Config::set('scraper.target_url', 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4');

        $response = $this->get('/');

        $response->assertStatus(200);
        $this->assertDatabaseHas('scraped_videos', [
            'source_domain' => 'commondatastorage.googleapis.com',
        ]);
    }

    public function test_auto_syncs_multiple_links_configured_in_env(): void
    {
        Config::set('scraper.target_url', "https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4, https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4");

        $response = $this->get('/');
        $response->assertStatus(200);

        $this->assertDatabaseHas('scraped_videos', [
            'title' => 'BigBuckBunny',
        ]);
        $this->assertDatabaseHas('scraped_videos', [
            'title' => 'ElephantsDream',
        ]);
    }

    public function test_watch_page_loads_with_centered_cover_player(): void
    {
        $video = ScrapedVideo::create([
            'source_url' => 'https://example.com/movie',
            'source_domain' => 'example.com',
            'title' => 'Test Action Movie',
            'video_url' => 'https://example.com/stream.mp4',
            'thumbnail_url' => 'https://example.com/poster.jpg',
            'video_type' => 'direct',
        ]);

        $response = $this->get('/watch/' . $video->id);
        $response->assertStatus(200);

        // Verify friendly video name slug route
        $slugResponse = $this->get('/watch/' . $video->slug);
        $slugResponse->assertStatus(200);
        $slugResponse->assertSee('Test Action Movie');
        $slugResponse->assertSee('player-cover');
        $slugResponse->assertSee('startPlayback()');
    }

    public function test_watch_page_supports_embed_player(): void
    {
        $video = ScrapedVideo::create([
            'source_url' => 'https://example.com/embed-test',
            'source_domain' => 'example.com',
            'title' => 'Embedded Player Video',
            'embed_url' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
            'video_type' => 'embed',
        ]);

        $response = $this->get('/watch/' . $video->id);

        $response->assertStatus(200);
        $response->assertSee('Embedded Player Video');
        $response->assertSee('embed-player');
    }

    public function test_stream_proxy_requires_valid_video(): void
    {
        $response = $this->get('/stream-proxy/non-existent-id');
        $response->assertStatus(404);
    }

    public function test_category_filtering_and_onclick_search(): void
    {
        Config::set('scraper.target_url', null);

        ScrapedVideo::create([
            'source_url' => 'https://example.com/v1',
            'title' => 'Special Busty Anime Clip',
            'video_url' => 'https://example.com/v1.mp4',
            'video_type' => 'direct',
        ]);

        ScrapedVideo::create([
            'source_url' => 'https://example.com/v2',
            'title' => 'Hilarious Meme Laughing Joke',
            'video_url' => 'https://example.com/v2.mp4',
            'video_type' => 'direct',
        ]);

        // Filter by 'busty' category
        $response = $this->get('/?category=busty');
        $response->assertStatus(200);
        $response->assertSee('Special Busty Anime Clip');
        $response->assertDontSee('Hilarious Meme Laughing Joke');

        // Funny videos and clips/memes category are removed
        $this->assertArrayNotHasKey('clips', ScrapedVideo::$categoryDefinitions);
        $responseHome = $this->get('/');
        $responseHome->assertDontSee('Hilarious Meme Laughing Joke');
    }
}
