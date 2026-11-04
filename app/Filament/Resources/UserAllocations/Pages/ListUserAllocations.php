<?php

namespace App\Filament\Resources\UserAllocations\Pages;

use App\Filament\Resources\UserAllocations\UserAllocationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserAllocations extends ListRecords
{
    protected static string $resource = UserAllocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
