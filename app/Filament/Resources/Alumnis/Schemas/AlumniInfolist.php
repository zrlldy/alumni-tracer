<?php

namespace App\Filament\Resources\Alumnis\Schemas;

use App\Models\Alumni;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AlumniInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Personal Information')
                    ->description(
                        'Basic information and contact details of the alumnus.'
                    )
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([

                        TextEntry::make('student_number')
                            ->label('Student ID')
                            ->icon('heroicon-m-identification')
                            ->badge()
                            ->color('gray')
                            ->copyable(),

                        TextEntry::make('first_name')
                            ->label('First Name')
                            ->icon('heroicon-m-user')
                            ->placeholder('-'),

                        TextEntry::make('middle_name')
                            ->label('Middle Name')
                            ->placeholder('-'),

                        TextEntry::make('last_name')
                            ->label('Last Name')
                            ->placeholder('-'),

                        TextEntry::make('email')
                            ->label('Email Address')
                            ->icon('heroicon-m-envelope')
                            ->copyable()
                            ->copyMessage('Email copied')
                            ->placeholder('-'),

                        TextEntry::make('phone_number')
                            ->label('Contact Number')
                            ->icon('heroicon-m-phone')
                            ->copyable()
                            ->placeholder('-'),

                        TextEntry::make('current_address')
                            ->label('Current Address')
                            ->icon('heroicon-m-map-pin')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Academic Information')
                    ->description(
                        'Program and graduation information.'
                    )
                    ->icon('heroicon-o-academic-cap')
                    ->columns(2)
                    ->schema([

                        TextEntry::make('program.program_name')
                            ->label('Department')
                            ->icon('heroicon-m-building-library')
                            ->placeholder('-'),

                        TextEntry::make('graduation_year')
                            ->label('Graduation Date')
                            ->icon('heroicon-m-calendar-days')
                            ->date('M d, Y')
                            ->placeholder('-'),
                    ]),

                Section::make('Employment Information')
                    ->description(
                        'Current employment and alumni tracing status.'
                    )
                    ->icon('heroicon-o-briefcase')
                    ->columns(2)
                    ->schema([

                        TextEntry::make('employment_status')
                            ->label('Employment Status')
                            ->badge()
                            ->formatStateUsing(
                                fn (?string $state): string => match ($state) {
                                    'employed' => 'Employed',
                                    'unemployed' => 'Unemployed',
                                    'untraced' => 'Untraced',
                                    default => 'Unknown',
                                }
                            )
                            ->color(
                                fn (?string $state): string => match ($state) {
                                    'employed' => 'success',
                                    'unemployed' => 'warning',
                                    'untraced' => 'gray',
                                    default => 'gray',
                                }
                            )
                            ->icon(
                                fn (?string $state): string => match ($state) {
                                    'employed' => 'heroicon-m-briefcase',
                                    'unemployed' => 'heroicon-m-clock',
                                    'untraced' => 'heroicon-m-question-mark-circle',
                                    default => 'heroicon-m-minus-circle',
                                }
                            )
                            ->placeholder('-'),

                        TextEntry::make('date_traced')
                            ->label('Date Traced')
                            ->icon('heroicon-m-calendar')
                            ->date('M d, Y')
                            ->placeholder('-'),

                        TextEntry::make('trace_by')
                            ->label('Traced By')
                            ->icon('heroicon-m-user-circle')
                            ->placeholder('-'),

                        TextEntry::make('remarks')
                            ->label('Remarks')
                            ->icon('heroicon-m-chat-bubble-left-ellipsis')
                            ->placeholder('No remarks')
                            ->columnSpanFull(),
                    ]),

                Section::make('Record Information')
                    ->description(
                        'System information related to this alumni record.'
                    )
                    ->icon('heroicon-o-information-circle')
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
                    ->schema([

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->icon('heroicon-m-plus-circle')
                            ->dateTime('M d, Y • h:i A')
                            ->placeholder('-'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->icon('heroicon-m-pencil-square')
                            ->dateTime('M d, Y • h:i A')
                            ->placeholder('-'),

                        TextEntry::make('deleted_at')
                            ->label('Deleted At')
                            ->icon('heroicon-m-trash')
                            ->color('danger')
                            ->dateTime('M d, Y • h:i A')
                            ->visible(
                                fn (Alumni $record): bool => $record->trashed()
                            ),
                    ]),
            ]);
    }
}
