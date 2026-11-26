<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ads Master Switch
    |--------------------------------------------------------------------------
    | Globally enables or disables all advertisements across the platform.
    | Set ADS_ENABLED=false in your .env file to turn off all ads.
    */
    'enabled' => env('ADS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Visual Placeholders / Working Creative Banners
    |--------------------------------------------------------------------------
    | When true, displays active working high-converting banners if custom ad
    | code is empty, so your site shows working ads immediately.
    */
    'show_placeholders' => env('ADS_SHOW_PLACEHOLDERS', true),

    /*
    |--------------------------------------------------------------------------
    | In-Stream Video Ads (VAST Pre-Roll)
    |--------------------------------------------------------------------------
    | Plays a video advertisement before the actual video playback begins.
    | Supports VAST 2.0 / 3.0 / 4.0 tags (e.g., ExoClick In-Stream Video).
    */
    'instream' => [
        'enabled'     => env('ADS_INSTREAM_ENABLED', true),
        'vast_url'    => env('ADS_INSTREAM_VAST_URL', 'https://s.magsrv.com/v1/vast.php?idz=6109862'),
        'skip_offset' => (int) env('ADS_INSTREAM_SKIP_OFFSET', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | On-Click / Popunder Engine (The #1 Profit Driver)
    |--------------------------------------------------------------------------
    | Triggers an ad in a new tab when a user clicks anywhere on the page.
    | Video play button uses in-stream VAST ads; popunder triggers on page/card clicks.
    */
    'onclick' => [
        'enabled'                => env('ADS_ONCLICK_ENABLED', true),
        'url'                    => env('ADS_ONCLICK_URL', ''),
        'frequency_minutes'      => (int) env('ADS_ONCLICK_FREQUENCY_MINUTES', 3),
        'trigger_on_play'        => env('ADS_ONCLICK_ON_PLAY', false),
        'trigger_on_first_click' => env('ADS_ONCLICK_ON_FIRST_CLICK', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Head Script
    |--------------------------------------------------------------------------
    | Injected inside <head>. Ideal for ad network verification meta tags,
    | header scripts (e.g., Adsterra, ExoClick, JuicyAds verification/scripts).
    */
    'head_script' => env('ADS_HEAD_SCRIPT', ''),

    /*
    |--------------------------------------------------------------------------
    | Popunder / On-Click Ads (Raw Script Tag)
    |--------------------------------------------------------------------------
    | Injected right before the closing </body> tag.
    */
    'popunder' => [
        'enabled' => env('ADS_POPUNDER_ENABLED', true),
        'code'    => env('ADS_POPUNDER_CODE', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ad Placement Slots
    |--------------------------------------------------------------------------
    | Supported standard slots:
    | - header: Responsive leaderboard banner (728x90 desktop / 320x50 mobile)
    | - watch_below_player: High-CTR banner right below video player controls
    | - watch_sidebar: Medium rectangle banner (300x250) in related videos sidebar
    | - grid_feed: In-feed sponsored card inside video thumbnail grids
    | - footer_sticky: Floating bottom banner with close button
    */
    'slots' => [
        'header' => [
            'enabled' => env('ADS_HEADER_ENABLED', true),
            'size'    => '728x90 / 320x50',
            'code'    => env('ADS_HEADER_CODE', ''),
            'url'     => env('ADS_HEADER_URL', env('ADS_ONCLICK_URL', '')),
        ],

        'watch_below_player' => [
            'enabled' => env('ADS_WATCH_BELOW_PLAYER_ENABLED', true),
            'size'    => '728x90 / 300x250',
            'code'    => env('ADS_WATCH_BELOW_PLAYER_CODE', ''),
            'url'     => env('ADS_WATCH_BELOW_PLAYER_URL', env('ADS_ONCLICK_URL', '')),
        ],

        'watch_sidebar' => [
            'enabled' => env('ADS_WATCH_SIDEBAR_ENABLED', true),
            'size'    => '300x250',
            'code'    => env('ADS_WATCH_SIDEBAR_CODE', ''),
            'url'     => env('ADS_WATCH_SIDEBAR_URL', env('ADS_ONCLICK_URL', '')),
        ],

        'grid_feed' => [
            'enabled' => env('ADS_GRID_FEED_ENABLED', true),
            'size'    => 'Native / 300x250',
            'code'    => env('ADS_GRID_FEED_CODE', ''),
            'url'     => env('ADS_GRID_FEED_URL', env('ADS_ONCLICK_URL', '')),
        ],

        'footer_sticky' => [
            'enabled' => env('ADS_FOOTER_STICKY_ENABLED', true),
            'size'    => '728x90 / 320x50',
            'code'    => env('ADS_FOOTER_STICKY_CODE', ''),
            'url'     => env('ADS_FOOTER_STICKY_URL', env('ADS_ONCLICK_URL', '')),
        ],

        'home_bottom' => [
            'enabled' => env('ADS_HOME_BOTTOM_ENABLED', true),
            'size'    => '728x90 / 320x50',
            'code'    => env('ADS_HOME_BOTTOM_CODE', ''),
            'url'     => env('ADS_HOME_BOTTOM_URL', env('ADS_ONCLICK_URL', '')),
        ],
    ],

];
