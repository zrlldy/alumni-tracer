<?php

namespace App\Filament\Widgets;

use App\Models\Alumni;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentlyTraced extends TableWidget
{
    protected static ?string $heading = 'Recently Traced Alumni';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn(): Builder => Alumni::query()
                    ->whereNotNull('date_traced')
                    ->latest('date_traced')
            )
            ->columns([
                TextColumn::make('student_number')
                    ->label('Student ID')
                    ->icon('heroicon-o-identification')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('full_name')
                    ->label('Alumni')
                    ->state(fn(Alumni $record): string => collect([
                        $record->first_name,
                        $record->middle_name,
                        $record->last_name,
                    ])
                        ->filter()
                        ->implode(' ')
                    )
                    ->description(fn(Alumni $record): string => $record->email)
                    ->icon('heroicon-o-user')
                    ->searchable([
                        'first_name',
                        'middle_name',
                        'last_name',
                    ])
                    ->weight('semibold'),

                TextColumn::make('program.program_name')
                    ->label('Program')
                    ->icon('heroicon-o-academic-cap')
                    ->badge()
                    ->color('gray')
                    ->placeholder('N/A')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('graduation_year')
                    ->label('Batch')
                    ->formatStateUsing(
                        fn($state) => $state
                            ? Carbon::parse($state)->format('Y')
                            : 'N/A'
                    )
                    ->icon('heroicon-o-calendar-days')
                    ->sortable(),

                TextColumn::make('employment_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn(string $state): string => ucfirst($state)
                    )
                    ->color(fn(string $state): string => match ($state) {
                        'employed' => 'success',
                        'unemployed' => 'danger',
                        'untraced' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): string => match ($state) {
                        'employed' => 'heroicon-o-check-circle',
                        'unemployed' => 'heroicon-o-x-circle',
                        'untraced' => 'heroicon-o-question-mark-circle',
                        default => 'heroicon-o-minus-circle',
                    }),

                TextColumn::make('date_traced')
                    ->label('Date Traced')
                    ->date('M d, Y')
                    ->description(
                        fn(Alumni $record): ?string => $record->date_traced
                            ? Carbon::parse($record->date_traced)->diffForHumans()
                            : null
                    )
                    ->icon('heroicon-o-clock')
                    ->sortable(),

                TextColumn::make('trace_by')
                    ->label('Traced By')
                    ->icon('heroicon-o-user-circle')
                    ->placeholder('N/A')
                    ->toggleable(),

                TextColumn::make('phone_number')
                    ->label('Contact')
                    ->icon('heroicon-o-phone')
                    ->placeholder('N/A')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('current_address')
                    ->label('Address')
                    ->icon('heroicon-o-map-pin')
                    ->limit(30)
                    ->tooltip(fn(Alumni $record): ?string => $record->current_address)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(30)
                    ->placeholder('No remarks')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date_traced', 'desc')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->striped();
    }
}
