<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Domain Kanonikal & Identitas Pencarian (SEO / GEO)
    |--------------------------------------------------------------------------
    |
    | Seluruh canonical URL, Open Graph, JSON-LD, sitemap.xml, dan llms.txt
    | mengacu ke domain resmi ini agar mesin pencari dan ringkasan AI
    | mengenali satu sumber otoritas tunggal.
    |
    */
    'canonical_url' => rtrim((string) env('SEO_CANONICAL_URL', 'https://samsaracikuray.id'), '/'),

    'site_name' => env('SEO_SITE_NAME', 'SAMSARA EXPEDITION — Gunung Cikuray'),

    'locale' => 'id_ID',
    'hreflang' => 'id-ID',

    'author' => 'Basecamp SAMSARA — Gunung Cikuray',

    'geo' => [
        'region' => 'ID-JB',
        'placename' => 'Garut, Jawa Barat',
        'country' => 'ID',
    ],

    'og_image' => [
        'path' => 'assets/images/og-samsara-cikuray.png',
        'width' => 1200,
        'height' => 630,
        'type' => 'image/png',
    ],

    'keywords' => [
        'SIMAKSI Gunung Cikuray',
        'Basecamp SAMSARA Cikuray',
        'Jalur Pendakian Gunung Cikuray via Samsara',
        'Booking SIMAKSI Cikuray Online',
        'Kuota Pendakian Gunung Cikuray',
        'Tarif SIMAKSI Gunung Cikuray',
        'Gunung Cikuray 2821 MDPL',
        'Pendakian Cikuray Garut',
        'E-SIMAKSI Cikuray',
        'samsaracikuray.id',
    ],

    'verification' => [
        'google' => (string) env('GOOGLE_SITE_VERIFICATION', ''),
        'bing' => (string) env('BING_SITE_VERIFICATION', ''),
    ],
];
