<?php

namespace App\Filament\Pages;

use App\Models\Location;
use App\Models\PickupEvent;
use App\Models\Route;
use App\Models\RouteRun;
use App\Models\ScheduledStop;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class DispatchDashboard extends Page implements HasActions
{
    use InteractsWithActions;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected string $view = 'filament.pages.dispatch-dashboard';

    protected static ?string $title = 'Dispatch Dashboard';

    protected static ?string $navigationLabel = 'Dispatch Dashboard';

    public ?string $date = null;
    public ?int $selectedRouteId = null;

    public function mount(): void
    {
        $this->date = Carbon::today()->toDateString();
    }

    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['Admin', 'Dispatcher']);
    }

    /**
     * Get routes running on the selected date.
     */
    public function getRoutesProperty(): Collection
    {
        if (!$this->date) {
            return collect();
        }

        $weekday = Carbon::parse($this->date)->format('l');

        // Fetch routes that service this weekday with their driver relationship eager-loaded
        $routes = Route::with('assignedDriver')->whereJsonContains('service_days', $weekday)->get();

        return $routes->map(function (Route $route) {
            $scheduledStops = ScheduledStop::where('route_id', $route->id)
                ->whereDate('date', $this->date)
                ->get();

            $totalStops = $scheduledStops->count();
            $completedStops = $scheduledStops->where('status', 'completed')->count();
            $skippedStops = $scheduledStops->where('status', 'skipped')->count();

            $routeRun = RouteRun::where('route_id', $route->id)
                ->whereDate('date', $this->date)
                ->first();

            return (object) [
                'id' => $route->id,
                'name' => $route->name,
                'driver_name' => $route->assignedDriver?->name ?? 'Unassigned',
                'total_stops' => $totalStops,
                'completed_stops' => $completedStops,
                'skipped_stops' => $skippedStops,
                'status' => $routeRun?->status ?? 'not_started',
                'started_at' => $routeRun?->started_at,
                'ended_at' => $routeRun?->ended_at,
            ];
        });
    }

    /**
     * Get scheduled stops for the selected route on the selected date.
     */
    public function getStopsProperty(): Collection
    {
        if (!$this->selectedRouteId || !$this->date) {
            return collect();
        }

        return ScheduledStop::with(['location.customer', 'pickupEvent'])
            ->where('route_id', $this->selectedRouteId)
            ->whereDate('date', $this->date)
            ->orderBy('position', 'asc')
            ->get();
    }

    /**
     * Set the active route to view stops for.
     */
    public function selectRoute(int $routeId): void
    {
        $this->selectedRouteId = $routeId;
    }

    /**
     * Helper to load other routes running today for reassignment options.
     */
    public function getAvailableRoutesOptions(): array
    {
        if (!$this->date) return [];
        $weekday = Carbon::parse($this->date)->format('l');
        return Route::whereJsonContains('service_days', $weekday)
            ->where('id', '!=', $this->selectedRouteId)
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Define page header actions.
     */
    protected function getHeaderActions(): array
    {
        $actions = [];

        if ($this->selectedRouteId) {
            $actions[] = $this->addEmergencyStopAction();
        }

        if ($this->date) {
            $actions[] = $this->generateDailyStopsAction();
        }

        return $actions;
    }

    /**
     * Generate stops action.
     */
    public function generateDailyStopsAction(): Action
    {
        return Action::make('generateDailyStops')
            ->label('Generate Stops')
            ->icon('heroicon-o-arrow-path')
            ->color('amber')
            ->requiresConfirmation()
            ->modalHeading('Generate Stops for Date')
            ->modalDescription('This will automatically generate scheduled stops for all active routes on the selected date based on frequencies.')
            ->action(function () {
                $scheduler = app(\App\Services\RouteScheduler::class);
                $generated = $scheduler->generateStopsForDate(Carbon::parse($this->date));

                \Filament\Notifications\Notification::make()
                    ->success()
                    ->title("Stops generated successfully.")
                    ->body("Generated {$generated} scheduled stops for " . $this->date . ".")
                    ->send();
            });
    }

    /**
     * Add emergency stop action.
     */
    public function addEmergencyStopAction(): Action
    {
        return Action::make('addEmergencyStop')
            ->label('Add Emergency Stop')
            ->form([
                Select::make('location_id')
                    ->label('Restaurant Location')
                    ->options(Location::where('status', 'active')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
            ])
            ->action(function (array $data) {
                if (!$this->selectedRouteId || !$this->date) return;

                $lastPosition = ScheduledStop::where('route_id', $this->selectedRouteId)
                    ->whereDate('date', $this->date)
                    ->max('position') ?? 0;

                ScheduledStop::create([
                    'route_id' => $this->selectedRouteId,
                    'location_id' => $data['location_id'],
                    'date' => $this->date,
                    'position' => $lastPosition + 1,
                    'status' => 'pending',
                ]);

                \Filament\Notifications\Notification::make()->success()->title('Stop added successfully.')->send();
            });
    }

    /**
     * Reassign stop action.
     */
    public function reassignStopAction(): Action
    {
        return Action::make('reassignStop')
            ->form(fn () => [
                Select::make('route_id')
                    ->label('Target Route')
                    ->options($this->getAvailableRoutesOptions())
                    ->required(),
            ])
            ->action(function (array $data, array $arguments) {
                $stopId = $arguments['stopId'] ?? null;
                if (!$stopId) return;

                $stop = ScheduledStop::findOrFail($stopId);
                $oldRouteId = $stop->route_id;

                // Get last position of target route
                $lastPosition = ScheduledStop::where('route_id', $data['route_id'])
                    ->whereDate('date', $this->date)
                    ->max('position') ?? 0;

                $stop->update([
                    'route_id' => $data['route_id'],
                    'position' => $lastPosition + 1,
                ]);

                // Recalculate positions on old route
                $this->reorderStops($oldRouteId, $this->date);

                \Filament\Notifications\Notification::make()->success()->title('Stop reassigned successfully.')->send();
            });
    }

    /**
     * Delete stop action.
     */
    public function deleteStopAction(): Action
    {
        return Action::make('deleteStop')
            ->requiresConfirmation()
            ->action(function (array $arguments) {
                $stopId = $arguments['stopId'] ?? null;
                if (!$stopId) return;

                $stop = ScheduledStop::findOrFail($stopId);
                $routeId = $stop->route_id;
                $stop->delete();

                // Reorder remaining stops
                $this->reorderStops($routeId, $this->date);

                \Filament\Notifications\Notification::make()->success()->title('Stop removed from route.')->send();
            });
    }

    /**
     * Mark stop complete manually action.
     */
    public function markCompleteManuallyAction(): Action
    {
        return Action::make('markCompleteManually')
            ->form([
                TextInput::make('pounds_collected')
                    ->label('Pounds Collected')
                    ->numeric()
                    ->required()
                    ->minValue(0),
                Textarea::make('notes')
                    ->label('Dispatcher Notes')
                    ->nullable(),
            ])
            ->action(function (array $data, array $arguments) {
                $stopId = $arguments['stopId'] ?? null;
                if (!$stopId) return;

                $stop = ScheduledStop::findOrFail($stopId);

                // Create completed pickup event
                $pickup = PickupEvent::create([
                    'location_id' => $stop->location_id,
                    'route_id' => $stop->route_id,
                    'driver_id' => auth()->id(), // Dispatcher/Admin who did the action
                    'occurred_at' => now(),
                    'pounds_collected' => $data['pounds_collected'],
                    'notes' => $data['notes'],
                    'status' => 'completed',
                ]);

                $stop->update([
                    'status' => 'completed',
                    'pickup_event_id' => $pickup->id,
                ]);

                \Filament\Notifications\Notification::make()->success()->title('Stop marked complete.')->send();
            });
    }

    /**
     * Recalculate ordering sequence for a route's scheduled stops on a date.
     */
    private function reorderStops(int $routeId, string $date): void
    {
        $stops = ScheduledStop::where('route_id', $routeId)
            ->whereDate('date', $date)
            ->orderBy('position', 'asc')
            ->get();

        foreach ($stops as $idx => $stop) {
            $stop->update(['position' => $idx + 1]);
        }
    }
}
