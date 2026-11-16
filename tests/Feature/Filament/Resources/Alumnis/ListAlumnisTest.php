<?php

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Alumnis\AlumniResource;
use App\Filament\Resources\Alumnis\Pages\ListAlumnis;
use App\Models\Alumni;
use App\Models\Campus;
use App\Models\Program;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAllocation;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createAlumniDirectoryStaff(): User
{
    $role = Role::query()->create([
        'name' => 'Directory Staff',
        'action' => ['view'],
        'access_page' => [AlumniResource::getSlug(), Dashboard::getSlug()],
        'access_subpage' => [],
        'access_widget' => [],
    ]);
    $allocation = UserAllocation::query()->create([
        'role_id' => $role->id,
        'management_scope' => 'all_program',
        'max_users' => 10,
    ]);

    return User::factory()->create(['user_allocation_id' => $allocation->id]);
}

it('shows alumni in a compact summary table', function () {
    $staff = createAlumniDirectoryStaff();
    $program = Program::factory()->for(Campus::factory())->create([
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

    Livewire::actingAs($staff)
        ->test(ListAlumnis::class)
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

it('does not repeat the middle name when it is already at the end of the first name', function () {
    $staff = createAlumniDirectoryStaff();
    $program = Program::factory()->for(Campus::factory())->create([
        'program_name' => 'Bachelor of Science in Information Technology',
        'is_active' => true,
    ]);
    $alumni = Alumni::factory()->for($program)->create([
        'first_name' => 'Maria Santos',
        'middle_name' => 'Santos',
        'last_name' => 'Cruz',
    ]);

    Filament::setCurrentPanel('online');

    Livewire::actingAs($staff)
        ->test(ListAlumnis::class)
        ->assertTableColumnStateSet('name', 'Maria Santos Cruz', $alumni);
});
