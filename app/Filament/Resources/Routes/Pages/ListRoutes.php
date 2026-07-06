<?php

namespace App\Filament\Resources\Routes\Pages;

use App\Filament\Resources\Routes\RouteResource;
use App\Models\Route;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListRoutes extends ListRecords
{
    protected static string $resource = RouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            
            Action::make('exportRoutes')
                ->label('Export to CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function (): StreamedResponse {
                    $filename = 'routes-export-' . now()->format('Y-md-His') . '.csv';
                    return response()->streamDownload(function () {
                        $handle = fopen('php://output', 'w');
                        fputcsv($handle, ['ID', 'Route Name', 'Service Days', 'Assigned Driver Name']);
                        
                        Route::with('assignedDriver')->chunk(200, function ($routes) use ($handle) {
                            foreach ($routes as $record) {
                                fputcsv($handle, [
                                    $record->id,
                                    $record->name,
                                    is_array($record->service_days) ? implode(', ', $record->service_days) : $record->service_days,
                                    $record->assignedDriver?->name,
                                ]);
                            }
                        });

                        fclose($handle);
                    }, $filename, [
                        'Content-Type' => 'text/csv',
                        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                    ]);
                }),
        ];
    }
}
