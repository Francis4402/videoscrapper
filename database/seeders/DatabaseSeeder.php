<?php

namespace Database\Seeders;

use App\Models\ScrapedVideo;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with sample scraped cards.
     */
    public function run(): void
    {
        $samples = [
            [
                'source_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                'source_domain' => 'peach.blender.org',
                'title' => 'Big Buck Bunny (Animation Film)',
                'description' => 'Big Buck Bunny tells the story of a giant rabbit with a heart bigger than himself who meets three bullying woodland critters: a chinchilla, a squirrel, and a flying squirrel.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1534447677768-be436bb09401?w=800&auto=format&fit=crop&q=80',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                'embed_url' => null,
                'video_type' => 'direct',
                'duration' => '09:56',
            ],
            [
                'source_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
                'source_domain' => 'mango.blender.org',
                'title' => 'Tears of Steel (Sci-Fi VFX Short)',
                'description' => 'Set in a dystopian future Amsterdam, a group of scientists and warriors attempt to stage a crucial event in the past to save the world from destructive robotic drones.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=800&auto=format&fit=crop&q=80',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/TearsOfSteel.mp4',
                'embed_url' => null,
                'video_type' => 'direct',
                'duration' => '12:14',
            ],
            [
                'source_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
                'source_domain' => 'orange.blender.org',
                'title' => 'Elephants Dream (Open Movie)',
                'description' => 'Two characters navigate the strange, mechanical labyrinth of an immense and sinister machine that seems to control reality itself.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1578632767115-351597cf2477?w=800&auto=format&fit=crop&q=80',
                'video_url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
                'embed_url' => null,
                'video_type' => 'direct',
                'duration' => '10:53',
            ],
            [
                'source_url' => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
                'source_domain' => 'mux.com',
                'title' => 'Big Buck Bunny Adaptive HLS Live Stream',
                'description' => 'High quality multi-bitrate HLS (.m3u8) video stream demonstrating adaptive bitrate switching and zero-buffering playback.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1536240478700-b869070f9279?w=800&auto=format&fit=crop&q=80',
                'video_url' => 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8',
                'embed_url' => null,
                'video_type' => 'hls',
                'duration' => 'Multi-bitrate',
            ],
            [
                'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'source_domain' => 'youtube.com',
                'title' => 'Sandboxed Video Embed Player Demo',
                'description' => 'Demonstrates sandboxed third-party embed playback without allowing the external site to trigger unwanted popups or browser redirects.',
                'thumbnail_url' => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=800&auto=format&fit=crop&q=80',
                'video_url' => null,
                'embed_url' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
                'video_type' => 'embed',
                'duration' => '03:32',
            ]
        ];

        foreach ($samples as $sample) {
            ScrapedVideo::updateOrCreate(
                ['source_url' => $sample['source_url']],
                $sample
            );
        }
    }
}
