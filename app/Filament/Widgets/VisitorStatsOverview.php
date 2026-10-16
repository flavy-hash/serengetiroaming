<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Anonymous visitors and page views: today compared with the last 7 days.
 */
class VisitorStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 6;

    protected ?string $heading = 'Website visitors';

    protected ?string $description = 'Anonymous statistics — no cookies, no IP addresses.';

    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $week = now()->subDays(6)->startOfDay();

        // The visitor ID changes daily, so a week's visitors are the sum of each day's.
        $daily = PageView::where('created_at', '>=', $week)
            ->get(['visitor_hash', 'created_at'])
            ->groupBy(fn (PageView $v) => $v->created_at->toDateString());

        $visitorsToday = $daily->get($today->toDateString(), collect())->unique('visitor_hash')->count();
        $visitorsWeek = $daily->sum(fn ($views) => $views->unique('visitor_hash')->count());
        $viewsToday = $daily->get($today->toDateString(), collect())->count();
        $viewsWeek = $daily->sum(fn ($views) => $views->count());

        $trend = collect(range(6, 0))
            ->map(fn (int $i) => $daily->get(now()->subDays($i)->toDateString(), collect())->unique('visitor_hash')->count())
            ->all();

        return [
            Stat::make('Visitors today', number_format($visitorsToday))
                ->description(number_format($visitorsWeek).' in the last 7 days')
                ->descriptionIcon(Heroicon::OutlinedUsers)
                ->chart($trend)
                ->color('success'),
            Stat::make('Page views today', number_format($viewsToday))
                ->description(number_format($viewsWeek).' in the last 7 days')
                ->descriptionIcon(Heroicon::OutlinedEye),
            Stat::make('Pages per visitor (7 days)', $visitorsWeek ? number_format($viewsWeek / $visitorsWeek, 1) : '—')
                ->description('Average pages each visitor looked at')
                ->descriptionIcon(Heroicon::OutlinedDocumentDuplicate),
        ];
    }
}
