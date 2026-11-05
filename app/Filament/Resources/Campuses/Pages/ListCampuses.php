<?php

namespace App\Filament\Resources\Campuses\Pages;

use App\Filament\Resources\Campuses\CampusResource;
use App\Traits\RoleBasedAccessControl;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCampuses extends ListRecords
{
    use RoleBasedAccessControl;
    protected static string $resource = CampusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => self::hasPermission('create')),
        ];
    }
}
