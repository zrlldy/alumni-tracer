<?php

namespace App\Filament\Resources\Programs\Pages;

use App\Filament\Resources\Programs\ProgramResource;
use App\Models\Program;
use App\Traits\RoleBasedAccessControl;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditProgram extends EditRecord
{
    use RoleBasedAccessControl;

    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->visible(fn () => self::hasPermission('view')),
            DeleteAction::make()
                ->before(fn (Action $action, Program $record) => ProgramResource::preventDeletingParents($action, [$record]))
                ->visible(fn () => self::hasPermission('delete')),
            ForceDeleteAction::make()
                ->before(fn (Action $action, Program $record) => ProgramResource::preventDeletingParents($action, [$record]))
                ->visible(fn ($record) => self::hasPermission('force_delete') && $record->trashed()),
            RestoreAction::make()
                ->visible(fn ($record) => self::hasPermission('restore') && $record->trashed()),
        ];
    }
}
