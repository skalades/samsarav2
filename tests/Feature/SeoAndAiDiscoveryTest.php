<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\DailyQuota;
use App\Models\SimaksiRegistration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAndAiDiscoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_includes_canonical_og_twitter_geo_and_json_ld_for_samsaracikuray(): void
    {
        DailyQuota::create([
            'quota_date' => now()->toDateString(),
            'max_quota' => 150,
            'booked_quota' => 12,
        ]);

        $response = $this->get(route('portal.home'));
        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="https://samsaracikuray.id/">', $html);
        $this->assertStringContainsString('<link rel="alternate" hreflang="id-ID" href="https://samsaracikuray.id/">', $html);
        $this->assertStringContainsString('<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">', $html);
        $this->assertStringContainsString('<meta property="og:url" content="https://samsaracikuray.id/">', $html);
        $this->assertStringContainsString('https://samsaracikuray.id/assets/images/og-samsara-cikuray.png', $html);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
        $this->assertStringContainsString('<meta name="application-name" content="SAMSARA EXPEDITION">', $html);

        // Ringkasan pos & jam operasional serta keyword target harus terlihat langsung di DOM (tidak tersembunyi)
        $this->assertStringContainsString('SIMAKSI Online Gunung Cikuray', $html);
        $this->assertStringContainsString('Pos / Titik', $html);
        $this->assertStringContainsString('Thun 30', $html);
        $this->assertStringContainsString('2.821 MDPL', $html);

        // Validasi payload JSON-LD @graph
        $matched = preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $html, $matches);
        $this->assertSame(1, $matched, 'JSON-LD script block must exist on landing page.');

        $jsonLd = json_decode($matches[1], true);
        $this->assertIsArray($jsonLd);
        $this->assertSame('https://schema.org', $jsonLd['@context']);
        $this->assertArrayHasKey('@graph', $jsonLd);

        $types = collect($jsonLd['@graph'])->pluck('@type')->flatten()->all();
        foreach (['WebSite', 'WebPage', 'Organization', 'Mountain', 'Service', 'HowTo', 'FAQPage', 'BreadcrumbList'] as $expectedType) {
            $this->assertContains($expectedType, $types, "JSON-LD @graph missing {$expectedType}");
        }
    }

    public function test_sitemap_xml_endpoint_is_valid_and_uses_canonical_domain(): void
    {
        $response = $this->get(route('seo.sitemap'));
        $response->assertOk();
        $this->assertStringContainsString('application/xml', (string) $response->headers->get('Content-Type'));

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml);
        $this->assertSame('https://samsaracikuray.id/', (string) $xml->url[0]->loc);
    }

    public function test_llms_txt_and_llms_full_txt_expose_structured_grounding_data(): void
    {
        AppSetting::set('simaksi_fee_per_person', '30000', 'Tarif SIMAKSI');

        $llms = $this->get(route('seo.llms'));
        $llms->assertOk();
        $this->assertStringContainsString('text/plain', (string) $llms->headers->get('Content-Type'));
        $llms->assertSee('https://samsaracikuray.id');
        $llms->assertSee('Rp 30.000');
        $llms->assertSee('Pos 3');
        $llms->assertSee('https://samsaracikuray.id/llms-full.txt');

        $llmsFull = $this->get(route('seo.llms_full'));
        $llmsFull->assertOk();
        $llmsFull->assertSee('Pertanyaan yang Sering Diajukan (FAQ)');
        $llmsFull->assertSee('Peralatan Wajib yang Diperiksa di Gerbang');
    }

    public function test_robots_txt_and_og_image_exist_with_proper_directives(): void
    {
        $robots = file_get_contents(public_path('robots.txt'));
        $this->assertStringContainsString('Sitemap: https://samsaracikuray.id/sitemap.xml', $robots);
        $this->assertStringContainsString('Disallow: /management', $robots);
        $this->assertStringContainsString('Allow: /llms.txt', $robots);
        $this->assertStringContainsString('Google-Extended', $robots);
        $this->assertStringContainsString('OAI-SearchBot', $robots);

        $ogPath = public_path('assets/images/og-samsara-cikuray.png');
        $this->assertFileExists($ogPath);
        $size = getimagesize($ogPath);
        $this->assertSame(1200, $size[0]);
        $this->assertSame(630, $size[1]);
    }

    public function test_private_management_and_ticket_pages_emit_noindex_nofollow(): void
    {
        $this->get(route('management.login'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $user = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('management.dashboard'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $reg = SimaksiRegistration::create([
            'registration_code' => 'SMK-202611-NOINDEX',
            'leader_name' => 'Pendaki Uji',
            'leader_nik' => '3205011990000002',
            'leader_phone' => '081234567891',
            'emergency_contact_name' => 'Keluarga',
            'emergency_contact_phone' => '081299990001',
            'hiking_date' => now()->addDays(2)->toDateString(),
            'total_climbers' => 1,
            'status' => 'paid',
            'payment_status' => 'paid',
            'payment_method' => 'transfer',
            'simaksi_fee' => 25000,
            'total_amount' => 25000,
        ]);

        $this->get($reg->publicTicketUrl())
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }
}
