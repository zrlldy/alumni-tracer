<?php

namespace App\Filament\Resources\Alumnis\Pages;

use App\Filament\Resources\Alumnis\AlumniResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAlumnis extends ListRecords
{
    protected static string $resource = AlumniResource::class;
    protected ?string $heading = 'Alumni';

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         // CreateAction::make('CreateAlumni')
    //         //     ->label('Create Alumni Record')
    //         //     ->icon('heroicon-o-plus')
    //         //     ->modalHeading('Create Alumni')
    //         //     ->modalDescription('Enter the information of the new alumnus.')
    //         //     ->modalSubmitActionLabel('Create Alumni')
    //         //     ->successNotificationTitle('Alumni created successfully'),
    //     ];
    // }
}
