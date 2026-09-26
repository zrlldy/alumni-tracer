<?php

namespace App\Filament\Resources\Alumnis\Schemas;

use App\Models\Alumni;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AlumniInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('student_number')
                    ->numeric(),
                TextEntry::make('first_name'),
                TextEntry::make('middle_name'),
                TextEntry::make('last_name'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('phone_number'),
                TextEntry::make('current_address'),
                TextEntry::make('program.id')
                    ->label('Program'),
                TextEntry::make('graduation_year'),
                TextEntry::make('employment_status')
                    ->badge()
                    ->placeholder('-'),
                TextEntry::make('remarks')
                    ->placeholder('-'),
                TextEntry::make('date_traced')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('trace_by')
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Alumni $record): bool => $record->trashed()),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
