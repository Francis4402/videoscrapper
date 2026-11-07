<?php

use App\Http\Controllers\ImageProxyController;
use App\Http\Controllers\StreamProxyController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::get('/', [VideoController::class, 'index'])->name('home');
Route::post('/scrape', [VideoController::class, 'scrape'])->name('videos.scrape');
Route::post('/scrape-configured', [VideoController::class, 'scrapeConfigured'])->name('videos.scrapeConfigured');
Route::get('/watch/{slug}', [VideoController::class, 'watch'])->name('videos.watch');
Route::delete('/video/{id}', [VideoController::class, 'destroy'])->name('videos.destroy');
Route::post('/clear-all', [VideoController::class, 'clearAll'])->name('videos.clearAll');

Route::get('/stream-proxy/{id}', [StreamProxyController::class, 'proxy'])->name('stream.proxy');
Route::get('/image-proxy', [ImageProxyController::class, 'proxy'])->name('image.proxy');
