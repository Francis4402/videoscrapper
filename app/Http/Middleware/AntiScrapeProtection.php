<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AntiScrapeProtection
{
    /**
     * List of banned scraper / bot User-Agent substrings (case-insensitive).
     */
    protected array $blockedAgents = [
        'python-requests',
        'aiohttp',
        'scrapy',
        'httpx',
        'urllib',
        'curl/',
        'wget/',
        'libwww-perl',
        'pycurl',
        'httpclient',
        'go-http-client',
        'postmanruntime',
        'insomnia',
        'bytespider',
        'petalbot',
        'dataforseobot',
        'semrushbot',
        'ahrefsbot',
        'dotbot',
        'mj12bot',
    ];

    /**
     * Allowed legitimate search engine bots and testing agents.
     */
    protected array $allowedAgents = [
        'googlebot',
        'google-inspectiontool',
        'google',
        'bingbot',
        'duckduckbot',
        'yandexbot',
        'slurp',
        'baiduspider',
        'symfony',
        'laravel',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 0. Whitelist public SEO discovery endpoints so search engine validators never fail
        if ($request->is('sitemap.xml') || $request->is('robots.txt')) {
            return $next($request);
        }

        $userAgent = trim((string) $request->header('User-Agent', ''));

        // 1. Block requests with empty or missing User-Agent (real browsers always send one)
        if (empty($userAgent)) {
            return response()->json([
                'error' => 'Access Denied',
                'message' => 'Automated scraping tools without a valid User-Agent are blocked.',
            ], 403);
        }

        $lowerAgent = strtolower($userAgent);

        // Allow legitimate search engine crawlers and framework test clients
        foreach ($this->allowedAgents as $allowed) {
            if (str_contains($lowerAgent, $allowed)) {
                $response = $next($request);
                return $this->addSecurityHeaders($request, $response);
            }
        }

        // 2. Block known automated scraping tools and scrapers
        foreach ($this->blockedAgents as $blocked) {
            if (str_contains($lowerAgent, $blocked)) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'Automated scraping and data harvesting is prohibited on HentaiFlex.',
                ], 403);
            }
        }

        // 3. Hotlink and Cross-Origin Leeching Protection for Stream & Image Proxies
        if ($request->is('stream-proxy/*') || $request->is('image-proxy*')) {
            $referer = $request->header('Referer');
            $origin = $request->header('Origin');
            $host = strtolower($request->getHost());

            $allowedDomains = [
                'localhost',
                '127.0.0.1',
                $host,
                'hentaiflex.com',
                'www.hentaiflex.com',
            ];

            if ($referer) {
                $refHost = strtolower((string) parse_url($referer, PHP_URL_HOST));
                if ($refHost && !in_array($refHost, $allowedDomains, true)) {
                    return response()->json([
                        'error' => 'Hotlinking Denied',
                        'message' => 'External video hotlinking and bandwidth leeching is prohibited.',
                    ], 403);
                }
            }

            if ($origin) {
                $origHost = strtolower((string) parse_url($origin, PHP_URL_HOST));
                if ($origHost && !in_array($origHost, $allowedDomains, true)) {
                    return response()->json([
                        'error' => 'CORS Denied',
                        'message' => 'Cross-origin video embedding is prohibited.',
                    ], 403);
                }
            }
        }

        $response = $next($request);

        return $this->addSecurityHeaders($request, $response);
    }

    /**
     * Attach anti-scraping and anti-clickjacking security headers.
     */
    protected function addSecurityHeaders(Request $request, Response $response): Response
    {
        if ($request->is('embed/*')) {
            $response->headers->remove('X-Frame-Options');
            $response->headers->set('Content-Security-Policy', 'frame-ancestors *');
        } else {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
            $response->headers->set('X-Robots-Tag', 'noarchive');
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
