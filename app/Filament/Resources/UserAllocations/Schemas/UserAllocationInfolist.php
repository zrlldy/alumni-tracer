<?php

namespace App\Filament\Resources\UserAllocations\Schemas;

use App\Models\UserAllocation;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserAllocationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Allocation Details')
                    ->description('Role, coverage scope, and user capacity.')
                    ->icon('heroicon-o-link')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('code')
                            ->label('Allocation Code')
                            ->badge()
                            ->color('gray')
                            ->copyable()
                            ->placeholder('N/A'),

                        TextEntry::make('role.name')
                            ->label('Role')
                            ->icon('heroicon-m-shield-check')
                            ->placeholder('No role assigned'),

                        TextEntry::make('management_scope')
                            ->label('Management Scope')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'no_access' => 'No Data',
                                'my_records' => 'My Records',
                                'my_program' => 'My Program',
                                'all_program' => 'All Programs',
                                'specified_program' => 'Specified Programs',
                                default => $state,
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'no_access' => 'gray',
                                'my_records', 'my_program' => 'info',
                                'all_program' => 'warning',
                                'specified_program' => 'primary',
                                default => 'gray',
                            })
                            ->placeholder('N/A'),

                        TextEntry::make('max_users')
                            ->label('Maximum Users')
                            ->numeric()
                            ->icon('heroicon-m-users')
                            ->placeholder('N/A'),
                    ]),

                Section::make('Assigned Coverage')
                    ->description('Campuses and programs included in this allocation.')
                    ->icon('heroicon-o-map')
                    ->columns(2)
                    ->visible(fn (UserAllocation $record): bool => $record->management_scope === 'specified_program')
                    ->schema([
                        TextEntry::make('campuses.name')
                            ->label('Campuses')
                            ->listWithLineBreaks()
                            ->placeholder('No campuses assigned'),

                        TextEntry::make('programs.program_name')
                            ->label('Programs')
                            ->listWithLineBreaks()
                            ->placeholder('No programs assigned'),
                    ]),

                Section::make('Record Information')
                    ->description('When this allocation record was created or last changed.')
                    ->icon('heroicon-o-clock')
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Date Created')
                            ->icon('heroicon-m-calendar-days')
                            ->dateTime('M d, Y · h:i A')
                            ->placeholder('N/A'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->icon('heroicon-m-arrow-path')
                            ->dateTime('M d, Y · h:i A')
                            ->placeholder('N/A'),

                        TextEntry::make('deleted_at')
                            ->label('Date Deleted')
                            ->icon('heroicon-m-trash')
                            ->color('danger')
                            ->dateTime('M d, Y · h:i A')
                            ->visible(fn (UserAllocation $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
