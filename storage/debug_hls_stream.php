<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$masterUrl = 'https://hls.hentaicity.com/_hls/flv/0629/38299/,default,mobile,480p,.mp4.urlset/master.m3u8?validfrom=1794077452&validto=1794084652&hash=sfxl6a4IUY8jtY4LBy90OtV%2FQRw%3D';

$res = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
    'Origin' => 'http://127.0.0.1:8000',
    'Referer' => 'https://www.hentaicity.com/',
])->withoutVerifying()->get($masterUrl);

echo "Master status: " . $res->status() . "\n";
echo "Master body:\n" . $res->body() . "\n";

// Now parse the child playlist
if (preg_match('/(index[^\s\n\r]+)/', $res->body(), $m)) {
    $childRel = $m[1];
    $base = dirname($masterUrl);
    $childUrl = $base . '/' . $childRel;
    echo "Child URL: $childUrl\n";

    $childRes = Http::withHeaders([
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        'Origin' => 'http://127.0.0.1:8000',
        'Referer' => 'https://www.hentaicity.com/',
    ])->withoutVerifying()->get($childUrl);

    echo "Child status: " . $childRes->status() . "\n";
    echo "Child body:\n" . substr($childRes->body(), 0, 400) . "\n";

    // Now parse a TS chunk
    if (preg_match('/([^\s\n\r]+\.ts[^\s\n\r]*)/', $childRes->body(), $tm)) {
        $tsRel = $tm[1];
        $tsUrl = dirname($childUrl) . '/' . $tsRel;
        echo "TS URL: $tsUrl\n";

        $tsRes = Http::withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
            'Origin' => 'http://127.0.0.1:8000',
        ])->withoutVerifying()->head($tsUrl);

        echo "TS status: " . $tsRes->status() . "\n";
        echo "TS CORS: " . $tsRes->header('Access-Control-Allow-Origin') . "\n";
    }
}
