<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Widgets\ChartWidget;

class TrafficSourcesChart extends ChartWidget
{
    protected static ?int $sort = 9;

    protected ?string $heading = 'Where visitors came from (30 days)';

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        // Count each visitor's day once per source, not every page they opened.
        $sources = PageView::where('created_at', '>=', now()->subDays(30))
            ->get(['visitor_hash', 'referrer_host'])
            ->unique(fn (PageView $v) => $v->visitor_hash.'|'.$v->referrer_host)
            ->countBy(fn (PageView $v) => $v->referrer_host ?: 'Direct / unknown')
            ->sortDesc()
            ->take(8);

        return [
            'datasets' => [
                ChartStyle::barDataset('Visitors', $sources->values()->all()),
            ],
            'labels' => $sources->keys()->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return ChartStyle::options(horizontal: true);
    }
}
