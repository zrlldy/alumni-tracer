<?php

namespace App\Filament\Pages;

use App\Support\SystemSecurity;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class GetStarted extends Page
{
    protected string $view = 'filament.pages.get-started';

    /**
     * Allow authenticated users to enter Get Started.
     *
     * The mount method will redirect users who already
     * have an accessible administrative destination.
     */
    public static function canAccess(): bool
    {
        return Auth::check();
    }

    /**
     * Automatically redirect users to their first
     * accessible administrative page.
     */
    public function mount(): void
    {
        $user = Auth::user();

        if ($user === null) {
            return;
        }

        $user->refresh();

        // Bootstrap Super Admin always returns to Dashboard.
        if (SystemSecurity::isBootstrapSuperAdmin($user)) {
            $this->redirect(Dashboard::getUrl());

            return;
        }

        $destination = Dashboard::getFirstAllowedPageUrl();

        // Redirect only when a permitted destination exists.
        if (filled($destination)) {
            $this->redirect($destination);

            return;
        }

        // Otherwise, display the Get Started page.
    }

    /**
     * Get Started is not a navigation menu item.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
