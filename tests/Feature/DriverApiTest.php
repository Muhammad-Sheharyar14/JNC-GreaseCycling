<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Location;
use App\Models\PickupEvent;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\RouteRun;
use App\Models\ScheduledStop;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverApiTest extends TestCase
{
    use RefreshDatabase;

    private User $driverUser;
    private Route $route;
    private Location $location;
    private ScheduledStop $scheduledStop;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles & permissions
        $this->seed(RolesAndPermissionsSeeder::class);

        // Get the driver seeded user
        $this->driverUser = User::whereEmail('driver@greasecycling.com')->first();

        // Create route and stops
        $this->route = Route::create([
            'name' => 'Monday Test Route',
            'service_days' => ['Monday'],
            'assigned_driver_id' => $this->driverUser->id,
        ]);

        $customer = Customer::create(['name' => 'Pizza House', 'status' => 'active']);
        $this->location = Location::create([
            'customer_id' => $customer->id,
            'name' => 'North Pizza',
            'service_address' => '789 Pepperoni Lane',
            'service_frequency' => 'weekly',
            'status' => 'active',
        ]);

        RouteStop::create([
            'route_id' => $this->route->id,
            'location_id' => $this->location->id,
            'position' => 1,
        ]);

        $this->scheduledStop = ScheduledStop::create([
            'route_id' => $this->route->id,
            'location_id' => $this->location->id,
            'date' => now()->toDateString(),
            'position' => 1,
            'status' => 'pending',
        ]);
    }

    /**
     * Test login returns token for active drivers, blocks other roles.
     */
    public function test_login_authenticates_driver_and_returns_token(): void
    {
        // 1. Success login
        $response = $this->postJson('/api/login', [
            'email' => 'driver@greasecycling.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);

        // 2. Failure: Invalid credentials
        $response = $this->postJson('/api/login', [
            'email' => 'driver@greasecycling.com',
            'password' => 'wrongpassword',
        ]);
        $response->assertStatus(422);

        // 3. Failure: Access denied for non-driver role
        $response = $this->postJson('/api/login', [
            'email' => 'admin@greasecycling.com',
            'password' => 'password',
        ]);
        $response->assertStatus(403)
            ->assertJsonPath('message', 'Access denied. Driver role required.');
    }

    /**
     * Test driver can access protected routes when authenticated.
     */
    public function test_unauthenticated_requests_are_blocked(): void
    {
        $this->getJson('/api/driver/route')->assertStatus(401);
        $this->postJson('/api/driver/route/start', ['route_id' => $this->route->id])->assertStatus(401);
        $this->getJson("/api/driver/stops/{$this->scheduledStop->id}")->assertStatus(401);
    }

    /**
     * Test retrieving today's stops for the authenticated driver.
     */
    public function test_driver_can_list_todays_route_and_stops(): void
    {
        $response = $this->actingAs($this->driverUser, 'sanctum')
            ->getJson('/api/driver/route');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'stops')
            ->assertJsonPath('stops.0.location.name', 'North Pizza');
    }

    /**
     * Test starting the route run explicitly.
     */
    public function test_driver_can_start_route_run(): void
    {
        $response = $this->actingAs($this->driverUser, 'sanctum')
            ->postJson('/api/driver/route/start', ['route_id' => $this->route->id]);

        $response->assertStatus(200)
            ->assertJsonPath('route_run.status', 'in_progress');
        
        $this->assertNotNull($response->json('route_run.started_at'));

        $this->assertDatabaseHas('route_runs', [
            'route_id' => $this->route->id,
            'status' => 'in_progress',
        ]);
    }

    /**
     * Test logging completed pickup.
     */
    public function test_driver_can_log_completed_pickup(): void
    {
        $response = $this->actingAs($this->driverUser, 'sanctum')
            ->postJson("/api/driver/stops/{$this->scheduledStop->id}/pickup", [
                'status' => 'completed',
                'pounds_collected' => 125.50,
                'notes' => 'Tanks in good shape',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('pickup_event.pounds_collected', "125.50")
            ->assertJsonPath('pickup_event.status', 'completed')
            ->assertJsonPath('warning', null);

        // Verify database updates
        $this->assertDatabaseHas('pickup_events', [
            'location_id' => $this->location->id,
            'pounds_collected' => 125.50,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('scheduled_stops', [
            'id' => $this->scheduledStop->id,
            'status' => 'completed',
        ]);

        // Verify route run was automatically started
        $this->assertDatabaseHas('route_runs', [
            'route_id' => $this->route->id,
            'status' => 'in_progress',
        ]);
    }

    /**
     * Test logging skipped stop.
     */
    public function test_driver_can_log_skipped_stop(): void
    {
        $response = $this->actingAs($this->driverUser, 'sanctum')
            ->postJson("/api/driver/stops/{$this->scheduledStop->id}/pickup", [
                'status' => 'skipped',
                'skip_reason' => 'no_access',
                'notes' => 'Gate locked',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('pickup_event.status', 'skipped')
            ->assertJsonPath('pickup_event.skip_reason', 'no_access');

        $this->assertDatabaseHas('pickup_events', [
            'location_id' => $this->location->id,
            'status' => 'skipped',
            'skip_reason' => 'no_access',
        ]);
    }

    /**
     * Test duplicate logging returns warning.
     */
    public function test_duplicate_pickup_logs_warning(): void
    {
        // 1st completed pickup log
        $this->actingAs($this->driverUser, 'sanctum')
            ->postJson("/api/driver/stops/{$this->scheduledStop->id}/pickup", [
                'status' => 'completed',
                'pounds_collected' => 100,
            ]);

        // 2nd completed pickup log
        $response = $this->actingAs($this->driverUser, 'sanctum')
            ->postJson("/api/driver/stops/{$this->scheduledStop->id}/pickup", [
                'status' => 'completed',
                'pounds_collected' => 80,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('warning', 'A pickup has already been logged for this location today.');
    }

    /**
     * Test completing the route run.
     */
    public function test_driver_can_complete_route_run(): void
    {
        // Start first
        RouteRun::create([
            'route_id' => $this->route->id,
            'driver_id' => $this->driverUser->id,
            'date' => now()->toDateString(),
            'started_at' => now(),
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($this->driverUser, 'sanctum')
            ->postJson('/api/driver/route/complete', ['route_id' => $this->route->id]);

        $response->assertStatus(200)
            ->assertJsonPath('route_run.status', 'completed');
            
        $this->assertNotNull($response->json('route_run.ended_at'));

        $this->assertDatabaseHas('route_runs', [
            'route_id' => $this->route->id,
            'status' => 'completed',
        ]);
    }
}
