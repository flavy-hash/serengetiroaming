<?php

namespace App\Filament\Widgets;

/**
 * Shared look for the dashboard charts. Every chart is a single series in one
 * brand green, which keeps 3:1 contrast on both the light and dark panel
 * backgrounds (Chart.js colours can't switch with the theme).
 */
class ChartStyle
{
    public const COLOR = '#5f8a3d';

    public const FILL = 'rgba(95, 138, 61, 0.12)';

    private const GRID = 'rgba(128, 128, 128, 0.15)';

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function options(array $overrides = [], bool $horizontal = false): array
    {
        $valueAxis = [
            'beginAtZero' => true,
            'ticks' => ['precision' => 0],
            'grid' => ['color' => self::GRID],
            'border' => ['display' => false],
        ];
        $categoryAxis = [
            'grid' => ['display' => false],
        ];

        return array_replace_recursive([
            'indexAxis' => $horizontal ? 'y' : 'x',
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => $horizontal ? $valueAxis : $categoryAxis,
                'y' => $horizontal ? $categoryAxis : $valueAxis,
            ],
        ], $overrides);
    }

    /**
     * @return array<string, mixed>
     */
    public static function barDataset(string $label, array $data): array
    {
        return [
            'label' => $label,
            'data' => $data,
            'backgroundColor' => self::COLOR,
            'borderRadius' => 4,
            'borderSkipped' => 'start',
            'maxBarThickness' => 32,
        ];
    }
}
