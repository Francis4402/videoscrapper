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
    | Visual Placeholders (Testing / Setup Mode)
    |--------------------------------------------------------------------------
    | When true, clean styled placeholders will be displayed for empty slots
    | so you can easily preview ad placements on desktop and mobile.
    | Set ADS_SHOW_PLACEHOLDERS=false in production once your ads are live.
    */
    'show_placeholders' => env('ADS_SHOW_PLACEHOLDERS', true),

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
    | Popunder / On-Click Ads
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
        ],

        'watch_below_player' => [
            'enabled' => env('ADS_WATCH_BELOW_PLAYER_ENABLED', true),
            'size'    => '728x90 / 300x250',
            'code'    => env('ADS_WATCH_BELOW_PLAYER_CODE', ''),
        ],

        'watch_sidebar' => [
            'enabled' => env('ADS_WATCH_SIDEBAR_ENABLED', true),
            'size'    => '300x250',
            'code'    => env('ADS_WATCH_SIDEBAR_CODE', ''),
        ],

        'grid_feed' => [
            'enabled' => env('ADS_GRID_FEED_ENABLED', true),
            'size'    => 'Native / 300x250',
            'code'    => env('ADS_GRID_FEED_CODE', ''),
        ],

        'footer_sticky' => [
            'enabled' => env('ADS_FOOTER_STICKY_ENABLED', true),
            'size'    => '728x90 / 320x50',
            'code'    => env('ADS_FOOTER_STICKY_CODE', ''),
        ],
    ],

];
