<?php

use App\Filament\Resources\Programs\Pages\ListPrograms;
use App\Models\Program;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows programs in a status-focused directory', function () {
    $activeProgram = Program::query()->create([
        'program_name' => 'Bachelor of Science in Information Technology',
        'is_active' => true,
    ]);
    $inactiveProgram = Program::query()->create([
        'program_name' => 'Bachelor of Science in Business Administration',
        'is_active' => false,
    ]);

    Filament::setCurrentPanel('online');

    Livewire::test(ListPrograms::class)
        ->assertCanSeeTableRecords([$activeProgram, $inactiveProgram])
        ->assertCanRenderTableColumn('program_name')
        ->assertCanRenderTableColumn('is_active')
        ->assertTableColumnStateSet('program_name', 'Bachelor of Science in Information Technology', $activeProgram)
        ->assertSee('Program Directory')
        ->assertSee('Manage the academic programs available for alumni records.')
        ->assertSeeHtml('wire:name="App\\Filament\\Widgets\\ProgramStats"')
        ->assertSee('Academic programs')
        ->assertSee('Review program availability or open a record to update its details.')
        ->assertSee('Active')
        ->assertSee('Inactive');
});
