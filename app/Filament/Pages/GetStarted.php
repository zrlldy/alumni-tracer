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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    public static function canAccess(): bool
    {
        $user = Auth::user()?->fresh();

        if ($user === null) {
            return false;
        }

        abort_unless(
            ! SystemSecurity::isBootstrapSuperAdmin($user)
            && $user->user_allocation_id === null,
            404
        );

        return true;
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user()?->fresh();

        return $user !== null
            && ! SystemSecurity::isBootstrapSuperAdmin($user)
            && $user->user_allocation_id === null;
    }
}
