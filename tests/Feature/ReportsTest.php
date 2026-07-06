<?php

namespace Tests\Feature;

use App\Filament\Pages\Reports;
use App\Filament\Resources\Locations\Widgets\LocationStatsWidget;
use App\Models\Customer;
use App\Models\Location;
use App\Models\PickupEvent;
use App\Models\Route;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $dispatcherUser;
    private User $driverUser;
    private User $accountingUser;

    private Location $location;
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

        // Create route
        $this->route = Route::create([
            'name' => 'Route A',
            'service_days' => ['Monday'],
            'assigned_driver_id' => $this->driverUser->id,
        ]);

        // Create location
        $customer = Customer::create(['name' => 'Steakhouse', 'status' => 'active']);
        $this->location = Location::create([
            'customer_id' => $customer->id,
            'name' => 'Main Steakhouse',
            'service_address' => '789 Meat St',
            'service_frequency' => 'weekly',
            'status' => 'active',
        ]);

        // Clear Spatie permissions cache to prevent cross-request testing bugs
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Test page access control by role.
     */
    public function test_admin_can_access_reports_page(): void
    {
        $this->actingAs($this->adminUser)->get(Reports::getUrl())->assertSuccessful();
    }

    public function test_dispatcher_can_access_reports_page(): void
    {
        $this->actingAs($this->dispatcherUser)->get(Reports::getUrl())->assertSuccessful();
    }

    public function test_accounting_can_access_reports_page(): void
    {
        $this->actingAs($this->accountingUser)->get(Reports::getUrl())->assertSuccessful();
    }

    public function test_driver_cannot_access_reports_page(): void
    {
        $this->actingAs($this->driverUser)->get(Reports::getUrl())->assertForbidden();
    }

    /**
     * Test reports state properties and calculations.
     */
    public function test_reports_computes_correct_summaries(): void
    {
        // Log a completed pickup
        PickupEvent::create([
            'location_id' => $this->location->id,
            'route_id' => $this->route->id,
            'driver_id' => $this->driverUser->id,
            'occurred_at' => now(),
            'pounds_collected' => 200.00,
            'status' => 'completed',
        ]);

        // Log a skipped stop
        PickupEvent::create([
            'location_id' => $this->location->id,
            'route_id' => $this->route->id,
            'driver_id' => $this->driverUser->id,
            'occurred_at' => now(),
            'skip_reason' => 'closed',
            'status' => 'skipped',
        ]);

        $testable = Livewire::actingAs($this->adminUser)
            ->test(Reports::class)
            ->assertSet('startDate', now()->subDays(30)->toDateString())
            ->assertSet('endDate', now()->toDateString());

        $summary = $testable->instance()->summary;
        $this->assertEquals(200.00, $summary->total_pounds);
        $this->assertEquals(1, $summary->completed_count);
        $this->assertEquals(1, $summary->skipped_count);
        $this->assertEquals(200.00, $summary->avg_pounds);
    }

    /**
     * Test CSV export stream download.
     */
    public function test_reports_can_export_csv(): void
    {
        // Log a pickup event
        PickupEvent::create([
            'location_id' => $this->location->id,
            'route_id' => $this->route->id,
            'driver_id' => $this->driverUser->id,
            'occurred_at' => now(),
            'pounds_collected' => 150.00,
            'status' => 'completed',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(Reports::class)
            ->call('exportCsv', 'drivers')
            ->assertFileDownloaded();
    }

    /**
     * Test LocationStatsWidget calculation logic.
     */
    public function test_location_stats_widget_computes_correctly(): void
    {
        // Log multiple pickups for the location
        PickupEvent::create([
            'location_id' => $this->location->id,
            'route_id' => $this->route->id,
            'driver_id' => $this->driverUser->id,
            'occurred_at' => now()->subDays(5),
            'pounds_collected' => 100.00,
            'status' => 'completed',
        ]);

        PickupEvent::create([
            'location_id' => $this->location->id,
            'route_id' => $this->route->id,
            'driver_id' => $this->driverUser->id,
            'occurred_at' => now()->subDays(3),
            'pounds_collected' => 200.00,
            'status' => 'completed',
        ]);

        $widget = Livewire::test(LocationStatsWidget::class, ['record' => $this->location]);
        $stats = $widget->instance()->getStats();

        // Average should be 150.00
        $avgStat = $stats[0];
        $this->assertEquals('150.00 lbs', $avgStat->getValue());

        // Time since last service should be 3 days
        $timeStat = $stats[1];
        $this->assertEquals('3 days', $timeStat->getValue());

        // Total pickups count should be 2
        $countStat = $stats[2];
        $this->assertEquals(2, $countStat->getValue());
    }
}
