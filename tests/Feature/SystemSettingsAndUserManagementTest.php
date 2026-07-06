<?php

namespace Tests\Feature;

use App\Filament\Pages\SystemSettings;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Locations\Pages\ListLocations;
use App\Filament\Resources\Routes\Pages\ListRoutes;
use App\Filament\Resources\Users\UserResource;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Route;
use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SystemSettingsAndUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $dispatcherUser;
    private User $driverUser;
    private User $accountingUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->adminUser = User::whereEmail('admin@greasecycling.com')->first();
        $this->dispatcherUser = User::whereEmail('dispatcher@greasecycling.com')->first();
        $this->driverUser = User::whereEmail('driver@greasecycling.com')->first();
        $this->accountingUser = User::whereEmail('accounting@greasecycling.com')->first();

        // Clear Spatie permissions cache to prevent cross-request testing bugs
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Test page access control for system settings page.
     */
    public function test_admin_can_access_system_settings(): void
    {
        $this->actingAs($this->adminUser)->get(SystemSettings::getUrl())->assertSuccessful();
    }

    public function test_dispatcher_cannot_access_system_settings(): void
    {
        // Should redirect or be forbidden
        $response = $this->actingAs($this->dispatcherUser)->get(SystemSettings::getUrl());
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    public function test_driver_cannot_access_system_settings(): void
    {
        $response = $this->actingAs($this->driverUser)->get(SystemSettings::getUrl());
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    public function test_accounting_cannot_access_system_settings(): void
    {
        $response = $this->actingAs($this->accountingUser)->get(SystemSettings::getUrl());
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    /**
     * Test settings save flow.
     */
    public function test_system_settings_can_be_saved(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(SystemSettings::class)
            ->set('default_reimbursement_rate', '0.12')
            ->set('default_payout_frequency', 'monthly')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEquals('0.12', SystemSetting::get('default_reimbursement_rate'));
        $this->assertEquals('monthly', SystemSetting::get('default_payout_frequency'));
    }

    /**
     * Test User resource access control.
     */
    public function test_admin_can_access_user_resource(): void
    {
        $this->actingAs($this->adminUser)->get(UserResource::getUrl())->assertSuccessful();
    }

    public function test_dispatcher_cannot_access_user_resource(): void
    {
        $response = $this->actingAs($this->dispatcherUser)->get(UserResource::getUrl());
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    public function test_driver_cannot_access_user_resource(): void
    {
        $response = $this->actingAs($this->driverUser)->get(UserResource::getUrl());
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    public function test_accounting_cannot_access_user_resource(): void
    {
        $response = $this->actingAs($this->accountingUser)->get(UserResource::getUrl());
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    /**
     * Test user creation, status deactivation, and Spatie roles assignment.
     */
    public function test_user_management_crud(): void
    {
        // 1. Create a user
        Livewire::actingAs($this->adminUser)
            ->test(\App\Filament\Resources\Users\Pages\CreateUser::class)
            ->fillForm([
                'name' => 'New Dispatcher',
                'email' => 'newdisp@greasecycling.com',
                'password' => 'password123',
                'phone' => '555-555-5555',
                'active' => true,
                'roles' => $this->dispatcherUser->roles->first()?->id,
            ])
            ->call('create')
            ->assertHasNoErrors();

        $newUser = User::whereEmail('newdisp@greasecycling.com')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('New Dispatcher', $newUser->name);
        $this->assertTrue($newUser->active);
        $this->assertTrue($newUser->hasRole('Dispatcher'));

        // 2. Deactivate the user
        Livewire::actingAs($this->adminUser)
            ->test(\App\Filament\Resources\Users\Pages\EditUser::class, ['record' => $newUser->getKey()])
            ->fillForm([
                'active' => false,
            ])
            ->call('save')
            ->assertHasNoErrors();

        $newUser->refresh();
        $this->assertFalse($newUser->active);
    }

    /**
     * Test CSV import and export flows for Customers.
     */
    public function test_customer_csv_import_and_export(): void
    {
        Storage::fake('public');

        // Export CSV download test
        $customer = Customer::create(['name' => 'Pizza Palace', 'status' => 'active']);
        Livewire::actingAs($this->adminUser)
            ->test(ListCustomers::class)
            ->callAction('exportCustomers')
            ->assertFileDownloaded();

        // Import CSV flow test
        $csvContent = "Name,Contact Name,Phone,Email,Billing Address,Notes,Status\n";
        $csvContent .= "CSV Imported Customer,John Doe,1234567,john@example.com,123 Main St,Some notes,active\n";
        
        $file = UploadedFile::fake()->createWithContent('customers.csv', $csvContent);
        $path = $file->store('imports', 'public');

        Livewire::actingAs($this->adminUser)
            ->test(ListCustomers::class)
            ->callAction('importCustomers', [
                'csv_file' => [$path],
            ]);

        $this->assertDatabaseHas('customers', [
            'name' => 'CSV Imported Customer',
            'contact_name' => 'John Doe',
            'phone' => '1234567',
            'email' => 'john@example.com',
            'billing_address' => '123 Main St',
            'notes' => 'Some notes',
            'status' => 'active',
        ]);
    }

    /**
     * Test CSV import and export flows for Locations.
     */
    public function test_location_csv_import_and_export(): void
    {
        Storage::fake('public');

        // Setup parent customer
        $customer = Customer::create(['name' => 'Pizza Palace', 'status' => 'active']);

        // Export locations test
        $location = Location::create([
            'customer_id' => $customer->id,
            'name' => 'Palace North',
            'service_address' => '123 North St',
            'service_frequency' => 'weekly',
            'reimbursement_rate' => 0.10,
            'status' => 'active',
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListLocations::class)
            ->callAction('exportLocations')
            ->assertFileDownloaded();

        // Import locations test
        $csvContent = "Customer Name,Location Name,Service Address,Service Frequency,Reimbursement Rate,Status,Default Route Name\n";
        $csvContent .= "Pizza Palace,Palace West,789 West St,biweekly,0.08,active,\n";

        $file = UploadedFile::fake()->createWithContent('locations.csv', $csvContent);
        $path = $file->store('imports', 'public');

        Livewire::actingAs($this->adminUser)
            ->test(ListLocations::class)
            ->callAction('importLocations', [
                'csv_file' => [$path],
            ]);

        $this->assertDatabaseHas('locations', [
            'name' => 'Palace West',
            'service_address' => '789 West St',
            'service_frequency' => 'biweekly',
            'reimbursement_rate' => 0.08,
            'status' => 'active',
        ]);
    }

    /**
     * Test Route CSV export.
     */
    public function test_route_csv_export(): void
    {
        Route::create([
            'name' => 'Route Z',
            'service_days' => ['Monday'],
            'assigned_driver_id' => $this->driverUser->id,
        ]);

        Livewire::actingAs($this->adminUser)
            ->test(ListRoutes::class)
            ->callAction('exportRoutes')
            ->assertFileDownloaded();
    }
}
