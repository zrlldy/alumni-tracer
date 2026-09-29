<?php

namespace App\Filament\Resources\Programs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProgramForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('program_name')
                    ->label('Program Name')
                    ->placeholder('E.g. Bachelor of Science in Computer Science')
                    ->required(),
                Toggle::make('is_active')
                    ->label('Status')
                    ->required()
                    ->inline(false),
            ]);

    }
}
