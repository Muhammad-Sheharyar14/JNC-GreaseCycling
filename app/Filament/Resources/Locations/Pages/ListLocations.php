<?php

namespace App\Filament\Resources\Locations\Pages;

use App\Filament\Resources\Locations\LocationResource;
use App\Models\Customer;
use App\Models\Location;
use App\Models\Route;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListLocations extends ListRecords
{
    protected static string $resource = LocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            // Export Locations to CSV
            Action::make('exportLocations')
                ->label('Export to CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function (): StreamedResponse {
                    $filename = 'locations-export-' . now()->format('Y-md-His') . '.csv';
                    return response()->streamDownload(function () {
                        $handle = fopen('php://output', 'w');
                        fputcsv($handle, ['ID', 'Customer Name', 'Location Name', 'Service Address', 'Map Link', 'Special Instructions', 'Service Frequency', 'Reimbursement Rate', 'Status', 'Default Route Name']);
                        
                        Location::with(['customer', 'defaultRoute'])->chunk(200, function ($locations) use ($handle) {
                            foreach ($locations as $record) {
                                fputcsv($handle, [
                                    $record->id,
                                    $record->customer?->name,
                                    $record->name,
                                    $record->service_address,
                                    $record->map_link,
                                    $record->special_instructions,
                                    $record->service_frequency,
                                    $record->reimbursement_rate,
                                    $record->status,
                                    $record->defaultRoute?->name,
                                ]);
                            }
                        });

                        fclose($handle);
                    }, $filename, [
                        'Content-Type' => 'text/csv',
                        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                    ]);
                }),

            // Import Locations from CSV
            Action::make('importLocations')
                ->label('Import from CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('amber')
                ->form([
                    \Filament\Forms\Components\Placeholder::make('import_instructions')
                        ->label('CSV Format Instructions')
                        ->content(new \Illuminate\Support\HtmlString('
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                <p class="mb-2">Your CSV file must include a header row. The following columns are supported:</p>
                                <ul class="list-disc pl-5 space-y-1 mb-2">
                                    <li><strong>Customer Name</strong> <span class="text-danger-600 font-bold">*Required</span> (creates or matches existing customer)</li>
                                    <li><strong>Location Name</strong> <span class="text-danger-600 font-bold">*Required</span> (name of service site)</li>
                                    <li><strong>Service Address</strong> (street address of the service site)</li>
                                    <li><strong>Service Frequency</strong> (supported values: <code>weekly</code>, <code>biweekly</code>, <code>monthly</code>, <code>on_call</code>; default is <code>weekly</code>)</li>
                                    <li><strong>Reimbursement Rate</strong> (numeric rate per pound, default is 0.00)</li>
                                    <li><strong>Status</strong> (supported values: <code>active</code>, <code>paused</code>, <code>cancelled</code>; default is <code>active</code>)</li>
                                    <li><strong>Special Instructions</strong> (driver notes)</li>
                                    <li><strong>Default Route Name</strong> (matches route name to assign automatically)</li>
                                </ul>
                                <p class="text-xs text-gray-500">Note: Matching location names for the same customer will update existing records.</p>
                            </div>
                        ')),
                    FileUpload::make('csv_file')
                        ->label('CSV File')
                        ->acceptedFileTypes(['text/csv', 'text/plain', 'application/vnd.ms-excel'])
                        ->disk('public')
                        ->directory('imports')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $fileVal = is_array($data['csv_file']) ? array_values($data['csv_file'])[0] : $data['csv_file'];
                    $filePath = Storage::disk('public')->path($fileVal);

                    if (!file_exists($filePath) || !is_readable($filePath)) {
                        Notification::make()->danger()->title('Unable to read CSV file.')->send();
                        return;
                    }

                    $handle = fopen($filePath, 'r');
                    $headers = fgetcsv($handle);

                    if (!$headers) {
                        fclose($handle);
                        Notification::make()->danger()->title('CSV file is empty.')->send();
                        return;
                    }

                    // Normalize headers
                    $headers = array_map(fn ($h) => strtolower(trim(str_replace([' ', '_'], '', $h))), $headers);

                    $importedCount = 0;
                    while (($row = fgetcsv($handle)) !== false) {
                        // Find indexes
                        $customerNameKey = array_search('customername', $headers);
                        $nameKey = array_search('locationname', $headers);
                        if ($nameKey === false) {
                            $nameKey = array_search('name', $headers);
                        }
                        $addressKey = array_search('serviceaddress', $headers);
                        $frequencyKey = array_search('servicefrequency', $headers);
                        $rateKey = array_search('reimbursementrate', $headers);
                        $statusKey = array_search('status', $headers);
                        $instructionsKey = array_search('specialinstructions', $headers);
                        $routeNameKey = array_search('defaultroutename', $headers);

                        $customerName = $customerNameKey !== false ? trim($row[$customerNameKey]) : null;
                        $name = $nameKey !== false ? trim($row[$nameKey]) : null;

                        if (!$customerName || !$name) {
                            continue;
                        }

                        // Lookup or create customer
                        $customer = Customer::firstOrCreate(
                            ['name' => $customerName],
                            ['status' => 'active']
                        );

                        // Lookup Route if provided
                        $routeId = null;
                        if ($routeNameKey !== false && !empty(trim($row[$routeNameKey]))) {
                            $routeName = trim($row[$routeNameKey]);
                            $route = Route::where('name', $routeName)->first();
                            if ($route) {
                                $routeId = $route->id;
                            }
                        }

                        $frequency = $frequencyKey !== false ? strtolower(trim($row[$frequencyKey])) : 'weekly';
                        if (!in_array($frequency, ['weekly', 'biweekly', 'monthly', 'on_call'])) {
                            $frequency = 'weekly';
                        }

                        $status = $statusKey !== false ? strtolower(trim($row[$statusKey])) : 'active';
                        if (!in_array($status, ['active', 'paused', 'cancelled'])) {
                            $status = 'active';
                        }

                        Location::updateOrCreate(
                            [
                                'customer_id' => $customer->id,
                                'name' => $name,
                            ],
                            [
                                'service_address' => $addressKey !== false ? trim($row[$addressKey]) : 'Unknown Address',
                                'service_frequency' => $frequency,
                                'reimbursement_rate' => $rateKey !== false ? (float) $row[$rateKey] : 0.00,
                                'status' => $status,
                                'special_instructions' => $instructionsKey !== false ? trim($row[$instructionsKey]) : null,
                                'default_route_id' => $routeId,
                            ]
                        );

                        $importedCount++;
                    }

                    fclose($handle);
                    Storage::disk('public')->delete($fileVal);

                    Notification::make()
                        ->success()
                        ->title('CSV Import complete.')
                        ->body("Successfully imported/updated {$importedCount} location records.")
                        ->send();
                }),
        ];
    }
}
