<?php

namespace App\Filament\Widgets;

use App\Models\Program;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProgramStats extends StatsOverviewWidget
{
    protected ?string $heading = 'Program status snapshot';

    protected function getStats(): array
    {
        $statusCounts = Program::query()
            ->selectRaw('is_active, COUNT(*) as total')
            ->groupBy('is_active')
            ->pluck('total', 'is_active');

        $active = (int) ($statusCounts[1] ?? 0);
        $inactive = (int) ($statusCounts[0] ?? 0);

        return [
            Stat::make('Total Programs', number_format($active + $inactive))
                ->description('All current programs')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),
            Stat::make('Active', number_format($active))
                ->description('Available for alumni records')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Inactive', number_format($inactive))
                ->description('Unavailable for new records')
                ->descriptionIcon('heroicon-m-pause-circle')
                ->color('gray'),
        ];
    }
}
