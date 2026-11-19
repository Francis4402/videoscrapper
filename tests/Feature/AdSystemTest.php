<?php

namespace Tests\Feature;

use App\Models\ScrapedVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_ads_placeholder_renders_when_enabled_and_code_is_empty(): void
    {
        config([
            'ads.enabled' => true,
            'ads.show_placeholders' => true,
            'ads.slots.header.enabled' => true,
            'ads.slots.header.code' => '',
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('ad-placeholder-header');
        $response->assertSee('ADS_HEADER_CODE');
    }

    public function test_custom_ad_script_renders_in_header_slot(): void
    {
        config([
            'ads.enabled' => true,
            'ads.slots.header.enabled' => true,
            'ads.slots.header.code' => '<div id="test-banner-728x90">EXOCLICK_BANNER</div>',
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('EXOCLICK_BANNER', false);
        $response->assertSee('ad-slot-header');
    }

    public function test_ads_can_be_disabled_globally(): void
    {
        config([
            'ads.enabled' => false,
            'ads.slots.header.code' => '<div>SHOULD_NOT_APPEAR</div>',
        ]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('SHOULD_NOT_APPEAR');
        $response->assertDontSee('ad-placeholder-header');
    }

    public function test_watch_page_renders_player_and_sidebar_ad_slots(): void
    {
        $video = ScrapedVideo::create([
            'title' => 'Sample Test Video',
            'slug' => 'sample-test-video',
            'source_url' => 'https://example.com/source/123',
            'video_url' => 'https://example.com/test.mp4',
            'thumbnail_url' => 'https://example.com/thumb.jpg',
            'categories' => ['hentai'],
        ]);

        config([
            'ads.enabled' => true,
            'ads.show_placeholders' => true,
            'ads.slots.watch_below_player.enabled' => true,
            'ads.slots.watch_sidebar.enabled' => true,
        ]);

        $response = $this->get(route('videos.watch', $video->id));
        $response->assertStatus(200);
        $response->assertSee('ad-placeholder-watch_below_player');
        $response->assertSee('ad-placeholder-watch_sidebar');
    }
}
