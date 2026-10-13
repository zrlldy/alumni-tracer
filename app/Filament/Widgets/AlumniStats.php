<?php

namespace App\Filament\Widgets;

use App\Models\Alumni;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AlumniStats extends StatsOverviewWidget
{
    protected ?string $heading = 'Employment & tracing snapshot';

    protected function getStats(): array
    {
        $total = Alumni::count();

        $statusCounts = Alumni::query()
            ->selectRaw('employment_status, COUNT(*) as total')
            ->groupBy('employment_status')
            ->pluck('total', 'employment_status');

        $employed = (int) ($statusCounts['employed'] ?? 0);
        $unemployed = (int) ($statusCounts['unemployed'] ?? 0);
        $untraced = (int) ($statusCounts['untraced'] ?? 0);

        $percentage = fn (int $count): string => $total > 0
            ? number_format(($count / $total) * 100, 1).'% of alumni'
            : 'No alumni records';

        return [
            Stat::make(
                'Total Alumni',
                number_format($total)
            )
                ->description('All registered alumni')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),

            Stat::make(
                'Employed',
                number_format($employed)
            )
                ->description($percentage($employed))
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('success'),

            Stat::make(
                'Unemployed',
                number_format($unemployed)
            )
                ->description($percentage($unemployed))
                ->descriptionIcon('heroicon-m-user-minus')
                ->color('danger'),

            Stat::make(
                'Untraced',
                number_format($untraced)
            )
                ->description($percentage($untraced))
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('warning'),
        ];
    }
}
