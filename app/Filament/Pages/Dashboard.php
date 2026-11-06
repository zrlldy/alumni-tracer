<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AlumniByProgramChart;
use App\Filament\Widgets\AlumniLineChart;
use App\Filament\Widgets\AlumniStats;
use App\Filament\Widgets\EmploymentStatus;
use App\Filament\Widgets\RecentlyTraced;
use App\Filament\Widgets\TracingProgress;
use App\Support\SystemSecurity;
use App\Traits\RoleBasedAccessControl;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard as BaseDashboard;
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
        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        $user->refresh();

        // Bootstrap Super Admin always has access.
        if (SystemSecurity::isBootstrapSuperAdmin($user)) {
            return true;
        }

        // Allow the initial request so mount() can redirect
        // users who have no administrative Dashboard access.
        if (static::shouldRedirectToGetStarted($user)) {
            return ! request()->hasHeader('X-Livewire');
        }

        return static::hasPageAccess();
    }

    /**
     * Determine whether the user must be redirected to Get Started.
     *
     * Uses the dynamic UserAllocation -> Role relationship.
     */
        
    /**
     * Shared check for users who should be redirected to Get Started.
     *
     * Uses the current UserAllocation and its assigned Role.
     */
    public static function shouldRedirectToGetStarted(): bool
    {
        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        $user->refresh();

        // Bootstrap Super Admin never needs onboarding.
        if (SystemSecurity::isBootstrapSuperAdmin($user)) {
            return false;
        }

        $allocation = $user->userAllocation;
        $role = $allocation?->role;

        // Missing or inactive allocation.
        if (
            $user->user_allocation_id === null
            || $allocation === null
            || $allocation->trashed()
        ) {
            return true;
        }

        // Missing or inactive role.
        if (
            $role === null
            || $role->trashed()
        ) {
            return true;
        }

        // Redirect when the assigned role does not grant Dashboard access.
        return ! static::hasPageAccess();
    }


    /**
     * Redirect users without Dashboard administrative access.
     */
    public function mount(): void
    {
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $user->refresh();

        // Never redirect Bootstrap Super Admin.
        if (SystemSecurity::isBootstrapSuperAdmin($user)) {
            return;
        }

        if (static::shouldRedirectToGetStarted($user)) {
            $this->redirect('online/get-started');

            return;
        }
    }

    /**
     * Control dashboard navigation visibility.
     */
    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        $user->refresh();

        if (SystemSecurity::isBootstrapSuperAdmin($user)) {
            return true;
        }

        return ! static::shouldRedirectToGetStarted($user);
    }

    /**
     * Dynamic dashboard heading.
     */
    public function getHeading(): string
    {
        $user = Auth::user();

        if ($user === null) {
            return parent::getHeading();
        }

        $user->refresh();

        $allocation = $user->userAllocation;

        if (
            ! SystemSecurity::isBootstrapSuperAdmin($user)
            && $allocation !== null
            && ! $allocation->trashed()
            && $allocation->management_scope === 'my_records'
        ) {
            return 'My alumni dashboard';
        }

        return parent::getHeading();
    }

    /**
     * Dashboard widgets.
     *
     * Each widget follows its own access_widget permission.
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



    /**
     * Find the first page or resource the current user is allowed to access.
     *
     * Follows the currently assigned UserAllocation and Role.
     */
    public static function getFirstAllowedPageUrl(): ?string
    {
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        $user->refresh();

        $panel = Filament::getCurrentPanel();

        if ($panel === null) {
            return null;
        }

        // Bootstrap Super Admin always goes to Dashboard.
        if (SystemSecurity::isBootstrapSuperAdmin($user)) {
            return static::getUrl(panel: $panel->getId());
        }

        $allocation = $user->userAllocation;
        $role = $allocation?->role;

        // Require a valid allocation and active role.
        if (
            $allocation === null
            || $allocation->trashed()
            || $role === null
            || $role->trashed()
        ) {
            return null;
        }

        $allowedPages = (array) ($role->access_page ?? []);

        $hasSuperAdminAccess = in_array(
            'super_admin',
            $allowedPages,
            true
        );

        /*
        * 1. Check registered Filament pages.
        */
        foreach ($panel->getPages() as $page) {

            // Never redirect back to Get Started.
            if ($page === GetStarted::class) {
                continue;
            }

            $slug = $page::getSlug();

            // Check the role's administrative permissions.
            if (
                ! $hasSuperAdminAccess
                && ! in_array($slug, $allowedPages, true)
            ) {
                continue;
            }

            // Respect the page's own authorization.
            if (
                method_exists($page, 'canAccess')
                && ! $page::canAccess()
            ) {
                continue;
            }

            return $page::getUrl(
                panel: $panel->getId()
            );
        }

        /*
        * 2. Check registered Filament resources.
        */
        foreach ($panel->getResources() as $resource) {

            $slug = $resource::getSlug();

            if (
                ! $hasSuperAdminAccess
                && ! in_array($slug, $allowedPages, true)
            ) {
                continue;
            }

            // Respect resource-level authorization.
            if (
                method_exists($resource, 'canAccess')
                && ! $resource::canAccess()
            ) {
                continue;
            }

            $resourcePages = $resource::getPages();

            if (empty($resourcePages)) {
                continue;
            }

            // Prefer the List / Index page.
            $pageName = array_key_exists('index', $resourcePages)
                ? 'index'
                : array_key_first($resourcePages);

            if (blank($pageName)) {
                continue;
            }

            return $resource::getUrl(
                $pageName,
                panel: $panel->getId()
            );
        }

        // No permitted administrative destination.
        return null;
    }


}
