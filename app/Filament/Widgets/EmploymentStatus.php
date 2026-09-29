<?php

namespace App\Filament\Widgets;

use App\Models\Alumni;
use Filament\Widgets\ChartWidget;

class EmploymentStatus extends ChartWidget
{
    protected ?string $heading = 'Employment Status';

    protected ?string $description = 'Distribution of alumni employment status';

    protected ?string $maxHeight = '300px';

    // Responsive widget width
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

        return [
            'datasets' => [
                [
                    'label' => 'Alumni',

                    'data' => [
                        $employed,
                        $unemployed,
                        $untraced,
                    ],

                    'backgroundColor' => [
                        '#22C55E',
                        '#EF4444',
                        '#F59E0B',
                    ],

                    'borderWidth' => 0,
                    'borderRadius' => 6,
                    'spacing' => 3,
                    'hoverOffset' => 6,
                ],
            ],

            'labels' => [
                "Employed {$employed}",
                "Unemployed {$unemployed}",
                "Untraced {$untraced}",
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,

            // Very important for responsive Filament widgets
            'maintainAspectRatio' => false,

            // Slightly thinner / cleaner doughnut
            'cutout' => '72%',

            // Gives the chart space from the card edges
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

                        // Less bulky legend
                        'boxWidth' => 8,
                        'boxHeight' => 8,

                        // Space between legend entries
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

                    'titleFont' => [
                        'size' => 13,
                    ],

                    'bodyFont' => [
                        'size' => 12,
                    ],

                    'cornerRadius' => 8,
                ],
            ],
        ];
    }
}
