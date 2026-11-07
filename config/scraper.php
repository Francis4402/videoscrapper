<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Target Scrape URL
    |--------------------------------------------------------------------------
    |
    | Specify the target website URL from which videos should be scraped.
    | You can also provide multiple URLs separated by commas.
    |
    | Example: SCRAPE_TARGET_URL="https://example.com/videos"
    |
    */
    'target_url' => env('SCRAPE_TARGET_URL', ''),

    /*
    |--------------------------------------------------------------------------
    | Auto-Scrape on First Visit
    |--------------------------------------------------------------------------
    |
    | When set to true, visiting the homepage will automatically scrape the
    | target URL if no cards exist yet in the database.
    |
    */
    'auto_scrape' => env('SCRAPE_AUTO_ON_FIRST_VISIT', false),
];
