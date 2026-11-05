<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\Role;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RoleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('action')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('access_page')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('access_subpage')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('access_widget')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Role $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
