<?php

namespace App\Filament\Resources\Alumnis\Pages;

use App\Filament\Resources\Alumnis\AlumniResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Override;

class CreateAlumni extends CreateRecord
{
    protected static string $resource = AlumniResource::class;

    #[Override]
    public function getHeading(): string
    {
        return "Alumni Record Creation";
    }
}
