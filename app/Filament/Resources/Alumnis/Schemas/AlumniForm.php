<?php

namespace App\Filament\Resources\Alumnis\Schemas;

use App\Models\Alumni;
use App\Models\Campus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AlumniForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...self::informationSections(),
                self::tracingSection(),
                ...self::historySections(),
            ]);
    }

    /**
     * @return array<Section>
     */
    private static function historySections(): array
    {
        return [
            Section::make('Education History')
                ->description('Optional. Add one or more education records.')
                ->icon('heroicon-o-academic-cap')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Repeater::make('alumniEducation')
                        ->relationship(modifyRecordsUsing: fn (Collection $records): Collection => $records->whereNull('deleted_at'))
                        ->defaultItems(0)
                        ->addActionLabel('Add education record')
                        ->itemLabel(fn (array $state): ?string => filled($state['instituion'] ?? null) ? $state['instituion'] : 'Education record')
                        ->deletable(false)
                        ->schema([
                            TextInput::make('instituion')->label('Institution')->live(onBlur: true)->required()->maxLength(255),
                            TextInput::make('program')->label('Program or Course')->required()->maxLength(255),
                            TextInput::make('degree_level')->label('Degree Level')->required()->maxLength(255),
                            TextInput::make('units_completed')->numeric()->integer()->minValue(0)->maxValue(4294967295)->required(),
                            TextInput::make('status')->label('Study Status')->required()->maxLength(255),
                            DatePicker::make('started_at')->label('Start Date')->required(),
                            DatePicker::make('ended_at')->label('End Date')->required()->afterOrEqual('started_at'),
                        ])
                        ->columns(2),
                ]),

            Section::make('Employment History')
                ->description('Optional. Add jobs, including concurrent employment.')
                ->icon('heroicon-o-briefcase')
                ->collapsible()
                ->collapsed()
                ->schema([
                    Repeater::make('alumniEmployment')
                        ->relationship(modifyRecordsUsing: fn (Collection $records): Collection => $records->whereNull('deleted_at'))
                        ->defaultItems(0)
                        ->addActionLabel('Add employment record')
                        ->itemLabel(fn (array $state): ?string => filled($state['company_name'] ?? null) ? $state['company_name'] : 'Employment record')
                        ->deletable(false)
                        ->schema([
                            TextInput::make('company_name')->label('Company or Organization')->live(onBlur: true)->required()->maxLength(255),
                            TextInput::make('position')->maxLength(255),
                            TextInput::make('company_address')->required()->maxLength(255),
                            TextInput::make('industry')->maxLength(255),
                            TextInput::make('employment_type')->required()->maxLength(255),
                            Select::make('is_course_related')->label('Related to the alumnus’s course?')
                                ->options([1 => 'Yes', 0 => 'No'])
                                ->placeholder('Not specified'),
                            DatePicker::make('date_hired')->label('Date Hired')->required(),
                            DatePicker::make('starting_date')->label('Work Start Date'),
                            DatePicker::make('ended_at')->label('Work End Date')->afterOrEqual('date_hired')->afterOrEqual('starting_date'),
                            TextInput::make('supported_documents')->label('Supporting Document Reference')
                                ->helperText('Enter an existing document reference or URL.')
                                ->required()->maxLength(255),
                            Toggle::make('is_current')->label('Currently works here')->default(false)->required(),
                        ])
                        ->columns(2),
                ]),
        ];
    }

    /**
     * @return array<Section>
     */
    public static function informationSections(bool $lockIdentity = false): array
    {
        return [
            Section::make('Personal Information')
                ->description('Basic information about the alumnus.')
                ->icon('heroicon-o-user')
                ->schema([
                    TextInput::make('student_number')
                        ->integer()
                        ->minValue(0)
                        ->maxValue(4294967295)
                        ->disabled($lockIdentity)
                        ->label('Student ID')
                        ->placeholder('e.g. 20240001')
                        ->required()
                        ->maxLength(50),

                    TextInput::make('first_name')
                        ->disabled($lockIdentity)
                        ->label('First Name')
                        ->placeholder('Enter first name')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('middle_name')
                        ->disabled($lockIdentity)
                        ->label('Middle Name')
                        ->placeholder('Enter middle name')
                        ->maxLength(100),

                    TextInput::make('last_name')
                        ->disabled($lockIdentity)
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
                        ->maxLength(255)
                        ->required(),

                    TextInput::make('current_address')
                        ->label('Current Address')
                        ->placeholder('Enter current address')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Academic Information')
                ->description('Program and graduation details.')
                ->icon('heroicon-o-academic-cap')
                ->schema([
                    Select::make('campus_id')
                        ->disabled($lockIdentity)
                        ->label('Campus')
                        ->options(fn (): array => Campus::query()
                            ->when($lockIdentity, fn (Builder $query): Builder => $query->withTrashed())
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required()
                        ->dehydrated(false)
                        ->afterStateHydrated(fn (Select $component, ?Alumni $record) => $component->state($record?->program?->campus_id))
                        ->afterStateUpdated(fn (Set $set) => $set('program_id', null)),

                    Select::make('program_id')
                        ->disabled($lockIdentity)
                        ->label('Department')
                        ->relationship(
                            'program',
                            'program_name',
                            modifyQueryUsing: fn (Builder $query, Get $get): Builder => $query
                                ->where('campus_id', $get('campus_id'))
                                ->when($lockIdentity, fn (Builder $query): Builder => $query->withTrashed()),
                        )
                        ->searchable()
                        ->preload()
                        ->disabled(fn (Get $get): bool => blank($get('campus_id')) && ! $lockIdentity)
                        ->required(),

                    DatePicker::make('graduation_year')
                        ->disabled($lockIdentity)
                        ->label('Graduation Year')
                        ->placeholder('e.g. 2026')
                        ->required(),
                ])
                ->columns(2),
        ];
    }

    public static function tracingSection(): Section
    {
        return Section::make('Employment Status & Tracing')
            ->description('Record the reported employment status and tracing details separately from job history.')
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
            ->columns(2);
    }
}
