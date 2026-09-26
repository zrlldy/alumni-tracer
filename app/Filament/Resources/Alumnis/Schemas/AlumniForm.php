<?php

namespace App\Filament\Resources\Alumnis\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AlumniForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('student_number')
                    ->required()
                    ->numeric(),
                TextInput::make('first_name')
                    ->required(),
                TextInput::make('middle_name')
                    ->required(),
                TextInput::make('last_name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('phone_number')
                    ->tel()
                    ->required(),
                TextInput::make('current_address')
                    ->required(),
                Select::make('program_id')
                    ->relationship('program', 'id')
                    ->required(),
                TextInput::make('graduation_year')
                    ->required(),
                Select::make('employment_status')
                    ->options(['unemployed,employed' => 'Unemployed,employed', 'untraced' => 'Untraced'])
                    ->default(null),
                TextInput::make('remarks')
                    ->default(null),
                DatePicker::make('date_traced'),
                TextInput::make('trace_by')
                    ->default(null),
            ]);
    }
}
