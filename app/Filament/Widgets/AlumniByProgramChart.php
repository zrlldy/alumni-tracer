<?php

namespace App\Filament\Widgets;

use App\Support\AlumniDashboardData;
use App\Traits\RoleBasedAccessControl;
use Filament\Widgets\ChartWidget;

class AlumniByProgramChart extends ChartWidget
{
    use RoleBasedAccessControl;

    public static function canView(): bool
    {
        return static::hasWidgetAccess();
    }

    protected ?string $heading = 'Employment Status by Program';

    protected ?string $description = 'Bars show the percentage in each status, including missing or unrecognized status. Program labels show the total number of alumni, including archived programs.';

    protected ?string $maxHeight = '400px';

    public function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $programData = app(AlumniDashboardData::class)->employmentByProgram();

        return [
            'labels' => $programData['labels'],
            'datasets' => [
                ['label' => 'Employed', 'data' => $programData['percentages']['employed'], 'backgroundColor' => '#22C55E'],
                ['label' => 'Unemployed', 'data' => $programData['percentages']['unemployed'], 'backgroundColor' => '#3B82F6'],
                ['label' => 'Untraced', 'data' => $programData['percentages']['untraced'], 'backgroundColor' => '#F59E0B'],
                ['label' => 'Unknown', 'data' => $programData['percentages']['unknown'], 'backgroundColor' => '#9CA3AF'],
            ],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => false,
            'indexAxis' => 'y',
            'plugins' => [
                'legend' => ['display' => true, 'position' => 'bottom'],
                'tooltip' => ['mode' => 'index', 'intersect' => false, 'axis' => 'y'],
            ],
            'scales' => [
                'x' => ['stacked' => true, 'min' => 0, 'max' => 100],
                'y' => ['stacked' => true],
            ],
        ];
    }
}
