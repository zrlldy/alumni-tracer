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
    use HasWidgetGrid;
    use RoleBasedAccessControl;

    /**
     * Determine whether the user can access the dashboard.
     */
    public static function canAccess(): bool
    {
        $user = Auth::user()?->fresh();

        if ($user === null) {
            return false;
        }

        // Bootstrap Super Admin bypasses all dashboard restrictions.
        if (SystemSecurity::isBootstrapSuperAdmin($user)) {
            return true;
        }

        // Allow the initial page request so mount() can redirect.
        // Prevent subsequent Livewire requests from accessing the page.
        if ($user->user_allocation_id === null) {
            return ! request()->hasHeader('X-Livewire');
        }

        return static::hasPageAccess();
    }

    /**
     * Redirect users without an allocation to onboarding.
     */
    public function mount(): void
    {
        $user = Auth::user()?->fresh();

        if ($user === null) {
            return;
        }

        // Never redirect Bootstrap Super Admin.
        if (SystemSecurity::isBootstrapSuperAdmin($user)) {
            return;
        }

        if ($user->user_allocation_id === null) {
            $this->redirect('online/get-started');

            return;
        }
    }

    /**
     * Control dashboard navigation visibility.
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user()?->fresh();

        if ($user === null) {
            return false;
        }

        // Always show dashboard navigation for Bootstrap Super Admin.
        if (SystemSecurity::isBootstrapSuperAdmin($user)) {
            return true;
        }

        return $user->user_allocation_id !== null;
    }

    /**
     * Dashboard content.
     */
    public function content(Schema $schema): Schema
    {
        $user = Auth::user()?->fresh();

        // Bootstrap Super Admin always receives the default dashboard.
        if (
            $user !== null &&
            SystemSecurity::isBootstrapSuperAdmin($user)
        ) {
            return parent::content($schema);
        }

        $allocation = $user?->userAllocation;
        $role = $allocation?->role;

        if (
            $allocation !== null
            && ! $allocation->trashed()
            && $role !== null
            && ! $role->trashed()
            && in_array(
                $allocation->management_scope,
                [
                    'all_program',
                    'specified_program',
                    'my_program',
                    'my_department',
                ],
                true
            )
        ) {
            return parent::content($schema);
        }

        $canAccessAlumni = AlumniInformationForm::canAccess();

        return $schema->components([
            Livewire::make(AlumniInformationForm::class)
                ->key('alumni-information-form')
                ->visible($canAccessAlumni),

            Section::make('Alumni information')
                ->description(
                    'Ask the administrator to enable access to your alumni information form.'
                )
                ->visible(! $canAccessAlumni),
        ]);
    }

    /**
     * Get dashboard widgets.
     */
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
