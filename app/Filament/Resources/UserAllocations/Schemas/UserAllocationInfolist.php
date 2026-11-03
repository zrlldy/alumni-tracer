<?php

namespace App\Filament\Resources\UserAllocations\Schemas;

use App\Models\UserAllocation;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserAllocationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('role_id')
                    ->numeric(),
                TextEntry::make('management_scope'),
                TextEntry::make('code'),
                TextEntry::make('max_users')
                    ->numeric(),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (UserAllocation $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
