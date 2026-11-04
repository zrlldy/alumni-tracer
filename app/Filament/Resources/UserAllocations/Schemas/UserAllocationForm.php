<?php

namespace App\Filament\Resources\UserAllocations\Schemas;

use App\Models\Program;
use App\Models\UserAllocation;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserAllocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Role Assignment')
                ->description('Select a role and define the maximum number of users allowed.')
                ->icon('heroicon-o-identification')
                ->schema([
                    Grid::make(2)->schema([

                        Select::make('role_id')
                            ->label('Role')
                            ->relationship('role', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->placeholder('Select a role')
                            ->helperText('Choose the role to assign.'),

                        TextInput::make('max_users')
                            ->label('Maximum Users')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->placeholder('e.g. 10')
                            ->helperText('Limit how many users can be assigned to this role.'),

                    ]),
                ]),

            Section::make('Data Control')
                ->description('Define the scope of data this role can access.')
                ->icon('heroicon-o-map')
                ->schema([

                    Select::make('management_scope')
                        ->label('Management Scope')
                        ->required()
                        ->live()
                        ->native(false)
                        ->options([
                            'no_access' => 'No Data',
                            'my_records' => 'My Records',
                            'my_program' => 'My Program',
                            'all_program' => 'All Programs',
                            'specified_program' => 'Specified Programs',
                        ])
                        ->default('no_access')
                        ->afterStateUpdated(function (Set $set) {
                            $set('campuses', []);
                            $set('programs', []);
                        })
                        ->helperText('Define the scope of data accessible to this role.')
                        ->rules([
                            function (Get $get, ?Model $record) {
                                return function (
                                    string $attribute,
                                    $value,
                                    Closure $fail
                                ) use ($get, $record) {

                                    // Specified program allocations are
                                    // validated through the programs field.
                                    if ($value === 'specified_program') {
                                        return;
                                    }

                                    $roleId = $get('role_id');

                                    if (blank($roleId) || blank($value)) {
                                        return;
                                    }

                                    $query = UserAllocation::query()
                                        ->where('role_id', $roleId)
                                        ->where('management_scope', $value);

                                    if ($record) {
                                        $query->whereKeyNot($record->getKey());
                                    }

                                    if ($query->exists()) {
                                        $fail(
                                            'An allocation with this role and management scope already exists.'
                                        );
                                    }
                                };
                            },
                        ]),

                    Grid::make(2)->schema([

                        /*
                         * CAMPUSES
                         */
                        Select::make('campuses')
                            ->label('Campuses')
                            ->multiple()
                            ->relationship(
                                name: 'campuses',
                                titleAttribute: 'name',
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(fn (Get $get): bool =>
                                $get('management_scope') === 'specified_program'
                            )
                            ->visible(fn (Get $get): bool =>
                                $get('management_scope') === 'specified_program'
                            )
                            ->afterStateUpdated(function (Set $set) {
                                $set('programs', []);
                            })
                            ->helperText('Select campuses to filter the available programs.'),

                        /*
                         * PROGRAMS
                         */
                        Select::make('programs')
                            ->label('Programs')
                            ->multiple()
                            ->relationship(
                                name: 'programs',
                                titleAttribute: 'program_name',
                                modifyQueryUsing: function (
                                    Builder $query,
                                    Get $get
                                ) {
                                    $campusIds = $get('campuses');

                                    if (blank($campusIds)) {
                                        $query->whereRaw('1 = 0');

                                        return;
                                    }

                                    $query->whereIn(
                                        'campus_id',
                                        (array) $campusIds
                                    );
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required(fn (Get $get): bool =>
                                $get('management_scope') === 'specified_program'
                            )
                            ->visible(fn (Get $get): bool =>
                                $get('management_scope') === 'specified_program'
                            )
                            ->helperText('Select the programs this allocation can access.')
                            ->rules([
                                function (Get $get, ?Model $record) {
                                    return function (
                                        string $attribute,
                                        $value,
                                        Closure $fail
                                    ) use ($get, $record) {

                                        if ($get('management_scope') !== 'specified_program') {
                                            return;
                                        }

                                        $roleId = $get('role_id');

                                        $campusIds = collect($get('campuses') ?? [])
                                            ->map(fn ($id) => (int) $id)
                                            ->unique()
                                            ->values()
                                            ->all();

                                        $submitted = collect($value ?? [])
                                            ->map(fn ($id) => (int) $id)
                                            ->unique()
                                            ->sort()
                                            ->values()
                                            ->all();

                                        if (blank($roleId) || empty($submitted)) {
                                            return;
                                        }

                                        /*
                                         * Ensure all selected programs
                                         * belong to selected campuses.
                                         */
                                        if (! empty($campusIds)) {
                                            $invalidProgramExists = Program::query()
                                                ->whereIn('id', $submitted)
                                                ->whereNotIn('campus_id', $campusIds)
                                                ->exists();

                                            if ($invalidProgramExists) {
                                                $fail(
                                                    'One or more selected programs do not belong to the selected campuses.'
                                                );

                                                return;
                                            }
                                        }

                                        /*
                                         * Check duplicate role and exact
                                         * program combination.
                                         */
                                        $query = UserAllocation::query()
                                            ->where('role_id', $roleId)
                                            ->where('management_scope', 'specified_program');

                                        if ($record) {
                                            $query->whereKeyNot($record->getKey());
                                        }

                                        $duplicateExists = $query
                                            ->with('programs')
                                            ->get()
                                            ->contains(function (
                                                UserAllocation $allocation
                                            ) use ($submitted) {

                                                $existing = $allocation->programs
                                                    ->pluck('id')
                                                    ->map(fn ($id) => (int) $id)
                                                    ->sort()
                                                    ->values()
                                                    ->all();

                                                return $existing === $submitted;
                                            });

                                        if ($duplicateExists) {
                                            $fail(
                                                'An allocation with this role and exact program combination already exists.'
                                            );
                                        }
                                    };
                                },
                            ]),

                    ]),
                ]),
        ]);
    }
}