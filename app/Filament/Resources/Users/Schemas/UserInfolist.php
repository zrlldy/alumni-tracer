<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Information')
                    ->description('User identity and email verification details.')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Full Name')
                            ->icon('heroicon-m-user')
                            ->placeholder('N/A'),

                        TextEntry::make('student_id')
                            ->label('Student ID')
                            ->icon('heroicon-m-identification')
                            ->copyable()
                            ->placeholder('Not provided'),

                        TextEntry::make('email')
                            ->label('Email Address')
                            ->icon('heroicon-m-envelope')
                            ->copyable()
                            ->placeholder('N/A'),

                        TextEntry::make('email_verified_at')
                            ->label('Email Verified At')
                            ->icon('heroicon-m-check-circle')
                            ->dateTime('M d, Y · h:i A')
                            ->placeholder('Not verified'),
                    ]),

                Section::make('Profile & Sign-in')
                    ->description('Profile picture and connected Google account.')
                    ->icon('heroicon-o-identification')
                    ->columns(2)
                    ->schema([
                        ImageEntry::make('avatar_img')
                            ->label('Profile Picture')
                            ->disk('public')
                            ->imageHeight(96)
                            ->circular(),

                        TextEntry::make('google_id')
                            ->label('Google Account ID')
                            ->icon('heroicon-m-at-symbol')
                            ->copyable()
                            ->placeholder('Not connected'),
                    ]),

                Section::make('Record Information')
                    ->description('When this account record was created or last changed.')
                    ->icon('heroicon-o-clock')
                    ->columns(3)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Date Created')
                            ->icon('heroicon-m-calendar-days')
                            ->dateTime('M d, Y · h:i A')
                            ->placeholder('N/A'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->icon('heroicon-m-arrow-path')
                            ->dateTime('M d, Y · h:i A')
                            ->placeholder('N/A'),

                        TextEntry::make('deleted_at')
                            ->label('Date Deleted')
                            ->icon('heroicon-m-trash')
                            ->color('danger')
                            ->dateTime('M d, Y · h:i A')
                            ->visible(fn (User $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
