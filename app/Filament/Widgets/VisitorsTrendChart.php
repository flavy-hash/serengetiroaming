<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Widgets\ChartWidget;

class VisitorsTrendChart extends ChartWidget
{
    protected static ?int $sort = 7;

    protected int|string|array $columnSpan = 'full';

    protected ?string $heading = 'Visitors per day';

    protected ?string $maxHeight = '280px';

    public ?string $filter = '30';

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Last 7 days',
            '30' => 'Last 30 days',
            '90' => 'Last 90 days',
        ];
    }

    protected function getData(): array
    {
        $days = (int) $this->filter;
        $start = now()->subDays($days - 1)->startOfDay();

        // Grouped in PHP so this works on MySQL and SQLite alike.
        $visitors = PageView::where('created_at', '>=', $start)
            ->get(['visitor_hash', 'created_at'])
            ->groupBy(fn (PageView $v) => $v->created_at->toDateString())
            ->map(fn ($views) => $views->unique('visitor_hash')->count());

        $dates = collect(range(0, $days - 1))->map(fn (int $i) => $start->copy()->addDays($i));

        return [
            'datasets' => [
                [
                    'label' => 'Visitors',
                    'data' => $dates->map(fn ($d) => $visitors->get($d->toDateString(), 0))->all(),
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
            'labels' => $dates->map(fn ($d) => $d->format('M j'))->all(),
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
