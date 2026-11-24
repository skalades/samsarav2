<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_webmanifest_exists_and_is_valid_json(): void
    {
        $manifestPath = public_path('manifest.webmanifest');
        $this->assertFileExists($manifestPath);

        $content = file_get_contents($manifestPath);
        $json = json_decode($content, true);

        $this->assertIsArray($json);
        $this->assertEquals('SAMSARA EXPEDITION — Backoffice', $json['name']);
        $this->assertEquals('SAMSARA OPS', $json['short_name']);
        $this->assertEquals('/management', $json['start_url']);
        $this->assertEquals('standalone', $json['display']);
        $this->assertEquals('#0B0D10', $json['theme_color']);
        $this->assertEquals('#0B0D10', $json['background_color']);
        $this->assertNotEmpty($json['icons']);
        $this->assertNotEmpty($json['shortcuts']);
    }

    public function test_manifest_json_alias_exists_and_matches(): void
    {
        $manifestPath = public_path('manifest.json');
        $this->assertFileExists($manifestPath);

        $json = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($json);
        $this->assertEquals('SAMSARA OPS', $json['short_name']);
    }

    public function test_pwa_icons_and_assets_exist_and_are_valid(): void
    {
        $icons = [
            'assets/icons/icon-192.png',
            'assets/icons/icon-512.png',
            'assets/icons/icon-maskable-512.png',
            'assets/icons/apple-touch-icon.png',
            'assets/icons/icon.svg',
            'favicon.ico',
        ];

        foreach ($icons as $iconRelPath) {
            $fullPath = public_path($iconRelPath);
            $this->assertFileExists($fullPath, "Icon file {$iconRelPath} should exist");
            $this->assertGreaterThan(0, filesize($fullPath), "Icon file {$iconRelPath} should not be empty");
        }
    }

    public function test_service_worker_file_exists_and_contains_core_logic(): void
    {
        $swPath = public_path('sw.js');
        $this->assertFileExists($swPath);

        $content = file_get_contents($swPath);
        $this->assertStringContainsString('STATIC_CACHE_NAME', $content);
        $this->assertStringContainsString('/offline.html', $content);
        $this->assertStringContainsString('handleNavigationRequest', $content);
        $this->assertStringContainsString('handleStaleWhileRevalidate', $content);
    }

    public function test_offline_page_and_routes_render_successfully(): void
    {
        $offlineFile = public_path('offline.html');
        $this->assertFileExists($offlineFile);
        $this->assertStringContainsString('Koneksi Lapangan Terputus', file_get_contents($offlineFile));
        $this->assertStringContainsString('149.250 MHz', file_get_contents($offlineFile));

        $res1 = $this->get('/offline');
        $res1->assertStatus(200);
        $res1->assertSee('SAMSARA OPS');

        $res2 = $this->get('/management/offline');
        $res2->assertStatus(200);
        $res2->assertSee('Koneksi Lapangan Terputus');
    }

    public function test_management_login_page_renders_pwa_meta_tags(): void
    {
        $response = $this->get(route('management.login'));
        $response->assertStatus(200);
        $response->assertSee('manifest.webmanifest');
        $response->assertSee('apple-touch-icon');
        $response->assertSee('pwa.js');
    }

    public function test_management_dashboard_renders_pwa_indicators_and_install_triggers(): void
    {
        $user = User::factory()->create([
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('management.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('pwa-net-badge');
        $response->assertSee('pwa-install-trigger');
        $response->assertSee('manifest.webmanifest');
        $response->assertSee('pwa.js');
    }
}
