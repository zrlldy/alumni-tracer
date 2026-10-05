<?php

namespace App\Filament\Resources\Alumnis\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AlumniForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Personal Information')
                    ->description('Basic information about the alumnus.')
                    ->icon('heroicon-o-user')
                    ->schema([
                        TextInput::make('student_number')
                            ->integer()
                            ->label('Student ID')
                            ->placeholder('e.g. 2020-00001')
                            ->required()
                            ->maxLength(50),

                        TextInput::make('first_name')
                            ->label('First Name')
                            ->placeholder('Enter first name')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('middle_name')
                            ->label('Middle Name')
                            ->placeholder('Enter middle name')
                            ->maxLength(100),

                        TextInput::make('last_name')
                            ->label('Last Name')
                            ->placeholder('Enter last name')
                            ->required()
                            ->maxLength(100),
                    ])
                    ->columns(2),

                Section::make('Contact Information')
                    ->description('Current contact details of the alumnus.')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        TextInput::make('email')
                            ->label('Email Address')
                            ->placeholder('alumni@example.com')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->placeholder('+63 912 345 6789')
                            ->tel()
                            ->required(),

                        TextInput::make('current_address')
                            ->label('Current Address')
                            ->placeholder('Enter current address')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Academic Information')
                    ->description('Program and graduation details.')
                    ->icon('heroicon-o-academic-cap')
                    ->schema([
                        Select::make('program_id')
                            ->label('Program')
                            ->relationship('program', 'program_name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        DatePicker::make('graduation_year')
                            ->label('Graduation Year')
                            ->placeholder('e.g. 2026')
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Employment & Tracing')
                    ->description('Employment status and alumni tracing information.')
                    ->icon('heroicon-o-briefcase')
                    ->schema([
                        Select::make('employment_status')
                            ->label('Employment Status')
                            ->placeholder('Select employment status')
                            ->options([
                                'employed' => 'Employed',
                                'unemployed' => 'Unemployed',
                                'untraced' => 'Untraced',
                            ])
                            ->native(false),

                        DatePicker::make('date_traced')
                            ->label('Date Traced')
                            ->native(false),

                        TextInput::make('trace_by')
                            ->label('Traced By')
                            ->placeholder('Enter name of tracer'),

                        TextInput::make('remarks')
                            ->label('Remarks')
                            ->placeholder('Additional notes...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
