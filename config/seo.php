<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identitas situs
    |--------------------------------------------------------------------------
    |
    | Nilai default untuk meta tag, Open Graph, Twitter Card, dan structured
    | data. Bisa dioverride lewat environment variable.
    |
    */

    'site_name' => env('SEO_SITE_NAME', 'Creator Affiliate Intelligence'),

    'title' => env('SEO_TITLE', 'Creator Affiliate Intelligence'),

    'description' => env(
        'SEO_DESCRIPTION',
        'Platform analitik kolaborasi creator & affiliate. Ukur legitimately biaya kolaborasi creator, '
            .'analisa performa affiliate dari data Seller Center, dan ubah data menjadi scoring, insight, '
            .'serta rekomendasi tindakan yang bisa langsung dijalankan.'
    ),

    'keywords' => [
        'creator affiliate intelligence',
        'analitik collaborate creator',
        'scoring creator',
        'affiliate performance analytics',
        'tiktok seller center analytics',
        'insight performa affiliate',
    ],

    /*
    |--------------------------------------------------------------------------
    | Gambar & tema
    |--------------------------------------------------------------------------
    */

    'image' => 'images/og-image.png',

    'theme_color' => '#020617',

    'locale' => 'id_ID',

    'lang' => 'id',

    'type' => 'website',

    'twitter_handle' => env('SEO_TWITTER_HANDLE'),

    /*
    |--------------------------------------------------------------------------
    | Halaman publik (di-index mesin pencari)
    |--------------------------------------------------------------------------
    */

    'public_paths' => [
        '/',
        '/faq',
    ],

    /*
    |--------------------------------------------------------------------------
    | Path yang tidak boleh di-crawl
    |--------------------------------------------------------------------------
    */

    'disallow' => [
        '/dashboard',
        '/creators',
        '/campaigns',
        '/affiliates',
        '/imports',
        '/history',
        '/insights',
        '/settings',
        '/logout',
        '/email',
        '/user',
        '/api',
        '/storage',
        '/up',
    ],

];
