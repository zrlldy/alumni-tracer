<?php

use App\Filament\Resources\Programs\Pages\EditProgram;
use App\Filament\Resources\Programs\Pages\ListPrograms;
use App\Filament\Resources\Programs\ProgramResource;
use App\Models\Alumni;
use App\Models\Campus;
use App\Models\Program;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAllocation;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel('online');
});

function createProgramStaff(array $actions = ['view', 'edit', 'delete', 'force_delete']): User
{
    $role = Role::query()->create([
        'name' => 'Program Staff',
        'action' => $actions,
        'access_page' => [ProgramResource::getSlug()],
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

function attachProgramChild(Program $program, string $childType, bool $archived): void
{
    if ($childType === 'alumni') {
        Alumni::factory()->for($program)->create(['user_id' => null, 'deleted_at' => $archived ? now() : null]);

        return;
    }

    $allocation = auth()->user()->userAllocation;
    $allocation->programs()->attach($program);

    if ($archived) {
        $allocation->delete();
    }
}

function programDeletionWarning(): Notification
{
    return Notification::make()
        ->warning()
        ->title('Cannot delete program')
        ->body('This program is a parent record. Delete its child alumni records and remove its user allocation links first, including archived records.');
}

dataset('program deletion actions', [
    'delete' => ['delete', false],
    'force delete' => ['forceDelete', true],
]);

dataset('program children', [
    'active alumni' => ['alumni', false],
    'archived alumni' => ['alumni', true],
    'allocation link' => ['allocation', false],
    'archived allocation link' => ['allocation', true],
]);

it('distinguishes same-named programs using their campus, including an archived campus', function () {
    $staff = createProgramStaff();
    $northCampus = Campus::factory()->create(['name' => 'North Campus']);
    $southCampus = Campus::factory()->create(['name' => 'South Campus', 'deleted_at' => now()]);
    $northProgram = Program::factory()->for($northCampus)->create(['program_name' => 'Computer Science']);
    $southProgram = Program::factory()->for($southCampus)->create(['program_name' => 'Computer Science']);

    Livewire::actingAs($staff)->test(ListPrograms::class)
        ->assertCanSeeTableRecords([$northProgram, $southProgram])
        ->assertTableColumnStateSet('campus.name', 'North Campus', $northProgram)
        ->assertTableColumnStateSet('campus.name', 'South Campus', $southProgram)
        ->assertSee('North Campus')
        ->assertSee('South Campus');
});

it('blocks a parent program header deletion with a helpful toast', function (string $action, bool $trashed, string $childType, bool $archived) {
    $staff = createProgramStaff();
    $this->actingAs($staff);
    $program = Program::factory()->create(['deleted_at' => $trashed ? now() : null]);
    attachProgramChild($program, $childType, $archived);

    Livewire::test(EditProgram::class, ['record' => $program->getRouteKey()])
        ->callAction($action)
        ->assertNotified(programDeletionWarning());

    $this->assertDatabaseHas('programs', ['id' => $program->id, 'deleted_at' => $program->getRawOriginal('deleted_at')]);
    $this->assertDatabaseCount($childType === 'alumni' ? 'alumnis' : 'program_user_allocations', 1);
})->with('program deletion actions')->with('program children');

it('blocks the whole bulk selection when a program has children', function (string $action, bool $trashed, string $childType, bool $archived) {
    $staff = createProgramStaff();
    $this->actingAs($staff);
    $freeProgram = Program::factory()->create(['deleted_at' => $trashed ? now() : null]);
    $parentProgram = Program::factory()->create(['deleted_at' => $trashed ? now() : null]);
    attachProgramChild($parentProgram, $childType, $archived);

    Livewire::test(ListPrograms::class)
        ->filterTable('trashed', $trashed ? true : null)
        ->callTableBulkAction($action, [$freeProgram, $parentProgram])
        ->assertNotified(programDeletionWarning());

    $this->assertDatabaseHas('programs', ['id' => $parentProgram->id, 'deleted_at' => $parentProgram->getRawOriginal('deleted_at')]);
    $this->assertDatabaseHas('programs', ['id' => $freeProgram->id, 'deleted_at' => $freeProgram->getRawOriginal('deleted_at')]);
    $this->assertDatabaseCount($childType === 'alumni' ? 'alumnis' : 'program_user_allocations', 1);
})->with('program deletion actions')->with('program children');

it('deletes a child-free program from its edit page', function (string $action, bool $trashed) {
    $staff = createProgramStaff();
    $program = Program::factory()->create(['deleted_at' => $trashed ? now() : null]);

    Livewire::actingAs($staff)->test(EditProgram::class, ['record' => $program->getRouteKey()])
        ->callAction($action)
        ->assertNotified();

    if ($trashed) {
        $this->assertModelMissing($program);
    } else {
        $this->assertSoftDeleted($program);
    }
})->with('program deletion actions');

it('deletes child-free programs in bulk', function (string $action, bool $trashed) {
    $staff = createProgramStaff();
    $program = Program::factory()->create(['deleted_at' => $trashed ? now() : null]);

    Livewire::actingAs($staff)->test(ListPrograms::class)
        ->filterTable('trashed', $trashed ? true : null)
        ->callTableBulkAction($action, [$program])
        ->assertNotified();

    if ($trashed) {
        $this->assertModelMissing($program);
    } else {
        $this->assertSoftDeleted($program);
    }
})->with('program deletion actions');

it('keeps header deletion unavailable without the existing deletion grants', function (string $action, bool $trashed) {
    $staff = createProgramStaff(['view', 'edit']);
    $program = Program::factory()->create(['deleted_at' => $trashed ? now() : null]);

    Livewire::actingAs($staff)->test(EditProgram::class, ['record' => $program->getRouteKey()])
        ->assertActionHidden($action);

    $this->assertDatabaseHas('programs', ['id' => $program->id, 'deleted_at' => $program->getRawOriginal('deleted_at')]);
})->with('program deletion actions');
