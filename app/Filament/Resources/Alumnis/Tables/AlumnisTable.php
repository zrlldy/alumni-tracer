<?php

namespace App\Filament\Resources\Alumnis\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AlumnisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student_number')
                    ->label('Student ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('middle_name')
                    ->label('Middle Name')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('phone_number')
                    ->label('Contact Number')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('current_address')
                    ->label('Address')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('program.program_name')
                    ->label('Program/Department')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('graduation_year')
                    ->label('Graduation Year')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('employment_status')
                    ->label('Employment Status')
                    ->badge()
                    ->formatStateUsing(
                        fn(?string $state): string => match ($state) {
                            'employed' => 'Employed',
                            'unemployed' => 'Unemployed',
                            'untraced' => 'Untraced',
                            default => 'Unknown',
                        }
                    )
                    ->color(
                        fn(?string $state): string => match ($state) {
                            'employed' => 'success',
                            'unemployed' => 'warning',
                            'untraced' => 'gray',
                            default => 'gray',
                        }
                    ),

                TextColumn::make('date_traced')
                    ->label('Date Traced')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('trace_by')
                    ->label('Traced By')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),

                EditAction::make(),
            ])
            ->toolbarActions([


                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
