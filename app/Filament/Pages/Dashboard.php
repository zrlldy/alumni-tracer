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
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use JohnRivera7\FilamentWidgetGrid\Concerns\HasWidgetGrid;

class Dashboard extends BaseDashboard
{
    use HasWidgetGrid;

    public function content(Schema $schema): Schema
    {
        $user = Auth::user();
        $allocation = $user?->userAllocation;
        $role = $allocation?->role;

        if (SystemSecurity::isBootstrapSuperAdmin($user) || (
            $allocation !== null
            && ! $allocation->trashed()
            && $role !== null
            && ! $role->trashed()
            && in_array($allocation->management_scope, ['all_program', 'specified_program', 'my_program', 'my_department'], true)
        )) {
            return parent::content($schema);
        }

        $canAccessAlumni = AlumniInformationForm::canAccess();

        return $schema->components([
            Livewire::make(AlumniInformationForm::class)
                ->key('alumni-information-form')
                ->visible($canAccessAlumni),
            Section::make('Alumni information')
                ->description('Ask the administrator to enable access to your alumni information form.')
                ->visible(! $canAccessAlumni),
        ]);
    }

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
