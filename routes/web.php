<?php

use App\Http\Controllers\ImageProxyController;
use App\Http\Controllers\StreamProxyController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [VideoController::class, 'index'])->name('home');
Route::get('/category/{slug}', [VideoController::class, 'category'])->name('videos.category');
Route::get('/random', [VideoController::class, 'random'])->name('videos.random');
Route::get('/sitemap.xml', [VideoController::class, 'sitemap'])->name('sitemap');
Route::get('/sitemap-video.xml', [VideoController::class, 'sitemap'])->name('sitemap.video');
Route::get('/sitemap_index.xml', [VideoController::class, 'sitemap'])->name('sitemap.index');
Route::get('/feed.xml', [VideoController::class, 'feed'])->name('feed');
Route::get('/hentaiflex-indexnow.txt', fn() => response("hentaiflex-indexnow\nhttps://hentaiflex.com/hentaiflex-indexnow.txt", 200, ['Content-Type' => 'text/plain']));
Route::post('/scrape', [VideoController::class, 'scrape'])->name('videos.scrape');
Route::post('/scrape-configured', [VideoController::class, 'scrapeConfigured'])->name('videos.scrapeConfigured');
Route::get('/watch/{slug}', [VideoController::class, 'watch'])->name('videos.watch');
Route::get('/embed/{slug}', [VideoController::class, 'embed'])->name('videos.embed');
Route::get('/hentai/{slug}', fn($slug) => redirect()->route('videos.watch', $slug, 301));
Route::delete('/video/{id}', [VideoController::class, 'destroy'])->name('videos.destroy');
Route::post('/clear-all', [VideoController::class, 'clearAll'])->name('videos.clearAll');

Route::get('/stream-proxy/{id}', [StreamProxyController::class, 'proxy'])->name('stream.proxy');
Route::get('/image-proxy', [ImageProxyController::class, 'proxy'])->name('image.proxy');
