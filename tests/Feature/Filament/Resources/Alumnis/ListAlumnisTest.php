<?php

use App\Filament\Resources\Alumnis\Pages\ListAlumnis;
use App\Models\Alumni;
use App\Models\Program;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('shows alumni in a compact summary table', function () {
    $program = Program::query()->create([
        'program_name' => 'Bachelor of Science in Information Technology',
        'is_active' => true,
    ]);
    $alumni = Alumni::factory()->for($program)->create([
        'first_name' => 'Maria',
        'middle_name' => 'Santos',
        'last_name' => 'Cruz',
        'email' => 'maria.cruz@example.test',
        'graduation_year' => '2024-06-01',
        'employment_status' => 'employed',
        'date_traced' => '2026-10-07',
        'trace_by' => 'Admin User',
    ]);

    Filament::setCurrentPanel('online');

    Livewire::test(ListAlumnis::class)
        ->assertCanSeeTableRecords([$alumni])
        ->assertCanRenderTableColumn('student_number')
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('program.program_name')
        ->assertCanRenderTableColumn('graduation_year')
        ->assertCanRenderTableColumn('employment_status')
        ->assertCanRenderTableColumn('date_traced')
        ->assertTableColumnStateSet('name', 'Maria Santos Cruz', $alumni)
        ->assertSee('Alumni Directory')
        ->assertSee('Search records, update tracer information, and monitor alumni outcomes.')
        ->assertSeeHtml('wire:name="App\\Filament\\Widgets\\AlumniStats"')
        ->assertSee('Alumni records')
        ->assertSee('Open a record for complete information, or use search and filters to narrow the directory.')
        ->assertSee('maria.cruz@example.test')
        ->assertSee('Admin User');
});
