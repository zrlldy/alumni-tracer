<?php

namespace App\Filament\Resources\Programs\Tables;

use App\Traits\RoleBasedAccessControl;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProgramsTable
{
    use RoleBasedAccessControl;
    public static function configure(Table $table): Table
    {
        return $table
            ->heading('Academic programs')
            ->description('Review program availability or open a record to update its details.')
            ->columns([
                TextColumn::make('program_name')
                    ->label('Program Name')
                    ->icon('heroicon-o-academic-cap')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->icon(fn (bool $state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-pause-circle')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('deleted_at')
                    ->label('Date Deleted')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Date Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Date Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->placeholder('All'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                // ActionGroup::make([

                //     ViewAction::make()
                //         ->iconButton()
                //         ->tooltip('View program'),
                //     EditAction::make()
                //         ->iconButton()
                //         ->tooltip('Edit program'),

                // ]),
                ActionGroup::make([
                    ViewAction::make()
                        ->visible(fn () => self::hasPermission('view')),
                    EditAction::make()
                        ->visible(fn () => self::hasPermission('edit')),

                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('program_name')
            ->striped()
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateHeading('No programs yet')
            ->emptyStateDescription('Create a program to make it available when adding alumni records.');
    }
}
