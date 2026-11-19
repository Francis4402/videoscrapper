<?php

namespace App\Http\Controllers;

use App\Models\ScrapedVideo;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamProxyController extends Controller
{
    /**
     * Proxies video stream chunks to bypass CORS & hotlinking Referer blocks.
     */
    public function proxy(Request $request, string $id)
    {
        $video = ScrapedVideo::findOrFail($id);

        if (!$video->video_url) {
            abort(404, 'No direct video stream for this item.');
        }

        $targetUrl = $video->video_url;
        $referer = str_contains($video->source_domain ?? '', 'hentaicity.com')
            ? 'https://www.hentaicity.com/'
            : (str_contains($video->source_domain ?? '', 'watchhentai.net') || str_contains($targetUrl, 'hstorage.xyz')
                ? 'https://watchhentai.net/'
                : ($video->source_url ?: $targetUrl));

        $client = new Client([
            'verify' => false,
            'timeout' => 30,
        ]);

        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Referer' => $referer,
            'Accept' => '*/*',
        ];

        // Forward Range header for video seeking/scrubbing
        if ($request->hasHeader('Range')) {
            $headers['Range'] = $request->header('Range');
        }

        try {
            $response = $client->request('GET', $targetUrl, [
                'headers' => $headers,
                'stream' => true,
                'http_errors' => false,
            ]);

            $statusCode = $response->getStatusCode();

            // If the proxy gets an error status code, fallback to direct URL
            if ($statusCode >= 400) {
                return redirect()->away($targetUrl);
            }

            $contentType = $response->getHeaderLine('Content-Type') ?: 'video/mp4';
            $contentLength = $response->getHeaderLine('Content-Length');
            $contentRange = $response->getHeaderLine('Content-Range');
            $acceptRanges = $response->getHeaderLine('Accept-Ranges') ?: 'bytes';

            $origin = $request->header('Origin');
            $allowedOrigin = $origin && in_array(strtolower(parse_url($origin, PHP_URL_HOST) ?? ''), ['localhost', '127.0.0.1', strtolower($request->getHost()), 'hentaiflex.com', 'www.hentaiflex.com'], true)
                ? $origin
                : url('/');

            $responseHeaders = [
                'Content-Type' => $contentType,
                'Accept-Ranges' => $acceptRanges,
                'Access-Control-Allow-Origin' => $allowedOrigin,
                'Access-Control-Allow-Credentials' => 'true',
                'Cache-Control' => 'no-cache, no-store, private',
                'X-Content-Type-Options' => 'nosniff',
            ];

            if ($contentLength) {
                $responseHeaders['Content-Length'] = $contentLength;
            }
            if ($contentRange) {
                $responseHeaders['Content-Range'] = $contentRange;
            }

            $body = $response->getBody();

            return new StreamedResponse(function () use ($body) {
                while (!$body->eof()) {
                    echo $body->read(1024 * 64);
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }
            }, $statusCode, $responseHeaders);

        } catch (\Exception $e) {
            return redirect()->away($targetUrl);
        }
    }
}
