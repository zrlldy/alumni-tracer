<?php

namespace App\Filament\Resources\Campuses;

use App\Filament\Resources\Campuses\Pages\CreateCampus;
use App\Filament\Resources\Campuses\Pages\EditCampus;
use App\Filament\Resources\Campuses\Pages\ListCampuses;
use App\Filament\Resources\Campuses\Pages\ViewCampus;
use App\Filament\Resources\Campuses\Schemas\CampusForm;
use App\Filament\Resources\Campuses\Schemas\CampusInfolist;
use App\Filament\Resources\Campuses\Tables\CampusesTable;
use App\Models\Campus;
use App\Traits\RoleBasedAccessControl;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CampusResource extends Resource
{
    use RoleBasedAccessControl;

    public static function canAccess(): bool
    {
        return self::hasPageAccess();
    }

    protected static ?string $model = Campus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CampusForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CampusInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CampusesTable::configure($table);
    }

    /**
     * @param  iterable<Campus>  $records
     */
    public static function preventDeletingParents(Action $action, iterable $records): void
    {
        foreach ($records as $record) {
            if (! $record->programs()->withTrashed()->exists()
                && ! $record->userAllocations()->withTrashed()->exists()) {
                continue;
            }

            Notification::make()
                ->warning()
                ->title('Cannot delete campus')
                ->body('This campus is a parent record. Delete its child programs/departments and remove its user allocation links first, including archived records.')
                ->send();

            $action->halt();
        }
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCampuses::route('/'),
            'create' => CreateCampus::route('/create'),
            'view' => ViewCampus::route('/{record}'),
            'edit' => EditCampus::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
