<?php

namespace App\Filament\Resources\Alumnis\Tables;

use App\Filament\Imports\AlumniImporter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Throwable;

class AlumnisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make('CreateAlumni')
                    ->label('Create Record')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Create Alumni')
                    ->modalDescription(
                        'Enter the information of the new alumnus.'
                    )
                    ->modalSubmitActionLabel('Create Alumni')
                    ->successNotificationTitle(
                        'Alumni created successfully'
                    ),

                Action::make('import')
                    ->label('Import Alumni')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->modalHeading('Import Alumni')
                    ->modalDescription(
                        'Upload an XLSX file to import alumni data.'
                    )
                    ->schema([
                        FileUpload::make('file')
                            ->label('Excel File')
                            ->helperText(
                                'Import only supports XLSX files.'
                            )
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        try {

                            Excel::import(
                                new AlumniImporter,
                                $data['file']
                            );

                            Notification::make()
                                ->title(
                                    'Alumni imported successfully'
                                )
                                ->body(
                                    'All alumni records were imported successfully.'
                                )
                                ->success()
                                ->send();

                        } catch (ValidationException $e) {

                            $messages = collect(
                                $e->failures()
                            )
                                ->map(function ($failure) {
                                    $studentNumber =
                                        $failure->values()['student_number']
                                        ?? 'Unknown';

                                    return sprintf(
                                        'Row %d (Student #%s): %s',
                                        $failure->row(),
                                        $studentNumber,
                                        implode(
                                            ', ',
                                            $failure->errors()
                                        )
                                    );
                                })
                                ->implode("\n");

                            Notification::make()
                                ->title('Import validation failed')
                                ->body($messages)
                                ->danger()
                                ->persistent()
                                ->send();

                        } catch (Throwable $e) {

                            Notification::make()
                                ->title('Import failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),
            ])

            ->columns([
                TextColumn::make('student_number')
                    ->label('Student ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('middle_name')
                    ->label('Middle Name')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('phone_number')
                    ->label('Contact Number')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('current_address')
                    ->label('Address')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('program.program_name')
                    ->label('Program/Department')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('graduation_year')
                    ->label('Graduation Year')
                    ->date('M d, Y')
                    ->sortable(),

                TextColumn::make('employment_status')
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
                    ),

                TextColumn::make('date_traced')
                    ->label('Date Traced')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('trace_by')
                    ->label('Traced By')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(30)
                    ->toggleable(
                        isToggledHiddenByDefault: true
                    ),
            ])

            ->filters([
                SelectFilter::make('program')
                    ->label('Department')
                    ->relationship(
                        'program',
                        'program_name'
                    )
                    ->multiple(),

                SelectFilter::make('employment_status')
                    ->options([
                        'unemployed' => 'Unemployed',
                        'employed' => 'Employed',
                        'untraced' => 'Untraced',
                    ]),

                Filter::make('graduation_year')
                    ->schema([
                        DatePicker::make('graduation_year')
                            ->label('Graduation Date'),
                    ])
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            return $query->when(
                                $data['graduation_year']
                                    ?? null,

                                fn (
                                    Builder $query,
                                    $date
                                ): Builder => $query
                                    ->whereDate(
                                        'graduation_year',
                                        $date
                                    )
                            );
                        }
                    ),

                TrashedFilter::make(),
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
