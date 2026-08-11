<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\ScheduledStop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $driver = User::whereEmail('driver@greasecycling.com')->first();
        if (!$driver) {
            return;
        }

        $today = Carbon::today()->toDateString();

        // 1. Create or get Route for today
        $route = Route::firstOrCreate(
            ['name' => 'South Region Route', 'date_of_service' => $today],
            ['assigned_driver_id' => $driver->id]
        );
        $route->update(['assigned_driver_id' => $driver->id]);

        // 2. Create sample Customers & Locations
        $locationsData = [
            [
                'customer_name' => 'Kurai Restaurant',
                'location_name' => 'Kurai',
                'address' => '1201 S Jackson Rd #1, Pharr, TX 78577',
            ],
            [
                'customer_name' => 'Taco Fiesta',
                'location_name' => 'Taco Fiesta Downtown',
                'address' => '400 S Main St, McAllen, TX 78501',
            ],
            [
                'customer_name' => 'Burger House',
                'location_name' => 'Burger House Express',
                'address' => '2200 E Expressway 83, Weslaco, TX 78596',
            ],
        ];

        $pos = 1;
        foreach ($locationsData as $data) {
            $customer = Customer::firstOrCreate(
                ['name' => $data['customer_name']],
                ['status' => 'active']
            );

            $location = Location::firstOrCreate(
                ['customer_id' => $customer->id, 'name' => $data['location_name']],
                [
                    'service_address' => $data['address'],
                    'service_frequency' => 'weekly',
                    'reimbursement_rate' => 0.15,
                    'status' => 'active',
                ]
            );

            RouteStop::firstOrCreate(
                ['route_id' => $route->id, 'location_id' => $location->id],
                ['position' => $pos]
            );

            ScheduledStop::firstOrCreate(
                ['route_id' => $route->id, 'location_id' => $location->id, 'date' => $today],
                [
                    'position' => $pos,
                    'status' => 'pending',
                ]
            );

            $pos++;
        }
    }
}
