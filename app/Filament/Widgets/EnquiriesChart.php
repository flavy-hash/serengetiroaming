<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class EnquiriesChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Enquiries over time';

    protected ?string $maxHeight = '280px';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
            '90' => 'Last 90 days',
            '365' => 'Last 12 months',
        ];
    }

    protected function getData(): array
    {
        // Group in PHP rather than SQL so this works on MySQL and SQLite alike.
        $byMonth = $this->filter === '365';
        $buckets = $byMonth ? 12 : (int) $this->filter;
        $start = $byMonth
            ? now()->subMonthsNoOverflow($buckets - 1)->startOfMonth()
            : now()->subDays($buckets - 1)->startOfDay();
        $keyFormat = $byMonth ? 'Y-m' : 'Y-m-d';

        $counts = Booking::where('created_at', '>=', $start)
            ->pluck('created_at')
            ->countBy(fn ($date) => $date->format($keyFormat));

        $periods = collect(range(0, $buckets - 1))
            ->map(fn (int $i) => $byMonth ? $start->copy()->addMonthsNoOverflow($i) : $start->copy()->addDays($i));

        return [
            'datasets' => [
                [
                    'label' => 'Enquiries',
                    'data' => $periods->map(fn ($date) => $counts->get($date->format($keyFormat), 0))->all(),
                    'borderColor' => ChartStyle::COLOR,
                    'backgroundColor' => ChartStyle::FILL,
                    'fill' => true,
                    'tension' => 0.3,
                    'borderWidth' => 2,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 5,
                    'pointHitRadius' => 12,
                ],
            ],
            'labels' => $periods->map(fn ($date) => $date->format($byMonth ? 'M Y' : 'M j'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return ChartStyle::options([
            'interaction' => ['mode' => 'index', 'intersect' => false],
        ]);
    }
}
