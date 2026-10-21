<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Resolver metadata SEO per halaman.
 *
 * Semua meta tag (title, description, canonical, Open Graph, Twitter Card,
 * robots, dan structured data) dihitung di satu tempat supaya hasil render
 * di server dan pembaruan meta tag saat navigasi SPA selalu konsisten.
 */
class Seo
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(private readonly array $config)
    {
    }

    /**
     * Metadata lengkap untuk request saat ini.
     *
     * @return array{
     *     title: string,
     *     description: string,
     *     keywords: string,
     *     canonical: string,
     *     image: string,
     *     robots: string,
     *     type: string,
     *     locale: string,
     *     site_name: string,
     *     jsonLd: list<array<string, mixed>>
     * }
     */
    public function metadata(Request $request): array
    {
        $path = $this->path($request);

        $meta = [
            'title' => $this->title($this->config['title']),
            'description' => $this->config['description'],
            'keywords' => implode(', ', $this->config['keywords']),
            'canonical' => $this->url($path),
            'image' => $this->imageUrl(),
            'robots' => $this->robots($path),
            'type' => $this->config['type'],
            'locale' => $this->config['locale'],
            'site_name' => $this->config['site_name'],
            'jsonLd' => [],
        ];

        if ($path === '/') {
            $meta['description'] = 'Platform analitik kolaborasi creator & affiliate untuk toko online. '
                .'Nilai biaya kolaborasi creator, analisis performa affiliate dari data Seller Center, '
                .'serta insight dan rekomendasi tindakan berbasis data.';
            $meta['jsonLd'] = [
                $this->organizationJsonLd(),
                $this->webSiteJsonLd(),
            ];
        }

        if ($path === '/faq') {
            $meta['title'] = $this->title('FAQ');
            $meta['description'] = 'Jawaban lengkap mengenai fitur, scoring, import data Seller Center, '
                .'dan penggunaan Creator Affiliate Intelligence untuk tim creator, KOL, affiliate, dan campaign.';
            $meta['jsonLd'] = [$this->faqJsonLd()];
        }

        return $meta;
    }

    /**
     * Format judul halaman: "Judul - Nama Aplikasi".
     */
    public function title(string $pageTitle): string
    {
        $siteName = (string) config('app.name');

        if ($pageTitle === '' || $pageTitle === $siteName) {
            return $siteName;
        }

        return $pageTitle.' - '.$siteName;
    }

    /**
     * URL absolut untuk path aplikasi.
     */
    public function url(string $path = '/'): string
    {
        $base = rtrim((string) config('app.url'), '/');

        return $base.'/'.ltrim($path, '/');
    }

    /**
     * URL absolut gambar Open Graph.
     */
    public function imageUrl(): string
    {
        return $this->url('/'.ltrim((string) $this->config['image'], '/'));
    }

    /**
     * Aturan robots untuk path tertentu.
     */
    public function robots(string $path): string
    {
        return in_array($path, $this->config['public_paths'], true)
            ? 'index, follow'
            : 'noindex, nofollow';
    }

    /**
     * Path request tanpa query string dan tanpa trailing slash.
     */
    public function path(Request $request): string
    {
        $path = '/'.trim($request->path(), '/');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    /**
     * Structured data Organization.
     *
     * @return array<string, mixed>
     */
    private function organizationJsonLd(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $this->config['site_name'],
            'url' => $this->url('/'),
            'description' => $this->config['description'],
            'logo' => $this->imageUrl(),
            'image' => $this->imageUrl(),
        ];
    }

    /**
     * Structured data WebSite.
     *
     * @return array<string, mixed>
     */
    private function webSiteJsonLd(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $this->config['site_name'],
            'url' => $this->url('/'),
            'description' => $this->config['description'],
            'inLanguage' => $this->config['lang'],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->config['site_name'],
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $this->imageUrl(),
                ],
            ],
        ];
    }

    /**
     * Structured data FAQPage.
     *
     * @return array<string, mixed>
     */
    private function faqJsonLd(): array
    {
        $mainEntity = [];

        foreach (Faq::items() as $item) {
            $mainEntity[] = [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['answer'],
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity,
        ];
    }
}
