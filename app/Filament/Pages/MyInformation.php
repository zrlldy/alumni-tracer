<?php

namespace App\Filament\Pages;

use App\Livewire\AlumniInformationForm;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class MyInformation extends Page
{
    protected static ?string $slug = 'my-information';

    protected static ?string $title = 'My Information';

    protected static ?string $navigationLabel = 'My Information';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInformationCircle;

    protected string $view = 'filament.pages.my-information';

    /**
     * Reuse the Livewire component's authorization so the page and component
     * enforce exactly the same role, scope, and action rules.
     */
    public static function canAccess(): bool
    {
        return AlumniInformationForm::canAccess();
    }

    /**
     * Only show the navigation link when the assigned role grants access.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
