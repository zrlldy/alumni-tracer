<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\Employee;
use App\Models\User;
use App\Traits\RoleBasedAccessControl;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    use RoleBasedAccessControl;
    public static function configure(Table $table): Table
    {
        return $table
            ->checkIfRecordIsSelectableUsing(
                fn (User $record) => ! in_array($record->email, User::protectedEmails()),
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('avatar_img')
                    ->label('Profile Pic')
                    ->disk('public')
                    ->extraAttributes(['class' => 'cursor-pointer'])
                    ->action(
                        Action::make('viewImage')
                            ->modalWidth('md')
                            ->modalHeading('Profile Picture')
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Close')
                            ->modalContent(fn ($record) => view('filament.modals.image-preview', [
                                'image' => $record->avatar_img
                                    ? asset('storage/' . $record->avatar_img)
                                    : null,
                            ]))
                    ),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->icon(Heroicon::Envelope)
                    ->iconPosition(IconPosition::After)
                    ->iconColor('primary') 
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('userAllocation.role.name')
                    ->label('Role')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($state, $record) => 
                        ($record->userAllocation?->role?->name ?? 'No Role') 
                        . ' • ' 
                        . ($record->userAllocation?->code ?? '')
                    )
                    ->badge()
                    ->color('success'),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->visible(fn () => self::hasPermission('view')),
                    EditAction::make()
                        ->visible(fn () => self::hasPermission('edit')),
                    DeleteAction::make()
                        ->visible(fn (User $record) => 
                            self::hasPermission('delete') && ! in_array($record->email, User::protectedEmails())
                        )
                        ->before(function (User $record) {
                            if (in_array($record->email, User::protectedEmails())) {
                                Notification::make()
                                    ->danger()
                                    ->title('Action blocked')
                                    ->body('This account is protected and cannot be deleted.')
                                    ->send();

                                throw new Halt();
                            }
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => self::hasPermission('delete'))
                        // ->accessSelectedRecords()
                        ->before(function (Collection $records) {
                            $protected = $records->whereIn('email', User::protectedEmails());

                            if ($protected->isNotEmpty()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Action blocked')
                                    ->body('One or more selected accounts are protected and cannot be deleted.')
                                    ->send();

                                throw new Halt();
                            }
                        }),
                    ForceDeleteBulkAction::make()
                        ->visible(fn () => self::hasPermission('force_delete'))
                        // ->accessSelectedRecords()
                        ->before(function (Collection $records) {
                            $protected = $records->whereIn('email', User::protectedEmails());

                            if ($protected->isNotEmpty()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Action blocked')
                                    ->body('One or more selected accounts are protected and cannot be deleted.')
                                    ->send();

                                throw new Halt();
                            }
                        }),
                    RestoreBulkAction::make()
                        ->visible(fn () => self::hasPermission('restore')),
                ]),
            ]);
    }
}
