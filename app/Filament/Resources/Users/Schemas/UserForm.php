<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use App\Models\UserAllocation;
use Filament\Forms\Components\FileUpload;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('User Information')
                    ->description('Manage user account details and assigned role')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([

                        Select::make('user_allocation_id')
                            ->label('Role Assignment')
                            ->options(function () {
                                return UserAllocation::with('role')
                                    ->get()
                                    ->mapWithKeys(function ($allocation) {

                                        $roleName = $allocation->role?->name ?? 'No Role';

                                        return [
                                            $allocation->id => "{$roleName} • {$allocation->code}"
                                        ];
                                    });
                            })
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->placeholder('Select role assignment')
                            ->helperText('Assign a role to define access permissions')
                            ->columnSpan(1),

                        Section::make()
                            ->columns(2)
                            ->columnSpan(2)
                            ->secondary()
                            ->schema([

                                TextInput::make('name')
                                    ->label('Full Name')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Enter full name'),

                                TextInput::make('email')
                                    ->label('Email Address')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Enter email address')
                                    ->disabled(fn (?User $record) => $record && in_array($record->email, User::protectedEmails()))
                                    ->dehydrated(),

                                DateTimePicker::make('email_verified_at')
                                    ->label('Email Verified At')
                                    ->placeholder('Not verified')
                                    ->seconds(false),

                                TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->revealable()
                                    ->placeholder(fn ($record) => $record ? 'Leave blank to keep current password' : 'Enter password')
                                    ->required(fn ($record) => $record === null)
                                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                                    ->dehydrated(fn ($state) => filled($state)),

                            ]),
                    ]),
            ]);
    }
}
