<?php

namespace Tests\Feature;

use App\Filament\Pages\DispatchDashboard;
use App\Models\Customer;
use App\Models\Location;
use App\Models\PickupEvent;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\ScheduledStop;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DispatchDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $dispatcherUser;
    private User $driverUser;
    private User $accountingUser;

    private Route $route1;
    private Route $route2;
    private Location $location;
    private ScheduledStop $scheduledStop;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->adminUser = User::whereEmail('admin@greasecycling.com')->first();
        $this->dispatcherUser = User::whereEmail('dispatcher@greasecycling.com')->first();
        $this->driverUser = User::whereEmail('driver@greasecycling.com')->first();
        $this->accountingUser = User::whereEmail('accounting@greasecycling.com')->first();

        // Create routes (running on Monday)
        $this->route1 = Route::create([
            'name' => 'Monday Route 1',
            'date_of_service' => '2026-06-29',
            'assigned_driver_id' => $this->driverUser->id,
        ]);

        $this->route2 = Route::create([
            'name' => 'Monday Route 2',
            'date_of_service' => '2026-06-29',
            'assigned_driver_id' => $this->driverUser->id,
        ]);

        $customer = Customer::create(['name' => 'Pizza Spot', 'status' => 'active']);
        $this->location = Location::create([
            'customer_id' => $customer->id,
            'name' => 'Downtown Pizza',
            'service_address' => '456 Crust Rd',
            'service_frequency' => 'weekly',
            'status' => 'active',
        ]);

        RouteStop::create([
            'route_id' => $this->route1->id,
            'location_id' => $this->location->id,
            'position' => 1,
        ]);

        $this->scheduledStop = ScheduledStop::create([
            'route_id' => $this->route1->id,
            'location_id' => $this->location->id,
            'date' => '2026-06-29', // Monday
            'position' => 1,
            'status' => 'pending',
        ]);

        // Clear Spatie permissions cache to prevent cross-request testing bugs
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Test Admin can access dashboard.
     */
    public function test_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->adminUser)->get(DispatchDashboard::getUrl())->assertSuccessful();
    }

    /**
     * Test Dispatcher can access dashboard.
     */
    public function test_dispatcher_can_access_dashboard(): void
    {
        $this->actingAs($this->dispatcherUser)->get(DispatchDashboard::getUrl())->assertSuccessful();
    }

    /**
     * Test Driver is blocked from dashboard.
     */
    public function test_driver_cannot_access_dashboard(): void
    {
        $this->actingAs($this->driverUser)->get(DispatchDashboard::getUrl())->assertForbidden();
    }

    /**
     * Test Accounting is blocked from dashboard.
     */
    public function test_accounting_cannot_access_dashboard(): void
    {
        $this->actingAs($this->accountingUser)->get(DispatchDashboard::getUrl())->assertForbidden();
    }

    /**
     * Test adding an emergency stop.
     */
    public function test_can_add_emergency_stop(): void
    {
        // We have 1 scheduled stop originally
        $this->assertEquals(1, ScheduledStop::count());

        Livewire::actingAs($this->adminUser)
            ->test(DispatchDashboard::class)
            ->set('date', '2026-06-29')
            ->set('selectedRouteId', $this->route1->id)
            ->callAction('addEmergencyStop', [
                'location_id' => $this->location->id,
            ])
            ->assertHasNoActionErrors();

        // Total scheduled stops is now 2
        $this->assertEquals(2, ScheduledStop::count());
        $this->assertDatabaseHas('scheduled_stops', [
            'route_id' => $this->route1->id,
            'location_id' => $this->location->id,
            'position' => 2,
            'status' => 'pending',
        ]);
    }

    /**
     * Test reassigning a stop.
     */
    public function test_can_reassign_stop_to_another_route(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(DispatchDashboard::class)
            ->set('date', '2026-06-29')
            ->set('selectedRouteId', $this->route1->id)
            ->callAction('reassignStop', [
                'route_id' => $this->route2->id,
            ], [
                'stopId' => $this->scheduledStop->id,
            ])
            ->assertHasNoActionErrors();

        // Verify stop was reassigned to route 2
        $this->assertDatabaseHas('scheduled_stops', [
            'id' => $this->scheduledStop->id,
            'route_id' => $this->route2->id,
            'position' => 1,
        ]);
    }

    /**
     * Test deleting a scheduled stop.
     */
    public function test_can_delete_scheduled_stop(): void
    {
        $this->assertEquals(1, ScheduledStop::count());

        Livewire::actingAs($this->adminUser)
            ->test(DispatchDashboard::class)
            ->set('date', '2026-06-29')
            ->set('selectedRouteId', $this->route1->id)
            ->callAction('deleteStop', [], [
                'stopId' => $this->scheduledStop->id,
            ])
            ->assertHasNoActionErrors();

        $this->assertEquals(0, ScheduledStop::count());
    }

    /**
     * Test manually marking a stop as complete.
     */
    public function test_can_manually_mark_stop_complete(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(DispatchDashboard::class)
            ->set('date', '2026-06-29')
            ->set('selectedRouteId', $this->route1->id)
            ->callAction('markCompleteManually', [
                'pounds_collected' => 180.50,
                'notes' => 'Logged by Admin',
            ], [
                'stopId' => $this->scheduledStop->id,
            ])
            ->assertHasNoActionErrors();

        // Verify scheduled stop status updated
        $this->assertDatabaseHas('scheduled_stops', [
            'id' => $this->scheduledStop->id,
            'status' => 'completed',
        ]);

        // Verify completed pickup event created
        $this->assertDatabaseHas('pickup_events', [
            'location_id' => $this->location->id,
            'route_id' => $this->route1->id,
            'pounds_collected' => 180.50,
            'status' => 'completed',
            'notes' => 'Logged by Admin',
        ]);
    }
}
