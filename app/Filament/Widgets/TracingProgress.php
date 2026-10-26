<?php

namespace App\Filament\Widgets;

use App\Models\Alumni;
use Filament\Widgets\ChartWidget;

class TracingProgress extends ChartWidget
{
    protected ?string $heading = 'Tracing Progress';

    protected ?string $description = 'Progress of traced and untraced alumni';

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
        $statusCounts = Alumni::query()
            ->selectRaw('employment_status, COUNT(*) as total')
            ->groupBy('employment_status')
            ->pluck('total', 'employment_status');

        $employed = (int)($statusCounts['employed'] ?? 0);
        $unemployed = (int)($statusCounts['unemployed'] ?? 0);
        $untraced = (int)($statusCounts['untraced'] ?? 0);

        $traced = $employed + $unemployed;

        $total = $traced + $untraced;

        $tracedPercentage = $total > 0
            ? round(($traced / $total) * 100, 1)
            : 0;

        $untracedPercentage = $total > 0
            ? round(($untraced / $total) * 100, 1)
            : 0;

        return [
            'datasets' => [
                [
                    'label' => 'Tracing Progress',
                    'data' => [
                        $tracedPercentage,
                        $untracedPercentage,
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
                "Traced ({$tracedPercentage}%)",
                "Untraced ({$untracedPercentage}%)",
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
