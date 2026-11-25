<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\DailyQuota;
use App\Models\TrailCondition;
use App\Support\InspectionTemplate;
use Carbon\Carbon;

/**
 * Layanan terpusat untuk metadata SEO (Google Search) & GEO (AI Overviews / LLM Search).
 * Mengambil seluruh fakta langsung dari AppSetting, DailyQuota, TrailCondition, dan InspectionTemplate
 * sehingga data di UI, JSON-LD, sitemap.xml, dan llms.txt selalu konsisten tanpa duplikasi.
 */
class SeoMetadataService
{
    /**
     * Bangun URL kanonikal absolut menggunakan domain resmi (default: https://samsaracikuray.id).
     */
    public function canonicalUrl(string $path = '/'): string
    {
        $base = rtrim((string) config('seo.canonical_url', 'https://samsaracikuray.id'), '/');
        $cleanPath = '/'.ltrim($path, '/');

        return $cleanPath === '/' ? $base.'/' : $base.$cleanPath;
    }

    /**
     * Bangun URL aset publik berbasis domain kanonikal untuk OG image, logo, dan schema.
     */
    public function assetUrl(string $relativePath): string
    {
        return rtrim((string) config('seo.canonical_url', 'https://samsaracikuray.id'), '/')
            .'/'.ltrim($relativePath, '/');
    }

    /**
     * Kumpulkan seluruh konteks portal (konten terstruktur, FAQ, langkah, metadata SEO, dan JSON-LD).
     */
    public function getPortalContext(
        ?DailyQuota $todayQuota = null,
        ?TrailCondition $trailCondition = null,
        ?array $tariffs = null,
        ?array $profile = null
    ): array {
        $profile ??= AppSetting::getExpeditionProfile();
        $tariffs ??= AppSetting::getTariffs();
        $trailCondition ??= TrailCondition::latest()->first();
        $trailConfig = AppSetting::getTrailConfig();
        $trailRoute = AppSetting::getTrailRoute();
        $checklist = InspectionTemplate::get();
        $facilities = AppSetting::getBasecampFacilities();

        if (! $todayQuota) {
            $defaultQuota = (int) ($profile['default_daily_quota'] ?? 150);
            $todayQuota = DailyQuota::whereDate('quota_date', Carbon::today()->toDateString())->first();
            $remainingQuota = $todayQuota ? (int) $todayQuota->remaining_quota : $defaultQuota;
            $maxQuota = $todayQuota ? (int) $todayQuota->max_quota : $defaultQuota;
        } else {
            $remainingQuota = (int) $todayQuota->remaining_quota;
            $maxQuota = (int) $todayQuota->max_quota;
        }

        if (! empty($trailRoute[0])) {
            $trailRoute[0]['name'] = $profile['basecamp_name'] ?? 'Basecamp SAMSARA';
        }

        $steps = $this->buildSteps($tariffs);
        $faqs = $this->buildFaqs($trailConfig, $tariffs, $profile);
        $seo = $this->buildSeoPayload(
            profile: $profile,
            tariffs: $tariffs,
            trailCondition: $trailCondition,
            trailConfig: $trailConfig,
            trailRoute: $trailRoute,
            steps: $steps,
            faqs: $faqs,
            maxQuota: $maxQuota,
            remainingQuota: $remainingQuota
        );

        return compact(
            'profile',
            'tariffs',
            'trailCondition',
            'trailConfig',
            'trailRoute',
            'checklist',
            'facilities',
            'steps',
            'faqs',
            'maxQuota',
            'remainingQuota',
            'seo'
        );
    }

    /**
     * Daftar 5 langkah resmi alur pendakian (dipakai bersama oleh UI, HowTo Schema, dan llms.txt).
     *
     * @return array<int, array{icon:string, title:string, text:string}>
     */
    public function buildSteps(array $tariffs): array
    {
        $hasOjek = (float) ($tariffs['ojek_fee_per_trip'] ?? 0) > 0;

        return [
            [
                'icon' => 'ticket',
                'title' => 'Daftar & bayar online',
                'text' => 'Pilih tanggal, isi data rombongan dan KTP ketua, lalu bayar via transfer, QRIS, atau tunai di basecamp.',
            ],
            [
                'icon' => 'whatsapp',
                'title' => 'E-SIMAKSI dikirim ke WhatsApp',
                'text' => 'Setelah kasir memverifikasi lunas, info pendakian lengkap dan tautan e-SIMAKSI langsung masuk ke WhatsApp ketua.',
            ],
            [
                'icon' => 'checklist',
                'title' => 'Pemeriksaan di gerbang',
                'text' => 'Gatekeeper mengecek anggota, alat wajib, memotret tim, dan menghitung logistik yang berpotensi jadi sampah.',
            ],
            [
                'icon' => 'printer',
                'title' => 'Ambil surat jalan, mulai mendaki',
                'text' => 'Kasir mencetak surat jalan'.($hasOjek ? ' dan voucher ojek' : '').'. Ranger mencatat rombongan di setiap pos.',
            ],
            [
                'icon' => 'arrow-down',
                'title' => 'Turun & hitung ulang',
                'text' => 'Logistik dicocokkan lagi di gerbang. Semua sampah kembali, lalu kasir menerbitkan izin keluar parkir.',
            ],
        ];
    }

    /**
     * Daftar FAQ resmi (dipakai bersama oleh UI, FAQPage JSON-LD, dan llms.txt).
     *
     * @return array<int, array{q:string, a:string}>
     */
    public function buildFaqs(array $trailConfig, array $tariffs, array $profile): array
    {
        $mountain = $profile['mountain_name'] ?? 'Gunung Cikuray';
        $basecamp = $profile['basecamp_name'] ?? 'Basecamp SAMSARA';
        $simaksiFee = $this->formatRp($tariffs['simaksi_fee_per_person'] ?? 25000);
        $ojekFee = $this->formatRp($tariffs['ojek_fee_per_trip'] ?? 30000);
        $maxQuota = (int) ($profile['default_daily_quota'] ?? 150);

        return [
            [
                'q' => 'Bagaimana cara daftar SIMAKSI online '.$mountain.' via '.$basecamp.'?',
                'a' => 'Buka portal SIMAKSI online '.$mountain.' resmi di samsaracikuray.id, pilih menu Reservasi, tentukan tanggal naik, jumlah pendaki (1–'.($profile['max_climbers_per_group'] ?? 15).' orang), isi identitas ketua beserta foto KTP dan kontak darurat keluarga, lalu selesaikan pembayaran via QRIS, transfer bank, atau tunai di loket basecamp.',
            ],
            [
                'q' => 'Berapa tarif SIMAKSI, ojek, porter, dan parkir di '.$basecamp.'?',
                'a' => 'Tarif resmi SIMAKSI adalah '.$simaksiFee.' per pendaki, shuttle ojek '.($tariffs['ojek_route_name'] ?? 'Basecamp → Pos 1').' sebesar '.$ojekFee.' per perjalanan, jasa porter lokal '.$this->formatRp($tariffs['porter_fee_per_trip'] ?? 350000).' per trip (maks. 20 kg), parkir motor '.$this->formatRp($tariffs['parking_motor_fee'] ?? 10000).', dan parkir mobil '.$this->formatRp($tariffs['parking_car_fee'] ?? 25000).'. Kuota konservasi dibatasi '.$maxQuota.' pendaki per hari.',
            ],
            [
                'q' => 'Jam berapa harus check-in di basecamp?',
                'a' => 'Pendaki tektok (pulang hari) check-in pukul '.$trailConfig['tektok_checkin_start'].'–'.$trailConfig['tektok_checkin_end'].' WIB dan wajib kembali sebelum pukul '.$trailConfig['tektok_max_checkout_hour'].':00 WIB. Pendaki camp (bermalam) check-in pukul '.$trailConfig['camp_checkin_start'].'–'.$trailConfig['camp_checkin_end'].' WIB.',
            ],
            [
                'q' => 'Di mana saya menerima e-tiket SIMAKSI?',
                'a' => 'Setelah pembayaran diverifikasi kasir, e-SIMAKSI dan info pendakian lengkap dikirim ke nomor WhatsApp ketua rombongan. Anda juga bisa melacak dan mengunduh tiket kapan saja lewat menu Cek tiket di samsaracikuray.id.',
            ],
            [
                'q' => 'Kenapa logistik dihitung di gerbang?',
                'a' => 'Agar tidak ada sampah tertinggal di '.$mountain.'. Jumlah barang seperti botol dan bungkus makanan dicatat saat naik oleh gatekeeper, lalu dicocokkan kembali dengan yang dibawa turun saat check-out.',
            ],
            [
                'q' => 'Bagaimana data KTP saya diperlakukan?',
                'a' => 'Foto KTP ketua hanya dipakai sementara untuk verifikasi identitas oleh petugas gerbang dan dihapus permanen secara otomatis setelah pendakian selesai, sesuai prinsip Undang-Undang Pelindungan Data Pribadi (UU PDP).',
            ],
            [
                'q' => 'Bolehkah mendirikan tenda di mana saja di jalur '.$mountain.'?',
                'a' => 'Tidak. Tenda hanya diperbolehkan di area camp resmi yang dijaga ranger, yaitu Pos 2 Batu Gendrung (1.682 MDPL, kawasan hutan pinus) dan Pos 3 Thun 30 (2.097 MDPL). Puncak dilarang keras untuk camp, dan pendaki tektok dilarang bermalam.',
            ],
            [
                'q' => 'Sebaiknya camp di Pos 2 atau Pos 3?',
                'a' => 'Keduanya boleh, namun kami menyarankan Pos 3 Thun 30 (2.097 MDPL) karena paling dekat ke puncak dan merupakan sumber air terakhir. Dirikan tenda dan tinggalkan perlengkapan di Pos 3, lalu summit attack dini hari cukup membawa daypack, air, headlamp, dan jaket.',
            ],
            [
                'q' => 'Di mana sumber air, toilet, dan shelter di jalur?',
                'a' => 'Pos 1 (Tanjakan EOD, 1.424 MDPL), Pos 2 (Batu Gendrung, 1.682 MDPL), dan Pos 3 (Thun 30, 2.097 MDPL) memiliki air melimpah, toilet, mushola, dan shelter. Dari Pos 4 hingga Puncak Cikuray (2.821 MDPL) tidak terdapat sumber air, sehingga pendaki wajib mengisi penuh botol minum di Pos 3.',
            ],
        ];
    }

    /**
     * Bangun seluruh tag metadata & JSON-LD Schema.org untuk halaman portal publik.
     */
    protected function buildSeoPayload(
        array $profile,
        array $tariffs,
        ?TrailCondition $trailCondition,
        array $trailConfig,
        array $trailRoute,
        array $steps,
        array $faqs,
        int $maxQuota,
        int $remainingQuota
    ): array {
        $expedition = $profile['expedition_name'] ?? 'SAMSARA EXPEDITION';
        $basecamp = $profile['basecamp_name'] ?? 'Basecamp SAMSARA';
        $mountain = $profile['mountain_name'] ?? 'Gunung Cikuray';
        $elevation = $profile['mountain_elevation'] ?? '2.821 MDPL';
        $location = $profile['basecamp_location'] ?? 'Kecamatan Cilawu / Bayongbong, Garut, Jawa Barat';
        $canonical = $this->canonicalUrl('/');

        $title = "SIMAKSI Online {$mountain} ({$elevation}) via {$basecamp} | samsaracikuray.id";
        $description = "Portal resmi SIMAKSI online {$mountain} ({$elevation}) via {$basecamp}, Cihuru, Cigedug, Garut. Cek kuota harian real-time ({$remainingQuota}/{$maxQuota}), pesan e-SIMAKSI mulai ".$this->formatRp($tariffs['simaksi_fee_per_person'] ?? 25000).'/orang, peta jalur GPS, dan e-tiket WhatsApp.';

        $ogImage = $this->resolveOgImageUrl($profile);
        $lat = trim((string) ($profile['basecamp_latitude'] ?? '-7.331855'));
        $lng = trim((string) ($profile['basecamp_longitude'] ?? '107.8096294'));

        $jsonLd = $this->buildJsonLdGraph(
            canonical: $canonical,
            ogImage: $ogImage,
            title: $title,
            description: $description,
            profile: $profile,
            tariffs: $tariffs,
            trailCondition: $trailCondition,
            trailRoute: $trailRoute,
            steps: $steps,
            faqs: $faqs,
            lat: $lat,
            lng: $lng,
            location: $location
        );

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'llms_url' => $this->canonicalUrl('/llms.txt'),
            'sitemap_url' => $this->canonicalUrl('/sitemap.xml'),
            'robots' => 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1',
            'keywords' => implode(', ', config('seo.keywords', [])),
            'author' => (string) config('seo.author', $basecamp),
            'site_name' => $expedition.' — '.$mountain,
            'locale' => (string) config('seo.locale', 'id_ID'),
            'hreflang' => (string) config('seo.hreflang', 'id-ID'),
            'geo_region' => (string) config('seo.geo.region', 'ID-JB'),
            'geo_placename' => $location,
            'geo_position' => "{$lat};{$lng}",
            'icbm' => "{$lat}, {$lng}",
            'og_image' => $ogImage,
            'og_image_width' => (int) config('seo.og_image.width', 1200),
            'og_image_height' => (int) config('seo.og_image.height', 630),
            'og_image_alt' => "{$expedition} — Portal Resmi SIMAKSI {$mountain} {$elevation} di samsaracikuray.id",
            'verification' => config('seo.verification', []),
            'json_ld' => $jsonLd,
        ];
    }

    /**
     * Bangun Schema.org @graph lengkap agar Google Search & AI Overviews memahami entitas, tarif, rute, cara daftar, dan FAQ.
     */
    protected function buildJsonLdGraph(
        string $canonical,
        string $ogImage,
        string $title,
        string $description,
        array $profile,
        array $tariffs,
        ?TrailCondition $trailCondition,
        array $trailRoute,
        array $steps,
        array $faqs,
        string $lat,
        string $lng,
        string $location
    ): array {
        $expedition = $profile['expedition_name'] ?? 'SAMSARA EXPEDITION';
        $basecamp = $profile['basecamp_name'] ?? 'Basecamp SAMSARA';
        $mountain = $profile['mountain_name'] ?? 'Gunung Cikuray';
        $elevationNum = (int) ($profile['mountain_elevation_num'] ?? 2821);

        $orgId = $canonical.'#organization';
        $websiteId = $canonical.'#website';
        $webpageId = $canonical.'#webpage';
        $mountainId = $canonical.'#mountain';

        $logoUrl = ! empty($profile['app_logo'])
            ? $this->assetUrl('storage/'.ltrim((string) $profile['app_logo'], '/'))
            : $this->assetUrl('assets/icons/icon-512.png');

        $organization = [
            '@type' => ['Organization', 'TouristInformationCenter', 'SportsActivityLocation'],
            '@id' => $orgId,
            'name' => $basecamp.' ('.$expedition.')',
            'alternateName' => ['SAMSARA Cikuray', $expedition, $basecamp],
            'url' => $canonical,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $logoUrl,
            ],
            'image' => $ogImage,
            'description' => "Posko & gerbang resmi pendaftaran SIMAKSI pendakian {$mountain} ({$elevationNum} MDPL) via jalur SAMSARA di Garut, Jawa Barat.",
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $location,
                'addressLocality' => 'Garut',
                'addressRegion' => 'Jawa Barat',
                'addressCountry' => 'ID',
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $lat,
                'longitude' => (float) $lng,
                'elevation' => (int) preg_replace('/\D/', '', (string) ($profile['basecamp_elevation'] ?? '1245')),
            ],
            'openingHoursSpecification' => [
                [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                    'opens' => '00:00',
                    'closes' => '23:59',
                ],
            ],
            'priceRange' => $this->formatRp($tariffs['simaksi_fee_per_person'] ?? 25000).' – '.$this->formatRp($tariffs['porter_fee_per_trip'] ?? 350000),
            'areaServed' => [
                '@type' => 'AdministrativeArea',
                'name' => 'Kabupaten Garut, Jawa Barat, Indonesia',
            ],
        ];

        $phone = trim((string) ($profile['admin_whatsapp_display'] ?: ($profile['emergency_hotline'] ?? '')));
        if ($phone !== '') {
            $organization['telephone'] = $phone;
        }
        if (! empty($profile['basecamp_maps_url'])) {
            $organization['hasMap'] = $profile['basecamp_maps_url'];
        }

        $itineraryItems = [];
        foreach (array_values($trailRoute) as $idx => $pt) {
            $itineraryItems[] = [
                '@type' => 'ListItem',
                'position' => $idx + 1,
                'item' => [
                    '@type' => 'Place',
                    'name' => trim($pt['code'].' — '.$pt['name']),
                    'description' => $pt['description'],
                    'geo' => [
                        '@type' => 'GeoCoordinates',
                        'latitude' => (float) $pt['lat'],
                        'longitude' => (float) $pt['lng'],
                        'elevation' => (int) $pt['elev'],
                    ],
                ],
            ];
        }

        $peak = collect($trailRoute)->last();
        $mountainSchema = [
            '@type' => ['TouristAttraction', 'Mountain'],
            '@id' => $mountainId,
            'name' => $mountain.' via '.$basecamp,
            'alternateName' => ['Jalur Pendakian Gunung Cikuray SAMSARA', 'Gunung Cikuray 2821 MDPL'],
            'description' => "Jalur pendakian resmi menuju Puncak {$mountain} ({$elevationNum} MDPL) melalui {$basecamp}, lengkap dengan ojek Basecamp–Pos 1, sumber air Pos 1–3, area camp resmi Pos 2 & Pos 3, dan pengawasan ranger serta SAR 24 jam.",
            'url' => $canonical,
            'image' => $ogImage,
            'publicAccess' => true,
            'isAccessibleForFree' => false,
            'touristType' => ['Pendaki Gunung', 'Trekker', 'Pecinta Alam'],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) ($peak['lat'] ?? -7.3225978),
                'longitude' => (float) ($peak['lng'] ?? 107.85985),
                'elevation' => $elevationNum,
            ],
            'itinerary' => [
                '@type' => 'ItemList',
                'name' => 'Pos Jalur Pendakian '.$mountain.' via '.$basecamp,
                'numberOfItems' => count($itineraryItems),
                'itemListElement' => $itineraryItems,
            ],
        ];

        $serviceSchema = [
            '@type' => 'Service',
            '@id' => $canonical.'#simaksi-service',
            'name' => "SIMAKSI Online {$mountain} & Layanan Pendakian {$basecamp}",
            'serviceType' => "SIMAKSI Online {$mountain} (Surat Izin Masuk Kawasan Konservasi) & Pendakian Gunung",
            'provider' => ['@id' => $orgId],
            'areaServed' => 'Gunung Cikuray, Garut, Jawa Barat',
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name' => 'Tarif Resmi Pendakian '.$basecamp,
                'itemListElement' => [
                    $this->buildOffer('Tiket SIMAKSI Online '.$mountain.' (per orang)', $tariffs['simaksi_fee_per_person'] ?? 25000, $canonical),
                    $this->buildOffer('Shuttle Ojek '.($tariffs['ojek_route_name'] ?? 'Basecamp → Pos 1').' (per perjalanan)', $tariffs['ojek_fee_per_trip'] ?? 30000, $canonical),
                    $this->buildOffer('Jasa Porter Lokal Gunung Cikuray (per trip 2H1M, maks. 20 kg)', $tariffs['porter_fee_per_trip'] ?? 350000, $canonical),
                    $this->buildOffer('Parkir Sepeda Motor 24 Jam Basecamp', $tariffs['parking_motor_fee'] ?? 10000, $canonical),
                    $this->buildOffer('Parkir Mobil Pribadi 24 Jam Basecamp', $tariffs['parking_car_fee'] ?? 25000, $canonical),
                ],
            ],
        ];

        $howToSteps = [];
        foreach (array_values($steps) as $idx => $step) {
            $howToSteps[] = [
                '@type' => 'HowToStep',
                'position' => $idx + 1,
                'name' => $step['title'],
                'text' => $step['text'],
                'url' => $canonical.'#sec-booking',
            ];
        }

        $howToSchema = [
            '@type' => 'HowTo',
            '@id' => $canonical.'#howto-simaksi',
            'name' => "Cara Daftar SIMAKSI Online {$mountain} via {$basecamp}",
            'description' => "Panduan 5 langkah pendaftaran e-SIMAKSI hingga check-out pendakian {$mountain} di {$basecamp}.",
            'inLanguage' => 'id-ID',
            'step' => $howToSteps,
        ];

        $faqEntities = [];
        foreach ($faqs as $faq) {
            $faqEntities[] = [
                '@type' => 'Question',
                'name' => $faq['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['a'],
                ],
            ];
        }

        $faqSchema = [
            '@type' => 'FAQPage',
            '@id' => $canonical.'#faq',
            'mainEntity' => $faqEntities,
        ];

        $websiteSchema = [
            '@type' => 'WebSite',
            '@id' => $websiteId,
            'url' => $canonical,
            'name' => $expedition.' — '.$mountain,
            'alternateName' => ['samsaracikuray.id', 'Basecamp SAMSARA Cikuray'],
            'inLanguage' => 'id-ID',
            'publisher' => ['@id' => $orgId],
        ];

        $lastModified = $trailCondition?->updated_at
            ? $trailCondition->updated_at->toIso8601String()
            : now()->startOfDay()->toIso8601String();

        $webPageSchema = [
            '@type' => 'WebPage',
            '@id' => $webpageId,
            'url' => $canonical,
            'name' => $title,
            'description' => $description,
            'inLanguage' => 'id-ID',
            'isPartOf' => ['@id' => $websiteId],
            'about' => ['@id' => $mountainId],
            'primaryImageOfPage' => [
                '@type' => 'ImageObject',
                'url' => $ogImage,
            ],
            'dateModified' => $lastModified,
        ];

        $breadcrumbSchema = [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical.'#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Beranda Portal SIMAKSI '.$mountain,
                    'item' => $canonical,
                ],
            ],
        ];

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                $websiteSchema,
                $webPageSchema,
                $organization,
                $mountainSchema,
                $serviceSchema,
                $howToSchema,
                $faqSchema,
                $breadcrumbSchema,
            ],
        ];
    }

    /**
     * Hasilkan dokumen /llms.txt ringkas & terstruktur untuk mesin pencari AI (ChatGPT, Perplexity, Gemini, Claude).
     */
    public function buildLlmsTxt(): string
    {
        $ctx = $this->getPortalContext();
        $profile = $ctx['profile'];
        $tariffs = $ctx['tariffs'];
        $trailCfg = $ctx['trailConfig'];
        $trailRoute = $ctx['trailRoute'];
        $condition = $ctx['trailCondition'];
        $canonical = $this->canonicalUrl('/');

        $mountain = $profile['mountain_name'] ?? 'Gunung Cikuray';
        $elevation = $profile['mountain_elevation'] ?? '2.821 MDPL';
        $basecamp = $profile['basecamp_name'] ?? 'Basecamp SAMSARA';
        $expedition = $profile['expedition_name'] ?? 'SAMSARA EXPEDITION';
        $statusMap = ['open' => 'DIBUKA (Aman)', 'caution' => 'WASPADA', 'closed' => 'DITUTUP'];
        $statusText = $statusMap[$condition->status ?? 'open'] ?? 'DIBUKA';
        $last = collect($trailRoute)->last();
        $totalKm = number_format(($last['cum']['dist_m'] ?? 0) / 1000, 1, ',', '.');
        $totalHours = round(($last['cum']['up_min'] ?? 0) / 60, 1);

        $lines = [
            "# {$expedition} — {$mountain} ({$elevation})",
            '',
            "> Portal resmi pendaftaran e-SIMAKSI, cek kuota harian, pemetaan jalur GPS, dan informasi pendakian {$mountain} via {$basecamp} di Kabupaten Garut, Jawa Barat.",
            '',
            "- Domain Resmi: {$canonical}",
            "- Lokasi Basecamp: {$profile['basecamp_location']} ({$profile['basecamp_elevation']})",
            "- Koordinat GPS Basecamp: {$profile['basecamp_coordinates']}",
            "- Status Jalur Saat Ini: {$statusText}",
            "- Kuota Harian Hari Ini: {$ctx['remainingQuota']} slot tersisa dari maksimal {$ctx['maxQuota']} pendaki/hari",
            "- Panjang Jalur & Estimasi Naik: ±{$totalKm} km (±{$totalHours} jam jalan kaki dari Basecamp ke Puncak)",
            '',
            '## Tarif Resmi (IDR)',
            '- SIMAKSI Pendakian: '.$this->formatRp($tariffs['simaksi_fee_per_person'] ?? 25000).' / orang',
            '- Shuttle Ojek Trail ('.($tariffs['ojek_route_name'] ?? 'Basecamp → Pos 1').'): '.$this->formatRp($tariffs['ojek_fee_per_trip'] ?? 30000).' / perjalanan',
            '- Porter Lokal (maks. 20 kg): '.$this->formatRp($tariffs['porter_fee_per_trip'] ?? 350000).' / trip (2H1M)',
            '- Parkir Motor (24 jam): '.$this->formatRp($tariffs['parking_motor_fee'] ?? 10000).' / unit',
            '- Parkir Mobil (24 jam): '.$this->formatRp($tariffs['parking_car_fee'] ?? 25000).' / unit',
            '',
            '## Jam Check-in & Aturan Camp',
            "- Kategori Tektok (1 Hari): Check-in {$trailCfg['tektok_checkin_start']}–{$trailCfg['tektok_checkin_end']} WIB, wajib turun sebelum {$trailCfg['tektok_max_checkout_hour']}:00 WIB (dilarang mendirikan tenda).",
            "- Kategori Camp (Bermalam): Check-in {$trailCfg['camp_checkin_start']}–{$trailCfg['camp_checkin_end']} WIB.",
            '- Area Camp Resmi: Pos 2 Batu Gendrung (1.682 MDPL) dan Pos 3 Thun 30 (2.097 MDPL, paling disarankan). Puncak (2.821 MDPL) DILARANG KERAS untuk camp.',
            '- Sumber Air: Pos 1, Pos 2, dan Pos 3 memiliki air melimpah, toilet, mushola, dan shelter. Pos 4 sampai Puncak tidak ada sumber air.',
            '- Pengendalian Sampah: Seluruh logistik kemasan dihitung petugas di gerbang saat naik dan dicocokkan ulang saat turun.',
            '- Privasi Data (UU PDP): Foto KTP ketua dihapus permanen otomatis setelah check-out selesai.',
            '',
            '## Titik Jalur Resmi (Survei GPS)',
        ];

        foreach ($trailRoute as $pt) {
            $fac = count($pt['facilities'] ?? []) ? ' · Fasilitas: '.implode(', ', $pt['facilities']) : '';
            $lines[] = "- {$pt['code']} — {$pt['name']} ({$pt['elev']} MDPL){$fac}: {$pt['description']}";
        }

        if (! empty($profile['basecamp_maps_url']) || ! empty($profile['admin_whatsapp_display']) || ! empty($profile['emergency_hotline'])) {
            $lines[] = '';
            $lines[] = '## Kontak & Navigasi Resmi';
            if (! empty($profile['basecamp_maps_url'])) {
                $lines[] = '- Google Maps Basecamp: '.$profile['basecamp_maps_url'];
            }
            if (! empty($profile['admin_whatsapp_display'])) {
                $lines[] = '- WhatsApp Admin: '.$profile['admin_whatsapp_display'];
            }
            if (! empty($profile['emergency_hotline'])) {
                $lines[] = '- Hotline SAR 24 Jam: '.$profile['emergency_hotline'];
            }
        }

        $lines[] = '';
        $lines[] = '## Dokumen Lanjutan';
        $lines[] = '- Panduan lengkap & FAQ: '.$this->canonicalUrl('/llms-full.txt');
        $lines[] = '- Sitemap XML: '.$this->canonicalUrl('/sitemap.xml');

        return implode("\n", $lines)."\n";
    }

    /**
     * Hasilkan dokumen /llms-full.txt komprehensif termasuk langkah pendaftaran, alat wajib, logistik sampah, dan seluruh FAQ.
     */
    public function buildLlmsFullTxt(): string
    {
        $ctx = $this->getPortalContext();
        $base = rtrim($this->buildLlmsTxt(), "\n");

        $extra = [
            '',
            '## Alur Pendaftaran & Pendakian (5 Langkah)',
        ];
        foreach ($ctx['steps'] as $i => $step) {
            $extra[] = ($i + 1).". **{$step['title']}**: {$step['text']}";
        }

        $extra[] = '';
        $extra[] = '## Peralatan Wajib yang Diperiksa di Gerbang';
        foreach ($ctx['checklist']['gear'] as $gear) {
            $extra[] = "- {$gear}";
        }

        $extra[] = '';
        $extra[] = '## Barang Logistik yang Dihitung Naik & Turun';
        foreach ($ctx['checklist']['logistic'] as $item) {
            $extra[] = "- {$item['name']} (dihitung per {$item['unit']})";
        }

        $extra[] = '';
        $extra[] = '## Pertanyaan yang Sering Diajukan (FAQ)';
        foreach ($ctx['faqs'] as $faq) {
            $extra[] = "### {$faq['q']}";
            $extra[] = $faq['a'];
            $extra[] = '';
        }

        return $base."\n".implode("\n", $extra)."\n";
    }

    /**
     * Hasilkan XML Sitemap standar Google Search & Image Sitemap untuk domain kanonikal.
     */
    public function buildSitemapXml(): string
    {
        $canonical = htmlspecialchars($this->canonicalUrl('/'), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $ogImage = htmlspecialchars($this->resolveOgImageUrl(AppSetting::getExpeditionProfile()), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $trailCondition = TrailCondition::latest()->first();
        $lastmod = ($trailCondition?->updated_at ?? now())->toAtomString();

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
  <url>
    <loc>{$canonical}</loc>
    <lastmod>{$lastmod}</lastmod>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
    <image:image>
      <image:loc>{$ogImage}</image:loc>
      <image:title>SAMSARA EXPEDITION — Portal Resmi SIMAKSI Gunung Cikuray 2.821 MDPL</image:title>
    </image:image>
  </url>
</urlset>
XML;
    }

    protected function buildOffer(string $name, float|int $price, string $url): array
    {
        return [
            '@type' => 'Offer',
            'name' => $name,
            'price' => (int) round((float) $price),
            'priceCurrency' => 'IDR',
            'availability' => 'https://schema.org/InStock',
            'url' => $url.'#sec-booking',
        ];
    }

    protected function resolveOgImageUrl(array $profile): string
    {
        $relative = (string) config('seo.og_image.path', 'assets/images/og-samsara-cikuray.png');
        if (is_file(public_path($relative))) {
            return $this->assetUrl($relative);
        }

        if (! empty($profile['app_logo'])) {
            return $this->assetUrl('storage/'.ltrim((string) $profile['app_logo'], '/'));
        }

        return $this->assetUrl('assets/icons/icon-512.png');
    }

    protected function formatRp(float|int $amount): string
    {
        return 'Rp '.number_format((float) $amount, 0, ',', '.');
    }
}
