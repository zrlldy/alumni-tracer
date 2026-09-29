<?php

namespace App\Filament\Resources\Programs\Schemas;

use App\Models\Program;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Program Information')
                    ->description('Basic information and current status of the program.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        TextEntry::make('program_name')
                            ->label('Program Name')
                            ->icon('heroicon-o-academic-cap')
                            ->placeholder('N/A'),

                        TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(
                                fn (bool $state): string => $state
                                    ? 'Active'
                                    : 'Inactive'
                            )
                            ->color(
                                fn (bool $state): string => $state
                                    ? 'success'
                                    : 'danger'
                            )
                            ->icon(
                                fn (bool $state): string => $state
                                    ? 'heroicon-o-check-circle'
                                    : 'heroicon-o-x-circle'
                            ),
                    ])
                    ->columns(2),

                Section::make('Record Information')
                    ->description('Information about when this program record was created or modified.')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Date Created')
                            ->icon('heroicon-o-calendar')
                            ->dateTime('M d, Y · h:i A')
                            ->placeholder('N/A'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->icon('heroicon-o-arrow-path')
                            ->dateTime('M d, Y · h:i A')
                            ->placeholder('N/A'),

                        TextEntry::make('deleted_at')
                            ->label('Date Deleted')
                            ->icon('heroicon-o-trash')
                            ->color('danger')
                            ->dateTime('M d, Y · h:i A')
                            ->visible(
                                fn (Program $record): bool => $record->trashed()
                            ),
                    ])
                    ->columns(2),
            ]);
    }
}
