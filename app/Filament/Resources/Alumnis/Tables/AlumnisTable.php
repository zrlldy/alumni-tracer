<?php

namespace App\Filament\Resources\Alumnis\Tables;

use App\Exports\AlumniImportTemplateExport;
use App\Filament\Imports\AlumniImporter;
use App\Models\Alumni;
use App\Models\User;
use App\Traits\RoleBasedAccessControl;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Validators\ValidationException;
use Throwable;

class AlumnisTable
{
    use RoleBasedAccessControl;

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
                        'Create an admin-managed alumni record. Account linking is handled separately. Education and work history are optional.'
                    )
                    ->modalSubmitActionLabel('Create Alumni')
                    ->successNotificationTitle(
                        'Alumni created successfully'
                    )
                    ->visible(fn () => self::hasPermission('create')),

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
                            Notification::make()
                                ->title('Check the Excel file')
                                ->body(self::formatImportFailures($e))
                                ->danger()
                                ->persistent()
                                ->send();
                        } catch (Throwable $e) {
                            Log::error('Alumni Excel import failed.', ['exception_class' => $e::class]);
                            Notification::make()
                                ->title('Import failed')
                                ->body('We could not import this file. Check that it uses the provided XLSX template, then try again.')
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    })
                    ->visible(fn () => self::hasPermission('import')),
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
                // ActionGroup::make([

                //     ViewAction::make()
                //         ->iconButton()
                //         ->tooltip('View alumni'),

                //     EditAction::make()
                //         ->iconButton()
                //         ->tooltip('Edit alumni'),

                // ]),
                ActionGroup::make([
                    ViewAction::make()
                        ->visible(fn () => self::hasPermission('view')),
                    EditAction::make()
                        ->visible(fn () => self::hasPermission('edit')),
                    Action::make('linkAccount')
                        ->label('Link Account')
                        ->icon('heroicon-o-link')
                        ->modalHeading('Link an Alumni Account')
                        ->modalDescription('Review the matching email or student ID, then confirm the account link. This does not create an account.')
                        ->modalSubmitActionLabel('Link Account')
                        ->schema([
                            Select::make('user_id')
                                ->label('Matching User Account')
                                ->options(fn (Alumni $record): array => self::matchingAccounts($record)
                                    ->orderBy('name')
                                    ->get(['id', 'name', 'email', 'student_id'])
                                    ->mapWithKeys(fn (User $user): array => [
                                        $user->id => collect([$user->name, $user->email, $user->student_id])
                                            ->filter()
                                            ->implode(' · '),
                                    ])
                                    ->all())
                                ->searchable()
                                ->required()
                                ->helperText('Candidates match by email or student ID. Verify the person before linking.'),
                        ])
                        ->action(function (array $data, Alumni $record): void {
                            abort_unless(self::hasPermission('edit') && self::canAccessRecord($record), 403);

                            try {
                                DB::transaction(function () use ($data, $record): void {
                                    $alumni = Alumni::withTrashed()
                                        ->lockForUpdate()
                                        ->findOrFail($record->id);

                                    if ($alumni->trashed() || $alumni->user_id !== null) {
                                        throw LaravelValidationException::withMessages([
                                            'user_id' => 'This alumni record is no longer available for linking.',
                                        ]);
                                    }

                                    abort_unless(self::canAccessRecord($alumni), 403);

                                    $user = User::query()
                                        ->lockForUpdate()
                                        ->find($data['user_id']);

                                    if (! $user || ! self::matchingAccounts($alumni)->whereKey($user->id)->exists()) {
                                        throw LaravelValidationException::withMessages([
                                            'user_id' => 'Select an active, unlinked account whose email or student ID matches this record.',
                                        ]);
                                    }

                                    $alumni->user()->associate($user);
                                    $alumni->save();
                                });
                            } catch (ModelNotFoundException) {
                                throw LaravelValidationException::withMessages([
                                    'user_id' => 'Select an active, unlinked account whose email or student ID matches this record.',
                                ]);
                            }

                            Notification::make()
                                ->title('Alumni account linked')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (Alumni $record): bool => self::hasPermission('edit')
                            && self::canAccessRecord($record)
                            && $record->user_id === null
                            && ! $record->trashed()),

                ]),
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

    private static function matchingAccounts(Alumni $alumni): Builder
    {
        $email = mb_strtolower(trim((string) $alumni->email));
        $studentNumber = (string) $alumni->student_number;

        return User::query()
            ->where(function (Builder $query) use ($email, $studentNumber): void {
                $query->whereRaw('LOWER(TRIM(email)) = ?', [$email])
                    ->orWhereRaw('TRIM(student_id) = ?', [$studentNumber]);
            })
            ->whereDoesntHave('alumnis', fn (Builder $query): Builder => $query->withTrashed());
    }

    private static function formatImportFailures(ValidationException $exception): string
    {
        $rows = collect($exception->failures())
            ->groupBy(fn (Failure $failure): int => $failure->row())
            ->map(function (Collection $failures, int|string $row): string {
                $studentNumber = trim((string) ($failures->first()->values()['student_number'] ?? ''));
                $rowLabel = "Row {$row}".($studentNumber !== '' ? " (Student #{$studentNumber})" : '');
                $fields = $failures
                    ->map(fn (Failure $failure): string => Str::headline($failure->attribute() === 'program_id' ? 'department' : $failure->attribute()))
                    ->unique()
                    ->values();
                $details = $failures
                    ->flatMap(fn (Failure $failure): array => $failure->errors())
                    ->map(function (string $message) use ($failures): ?string {
                        if (str_contains($message, 'must belong to the selected campus')) {
                            $values = $failures->first()->values();
                            $campus = Str::limit(Str::squish((string) ($values['campus'] ?? '')), 80);
                            $department = Str::limit(Str::squish((string) ($values['department'] ?? '')), 80);

                            if ($campus === '' && $department === '') {
                                return 'Department must match the selected campus.';
                            }

                            return 'Department “'.($department !== '' ? $department : '(blank)')
                                .'” does not uniquely match Campus “'.($campus !== '' ? $campus : '(blank)')
                                .'” in the active records.';
                        }

                        return match (true) {
                            str_contains($message, 'already exists') => 'Student number already exists in the system.',
                            str_contains($message, 'appears more than once') => 'Student number is repeated in this file.',
                            default => null,
                        };
                    })
                    ->filter()
                    ->unique()
                    ->values();

                return "{$rowLabel}: Check these fields: {$fields->implode(', ')}.".($details->isNotEmpty() ? ' '.$details->implode(' ') : '');
            })
            ->values();

        return "Some rows could not be imported. Check the listed fields, then upload the file again:\n\n".$rows->implode("\n");
    }
}
