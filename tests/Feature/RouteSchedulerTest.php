<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Location;
use App\Models\PickupEvent;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\ScheduledStop;
use App\Services\RouteScheduler;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteSchedulerTest extends TestCase
{
    use RefreshDatabase;

    private RouteScheduler $scheduler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scheduler = new RouteScheduler();
    }

    /**
     * Test scheduling based on route service days.
     */
    public function test_only_schedules_on_matching_service_days(): void
    {
        // 1. Create a Route running on Monday
        $route = Route::create([
            'name' => 'Monday Route',
            'date_of_service' => '2026-06-29',
        ]);

        $customer = Customer::create(['name' => 'Test Customer', 'status' => 'active']);
        
        $location = Location::create([
            'customer_id' => $customer->id,
            'name' => 'Weekly Spot',
            'service_address' => '123 Main St',
            'service_frequency' => 'weekly',
            'status' => 'active',
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'location_id' => $location->id,
            'position' => 1,
            'is_active' => true,
        ]);

        // Run scheduler for a Monday (2026-06-29)
        $monday = Carbon::parse('2026-06-29');
        $count = $this->scheduler->generateStopsForDate($monday);
        
        $this->assertTrue(
            ScheduledStop::where('route_id', $route->id)
                ->where('location_id', $location->id)
                ->whereDate('date', '2026-06-29')
                ->where('status', 'pending')
                ->exists()
        );

        // Clear stops
        ScheduledStop::query()->delete();

        // Run scheduler for a Tuesday (2026-06-30)
        $tuesday = Carbon::parse('2026-06-30');
        $count = $this->scheduler->generateStopsForDate($tuesday);

        $this->assertEquals(0, $count);
        $this->assertFalse(
            ScheduledStop::whereDate('date', '2026-06-30')->exists()
        );
    }

    /**
     * Test biweekly and monthly scheduling constraints.
     */
    public function test_respects_service_frequency_rules(): void
    {
        $route = Route::create([
            'name' => 'Monday Route',
            'date_of_service' => '2026-06-29',
        ]);

        $customer = Customer::create(['name' => 'Test Customer', 'status' => 'active']);

        // Biweekly Location
        $biweeklyLoc = Location::create([
            'customer_id' => $customer->id,
            'name' => 'Biweekly Spot',
            'service_address' => 'Biweekly Rd',
            'service_frequency' => 'biweekly',
            'status' => 'active',
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'location_id' => $biweeklyLoc->id,
            'position' => 1,
        ]);

        // Monthly Location
        $monthlyLoc = Location::create([
            'customer_id' => $customer->id,
            'name' => 'Monthly Spot',
            'service_address' => 'Monthly St',
            'service_frequency' => 'monthly',
            'status' => 'active',
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'location_id' => $monthlyLoc->id,
            'position' => 2,
        ]);

        // On-Call Location
        $onCallLoc = Location::create([
            'customer_id' => $customer->id,
            'name' => 'On Call Spot',
            'service_address' => 'On Call Ave',
            'service_frequency' => 'on_call',
            'status' => 'active',
        ]);

        RouteStop::create([
            'route_id' => $route->id,
            'location_id' => $onCallLoc->id,
            'position' => 3,
        ]);

        $monday1 = Carbon::parse('2026-06-29');

        // Initial run: both biweekly and monthly are scheduled because there are no past pickups. On-call is not.
        $count = $this->scheduler->generateStopsForDate($monday1);
        $this->assertEquals(2, $count);
        $this->assertTrue(
            ScheduledStop::where('location_id', $biweeklyLoc->id)
                ->whereDate('date', '2026-06-29')
                ->exists()
        );
        $this->assertTrue(
            ScheduledStop::where('location_id', $monthlyLoc->id)
                ->whereDate('date', '2026-06-29')
                ->exists()
        );
        $this->assertFalse(
            ScheduledStop::where('location_id', $onCallLoc->id)
                ->whereDate('date', '2026-06-29')
                ->exists()
        );

        // Complete the pickup events for 2026-06-29
        PickupEvent::create([
            'location_id' => $biweeklyLoc->id,
            'route_id' => $route->id,
            'occurred_at' => $monday1->copy()->addHours(10),
            'status' => 'completed',
        ]);
        PickupEvent::create([
            'location_id' => $monthlyLoc->id,
            'route_id' => $route->id,
            'occurred_at' => $monday1->copy()->addHours(11),
            'status' => 'completed',
        ]);

        // Run scheduler for next Monday (7 days later: 2026-07-06). 
        // Neither biweekly (7 days elapsed < 12) nor monthly (7 days elapsed < 26) should be scheduled.
        ScheduledStop::query()->delete();
        $monday2 = Carbon::parse('2026-07-06');
        $route->update(['date_of_service' => '2026-07-06']);
        $count = $this->scheduler->generateStopsForDate($monday2);
        $this->assertEquals(0, $count);

        // Run scheduler for 2nd Monday (14 days later: 2026-07-13).
        $monday3 = Carbon::parse('2026-07-13');
        $route->update(['date_of_service' => '2026-07-13']);
        $count = $this->scheduler->generateStopsForDate($monday3);
        $this->assertEquals(1, $count);
        $this->assertTrue(
            ScheduledStop::where('location_id', $biweeklyLoc->id)
                ->whereDate('date', '2026-07-13')
                ->exists()
        );
        $this->assertFalse(
            ScheduledStop::where('location_id', $monthlyLoc->id)
                ->whereDate('date', '2026-07-13')
                ->exists()
        );

        // Log completed pickup for biweekly on 2026-07-13
        PickupEvent::create([
            'location_id' => $biweeklyLoc->id,
            'route_id' => $route->id,
            'occurred_at' => $monday3->copy()->addHours(10),
            'status' => 'completed',
        ]);

        // Run scheduler for 4th Monday (28 days later since original: 2026-07-27).
        // Biweekly (14 days elapsed since last pickup on 2026-07-13) and Monthly (28 days elapsed since last pickup on 2026-06-29) should both be scheduled.
        ScheduledStop::query()->delete();
        $monday5 = Carbon::parse('2026-07-27');
        $route->update(['date_of_service' => '2026-07-27']);
        $count = $this->scheduler->generateStopsForDate($monday5);
        $this->assertEquals(2, $count);
        $this->assertTrue(
            ScheduledStop::where('location_id', $biweeklyLoc->id)
                ->whereDate('date', '2026-07-27')
                ->exists()
        );
        $this->assertTrue(
            ScheduledStop::where('location_id', $monthlyLoc->id)
                ->whereDate('date', '2026-07-27')
                ->exists()
        );
    }

    /**
     * Test scheduler ignores inactive, paused, or cancelled locations/customers.
     */
    public function test_ignores_inactive_paused_or_cancelled_status(): void
    {
        $route = Route::create([
            'name' => 'Monday Route',
            'date_of_service' => '2026-06-29',
        ]);

        $activeCust = Customer::create(['name' => 'Active Customer', 'status' => 'active']);
        $cancelledCust = Customer::create(['name' => 'Cancelled Customer', 'status' => 'cancelled']);

        // Paused location under active customer
        $pausedLoc = Location::create([
            'customer_id' => $activeCust->id,
            'name' => 'Paused Spot',
            'service_address' => 'Paused Rd',
            'service_frequency' => 'weekly',
            'status' => 'paused',
        ]);

        // Active location under cancelled customer
        $activeLocCancelledCust = Location::create([
            'customer_id' => $cancelledCust->id,
            'name' => 'Cancelled Spot',
            'service_address' => 'Cancelled St',
            'service_frequency' => 'weekly',
            'status' => 'active',
        ]);

        RouteStop::create(['route_id' => $route->id, 'location_id' => $pausedLoc->id, 'position' => 1]);
        RouteStop::create(['route_id' => $route->id, 'location_id' => $activeLocCancelledCust->id, 'position' => 2]);

        $monday = Carbon::parse('2026-06-29');
        $count = $this->scheduler->generateStopsForDate($monday);

        // Neither should be scheduled
        $this->assertEquals(0, $count);
    }

    /**
     * Test idempotency: running twice does not double-create pending stops.
     */
    public function test_generation_is_idempotent(): void
    {
        $route = Route::create([
            'name' => 'Monday Route',
            'date_of_service' => '2026-06-29',
        ]);

        $customer = Customer::create(['name' => 'Active Customer', 'status' => 'active']);
        $location = Location::create([
            'customer_id' => $customer->id,
            'name' => 'Weekly Spot',
            'service_address' => 'Weekly St',
            'service_frequency' => 'weekly',
            'status' => 'active',
        ]);

        RouteStop::create(['route_id' => $route->id, 'location_id' => $location->id, 'position' => 1]);

        $monday = Carbon::parse('2026-06-29');

        // Run 1
        $count1 = $this->scheduler->generateStopsForDate($monday);
        $this->assertEquals(1, $count1);
        $this->assertEquals(1, ScheduledStop::count());

        // Run 2
        $count2 = $this->scheduler->generateStopsForDate($monday);
        // Returns number of generated (which is 1 again, since it deletes existing pending and re-creates)
        $this->assertEquals(1, $count2);
        // Total stops in DB should still be 1, not 2
        $this->assertEquals(1, ScheduledStop::count());
    }
}
