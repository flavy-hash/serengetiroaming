<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Str;

class PopularTripsChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected ?string $heading = 'Most requested trips';

    protected ?string $description = 'Top 6 by number of enquiries';

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $trips = Booking::toBase()
            ->selectRaw('trip, count(*) as total')
            ->groupBy('trip')
            ->orderByDesc('total')
            ->limit(6)
            ->pluck('total', 'trip');

        return [
            'datasets' => [
                ChartStyle::barDataset('Enquiries', $trips->values()->map(fn ($total) => (int) $total)->all()),
            ],
            // Trip names look like "Zanzibar Beach Escape · 4 Days · From $650" — keep the name only.
            'labels' => $trips->keys()->map(fn (string $trip) => Str::limit(Str::before($trip, ' · '), 28))->all(),
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
