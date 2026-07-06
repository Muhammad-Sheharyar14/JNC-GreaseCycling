<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\PickupEvent;
use App\Models\Route;
use App\Models\User;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Reports extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected string $view = 'filament.pages.reports';

    protected static ?string $title = 'Business Reports';

    protected static ?string $navigationLabel = 'Reports';

    public ?string $startDate = null;
    public ?string $endDate = null;

    public function mount(): void
    {
        $this->startDate = Carbon::today()->subDays(30)->toDateString();
        $this->endDate = Carbon::today()->toDateString();
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['Admin', 'Dispatcher', 'Accounting']);
    }

    /**
     * Get summary metrics for the selected date range.
     */
    public function getSummaryProperty(): object
    {
        $query = PickupEvent::whereBetween(DB::raw('DATE(occurred_at)'), [$this->startDate, $this->endDate]);

        $completedQuery = clone $query;
        $skippedQuery = clone $query;

        $completedCount = $completedQuery->where('status', 'completed')->count();
        $totalPounds = $completedQuery->sum('pounds_collected');
        $skippedCount = $skippedQuery->where('status', 'skipped')->count();

        $avgPounds = $completedCount > 0 ? $totalPounds / $completedCount : 0;

        return (object) [
            'total_pounds' => $totalPounds,
            'completed_count' => $completedCount,
            'skipped_count' => $skippedCount,
            'avg_pounds' => $avgPounds,
        ];
    }

    /**
     * Get driver breakdown metrics.
     */
    public function getDriverBreakdownProperty(): Collection
    {
        return User::role('Driver')
            ->select('users.id', 'users.name')
            ->withCount([
                'pickupEvents as completed_count' => function ($query) {
                    $query->where('pickup_events.status', 'completed')
                        ->whereBetween(DB::raw('DATE(occurred_at)'), [$this->startDate, $this->endDate]);
                }
            ])
            ->withSum([
                'pickupEvents as total_pounds' => function ($query) {
                    $query->where('pickup_events.status', 'completed')
                        ->whereBetween(DB::raw('DATE(occurred_at)'), [$this->startDate, $this->endDate]);
                }
            ], 'pounds_collected')
            ->get()
            ->map(function ($driver) {
                $driver->total_pounds = $driver->total_pounds ?? 0;
                return $driver;
            });
    }

    /**
     * Get route breakdown metrics.
     */
    public function getRouteBreakdownProperty(): Collection
    {
        return Route::select('routes.id', 'routes.name')
            ->withCount([
                'pickupEvents as completed_count' => function ($query) {
                    $query->where('pickup_events.status', 'completed')
                        ->whereBetween(DB::raw('DATE(occurred_at)'), [$this->startDate, $this->endDate]);
                }
            ])
            ->withSum([
                'pickupEvents as total_pounds' => function ($query) {
                    $query->where('pickup_events.status', 'completed')
                        ->whereBetween(DB::raw('DATE(occurred_at)'), [$this->startDate, $this->endDate]);
                }
            ], 'pounds_collected')
            ->get()
            ->map(function ($route) {
                $route->total_pounds = $route->total_pounds ?? 0;
                return $route;
            });
    }

    /**
     * Get customer breakdown metrics.
     */
    public function getCustomerBreakdownProperty(): Collection
    {
        // Spatie DB query to sum pounds grouped by Customer
        return Customer::select('customers.id', 'customers.name')
            ->withCount([
                'pickupEvents as completed_count' => function ($query) {
                    $query->where('pickup_events.status', 'completed')
                        ->whereBetween(DB::raw('DATE(occurred_at)'), [$this->startDate, $this->endDate]);
                }
            ])
            ->withSum([
                'pickupEvents as total_pounds' => function ($query) {
                    $query->where('pickup_events.status', 'completed')
                        ->whereBetween(DB::raw('DATE(occurred_at)'), [$this->startDate, $this->endDate]);
                }
            ], 'pounds_collected')
            ->get()
            ->map(function ($customer) {
                $customer->total_pounds = $customer->total_pounds ?? 0;
                return $customer;
            });
    }

    /**
     * Get skipped pickups logs.
     */
    public function getSkippedLogsProperty(): Collection
    {
        return PickupEvent::with(['location.customer', 'driver', 'route'])
            ->where('status', 'skipped')
            ->whereBetween(DB::raw('DATE(occurred_at)'), [$this->startDate, $this->endDate])
            ->orderBy('occurred_at', 'desc')
            ->get();
    }

    /**
     * Export breakdown tables to CSV download format.
     */
    public function exportCsv(string $type): StreamedResponse
    {
        $filename = "report-{$type}-{$this->startDate}-to-{$this->endDate}.csv";

        return response()->streamDownload(function () use ($type) {
            $handle = fopen('php://output', 'w');

            if ($type === 'drivers') {
                fputcsv($handle, ['Driver ID', 'Driver Name', 'Completed Pickups', 'Total Pounds Collected']);
                foreach ($this->driverBreakdown as $row) {
                    fputcsv($handle, [$row->id, $row->name, $row->completed_count, $row->total_pounds]);
                }
            } elseif ($type === 'routes') {
                fputcsv($handle, ['Route ID', 'Route Name', 'Completed Pickups', 'Total Pounds Collected']);
                foreach ($this->routeBreakdown as $row) {
                    fputcsv($handle, [$row->id, $row->name, $row->completed_count, $row->total_pounds]);
                }
            } elseif ($type === 'customers') {
                fputcsv($handle, ['Customer ID', 'Customer Name', 'Completed Pickups', 'Total Pounds Collected']);
                foreach ($this->customerBreakdown as $row) {
                    fputcsv($handle, [$row->id, $row->name, $row->completed_count, $row->total_pounds]);
                }
            } elseif ($type === 'skips') {
                fputcsv($handle, ['Date & Time', 'Location Name', 'Customer Name', 'Route', 'Driver Name', 'Skip Reason', 'Notes']);
                foreach ($this->skippedLogs as $row) {
                    fputcsv($handle, [
                        $row->occurred_at->toDateTimeString(),
                        $row->location->name,
                        $row->location->customer->name,
                        $row->route->name,
                        $row->driver?->name ?? 'Dispatcher/Admin',
                        $row->skip_reason,
                        $row->notes ?? ''
                    ]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
