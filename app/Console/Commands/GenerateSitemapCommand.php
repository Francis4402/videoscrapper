<?php

namespace App\Console\Commands;

use App\Models\ScrapedVideo;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a dynamic Google Video & Image SEO sitemap using spatie/laravel-sitemap';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Building video sitemap with spatie/laravel-sitemap...');

        $sitemap = Sitemap::create();

        // 1. Homepage (Highest Priority 1.0)
        $sitemap->add(
            Url::create(rtrim(url('/'), '/') . '/')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_HOURLY)
                ->setPriority(1.0)
        );

        // 2. Dedicated SEO Category Hubs (Priority 0.8)
        foreach (ScrapedVideo::$categoryDefinitions as $slug => $cat) {
            $sitemap->add(
                Url::create(route('videos.category', $slug))
                    ->setLastModificationDate(now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setPriority(0.8)
            );
        }

        // 3. Video Pages with Google Video Tags & Thumbnails
        $videos = ScrapedVideo::notFunny()->latest()->take(1000)->get();
        $this->info("Adding {$videos->count()} videos to sitemap...");

        foreach ($videos as $video) {
            $sitemap->add($video);
        }

        $outputPath = public_path('sitmap.xml');
        $sitemap->writeToFile($outputPath);

        $this->info("Dynamic sitemap successfully written to {$outputPath}");

        return Command::SUCCESS;
    }
}
