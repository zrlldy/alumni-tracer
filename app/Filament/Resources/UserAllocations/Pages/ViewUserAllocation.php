<?php

namespace App\Filament\Resources\UserAllocations\Pages;

use App\Filament\Resources\UserAllocations\UserAllocationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUserAllocation extends ViewRecord
{
    protected static string $resource = UserAllocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
