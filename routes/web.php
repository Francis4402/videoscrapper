<?php

use App\Http\Controllers\ImageProxyController;
use App\Http\Controllers\StreamProxyController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [VideoController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [VideoController::class, 'sitemap'])->name('sitemap');
Route::post('/scrape', [VideoController::class, 'scrape'])->name('videos.scrape');
Route::post('/scrape-configured', [VideoController::class, 'scrapeConfigured'])->name('videos.scrapeConfigured');
Route::get('/watch/{slug}', [VideoController::class, 'watch'])->name('videos.watch');
Route::get('/embed/{slug}', [VideoController::class, 'embed'])->name('videos.embed');
Route::get('/hentai/{slug}', fn($slug) => redirect()->route('videos.watch', $slug, 301));
Route::delete('/video/{id}', [VideoController::class, 'destroy'])->name('videos.destroy');
Route::post('/clear-all', [VideoController::class, 'clearAll'])->name('videos.clearAll');

Route::get('/stream-proxy/{id}', [StreamProxyController::class, 'proxy'])->name('stream.proxy');
Route::get('/image-proxy', [ImageProxyController::class, 'proxy'])->name('image.proxy');
