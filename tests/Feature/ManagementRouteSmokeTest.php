<?php

namespace Tests\Feature;

use App\Models\TrailCondition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementRouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected User $admin;

    protected User $kasir;

    protected User $gateOfficer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superadmin = User::create([
            'name' => 'Superadmin SAMSARA',
            'email' => 'superadmin@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
        ]);

        $this->admin = User::create([
            'name' => 'Admin SAMSARA',
            'email' => 'admin@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->kasir = User::create([
            'name' => 'Kasir SAMSARA',
            'email' => 'kasir@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'kasir',
        ]);

        $this->gateOfficer = User::create([
            'name' => 'Petugas Gate',
            'email' => 'gate@samsara.id',
            'password' => bcrypt('password'),
            'role' => 'gate_officer',
        ]);

        TrailCondition::create([
            'status' => 'open',
            'pos2_water_status' => 'Melimpah (12 L/mnt)',
            'night_temp_celsius' => '9°C',
            'weather_condition' => 'Cerah Berawan',
        ]);
    }

    public function test_all_management_views_render_successfully(): void
    {
        // 1. Dashboard
        $response = $this->actingAs($this->admin)->get(route('management.dashboard'));
        $response->assertStatus(200);

        // 2. SIMAKSI List
        $response = $this->actingAs($this->admin)->get(route('management.simaksi.index'));
        $response->assertStatus(200);

        // 3. Quota Management
        $response = $this->actingAs($this->admin)->get(route('management.quota.index'));
        $response->assertStatus(200);

        // 4. POS System
        $response = $this->actingAs($this->kasir)->get(route('management.pos.index'));
        $response->assertStatus(200);

        // 5. POS History
        $response = $this->actingAs($this->kasir)->get(route('management.pos.history'));
        $response->assertStatus(200);

        // 6. Gate Check-in
        $response = $this->actingAs($this->gateOfficer)->get(route('management.gate.index'));
        $response->assertStatus(200);

        // 7. Ojek Dispatch & List
        $response = $this->actingAs($this->admin)->get(route('management.ojek.index'));
        $response->assertStatus(200);

        // 8. Ojek Drivers
        $response = $this->actingAs($this->admin)->get(route('management.ojek.drivers'));
        $response->assertStatus(200);

        // 9. Ojek Settlements
        $response = $this->actingAs($this->admin)->get(route('management.ojek.settlements'));
        $response->assertStatus(200);

        // 10. Inventory & Rental
        $response = $this->actingAs($this->admin)->get(route('management.inventory.index'));
        $response->assertStatus(200);

        // 11. Audit Logs
        $response = $this->actingAs($this->superadmin)->get(route('management.audit.index'));
        $response->assertStatus(200);

        // 12. Settings Tariffs
        $response = $this->actingAs($this->superadmin)->get(route('management.settings.tariffs'));
        $response->assertStatus(200);

        // 13. Operational Expenses
        $response = $this->actingAs($this->kasir)->get(route('management.pos.expenses.index'));
        $response->assertStatus(200);

        // 14. Financial Reports & Analytics
        $response = $this->actingAs($this->admin)->get(route('management.reports.index'));
        $response->assertStatus(200);

        // 15. Printable Financial Report
        $response = $this->actingAs($this->admin)->get(route('management.reports.print'));
        $response->assertStatus(200);
    }
}
