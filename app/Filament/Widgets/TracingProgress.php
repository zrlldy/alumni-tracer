<?php

namespace App\Filament\Widgets;

use App\Support\AlumniDashboardData;
use App\Traits\RoleBasedAccessControl;
use Filament\Widgets\ChartWidget;

class TracingProgress extends ChartWidget
{
    use RoleBasedAccessControl;

    public static function canView(): bool
    {
        return static::hasWidgetAccess();
    }

    protected ?string $heading = 'Tracing Progress';

    protected ?string $description = 'Shows how many alumni have a staff tracing date recorded.';

    protected ?string $maxHeight = '300px';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $summary = app(AlumniDashboardData::class)->summary();

        if ($summary['total'] === 0) {
            return [];
        }

        $notTracedPercentage = round(($summary['not_traced'] / $summary['total']) * 100, 1);

        return [
            'datasets' => [
                [
                    'label' => 'Tracing Progress',
                    'data' => [
                        $summary['traced'],
                        $summary['not_traced'],
                    ],

                    'backgroundColor' => [
                        '#22C55E',
                        '#F59E0B',
                    ],

                    'borderWidth' => 0,
                    'borderRadius' => 6,
                    'spacing' => 3,
                    'hoverOffset' => 6,
                ],
            ],

            'labels' => [
                'Traced: '.$summary['traced'].' of '.$summary['total'].' alumni ('.number_format($summary['tracing_coverage'], 1).'%)',
                'Not yet traced: '.$summary['not_traced'].' of '.$summary['total'].' alumni ('.number_format($notTracedPercentage, 1).'%)',
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,

            'cutout' => '72%',

            'layout' => [
                'padding' => [
                    'top' => 10,
                    'right' => 12,
                    'bottom' => 4,
                    'left' => 12,
                ],
            ],

            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'align' => 'center',

                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'circle',
                        'boxWidth' => 8,
                        'boxHeight' => 8,
                        'padding' => 18,

                        'font' => [
                            'size' => 12,
                            'weight' => 500,
                        ],
                    ],
                ],

                'tooltip' => [
                    'enabled' => true,
                    'padding' => 12,
                    'cornerRadius' => 8,
                ],
            ],
        ];
    }
}
