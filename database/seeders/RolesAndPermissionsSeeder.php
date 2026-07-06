<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage_users',
            'manage_settings',
            
            'view_customers',
            'create_customers',
            'update_customers',
            'delete_customers',
            
            'view_locations',
            'create_locations',
            'update_locations',
            'delete_locations',
            
            'view_routes',
            'create_routes',
            'update_routes',
            'delete_routes',
            
            'view_pickups',
            'create_pickups',
            'update_pickups',
            'delete_pickups',
            
            'view_payouts',
            'create_payouts',
            'update_payouts',
            'delete_payouts',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles and assign existing permissions

        // 1. Admin - full access
        $adminRole = Role::findOrCreate('Admin');
        $adminRole->givePermissionTo(Permission::all());

        // 2. Dispatcher - manage customers, locations, routes, and pickups. No settings or users.
        $dispatcherRole = Role::findOrCreate('Dispatcher');
        $dispatcherRole->givePermissionTo([
            'view_customers', 'create_customers', 'update_customers', 'delete_customers',
            'view_locations', 'create_locations', 'update_locations', 'delete_locations',
            'view_routes', 'create_routes', 'update_routes', 'delete_routes',
            'view_pickups', 'create_pickups', 'update_pickups', 'delete_pickups',
        ]);

        // 3. Driver - access via API only, can see own routes and log pickups.
        $driverRole = Role::findOrCreate('Driver');
        $driverRole->givePermissionTo([
            'view_routes',
            'view_pickups',
            'create_pickups',
        ]);

        // 4. Accounting - read-only access to pickups, and management of payouts.
        $accountingRole = Role::findOrCreate('Accounting');
        $accountingRole->givePermissionTo([
            'view_pickups',
            'view_payouts',
            'create_payouts',
            'update_payouts',
        ]);

        // Let's create seed users for each role to help test
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@greasecycling.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'active' => true,
            ]
        );
        $adminUser->assignRole($adminRole);

        $dispatcherUser = User::updateOrCreate(
            ['email' => 'dispatcher@greasecycling.com'],
            [
                'name' => 'Dispatcher User',
                'password' => Hash::make('password'),
                'active' => true,
            ]
        );
        $dispatcherUser->assignRole($dispatcherRole);

        $driverUser = User::updateOrCreate(
            ['email' => 'driver@greasecycling.com'],
            [
                'name' => 'Driver User',
                'password' => Hash::make('password'),
                'phone' => '1234567890',
                'active' => true,
            ]
        );
        $driverUser->assignRole($driverRole);

        $accountingUser = User::updateOrCreate(
            ['email' => 'accounting@greasecycling.com'],
            [
                'name' => 'Accounting User',
                'password' => Hash::make('password'),
                'active' => true,
            ]
        );
        $accountingUser->assignRole($accountingRole);
    }
}
