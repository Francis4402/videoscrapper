<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Http;

$tsUrl = 'https://hls.hentaicity.com/_hls/flv/0629/38299/,default,mobile,480p,.mp4.urlset/seg-1-f1-v1-a1.ts?validfrom=1794077452&validto=1794084652&hash=sfxl6a4IUY8jtY4LBy90OtV%2FQRw%3D';

$start = microtime(true);
$res = Http::withHeaders([
    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
])->withoutVerifying()->get($tsUrl);

$time = round((microtime(true) - $start) * 1000, 2);
echo "TS download time: {$time}ms | Length: " . strlen($res->body()) . " bytes\n";
