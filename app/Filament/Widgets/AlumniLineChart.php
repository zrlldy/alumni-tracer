<?php

namespace App\Filament\Widgets;

use App\Support\AlumniDashboardData;
use App\Traits\RoleBasedAccessControl;
use Filament\Widgets\ChartWidget;

class AlumniLineChart extends ChartWidget
{
    use RoleBasedAccessControl;

    public static function canView(): bool
    {
        return static::hasWidgetAccess();
    }

    protected ?string $heading = 'Employment Rate by Graduation Year';

    protected ?string $description = 'Percentage employed among alumni who reported being employed or unemployed. Labels show how many alumni are included in each rate.';

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $cohorts = app(AlumniDashboardData::class)->employmentByGraduationYear();

        return [
            'datasets' => [[
                'label' => 'Percent employed',
                'data' => $cohorts['rates'],
                'borderColor' => '#10b981',
                'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                'borderWidth' => 3,
                'tension' => 0.3,
                'pointRadius' => 3,
                'spanGaps' => false,
            ]],
            'labels' => $cohorts['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'min' => 0,
                    'max' => 100,
                    'ticks' => ['stepSize' => 20],
                ],
            ],
        ];
    }
}
