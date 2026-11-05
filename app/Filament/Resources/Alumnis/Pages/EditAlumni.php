<?php

namespace App\Filament\Resources\Alumnis\Pages;

use App\Filament\Resources\Alumnis\AlumniResource;
use App\Traits\RoleBasedAccessControl;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAlumni extends EditRecord
{
    use RoleBasedAccessControl;
    protected static string $resource = AlumniResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->visible(fn () => self::hasPermission('view')),
            DeleteAction::make()
                ->visible(fn () => self::hasPermission('delete')),
            ForceDeleteAction::make()
                ->visible(fn ($record) => self::hasPermission('force_delete') && $record->trashed()),
            RestoreAction::make()
                ->visible(fn ($record) => self::hasPermission('restore') && $record->trashed()),
        ];
    }
}
