<?php

namespace App\Filament\Resources\Campuses\Schemas;

use App\Models\Campus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CampusInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Campus Information')
                    ->description('Campus name and current availability.')
                    ->icon('heroicon-o-building-library')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Campus Name')
                            ->icon('heroicon-m-building-library')
                            ->placeholder('N/A'),

                        TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                            ->icon(fn (bool $state): string => $state ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle'),
                    ]),

                Section::make('Record Information')
                    ->description('When this campus record was created or last changed.')
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
                            ->visible(fn (Campus $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
