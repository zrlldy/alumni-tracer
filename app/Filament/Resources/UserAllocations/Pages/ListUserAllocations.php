<?php

namespace App\Filament\Resources\UserAllocations\Pages;

use App\Filament\Resources\UserAllocations\UserAllocationResource;
use App\Traits\RoleBasedAccessControl;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserAllocations extends ListRecords
{
    use RoleBasedAccessControl;
    protected static string $resource = UserAllocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => self::hasPermission('create')),
        ];
    }
}
