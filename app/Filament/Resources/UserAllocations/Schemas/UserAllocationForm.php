<?php

namespace App\Filament\Resources\UserAllocations\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserAllocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('role_id')
                    ->required()
                    ->numeric(),
                TextInput::make('management_scope')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                TextInput::make('max_users')
                    ->required()
                    ->numeric(),
            ]);
    }
}
