<?php

namespace App\Filament\Resources\UserAllocations\Tables;

use App\Models\User;
use App\Models\UserAllocation;
// use App\Traits\RoleBasedAccessControl;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UserAllocationsTable
{
    // use RoleBasedAccessControl;
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('role.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('management_scope')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'no_access' => 'No Data',
                        'my_records' => 'My Records',
                        'my_program' => 'My Program',
                        'all_program' => 'All Program',
                        'specified_program' => 'Specified Program',
                        default => $state,
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('campuses.name')
                    ->placeholder('-')
                    ->sortable(),
                TextColumn::make('programs.program_name')
                    ->placeholder('-')
                    ->badge()
                    ->color('primary')
                    ->separator(',')
                    ->sortable(),
                TextColumn::make('max_users')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function (UserAllocation $record) {
                        $usersCount = User::where('user_allocation_id', $record->id)->count();
                        $available = $record->max_users - $usersCount;
                        return $available <= 0 ? 'Full' : $available . ' available';
                    })
                    ->badge()
                    ->color(fn (string $state) => $state === 'Full' ? 'danger' : 'success'),
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
                ViewAction::make(),
                //     ->visible(fn () => self::hasPermission('view')),
                EditAction::make()
                //     ->visible(fn () => self::hasPermission('edit')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make()
                    //     ->visible(fn () => self::hasPermission('delete')),
                    // ForceDeleteBulkAction::make()
                    //     ->visible(fn () => self::hasPermission('force_delete')),
                    // RestoreBulkAction::make()
                    //     ->visible(fn () => self::hasPermission('restore')),
                ]),
            ]);
    }
}
