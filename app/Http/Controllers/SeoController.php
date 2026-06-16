<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

final class SeoController extends Controller
{
    public function robots(): Response
    {
        if (!$this->indexingEnabled()) {
            return response("User-agent: *\nDisallow: /\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }

        $baseUrl = rtrim(config('app.url'), '/');

        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            '',
            'Disallow: /dashboard',
            'Disallow: /settings',
            'Disallow: /bikes',
            'Disallow: /alerts',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /forgot-password',
            'Disallow: /reset-password',
            'Disallow: /two-factor-challenge',
            '',
            'Sitemap: ' . $baseUrl . '/sitemap.xml',
            '',
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(): Response
    {
        if (!$this->indexingEnabled()) {
            abort(404);
        }

        $baseUrl = rtrim(config('app.url'), '/');
        $lastmod = Carbon::now()->toDateString();

        $urls = collect(config('seo.sitemap_urls', []))
            ->map(fn(string $path): string => '/' . ltrim($path, '/'))
            ->unique()
            ->values();

        $xmlUrls = $urls
            ->map(function (string $path) use ($baseUrl, $lastmod): string {
                $loc = htmlspecialchars($baseUrl . $path, ENT_XML1, 'UTF-8');

                return <<<XML
                    <url>
                        <loc>{$loc}</loc>
                        <lastmod>{$lastmod}</lastmod>
                        <changefreq>weekly</changefreq>
                        <priority>{$this->priority($path)}</priority>
                    </url>
                XML;
            })
            ->implode("\n");

        $xml = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
        {$xmlUrls}
        </urlset>
        XML;

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    private function indexingEnabled(): bool
    {
        if (!config('seo.indexing_enabled')) {
            return false;
        }

        if (!App::environment('production')) {
            return false;
        }

        $productionHost = config('seo.production_host');

        if (!$productionHost) {
            return false;
        }

        return parse_url(config('app.url'), PHP_URL_HOST) === $productionHost;
    }

    private function priority(string $path): string
    {
        return match ($path) {
            '/' => '1.0',
            '/faq' => '0.8',
            '/privacy', '/legal-notice' => '0.4',
            default => '0.6',
        };
    }
}
