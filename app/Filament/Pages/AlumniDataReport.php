<?php

namespace App\Filament\Pages;

use App\Exports\AlumniDataExport;
use App\Models\Alumni;
use App\Models\Program;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class AlumniDataReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $title = 'Alumni Data Report';

    protected static ?string $navigationLabel = 'Alumni Data Report';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.alumni-data-report';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportXlsx')
                ->label('Export Alumni Data')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $graduationYear = $this->selectedGraduationYear();

                    return Excel::download(
                        new AlumniDataExport(
                            graduationYear: $graduationYear,
                            programId: $this->selectedDepartmentId(),
                            employmentStatus: $this->selectedEmploymentStatus(),
                        ),
                        $graduationYear === null
                            ? 'alumni-data-report.xlsx'
                            : "alumni-data-report-{$graduationYear}.xlsx"
                    );
                }),

        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Alumni::query()
                    ->with('program')
                    ->orderBy('id')
            )
            ->filters([
                Filter::make('graduation_year')
                    ->label('Graduation Year')
                    ->schema([
                        Select::make('year')
                            ->label('Graduation Year')
                            ->options(fn(): array => $this->graduationYearOptions())
                            ->placeholder('All years'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $year = $data['year'] ?? null;

                        if (! is_string($year) || ! ctype_digit($year) || strlen($year) !== 4) {
                            return $query;
                        }

                        return $query->graduatedInYear((int) $year);
                    }),
                Filter::make('department')
                    ->label('Department')
                    ->schema([
                        Select::make('program_id')
                            ->label('Department')
                            ->options(fn(): array => $this->departmentOptions())
                            ->searchable()
                            ->placeholder('All departments'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $programId = $data['program_id'] ?? null;

                        if (! is_string($programId) || ! ctype_digit($programId)) {
                            return $query;
                        }

                        return $query->forProgram((int) $programId);
                    }),
                Filter::make('employment_status')
                    ->label('Employment Status')
                    ->schema([
                        Select::make('status')
                            ->label('Employment Status')
                            ->options(fn(): array => $this->employmentStatusOptions())
                            ->placeholder('All employment statuses'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $employmentStatus = $data['status'] ?? null;

                        if (! is_string($employmentStatus) || ! array_key_exists($employmentStatus, $this->employmentStatusOptions())) {
                            return $query;
                        }

                        return $query->withEmploymentStatus($employmentStatus);
                    }),
            ])
            ->columns([
                TextColumn::make('student_number')
                    ->label('Student Number')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Alumni')
                    ->state(fn(Alumni $record): string => $this->alumniName($record))
                    ->description(fn(Alumni $record): ?string => $record->email)
                    ->searchable(['first_name', 'middle_name', 'last_name'])
                    ->sortable(['first_name', 'last_name'])
                    ->weight('medium'),
                TextColumn::make('program.program_name')
                    ->label('Department')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable()
                    ->limit(28)
                    ->tooltip(fn(?string $state): ?string => $state),
                TextColumn::make('graduation_year')
                    ->label('Graduated')
                    ->date('Y')
                    ->sortable(),
                TextColumn::make('employment_status')
                    ->label('Employment Status')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'employed' => 'Employed',
                        'unemployed' => 'Unemployed',
                        'untraced' => 'Untraced',
                        default => 'Not specified',
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        'employed' => 'success',
                        'unemployed' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('date_traced')
                    ->label('Last Traced')
                    ->date('M d, Y')
                    ->description(fn(Alumni $record): string => $record->trace_by ?? 'Not traced')
                    ->placeholder('Not traced'),
            ])
            ->recordActions([
                Action::make('viewDetails')
                    ->label('View details')
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->tooltip('View complete alumni record')
                    ->modalHeading(fn(Alumni $record): string => "Alumni details: {$this->alumniName($record)}")
                    ->modalWidth(Width::FourExtraLarge)
                    ->modal()
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(function (Alumni $record): View {
                        $record->loadMissing(['program', 'alumniEducation', 'alumniEmployment']);

                        return view('filament.pages.alumni-report-details', ['alumni' => $record]);
                    }),
            ])
            ->defaultSort('id')
            ->striped()
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateHeading('No alumni records')
            ->emptyStateDescription('Alumni records with education and employment history will appear here.');
    }

    /**
     * @return array<string, string>
     */
    private function graduationYearOptions(): array
    {
        return Alumni::query()
            ->orderByDesc('graduation_year')
            ->pluck('graduation_year')
            ->map(fn(string $graduationDate): string => str($graduationDate)->before('-')->toString())
            ->unique()
            ->mapWithKeys(fn(string $year): array => [$year => $year])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function departmentOptions(): array
    {
        return Program::query()
            ->orderBy('program_name')
            ->pluck('program_name', 'id')
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function employmentStatusOptions(): array
    {
        return [
            'employed' => 'Employed',
            'unemployed' => 'Unemployed',
            'untraced' => 'Untraced',
        ];
    }

    private function selectedGraduationYear(): ?int
    {
        $year = $this->getTableFilterState('graduation_year')['year'] ?? null;

        if (! is_string($year) || ! ctype_digit($year) || strlen($year) !== 4) {
            return null;
        }

        return (int) $year;
    }

    private function selectedDepartmentId(): ?int
    {
        $programId = $this->getTableFilterState('department')['program_id'] ?? null;

        if (! is_string($programId) || ! ctype_digit($programId)) {
            return null;
        }

        return (int) $programId;
    }

    private function selectedEmploymentStatus(): ?string
    {
        $employmentStatus = $this->getTableFilterState('employment_status')['status'] ?? null;

        if (! is_string($employmentStatus) || ! array_key_exists($employmentStatus, $this->employmentStatusOptions())) {
            return null;
        }

        return $employmentStatus;
    }

    private function alumniName(Alumni $alumni): string
    {
        return collect([
            $alumni->first_name,
            $alumni->middle_name,
            $alumni->last_name,
        ])->filter()->implode(' ');
    }
}
