<?php

namespace App\Filament\Widgets;

use App\Support\AlumniDashboardData;
use App\Traits\RoleBasedAccessControl;
use Filament\Widgets\ChartWidget;

class EmploymentStatus extends ChartWidget
{
    use RoleBasedAccessControl;

    public static function canView(): bool
    {
        return static::hasWidgetAccess();
    }

    protected ?string $heading = 'Reported Employment Status';

    protected ?string $description = 'Number of alumni in each reported status and its share of all active alumni.';

    protected ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $summary = app(AlumniDashboardData::class)->summary();
        $statuses = $summary['statuses'];
        $total = $summary['total'];
        $names = [
            'employed' => 'Employed',
            'unemployed' => 'Unemployed',
            'untraced' => 'Untraced',
            'unknown' => 'Unknown',
        ];

        return [
            'datasets' => [[
                'label' => 'Alumni',
                'data' => array_values($statuses),
                'backgroundColor' => ['#22C55E', '#EF4444', '#F59E0B', '#9CA3AF'],
                'borderWidth' => 0,
                'borderRadius' => 6,
            ]],
            'labels' => array_map(
                fn (string $status): string => $names[$status].' ('.($total > 0
                    ? number_format(($statuses[$status] / $total) * 100, 1)
                    : '0.0').'%)',
                array_keys($names),
            ),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }
}
