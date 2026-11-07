<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\Role;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RoleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Role Information')
                    ->description('Role name and summary.')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Role Name')
                            ->icon('heroicon-m-identification')
                            ->placeholder('N/A'),

                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('No description provided')
                            ->columnSpanFull(),
                    ]),

                Section::make('Permissions')
                    ->description('Access and actions assigned to this role.')
                    ->icon('heroicon-o-key')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('action')
                            ->label('System Actions')
                            ->formatStateUsing(fn (string $state): string => self::formatPermissionLabel($state, Role::actionOptions()))
                            ->listWithLineBreaks()
                            ->placeholder('No actions assigned'),

                        TextEntry::make('access_page')
                            ->label('Administrative Pages')
                            ->formatStateUsing(fn (string $state): string => self::formatPermissionLabel($state, Role::accessPageOptions()))
                            ->listWithLineBreaks()
                            ->placeholder('No page access assigned'),

                        TextEntry::make('access_subpage')
                            ->label('Subpages')
                            ->formatStateUsing(fn (string $state): string => self::formatPermissionLabel($state, Role::accessSubPageOptions()))
                            ->listWithLineBreaks()
                            ->placeholder('No subpage access assigned'),

                        TextEntry::make('access_widget')
                            ->label('Dashboard Widgets')
                            ->formatStateUsing(fn (string $state): string => self::formatPermissionLabel($state, Role::accessWidgetOptions()))
                            ->listWithLineBreaks()
                            ->placeholder('No widgets assigned'),
                    ]),

                Section::make('Record Information')
                    ->description('When this role record was created or last changed.')
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
                            ->visible(fn (Role $record): bool => $record->trashed()),
                    ]),
            ]);
    }

    /**
     * @param  array<string, string>  $options
     */
    private static function formatPermissionLabel(string $permission, array $options): string
    {
        return $options[$permission] ?? Str::headline($permission);
    }
}
