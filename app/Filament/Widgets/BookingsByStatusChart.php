<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Filament\Widgets\ChartWidget;

class BookingsByStatusChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected ?string $heading = 'Bookings by status';

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $counts = Booking::toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statuses = collect(BookingStatus::cases());

        return [
            'datasets' => [
                ChartStyle::barDataset('Bookings', $statuses->map(fn (BookingStatus $s) => (int) $counts->get($s->value, 0))->all()),
            ],
            'labels' => $statuses->map(fn (BookingStatus $s) => $s->getLabel())->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return ChartStyle::options();
    }
}
