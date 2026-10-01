<?php

namespace App\Filament\Widgets;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $thisMonth = Booking::where('created_at', '>=', now()->startOfMonth())->count();
        $lastMonth = Booking::whereBetween('created_at', [
            now()->subMonthNoOverflow()->startOfMonth(),
            now()->subMonthNoOverflow()->endOfMonth(),
        ])->count();

        $new = Booking::where('status', BookingStatus::New)->count();
        $total = Booking::count();
        $confirmed = Booking::where('status', BookingStatus::Confirmed)->count();
        $confirmedTravellers = Booking::where('status', BookingStatus::Confirmed)->sum('travellers');

        return [
            Stat::make('New enquiries', $new)
                ->description($new > 0 ? 'Waiting for a reply' : 'All caught up')
                ->descriptionIcon($new > 0 ? Heroicon::OutlinedClock : Heroicon::OutlinedCheckCircle)
                ->color($new > 0 ? 'warning' : 'success')
                ->url(route('filament.admin.resources.bookings.index', [
                    'filters' => ['status' => ['values' => [BookingStatus::New->value]]],
                ])),

            Stat::make('Enquiries this month', $thisMonth)
                ->description($this->monthOverMonth($thisMonth, $lastMonth))
                ->descriptionIcon($thisMonth >= $lastMonth ? Heroicon::ArrowTrendingUp : Heroicon::ArrowTrendingDown)
                ->color($thisMonth >= $lastMonth ? 'success' : 'danger')
                ->chart($this->dailyCounts(30))
                ->chartColor('primary'),

            Stat::make('Confirmed bookings', $confirmed)
                ->description(number_format($confirmedTravellers).' travellers in total')
                ->descriptionIcon(Heroicon::OutlinedUserGroup)
                ->color('primary'),

            Stat::make('Conversion rate', $total > 0 ? round($confirmed / $total * 100).'%' : '—')
                ->description("{$confirmed} of {$total} enquiries confirmed")
                ->descriptionIcon(Heroicon::OutlinedChartBar)
                ->color('gray'),
        ];
    }

    private function monthOverMonth(int $current, int $previous): string
    {
        if ($previous === 0) {
            return $current > 0 ? 'First enquiries vs last month' : 'No enquiries yet';
        }

        $change = round(($current - $previous) / $previous * 100);

        return ($change >= 0 ? "+{$change}%" : "{$change}%").' vs last month';
    }

    /**
     * @return array<int, int>
     */
    private function dailyCounts(int $days): array
    {
        $counts = Booking::where('created_at', '>=', now()->subDays($days - 1)->startOfDay())
            ->pluck('created_at')
            ->countBy(fn ($date) => $date->toDateString());

        return collect(range($days - 1, 0))
            ->map(fn (int $daysAgo) => $counts->get(now()->subDays($daysAgo)->toDateString(), 0))
            ->all();
    }
}
