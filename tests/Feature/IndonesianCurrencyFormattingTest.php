<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\InventoryItem;
use App\Models\User;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class IndonesianCurrencyFormattingTest extends TestCase
{
    use RefreshDatabase;

    public function test_money_helper_formats_rupiah_correctly(): void
    {
        $this->assertSame('Rp 25.000', Money::format(25000));
        $this->assertSame('Rp 1.500.000', Money::format(1500000));
        $this->assertSame('Rp 0', Money::format(0));
        $this->assertSame('Rp 0', Money::format(null));
        $this->assertSame('Rp 0', Money::format(''));
        $this->assertSame('-Rp 15.000', Money::format(-15000));

        $this->assertSame('25.000', Money::formatNumber(25000));
        $this->assertSame('1.500.000', Money::formatNumber(1500000));
        $this->assertSame('0', Money::formatNumber(0));
    }

    public function test_money_helper_parses_formatted_strings(): void
    {
        $this->assertSame(25000.0, Money::parse('Rp 25.000'));
        $this->assertSame(1500000.0, Money::parse('1.500.000'));
        $this->assertSame(0.0, Money::parse('0'));
        $this->assertSame(0.0, Money::parse(''));
        $this->assertSame(-50000.0, Money::parse('-Rp 50.000'));
    }

    public function test_money_helper_sanitizes_currency_strings_safely(): void
    {
        // Thousand dot formatted strings should be converted to numeric
        $this->assertSame(25000.0, Money::sanitize('25.000'));
        $this->assertSame(1500000.0, Money::sanitize('Rp 1.500.000'));
        $this->assertSame(50000.0, Money::sanitize('Rp 50000'));

        // Non-currency strings should be untouched
        $this->assertSame('081234567890', Money::sanitize('081234567890'));
        $this->assertSame('149.250 MHz', Money::sanitize('149.250 MHz'));
        $this->assertSame('Toko Barokah', Money::sanitize('Toko Barokah'));
    }

    public function test_global_helpers_and_blade_directives(): void
    {
        $this->assertSame('Rp 25.000', rupiah(25000));
        $this->assertSame('25.000', format_nominal(25000));
        $this->assertSame(25000.0, parse_rupiah('Rp 25.000'));

        $renderedRupiah = Blade::render('@rupiah($val)', ['val' => 50000]);
        $this->assertSame('Rp 50.000', $renderedRupiah);

        $renderedNominal = Blade::render('@nominal($val)', ['val' => 50000]);
        $this->assertSame('50.000', $renderedNominal);
    }

    public function test_middleware_sanitizes_formatted_tariffs_input(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);

        $response = $this->actingAs($admin)->post(route('management.settings.tariffs.update'), [
            'simaksi_fee_per_person' => '35.000',
            'ojek_fee_per_trip' => '40.000',
            'ojek_basecamp_share' => '5.000',
            'parking_motor_fee' => '15.000',
            'parking_car_fee' => '30.000',
            'trash_deposit_enabled' => '1',
            'trash_deposit_amount' => '60.000',
        ]);

        $response->assertSessionHas('success');

        $tariffs = AppSetting::getTariffs();
        $this->assertEquals(35000, $tariffs['simaksi_fee_per_person']);
        $this->assertEquals(40000, $tariffs['ojek_fee_per_trip']);
        $this->assertEquals(5000, $tariffs['ojek_basecamp_share']);
        $this->assertEquals(15000, $tariffs['parking_motor_fee']);
        $this->assertEquals(30000, $tariffs['parking_car_fee']);
        $this->assertEquals(60000, $tariffs['trash_deposit_amount']);
    }

    public function test_middleware_sanitizes_formatted_expense_amount(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);

        $response = $this->actingAs($cashier)->post(route('management.pos.expenses.store'), [
            'category' => 'bbm_genset',
            'amount' => '75.000',
            'recipient_name' => 'SPBU Simpang',
            'payment_method' => 'cash',
            'notes' => 'BBM genset malam hari',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('operational_expenses', [
            'recipient_name' => 'SPBU Simpang',
            'amount' => 75000,
        ]);
    }

    public function test_pos_checkout_accepts_clean_tendered_cash(): void
    {
        $cashier = User::factory()->create(['role' => 'kasir']);
        $item = InventoryItem::create([
            'sku' => 'TEST-01',
            'name' => 'Gas Hi-Cook',
            'category' => 'konsumsi',
            'type' => 'goods',
            'price' => 25000,
            'stock_available' => 10,
            'stock_total' => 10,
            'unit' => 'kaleng',
            'is_active' => true,
        ]);

        $response = $this->actingAs($cashier)->postJson(route('management.pos.store'), [
            'customer_name' => 'Budi Santoso',
            'items' => [
                ['inventory_item_id' => $item->id, 'quantity' => 2],
            ],
            'payment_method' => 'cash',
            'cash_tendered' => 60000,
        ]);

        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('pos_orders', [
            'customer_name' => 'Budi Santoso',
            'total_amount' => 50000,
            'cash_tendered' => 60000,
            'cash_change' => 10000,
        ]);
    }

    public function test_views_contain_data_currency_input_attributes(): void
    {
        $admin = User::factory()->create(['role' => 'superadmin']);

        // Tariffs page
        $resTariffs = $this->actingAs($admin)->get(route('management.settings.tariffs'));
        $resTariffs->assertStatus(200);
        $resTariffs->assertSee('data-currency-input');
        $resTariffs->assertSee('currency-input.js');

        // POS index
        $resPos = $this->actingAs($admin)->get(route('management.pos.index'));
        $resPos->assertStatus(200);
        $resPos->assertSee('data-currency-input');

        // POS expenses
        $resExpenses = $this->actingAs($admin)->get(route('management.pos.expenses.index'));
        $resExpenses->assertStatus(200);
        $resExpenses->assertSee('data-currency-input');

        // Inventory
        $resInventory = $this->actingAs($admin)->get(route('management.inventory.index'));
        $resInventory->assertStatus(200);
        $resInventory->assertSee('data-currency-input');
    }
}
