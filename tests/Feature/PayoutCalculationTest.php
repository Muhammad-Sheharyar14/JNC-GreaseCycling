<?php

namespace Tests\Feature;

use App\Filament\Resources\PayoutRecords\PayoutRecordResource;
use App\Models\Customer;
use App\Models\Location;
use App\Models\PayoutRecord;
use App\Models\PickupEvent;
use App\Models\Route;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PayoutCalculationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $dispatcherUser;
    private User $driverUser;
    private User $accountingUser;

    private Customer $customer;
    private Location $locationA;
    private Location $locationB;
    private Route $route;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->adminUser = User::whereEmail('admin@greasecycling.com')->first();
        $this->dispatcherUser = User::whereEmail('dispatcher@greasecycling.com')->first();
        $this->driverUser = User::whereEmail('driver@greasecycling.com')->first();
        $this->accountingUser = User::whereEmail('accounting@greasecycling.com')->first();

        // Create route & customer
        $this->route = Route::create([
            'name' => 'Route A',
            'service_days' => ['Monday'],
            'assigned_driver_id' => $this->driverUser->id,
        ]);

        $this->customer = Customer::create(['name' => 'Pizza Palace', 'status' => 'active']);

        // Create two locations with different rates for the customer
        $this->locationA = Location::create([
            'customer_id' => $this->customer->id,
            'name' => 'Palace North',
            'service_address' => '123 North St',
            'service_frequency' => 'weekly',
            'reimbursement_rate' => 0.10, // $0.10 / lb
            'status' => 'active',
        ]);

        $this->locationB = Location::create([
            'customer_id' => $this->customer->id,
            'name' => 'Palace South',
            'service_address' => '456 South St',
            'service_frequency' => 'weekly',
            'reimbursement_rate' => 0.05, // $0.05 / lb
            'status' => 'active',
        ]);

        // Clear Spatie permissions cache to prevent cross-request testing bugs
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Test page access control by role.
     */
    public function test_admin_can_access_payouts(): void
    {
        $this->actingAs($this->adminUser)->get(PayoutRecordResource::getUrl())->assertSuccessful();
    }

    public function test_accounting_can_access_payouts(): void
    {
        $this->actingAs($this->accountingUser)->get(PayoutRecordResource::getUrl())->assertSuccessful();
    }

    public function test_dispatcher_and_driver_cannot_access_payouts(): void
    {
        $this->actingAs($this->dispatcherUser)->get(PayoutRecordResource::getUrl())->assertForbidden();
        $this->actingAs($this->driverUser)->get(PayoutRecordResource::getUrl())->assertForbidden();
    }

    /**
     * Test generating payouts using default rates.
     */
    public function test_generating_payouts_uses_default_location_rates(): void
    {
        // 1. Log completed pickups
        // Location A: 100 lbs @ 0.10 = $10.00
        PickupEvent::create([
            'location_id' => $this->locationA->id,
            'route_id' => $this->route->id,
            'driver_id' => $this->driverUser->id,
            'occurred_at' => now()->startOfMonth(),
            'pounds_collected' => 100.00,
            'status' => 'completed',
        ]);

        // Location B: 200 lbs @ 0.05 = $10.00
        PickupEvent::create([
            'location_id' => $this->locationB->id,
            'route_id' => $this->route->id,
            'driver_id' => $this->driverUser->id,
            'occurred_at' => now()->startOfMonth()->addDays(2),
            'pounds_collected' => 200.00,
            'status' => 'completed',
        ]);

        // Total should be: 300 lbs, Owed = $20.00, Average rate = $20 / 300 = $0.0667
        Livewire::actingAs($this->adminUser)
            ->test(\App\Filament\Resources\PayoutRecords\Pages\ListPayoutRecords::class)
            ->callTableAction('generatePayouts', null, [
                'customer_id' => null, // all customers
                'date_range_start' => now()->startOfMonth()->toDateString(),
                'date_range_end' => now()->endOfMonth()->toDateString(),
                'override_rate' => null, // default location rates
            ]);

        $this->assertDatabaseHas('payout_records', [
            'customer_id' => $this->customer->id,
            'total_pounds' => 300.00,
            'reimbursement_rate' => 0.07, // 20 / 300 is rounded to 0.07 in DB decimal columns depending on formatting, or saved as 0.07
            'total_amount_owed' => 20.00,
            'is_paid' => false,
        ]);
    }

    /**
     * Test generating payouts using dynamic override rate.
     */
    public function test_generating_payouts_uses_override_rate(): void
    {
        // 1. Log completed pickups
        // Location A: 100 lbs
        PickupEvent::create([
            'location_id' => $this->locationA->id,
            'route_id' => $this->route->id,
            'driver_id' => $this->driverUser->id,
            'occurred_at' => now()->startOfMonth(),
            'pounds_collected' => 100.00,
            'status' => 'completed',
        ]);

        // Total should be: 100 lbs @ $0.15 override rate = $15.00
        Livewire::actingAs($this->adminUser)
            ->test(\App\Filament\Resources\PayoutRecords\Pages\ListPayoutRecords::class)
            ->callTableAction('generatePayouts', null, [
                'customer_id' => $this->customer->id,
                'date_range_start' => now()->startOfMonth()->toDateString(),
                'date_range_end' => now()->endOfMonth()->toDateString(),
                'override_rate' => 0.15,
            ]);

        $this->assertDatabaseHas('payout_records', [
            'customer_id' => $this->customer->id,
            'total_pounds' => 100.00,
            'reimbursement_rate' => 0.15,
            'total_amount_owed' => 15.00,
            'is_paid' => false,
        ]);
    }

    /**
     * Test generation deletes existing unpaid drafts in same range.
     */
    public function test_generating_payouts_deletes_previous_unpaid_drafts(): void
    {
        // Create an existing unpaid payout record
        $oldPayout = PayoutRecord::create([
            'customer_id' => $this->customer->id,
            'date_range_start' => now()->startOfMonth()->toDateString(),
            'date_range_end' => now()->endOfMonth()->toDateString(),
            'total_pounds' => 500.00,
            'reimbursement_rate' => 0.10,
            'total_amount_owed' => 50.00,
            'is_paid' => false,
        ]);

        // Log completed pickup
        PickupEvent::create([
            'location_id' => $this->locationA->id,
            'route_id' => $this->route->id,
            'driver_id' => $this->driverUser->id,
            'occurred_at' => now()->startOfMonth(),
            'pounds_collected' => 100.00,
            'status' => 'completed',
        ]);

        // Regenerate should replace the old payout record
        Livewire::actingAs($this->adminUser)
            ->test(\App\Filament\Resources\PayoutRecords\Pages\ListPayoutRecords::class)
            ->callTableAction('generatePayouts', null, [
                'customer_id' => $this->customer->id,
                'date_range_start' => now()->startOfMonth()->toDateString(),
                'date_range_end' => now()->endOfMonth()->toDateString(),
                'override_rate' => 0.20,
            ]);

        $this->assertDatabaseMissing('payout_records', [
            'id' => $oldPayout->id,
        ]);

        $this->assertDatabaseHas('payout_records', [
            'customer_id' => $this->customer->id,
            'total_pounds' => 100.00,
            'reimbursement_rate' => 0.20,
            'total_amount_owed' => 20.00,
        ]);
    }

    /**
     * Test marking payout as paid.
     */
    public function test_marking_payout_as_paid(): void
    {
        $payout = PayoutRecord::create([
            'customer_id' => $this->customer->id,
            'date_range_start' => now()->startOfMonth()->toDateString(),
            'date_range_end' => now()->endOfMonth()->toDateString(),
            'total_pounds' => 100.00,
            'reimbursement_rate' => 0.10,
            'total_amount_owed' => 10.00,
            'is_paid' => false,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(\App\Filament\Resources\PayoutRecords\Pages\ListPayoutRecords::class)
            ->callTableAction('markAsPaid', $payout, [
                'paid_at' => now()->toDateString(),
                'payment_method' => 'ach',
                'notes' => 'Settled via banking portal',
            ]);

        $payout->refresh();
        $this->assertTrue($payout->is_paid);
        $this->assertEquals('ach', $payout->payment_method);
        $this->assertEquals('Settled via banking portal', $payout->notes);
    }

    /**
     * Test CSV export stream.
     */
    public function test_payouts_can_export_csv(): void
    {
        $payout = PayoutRecord::create([
            'customer_id' => $this->customer->id,
            'date_range_start' => now()->startOfMonth()->toDateString(),
            'date_range_end' => now()->endOfMonth()->toDateString(),
            'total_pounds' => 100.00,
            'reimbursement_rate' => 0.10,
            'total_amount_owed' => 10.00,
            'is_paid' => false,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(\App\Filament\Resources\PayoutRecords\Pages\ListPayoutRecords::class)
            ->callTableAction('exportCsv', $payout)
            ->assertFileDownloaded();
    }
}
