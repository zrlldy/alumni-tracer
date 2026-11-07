<?php

namespace App\Filament\Widgets;

use App\Support\AlumniDashboardData;
use App\Traits\RoleBasedAccessControl;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AlumniStats extends StatsOverviewWidget
{
    use RoleBasedAccessControl;

    public static function canView(): bool
    {
        return static::hasWidgetAccess();
    }

    protected ?string $heading = 'Reported Status & Tracing Snapshot';

    protected function getStats(): array
    {
        $summary = app(AlumniDashboardData::class)->summary();
        $employmentRate = $summary['employment_rate'];
        $knownEmploymentTotal = $summary['known_employment_total'];
        $employmentRateValue = $employmentRate === null ? 'Unavailable' : number_format($employmentRate, 1).'%';
        $employmentRateDescription = $employmentRate === null
            ? 'No alumni have reported being employed or unemployed'
            : $summary['statuses']['employed'].' employed out of '.$knownEmploymentTotal.' alumni who reported employed or unemployed';
        $tracingCoverage = $summary['tracing_coverage'] === null
            ? 'Unavailable'
            : number_format($summary['tracing_coverage'], 1).'%';
        $tracingDescription = $summary['total'] > 0
            ? $summary['traced'].' of '.$summary['total'].' alumni traced'
            : 'No alumni records to trace';

        return [
            Stat::make(
                'Total Alumni',
                number_format($summary['total'])
            )
                ->description('All registered alumni')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('primary'),

            Stat::make('Employment Rate', $employmentRateValue)
                ->description($employmentRateDescription)
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('success'),

            Stat::make('Alumni Traced', $tracingCoverage)
                ->description($tracingDescription)
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('warning'),

            Stat::make(
                'Missing or Unrecognized Status',
                number_format($summary['statuses']['unknown'])
            )
                ->description('Includes blank or unrecognized status values')
                ->descriptionIcon('heroicon-m-question-mark-circle')
                ->color('gray'),
        ];
    }
}
