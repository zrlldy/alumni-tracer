<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AlumniByProgramChart;
use App\Filament\Widgets\AlumniLineChart;
use App\Filament\Widgets\AlumniStats;
use App\Filament\Widgets\EmploymentStatus;
use App\Filament\Widgets\RecentlyTraced;
use App\Filament\Widgets\TracingProgress;
use App\Livewire\AlumniInformationForm;
use App\Support\SystemSecurity;
use App\Traits\RoleBasedAccessControl;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use JohnRivera7\FilamentWidgetGrid\Concerns\HasWidgetGrid;

class Dashboard extends BaseDashboard
{
    use RoleBasedAccessControl;

    public static function canAccess(): bool
    {
        return self::hasPageAccess();
    }

    use HasWidgetGrid;

    public function content(Schema $schema): Schema
    {
        $canAccess = AlumniInformationForm::canAccess();
        $user = Auth::user();
        $allocation = $user?->userAllocation;

        if (SystemSecurity::isBootstrapSuperAdmin($user) || (
            $allocation !== null
            && ! $allocation->trashed()
            && $allocation->role !== null
            && ! $allocation->role->trashed()
            && in_array($allocation->management_scope, ['all_program', 'specified_program', 'my_program', 'my_department'], true)
        )) {
            return parent::content($schema);
        }

        return $schema->components([
            Livewire::make(AlumniInformationForm::class)
                ->key('alumni-information-form')
                ->visible($canAccess),
            Section::make('Alumni information')
                ->description('Ask the administrator to enable access to your alumni information form.')
                ->visible(! $canAccess),
        ]);
    }

    public function getWidgets(): array
    {
        return [
            AlumniStats::class,
            AlumniLineChart::class,
            AlumniByProgramChart::class,
            EmploymentStatus::class,
            TracingProgress::class,
            RecentlyTraced::class,
        ];
    }
}
