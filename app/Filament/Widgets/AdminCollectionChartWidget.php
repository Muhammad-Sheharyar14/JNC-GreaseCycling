<?php

namespace App\Filament\Widgets;

use App\Models\PickupEvent;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class AdminCollectionChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Grease Collection Volume (Past 7 Days)';

    protected string $color = 'amber';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        return auth()->user()?->hasRole('Admin') ?? false;
    }

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // Fetch collections for the last 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            
            $pounds = PickupEvent::where('status', 'completed')
                ->whereDate('occurred_at', $date->toDateString())
                ->sum('pounds_collected');

            $data[] = (float) $pounds;
            $labels[] = $date->format('M d (D)');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Grease Collected (lbs)',
                    'data' => $data,
                    'fill' => 'start',
                    'tension' => 0.3,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
