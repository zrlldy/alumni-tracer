<?php

namespace App\Filament\Resources\Programs\Pages;

use App\Filament\Resources\Programs\ProgramResource;
use App\Filament\Widgets\ProgramStats;
use App\Traits\RoleBasedAccessControl;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrograms extends ListRecords
{
    use RoleBasedAccessControl;
    protected static string $resource = ProgramResource::class;

    protected ?string $heading = 'Program Directory';

    protected ?string $subheading = 'Manage the academic programs available for alumni records.';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => self::hasPermission('create')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProgramStats::class,
        ];
    }
}
