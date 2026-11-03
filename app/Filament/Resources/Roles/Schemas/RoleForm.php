<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Role Information')
                    ->description('Define the role identity and basic details')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Grid::make(2)->schema([

                            TextInput::make('name')
                                ->label('Role Name')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn ($state, callable $set) =>
                                    $set('slug', Str::slug($state))
                                )
                                ->maxLength(100)
                                ->placeholder('e.g. HR Manager'),

                            // TextInput::make('slug')
                            //     ->label('System Identifier'),

                        ]),

                        Textarea::make('description')
                            ->label('Description')
                            ->autosize()
                            ->maxLength(500)
                            ->placeholder('Optional notes or responsibilities of this role...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Permission Matrix')
                    ->description('Control what actions this role can perform')
                    ->icon('heroicon-o-key')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('access_page')
                                ->label('Administrative Access')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->options(Role::accessPageOptions())
                                ->helperText('Advanced system privileges')
                                ->placeholder('Select page permissions')
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {

                                    $state = (array) $state;

                                    if (in_array('super_admin', $state)) {

                                        $set('access_page', ['super_admin']);
                                        $set('access_subpage', array_keys(Role::accessSubPageOptions()));
                                        $set('access_widget', array_keys(Role::accessWidgetOptions()));
                                        $set('action', array_keys(Role::actionOptions()));

                                        return;
                                    }
                                    // $set('access_subpage', []);
                                    // $set('access_widget', []);
                                    // $set('action', []);
                                }),

                            Select::make('access_subpage')
                                ->label('Sub Pages')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->options(Role::accessSubPageOptions())
                                ->helperText('Pages nested under a main administrative page')
                                ->placeholder('Select sub page permissions')
                                ->reactive()
                                ->disabled(fn (callable $get) =>
                                    in_array('super_admin', (array) $get('access_page'))
                                ),


                             Select::make('access_widget')
                                ->label('Widgets')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->options(Role::accessWidgetOptions())
                                ->helperText('Dashboard widgets visible to this role')
                                ->placeholder('Select widget permissions')
                                ->reactive()
                                ->disabled(fn (callable $get) =>
                                    in_array('super_admin', (array) $get('access_page'))
                                ),
                            
                            Select::make('action')
                                ->label('System Actions')
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->options(Role::actionOptions())
                                ->helperText('Standard CRUD permissions')
                                ->placeholder('Select actions')
                                ->disabled(fn (callable $get) =>
                                    in_array('super_admin', (array) $get('access_page'))
                                ),
                        ]),
                    ]),
            ]);
    }
}
