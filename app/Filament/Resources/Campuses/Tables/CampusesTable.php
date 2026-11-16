<?php

namespace App\Filament\Resources\Campuses\Tables;

use App\Filament\Resources\Campuses\CampusResource;
use App\Traits\RoleBasedAccessControl;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class CampusesTable
{
    use RoleBasedAccessControl;

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('programs.program_name')
                    ->label('Programs / Departments')
                    ->listWithLineBreaks()
                    ->placeholder('No programs / departments'),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->visible(fn () => self::hasPermission('view')),
                    EditAction::make()
                        ->visible(fn () => self::hasPermission('edit')),

                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(fn (Action $action, Collection $records) => CampusResource::preventDeletingParents($action, $records)),
                    ForceDeleteBulkAction::make()
                        ->before(fn (Action $action, Collection $records) => CampusResource::preventDeletingParents($action, $records)),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
