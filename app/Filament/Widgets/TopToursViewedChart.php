<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use App\Models\TourPage;
use Filament\Widgets\ChartWidget;

class TopToursViewedChart extends ChartWidget
{
    protected static ?int $sort = 8;

    protected ?string $heading = 'Most viewed tours (30 days)';

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $counts = PageView::query()
            ->whereNotNull('package_id')
            ->where('created_at', '>=', now()->subDays(30))
            ->toBase()
            ->selectRaw('package_id, count(*) as total')
            ->groupBy('package_id')
            ->orderByDesc('total')
            ->limit(8)
            ->pluck('total', 'package_id');

        $names = TourPage::whereKey($counts->keys())->pluck('package_name', 'id');

        return [
            'datasets' => [
                ChartStyle::barDataset('Views', $counts->values()->map(fn ($n) => (int) $n)->all()),
            ],
            'labels' => $counts->keys()->map(fn ($id) => $names->get($id, 'Deleted package'))->all(),
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
