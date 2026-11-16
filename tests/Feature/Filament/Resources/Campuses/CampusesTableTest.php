<?php

use App\Filament\Resources\Campuses\CampusResource;
use App\Filament\Resources\Campuses\Pages\EditCampus;
use App\Filament\Resources\Campuses\Pages\ListCampuses;
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

function createCampusStaff(array $actions = ['view', 'edit', 'delete', 'force_delete']): User
{
    $role = Role::query()->create([
        'name' => 'Campus Staff',
        'action' => $actions,
        'access_page' => [CampusResource::getSlug()],
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

function attachCampusChild(Campus $campus, string $childType, bool $archived): void
{
    if ($childType === 'program') {
        Program::factory()->for($campus)->create(['deleted_at' => $archived ? now() : null]);

        return;
    }

    $allocation = auth()->user()->userAllocation;
    $allocation->campuses()->attach($campus);

    if ($archived) {
        $allocation->delete();
    }
}

function campusDeletionWarning(): Notification
{
    return Notification::make()
        ->warning()
        ->title('Cannot delete campus')
        ->body('This campus is a parent record. Delete its child programs/departments and remove its user allocation links first, including archived records.');
}

dataset('campus deletion actions', [
    'delete' => ['delete', false],
    'force delete' => ['forceDelete', true],
]);

dataset('campus children', [
    'active program' => ['program', false],
    'archived program' => ['program', true],
    'allocation link' => ['allocation', false],
    'archived allocation link' => ['allocation', true],
]);

it('shows the programs belonging to each campus and an empty reference when none exist', function () {
    $staff = createCampusStaff();
    $campus = Campus::factory()->create(['name' => 'North Campus']);
    $otherCampus = Campus::factory()->create(['name' => 'South Campus']);
    Program::factory()->for($campus)->create(['program_name' => 'Computer Science']);
    Program::factory()->for($campus)->create(['program_name' => 'Business Administration']);

    Livewire::actingAs($staff)->test(ListCampuses::class)
        ->assertTableColumnStateSet('programs.program_name', ['Computer Science', 'Business Administration'], $campus)
        ->assertSee('Programs / Departments')
        ->assertSee('Computer Science')
        ->assertSee('Business Administration')
        ->assertSee('No programs / departments')
        ->assertCanSeeTableRecords([$campus, $otherCampus]);
});

it('blocks a parent campus header deletion with a helpful toast', function (string $action, bool $trashed, string $childType, bool $archived) {
    $staff = createCampusStaff();
    $this->actingAs($staff);
    $campus = Campus::factory()->create(['deleted_at' => $trashed ? now() : null]);
    attachCampusChild($campus, $childType, $archived);

    Livewire::test(EditCampus::class, ['record' => $campus->getRouteKey()])
        ->callAction($action)
        ->assertNotified(campusDeletionWarning());

    $this->assertDatabaseHas('campuses', ['id' => $campus->id, 'deleted_at' => $campus->getRawOriginal('deleted_at')]);
    $this->assertDatabaseCount($childType === 'program' ? 'programs' : 'campus_user_allocations', 1);
})->with('campus deletion actions')->with('campus children');

it('blocks the whole bulk selection when a campus has children', function (string $action, bool $trashed, string $childType, bool $archived) {
    $staff = createCampusStaff();
    $this->actingAs($staff);
    $freeCampus = Campus::factory()->create(['deleted_at' => $trashed ? now() : null]);
    $parentCampus = Campus::factory()->create(['deleted_at' => $trashed ? now() : null]);
    attachCampusChild($parentCampus, $childType, $archived);

    Livewire::test(ListCampuses::class)
        ->filterTable('trashed', $trashed ? true : null)
        ->callTableBulkAction($action, [$freeCampus, $parentCampus])
        ->assertNotified(campusDeletionWarning());

    $this->assertDatabaseHas('campuses', ['id' => $parentCampus->id, 'deleted_at' => $parentCampus->getRawOriginal('deleted_at')]);
    $this->assertDatabaseHas('campuses', ['id' => $freeCampus->id, 'deleted_at' => $freeCampus->getRawOriginal('deleted_at')]);
    $this->assertDatabaseCount($childType === 'program' ? 'programs' : 'campus_user_allocations', 1);
})->with('campus deletion actions')->with('campus children');

it('deletes a child-free campus from its edit page', function (string $action, bool $trashed) {
    $staff = createCampusStaff();
    $campus = Campus::factory()->create(['deleted_at' => $trashed ? now() : null]);

    Livewire::actingAs($staff)->test(EditCampus::class, ['record' => $campus->getRouteKey()])
        ->callAction($action)
        ->assertNotified();

    if ($trashed) {
        $this->assertModelMissing($campus);
    } else {
        $this->assertSoftDeleted($campus);
    }
})->with('campus deletion actions');

it('deletes child-free campuses in bulk', function (string $action, bool $trashed) {
    $staff = createCampusStaff();
    $campus = Campus::factory()->create(['deleted_at' => $trashed ? now() : null]);

    Livewire::actingAs($staff)->test(ListCampuses::class)
        ->filterTable('trashed', $trashed ? true : null)
        ->callTableBulkAction($action, [$campus])
        ->assertNotified();

    if ($trashed) {
        $this->assertModelMissing($campus);
    } else {
        $this->assertSoftDeleted($campus);
    }
})->with('campus deletion actions');

it('keeps header deletion unavailable without the existing deletion grants', function (string $action, bool $trashed) {
    $staff = createCampusStaff(['view', 'edit']);
    $campus = Campus::factory()->create(['deleted_at' => $trashed ? now() : null]);

    Livewire::actingAs($staff)->test(EditCampus::class, ['record' => $campus->getRouteKey()])
        ->assertActionHidden($action);

    $this->assertDatabaseHas('campuses', ['id' => $campus->id, 'deleted_at' => $campus->getRawOriginal('deleted_at')]);
})->with('campus deletion actions');
