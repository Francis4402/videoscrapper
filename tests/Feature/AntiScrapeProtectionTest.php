<?php

namespace Tests\Feature;

use App\Http\Middleware\AntiScrapeProtection;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AntiScrapeProtectionTest extends TestCase
{
    protected AntiScrapeProtection $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new AntiScrapeProtection();
    }

    public function test_blocks_empty_user_agent(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->remove('User-Agent');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_blocks_python_requests_scraper(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'python-requests/2.31.0');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_blocks_curl_scraper(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'curl/7.88.1');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_allows_legitimate_browser_user_agent(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/124.0.0.0 Safari/537.36');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    public function test_allows_googlebot_crawler_for_seo(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_blocks_external_hotlinking_on_stream_proxy(): void
    {
        $request = Request::create('/stream-proxy/12345', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        $request->headers->set('Referer', 'https://malicious-tube-site.com/watch');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals(403, $response->getStatusCode());
    }

    public function test_allows_same_origin_stream_proxy_access(): void
    {
        $request = Request::create('/stream-proxy/12345', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        $request->headers->set('Referer', 'https://hentaiflex.com/watch/sample');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_redirects_www_to_non_www_permanently(): void
    {
        $request = Request::create('https://www.hentaiflex.com/category/3d', 'GET');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');

        $response = $this->middleware->handle($request, function () {
            return new Response('OK');
        });

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('https://hentaiflex.com/category/3d', $response->headers->get('Location'));
    }
}
