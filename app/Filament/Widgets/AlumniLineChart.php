<?php

namespace App\Filament\Widgets;

use App\Models\Alumni;
use Filament\Widgets\ChartWidget;

class AlumniLineChart extends ChartWidget
{
    protected ?string $heading = 'Employment Status';

    protected function getData(): array
    {
        $years = range(2015, now()->year);

        $employed = [];
        $unemployed = [];
        $untraced = [];

        foreach ($years as $year) {
            $employed[] = Alumni::whereYear('graduation_year', $year)
                ->where('employment_status', 'employed')
                ->count();

            $unemployed[] = Alumni::whereYear('graduation_year', $year)
                ->where('employment_status', 'unemployed')
                ->count();

            $untraced[] = Alumni::whereYear('graduation_year', $year)
                ->where('employment_status', 'untraced')
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Employed',
                    'data' => $employed,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'pointRadius' => 3,
                    'fill' => true,
                ],

                [
                    'label' => 'Unemployed',
                    'data' => $unemployed,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.05)',
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'pointRadius' => 3,
                    'fill' => true,
                ],

                [
                    'label' => 'Untraced',
                    'data' => $untraced,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.05)',
                    'borderWidth' => 2,
                    'borderDash' => [6, 6],
                    'tension' => 0.4,
                    'pointRadius' => 3,
                    'fill' => true,
                ],
            ],

            'labels' => $years,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
