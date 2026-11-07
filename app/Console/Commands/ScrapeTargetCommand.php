<?php

namespace App\Console\Commands;

use App\Services\VideoScraperService;
use Illuminate\Console\Command;

class ScrapeTargetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:target {url? : Optional target URL to override .env configuration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrapes videos from the URL configured in .env (SCRAPE_TARGET_URL) and adds them to cards';

    /**
     * Execute the console command.
     */
    public function handle(VideoScraperService $scraperService): int
    {
        $target = $this->argument('url') ?: config('scraper.target_url');

        if (empty($target)) {
            $this->error('No SCRAPE_TARGET_URL configured in your .env file, and no URL argument was provided.');
            $this->info('Please set SCRAPE_TARGET_URL="https://example.com" in your .env file.');
            return self::FAILURE;
        }

        // Support comma-separated URLs
        $urls = array_filter(array_map('trim', explode(',', $target)));

        $totalExtracted = 0;
        foreach ($urls as $url) {
            $this->info("Scraping videos from: {$url}...");
            try {
                $videos = $scraperService->scrape($url);
                $count = count($videos);
                $totalExtracted += $count;
                $this->info("✓ Extracted {$count} video(s) from {$url}.");
            } catch (\Exception $e) {
                $this->warn("✗ Error scraping {$url}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Done! Total new/updated videos: {$totalExtracted}.");
        return self::SUCCESS;
    }
}
