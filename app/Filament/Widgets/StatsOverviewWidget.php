<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Location;
use App\Models\PickupEvent;
use App\Models\PayoutRecord;
use App\Models\Route;
use App\Models\ScheduledStop;
use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = auth()->user();

        if (!$user) {
            return [];
        }

        if ($user->hasRole('Admin')) {
            return $this->getAdminStats();
        }

        if ($user->hasRole('Dispatcher')) {
            return $this->getDispatcherStats();
        }

        if ($user->hasRole('Accounting')) {
            return $this->getAccountingStats();
        }

        return [];
    }

    protected function getAdminStats(): array
    {
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('status', 'active')->count();
        
        $totalLocations = Location::count();
        $activeLocations = Location::where('status', 'active')->count();

        $totalPounds = PickupEvent::where('status', 'completed')->sum('pounds_collected');
        $totalPayouts = PayoutRecord::sum('total_amount_owed');

        return [
            Stat::make('Active Customers', "{$activeCustomers} / {$totalCustomers}")
                ->description('Total active restaurant accounts')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
            Stat::make('Active Service Sites', "{$activeLocations} / {$totalLocations}")
                ->description('Configured locations in schedule')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('info'),
            Stat::make('Grease Recycled', number_format($totalPounds, 1) . ' lbs')
                ->description('Cumulative pounds collected')
                ->descriptionIcon('heroicon-m-scale')
                ->color('success'),
            Stat::make('Total Owed Payouts', '$' . number_format($totalPayouts, 2))
                ->description('All computed payouts to date')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('amber'),
        ];
    }

    protected function getDispatcherStats(): array
    {
        $today = Carbon::today();
        $weekday = $today->format('l');
        
        $routesTodayCount = Route::whereDate('date_of_service', $today)->count();
        
        $stopsToday = ScheduledStop::whereDate('date', $today->toDateString())->count();
        $completedStops = ScheduledStop::whereDate('date', $today->toDateString())
            ->where('status', 'completed')
            ->count();
        $skippedStops = ScheduledStop::whereDate('date', $today->toDateString())
            ->where('status', 'skipped')
            ->count();

        $activeDrivers = User::role('Driver')->where('is_active', true)->count();

        return [
            Stat::make('Routes Running Today', $routesTodayCount)
                ->description('Assigned routes for ' . $weekday)
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary'),
            Stat::make('Today\'s Stops Completed', "{$completedStops} / {$stopsToday}")
                ->description("With {$skippedStops} skipped stops today")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($stopsToday > 0 && $completedStops === $stopsToday ? 'success' : 'warning'),
            Stat::make('Active Drivers Available', $activeDrivers)
                ->description('Drivers eligible for dispatch')
                ->descriptionIcon('heroicon-m-identification')
                ->color('success'),
        ];
    }

    protected function getAccountingStats(): array
    {
        $unpaidCount = PayoutRecord::where('is_paid', false)->count();
        $unpaidAmount = PayoutRecord::where('is_paid', false)->sum('total_amount_owed');
        $paidAmount = PayoutRecord::where('is_paid', true)->sum('total_amount_owed');

        return [
            Stat::make('Pending Payouts', $unpaidCount)
                ->description('Outstanding payouts awaiting check/ACH')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('Total Unpaid Owed', '$' . number_format($unpaidAmount, 2))
                ->description('Amount owed to customers')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger'),
            Stat::make('Total Payouts Paid', '$' . number_format($paidAmount, 2))
                ->description('Settled payments history')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }
}
