<?php

namespace Tests\Feature;

use App\Models\Container;
use App\Models\Customer;
use App\Models\Location;
use App\Models\PayoutRecord;
use App\Models\PickupEvent;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that we can create models and that relationships are wired correctly.
     */
    public function test_can_create_entities_and_traverse_relationships(): void
    {
        // 1. Create a Driver User
        $driver = User::create([
            'name' => 'John Driver',
            'email' => 'john@driver.com',
            'password' => bcrypt('password'),
            'phone' => '123-456-7890',
            'active' => true,
        ]);

        // 2. Create a Route and assign it to the Driver
        $route = Route::create([
            'name' => 'Monday North Route',
            'service_days' => ['Monday'],
            'assigned_driver_id' => $driver->id,
        ]);

        // 3. Create a Customer
        $customer = Customer::create([
            'name' => 'Greasy Spoon Diner',
            'contact_name' => 'Bob Spoon',
            'phone' => '999-888-7777',
            'email' => 'bob@greasyspoon.com',
            'billing_address' => '123 Diner Lane, Cityville',
            'notes' => 'Prefers morning collections',
            'status' => 'active',
        ]);

        // 4. Create a Location for the Customer, assigned default route
        $location = Location::create([
            'customer_id' => $customer->id,
            'name' => 'Main Site',
            'service_address' => '123 Diner Lane, Cityville',
            'map_link' => 'https://maps.google.com/?q=123+Diner+Lane',
            'special_instructions' => 'Gate code is 1234',
            'service_frequency' => 'weekly',
            'reimbursement_rate' => 0.15,
            'status' => 'active',
            'default_route_id' => $route->id,
        ]);

        // 5. Create a Container for the Location
        $container = Container::create([
            'location_id' => $location->id,
            'container_type' => 'tank',
            'capacity' => '200 gal',
            'date_placed' => now()->subMonths(6),
        ]);

        // 6. Create a RouteStop
        $routeStop = RouteStop::create([
            'route_id' => $route->id,
            'location_id' => $location->id,
            'position' => 1,
            'is_active' => true,
        ]);

        // 7. Log a PickupEvent
        $pickup = PickupEvent::create([
            'location_id' => $location->id,
            'route_id' => $route->id,
            'driver_id' => $driver->id,
            'occurred_at' => now(),
            'pounds_collected' => 150.00,
            'status' => 'completed',
        ]);

        // 8. Create a PayoutRecord
        $payout = PayoutRecord::create([
            'customer_id' => $customer->id,
            'date_range_start' => now()->startOfMonth(),
            'date_range_end' => now()->endOfMonth(),
            'total_pounds' => 150.00,
            'reimbursement_rate' => 0.15,
            'total_amount_owed' => 22.50,
            'is_paid' => false,
        ]);

        // Assert Driver Relations
        $this->assertEquals('John Driver', $route->assignedDriver->name);
        $this->assertCount(1, $driver->routes);
        $this->assertEquals('Monday North Route', $driver->routes->first()->name);

        // Assert Customer Relations
        $this->assertCount(1, $customer->locations);
        $this->assertEquals('Main Site', $customer->locations->first()->name);
        $this->assertCount(1, $customer->payoutRecords);
        $this->assertEquals(22.50, $customer->payoutRecords->first()->total_amount_owed);

        // Assert Location Relations
        $this->assertEquals('Greasy Spoon Diner', $location->customer->name);
        $this->assertEquals('Monday North Route', $location->defaultRoute->name);
        $this->assertCount(1, $location->containers);
        $this->assertEquals('tank', $location->containers->first()->container_type);
        $this->assertCount(1, $location->routeStops);
        $this->assertCount(1, $location->pickupEvents);
        $this->assertEquals(150.00, $location->pickupEvents->first()->pounds_collected);

        // Assert Container Relations
        $this->assertEquals('Main Site', $container->location->name);

        // Assert RouteStop Relations
        $this->assertEquals('Monday North Route', $routeStop->route->name);
        $this->assertEquals('Main Site', $routeStop->location->name);

        // Assert PickupEvent Relations
        $this->assertEquals('Main Site', $pickup->location->name);
        $this->assertEquals('Monday North Route', $pickup->route->name);
        $this->assertEquals('John Driver', $pickup->driver->name);

        // Assert PayoutRecord Relations
        $this->assertEquals('Greasy Spoon Diner', $payout->customer->name);
    }
}
