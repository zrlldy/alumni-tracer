<?php

namespace App\Filament\Resources\UserAllocations;

use App\Filament\Clusters\UserManagement\UserManagementCluster;
use App\Filament\Resources\UserAllocations\Pages\CreateUserAllocation;
use App\Filament\Resources\UserAllocations\Pages\EditUserAllocation;
use App\Filament\Resources\UserAllocations\Pages\ListUserAllocations;
use App\Filament\Resources\UserAllocations\Pages\ViewUserAllocation;
use App\Filament\Resources\UserAllocations\Schemas\UserAllocationForm;
use App\Filament\Resources\UserAllocations\Schemas\UserAllocationInfolist;
use App\Filament\Resources\UserAllocations\Tables\UserAllocationsTable;
use App\Models\UserAllocation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserAllocationResource extends Resource
{

    protected static ?string $cluster = UserManagementCluster::class;

    protected static ?string $model = UserAllocation::class;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Link;

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return UserAllocationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserAllocationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserAllocationsTable::configure($table);
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
            'index' => ListUserAllocations::route('/'),
            'create' => CreateUserAllocation::route('/create'),
            'view' => ViewUserAllocation::route('/{record}'),
            'edit' => EditUserAllocation::route('/{record}/edit'),
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
