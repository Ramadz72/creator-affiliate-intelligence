<?php

namespace App\Http\Controllers;

use App\Support\Seo;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;

class SeoController extends Controller
{
    /**
     * robots.txt dinamis berdasarkan config/seo.php.
     */
    public function robots(Request $request, Seo $seo): HttpResponse
    {
        $config = config('seo');
        $baseUrl = rtrim((string) config('app.url'), '/');
        $lines = [
            'User-agent: *',
            'Allow: /',
        ];

        foreach ($config['disallow'] ?? [] as $path) {
            $lines[] = 'Disallow: '.$path;
        }

        $lines[] = 'Disallow: /*.json$';
        $lines[] = 'Disallow: /build/';
        $lines[] = '';
        $lines[] = 'Sitemap: '.$baseUrl.'/sitemap.xml';

        return response(implode("\n", $lines)."\n", 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    /**
     * sitemap.xml untuk halaman publik.
     */
    public function sitemap(Seo $seo): HttpResponse
    {
        $config = config('seo');
        $baseUrl = rtrim((string) config('app.url'), '/');
        $now = now()->toAtomString();
        $xml = [];

        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($config['public_paths'] ?? [] as $path) {
            $xml[] = '  <url>';
            $xml[] = '    <loc>'.$baseUrl.'/'.ltrim($path, '/').'</loc>';
            $xml[] = '    <lastmod>'.$now.'</lastmod>';
            $xml[] = '    <changefreq>'.$this->changeFreq($path).'</changefreq>';
            $xml[] = '    <priority>'.$this->priority($path).'</priority>';
            $xml[] = '  </url>';
        }

        $xml[] = '</urlset>';

        return response(implode("\n", $xml)."\n", 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    private function changeFreq(string $path): string
    {
        return $path === '/' ? 'weekly' : 'monthly';
    }

    private function priority(string $path): string
    {
        return $path === '/' ? '1.0' : '0.8';
    }
}
