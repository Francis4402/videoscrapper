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
    | On-Click / Popunder Engine (The #1 Profit Driver)
    |--------------------------------------------------------------------------
    | Triggers an ad in a new tab when a user clicks anywhere on the page
    | or clicks the big Play button on the video player.
    */
    'onclick' => [
        'enabled'                => env('ADS_ONCLICK_ENABLED', true),
        'url'                    => env('ADS_ONCLICK_URL', 'https://syndication.exoclick.com/splash.php?idzone=default'),
        'frequency_minutes'      => (int) env('ADS_ONCLICK_FREQUENCY_MINUTES', 3),
        'trigger_on_play'        => env('ADS_ONCLICK_ON_PLAY', true),
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
            'url'     => env('ADS_HEADER_URL', env('ADS_ONCLICK_URL', 'https://syndication.exoclick.com/splash.php?idzone=default')),
        ],

        'watch_below_player' => [
            'enabled' => env('ADS_WATCH_BELOW_PLAYER_ENABLED', true),
            'size'    => '728x90 / 300x250',
            'code'    => env('ADS_WATCH_BELOW_PLAYER_CODE', ''),
            'url'     => env('ADS_WATCH_BELOW_PLAYER_URL', env('ADS_ONCLICK_URL', 'https://syndication.exoclick.com/splash.php?idzone=default')),
        ],

        'watch_sidebar' => [
            'enabled' => env('ADS_WATCH_SIDEBAR_ENABLED', true),
            'size'    => '300x250',
            'code'    => env('ADS_WATCH_SIDEBAR_CODE', ''),
            'url'     => env('ADS_WATCH_SIDEBAR_URL', env('ADS_ONCLICK_URL', 'https://syndication.exoclick.com/splash.php?idzone=default')),
        ],

        'grid_feed' => [
            'enabled' => env('ADS_GRID_FEED_ENABLED', true),
            'size'    => 'Native / 300x250',
            'code'    => env('ADS_GRID_FEED_CODE', ''),
            'url'     => env('ADS_GRID_FEED_URL', env('ADS_ONCLICK_URL', 'https://syndication.exoclick.com/splash.php?idzone=default')),
        ],

        'footer_sticky' => [
            'enabled' => env('ADS_FOOTER_STICKY_ENABLED', true),
            'size'    => '728x90 / 320x50',
            'code'    => env('ADS_FOOTER_STICKY_CODE', ''),
            'url'     => env('ADS_FOOTER_STICKY_URL', env('ADS_ONCLICK_URL', 'https://syndication.exoclick.com/splash.php?idzone=default')),
        ],
    ],

];
