<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AlumniByProgramChart;
use App\Filament\Widgets\AlumniLineChart;
use App\Filament\Widgets\AlumniStats;
use App\Filament\Widgets\EmploymentStatus;
use App\Filament\Widgets\RecentlyTraced;
use App\Filament\Widgets\TracingProgress;
use App\Traits\RoleBasedAccessControl;
use Filament\Pages\Dashboard as BaseDashboard;
use JohnRivera7\FilamentWidgetGrid\Concerns\HasWidgetGrid;

class Dashboard extends BaseDashboard
{
    // use RoleBasedAccessControl;
    use HasWidgetGrid;

    // public static function canAccess(): bool
    // {
    //     return static::hasPageAccess();
    // }

    public function getWidgets(): array
    {
        return array_values(array_filter(
            [
                AlumniStats::class,
                AlumniLineChart::class,
                AlumniByProgramChart::class,
                EmploymentStatus::class,
                TracingProgress::class,
                RecentlyTraced::class,
            ],
            fn (string $widget): bool => $widget::canView(),
        ));
    }
}