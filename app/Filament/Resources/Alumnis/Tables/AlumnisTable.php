<?php

namespace App\Filament\Resources\Alumnis\Tables;

use App\Exports\AlumniImportTemplateExport;
use App\Filament\Imports\AlumniImporter;
use App\Models\Alumni;
use Carbon\Carbon;
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
                    ->label('Create Alumni')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->modalHeading('Create Alumni Record')
                    ->modalDescription(
                        'Enter the information of the new alumnus below.'
                    )
                    ->modalSubmitActionLabel('Create Alumni')
                    ->successNotificationTitle(
                        'Alumni created successfully'
                    ),

                Action::make('import')
                    ->label('Import Excel')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('gray')
                    ->outlined()
                    ->modalHeading('Import Alumni Records')
                    ->modalDescription(
                        'Upload an XLSX file containing alumni information.'
                    )
                    ->extraModalFooterActions([
                        Action::make('downloadTemplate')
                            ->label('Download Template')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->color('gray')
                            // missing the url path for the Download template for importing the alumnis
                            ->action(
                                fn () => Excel::download(new AlumniImportTemplateExport, 'alumni-template.xlsx')
                            )
                            ->openUrlInNewTab(false),
                    ])
                    ->schema([
                        FileUpload::make('file')
                            ->label('Excel File')
                            ->helperText(
                                'Only XLSX Excel files are supported.'
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
            ->heading('Alumni records')
            ->description('Open a record for complete information, or use search and filters to narrow the directory.')

            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('program'))
            ->columns([
                TextColumn::make('student_number')
                    ->label('Student ID')
                    ->icon('heroicon-m-identification')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Alumni')
                    ->icon('heroicon-m-user')
                    ->state(fn (Alumni $record): string => collect([
                        $record->first_name,
                        $record->middle_name,
                        $record->last_name,
                    ])->filter()->implode(' '))
                    ->description(fn (Alumni $record): ?string => $record->email)
                    ->searchable(['first_name', 'middle_name', 'last_name', 'email', 'phone_number'])
                    ->sortable(['first_name', 'last_name'])
                    ->weight('medium'),

                TextColumn::make('program.program_name')
                    ->label('Department')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->limit(28)
                    ->tooltip(fn (?string $state): ?string => $state),

                TextColumn::make('graduation_year')
                    ->label('Graduated')
                    ->icon('heroicon-m-calendar-days')
                    ->date('Y')
                    ->sortable(),

                TextColumn::make('employment_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            'employed' => 'Employed',
                            'unemployed' => 'Unemployed',
                            'untraced' => 'Untraced',
                            default => 'Unknown',
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
                    ->color(
                        fn (?string $state): string => match ($state) {
                            'employed' => 'success',
                            'unemployed' => 'warning',
                            'untraced' => 'gray',
                            default => 'gray',
                        }
                    ),

                TextColumn::make('date_traced')
                    ->label('Last Traced')
                    ->icon('heroicon-m-calendar')
                    ->date('M d, Y')
                    ->description(fn (Alumni $record): string => $record->trace_by ?? 'Not traced')
                    ->placeholder('Not traced')
                    ->sortable(),
            ])

            ->filters([
                SelectFilter::make('program')
                    ->label('Program / Department')
                    ->relationship(
                        'program',
                        'program_name'
                    )
                    ->multiple(),

                SelectFilter::make('employment_status')
                    ->label('Employment Status')
                    ->options([
                        'employed' => 'Employed',
                        'unemployed' => 'Unemployed',
                        'untraced' => 'Untraced',
                    ]),

                Filter::make('graduation_year')
                    ->schema([
                        DatePicker::make('graduation_year')
                            ->label('Graduation Date')
                            ->placeholder(Carbon::now())
                            ->closeOnDateSelection(true)
                            ->native(false),
                    ])
                    ->query(
                        function (
                            Builder $query,
                            array $data
                        ): Builder {
                            return $query->when(
                                $data['graduation_year'] ?? null,

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
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('View alumni'),

                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit alumni'),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->striped()
            ->defaultSort(
                'created_at',
                'desc'
            )
            ->emptyStateIcon(
                'heroicon-o-user-group'
            )
            ->emptyStateHeading(
                'No alumni records'
            )

            ->emptyStateDescription(
                'Create a new alumni record or import records from an Excel file.'
            );
    }
}
