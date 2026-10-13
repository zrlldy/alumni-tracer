<?php

namespace App\Filament\Resources\Alumnis\Pages;

use App\Filament\Resources\Alumnis\AlumniResource;
use App\Filament\Widgets\AlumniStats;
use Filament\Resources\Pages\ListRecords;

class ListAlumnis extends ListRecords
{
    protected static string $resource = AlumniResource::class;

    protected ?string $heading = 'Alumni Directory';

    protected ?string $subheading = 'Search records, update tracer information, and monitor alumni outcomes.';

    protected function getHeaderWidgets(): array
    {
        return [
            AlumniStats::class,
        ];
    }
}
