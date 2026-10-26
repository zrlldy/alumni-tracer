<?php

namespace App\Filament\Widgets;

use App\Models\Alumni;
use App\Models\Program;
use Filament\Widgets\ChartWidget;

class AlumniByProgramChart extends ChartWidget
{
    protected ?string $heading = 'Alumni by Program';

    protected ?string $description = 'Employment status distribution per program';

    protected ?string $maxHeight = '400px';

    public function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $programs = Program::query()
            ->whereHas('alumni')
            ->orderBy('program_name')
            ->get();

        $counts = Alumni::query()
            ->selectRaw('program_id, employment_status, COUNT(*) as total')
            ->groupBy('program_id', 'employment_status')
            ->get();

        $employed = $programs->map(function ($program) use ($counts) {
            return (int)($counts
                ->where('program_id', $program->id)
                ->where('employment_status', 'employed')
                ->first()?->total ?? 0);
        });

        $unemployed = $programs->map(function ($program) use ($counts) {
            return (int)($counts
                ->where('program_id', $program->id)
                ->where('employment_status', 'unemployed')
                ->first()?->total ?? 0);
        });

        $untraced = $programs->map(function ($program) use ($counts) {
            return (int)($counts
                ->where('program_id', $program->id)
                ->where('employment_status', 'untraced')
                ->first()?->total ?? 0);
        });

        return [
            'labels' => $programs
                ->pluck('program_name')
                ->toArray(),

            'datasets' => [
                [
                    'label' => 'Employed',
                    'data' => $employed->toArray(),
                    'backgroundColor' => '#22C55E',
                    'borderColor' => '#16A34A',
                    'borderWidth' => 1,
                    'borderRadius' => 5,
                    'borderSkipped' => false,
                    'barThickness' => 45,
                    'maxBarThickness' => 55,
                ],

                [
                    'label' => 'Unemployed',
                    'data' => $unemployed->toArray(),
                    'backgroundColor' => '#3B82F6',
                    'borderColor' => '#2563EB',
                    'borderWidth' => 1,
                    'borderRadius' => 5,
                    'borderSkipped' => false,
                    'barThickness' => 45,
                    'maxBarThickness' => 55,
                ],

                [
                    'label' => 'Untraced',
                    'data' => $untraced->toArray(),
                    'backgroundColor' => '#F59E0B',
                    'borderColor' => '#D97706',
                    'borderWidth' => 1,
                    'borderRadius' => 5,
                    'borderSkipped' => false,
                    'barThickness' => 45,
                    'maxBarThickness' => 55,
                ],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,

            'interaction' => [
                'mode' => 'index',
                'intersect' => false,
            ],

            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',

                    'labels' => [
                        'usePointStyle' => true,
                        'pointStyle' => 'rectRounded',
                        'padding' => 20,
                    ],
                ],

                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                ],
            ],

            'scales' => [
                'x' => [
                    'stacked' => true,

                    'grid' => [
                        'display' => false,
                    ],

                    'ticks' => [
                        'maxRotation' => 0,
                        'minRotation' => 0,
                        'autoSkip' => false,
                    ],
                ],

                'y' => [
                    'stacked' => true,
                    'beginAtZero' => true,

                    'ticks' => [
                        'precision' => 0,
                        'stepSize' => 1,
                    ],

                    'grid' => [
                        'color' => 'rgba(156, 163, 175, 0.15)',
                    ],

                    'border' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }
}
