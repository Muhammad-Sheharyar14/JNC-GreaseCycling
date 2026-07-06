<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            
            // Export All Customers to CSV
            Action::make('exportCustomers')
                ->label('Export to CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function (): StreamedResponse {
                    $filename = 'customers-export-' . now()->format('Y-md-His') . '.csv';
                    return response()->streamDownload(function () {
                        $handle = fopen('php://output', 'w');
                        fputcsv($handle, ['ID', 'Name', 'Contact Name', 'Phone', 'Email', 'Billing Address', 'Notes', 'Status', 'Created At']);
                        
                        Customer::chunk(200, function ($customers) use ($handle) {
                            foreach ($customers as $record) {
                                fputcsv($handle, [
                                    $record->id,
                                    $record->name,
                                    $record->contact_name,
                                    $record->phone,
                                    $record->email,
                                    $record->billing_address,
                                    $record->notes,
                                    $record->status,
                                    $record->created_at?->toDateTimeString(),
                                ]);
                            }
                        });

                        fclose($handle);
                    }, $filename, [
                        'Content-Type' => 'text/csv',
                        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                    ]);
                }),

            // Import Customers from CSV
            Action::make('importCustomers')
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
                                    <li><strong>Name</strong> <span class="text-danger-600 font-bold">*Required</span> (customer account name)</li>
                                    <li><strong>Contact Name</strong> (contact person name)</li>
                                    <li><strong>Phone</strong> (contact phone number)</li>
                                    <li><strong>Email</strong> (contact email address)</li>
                                    <li><strong>Billing Address</strong> (billing street address)</li>
                                    <li><strong>Notes</strong> (custom account remarks)</li>
                                    <li><strong>Status</strong> (supported values: <code>lead</code>, <code>active</code>, <code>cancelled</code>; default is <code>lead</code>)</li>
                                </ul>
                                <p class="text-xs text-gray-500">Note: Matching client names will automatically update existing customer records.</p>
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
                        $rowData = array_combine(
                            array_intersect_key($headers, $row),
                            array_slice($row, 0, count($headers))
                        );

                        // Find indices
                        $nameKey = array_search('name', $headers);
                        $name = $nameKey !== false ? trim($row[$nameKey]) : null;

                        if (!$name) {
                            continue;
                        }

                        $contactNameKey = array_search('contactname', $headers);
                        $phoneKey = array_search('phone', $headers);
                        $emailKey = array_search('email', $headers);
                        $addressKey = array_search('billingaddress', $headers);
                        $notesKey = array_search('notes', $headers);
                        $statusKey = array_search('status', $headers);

                        $status = $statusKey !== false ? strtolower(trim($row[$statusKey])) : 'lead';
                        if (!in_array($status, ['lead', 'active', 'cancelled'])) {
                            $status = 'lead';
                        }

                        Customer::updateOrCreate(
                            ['name' => $name],
                            [
                                'contact_name' => $contactNameKey !== false ? trim($row[$contactNameKey]) : null,
                                'phone' => $phoneKey !== false ? trim($row[$phoneKey]) : null,
                                'email' => $emailKey !== false ? trim($row[$emailKey]) : null,
                                'billing_address' => $addressKey !== false ? trim($row[$addressKey]) : null,
                                'notes' => $notesKey !== false ? trim($row[$notesKey]) : null,
                                'status' => $status,
                            ]
                        );

                        $importedCount++;
                    }

                    fclose($handle);
                    Storage::disk('public')->delete($fileVal);

                    Notification::make()
                        ->success()
                        ->title('CSV Import complete.')
                        ->body("Successfully imported/updated {$importedCount} customer records.")
                        ->send();
                }),
        ];
    }
}
