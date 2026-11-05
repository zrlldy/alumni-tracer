<?php

namespace App\Filament\Resources\Campuses\Schemas;

use App\Models\Campus;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CampusInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Campus $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
