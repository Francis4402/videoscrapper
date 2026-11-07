<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;

class ImageProxyController extends Controller
{
    /**
     * Proxy external video thumbnails to bypass hotlink and CORS protections.
     */
    public function proxy(Request $request)
    {
        $url = $request->query('url');

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            return redirect('https://placehold.co/640x360/1e293b/cbd5e1?text=No+Image');
        }

        try {
            $parsedUrl = parse_url($url);
            $domain = $parsedUrl['host'] ?? '';

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Referer' => $domain ? "https://{$domain}/" : $url,
                'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
            ])
            ->timeout(8)
            ->withoutVerifying()
            ->get($url);

            if (!$response->successful()) {
                return redirect('https://placehold.co/640x360/1e293b/cbd5e1?text=Image+Unavailable');
            }

            $contentType = $response->header('Content-Type') ?: 'image/jpeg';
            $body = $response->body();

            return Response::make($body, 200, [
                'Content-Type' => $contentType,
                'Cache-Control' => 'public, max-age=86400',
                'Access-Control-Allow-Origin' => '*',
            ]);
        } catch (\Exception $e) {
            return redirect('https://placehold.co/640x360/1e293b/cbd5e1?text=Error+Loading');
        }
    }
}
