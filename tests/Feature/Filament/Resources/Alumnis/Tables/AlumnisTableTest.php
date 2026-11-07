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
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('lets staff choose and explicitly link an email or student id match', function () {
    $role = Role::query()->create([
        'name' => 'Registry Staff',
        'action' => ['edit'],
        'access_page' => [AlumniResource::getSlug(), Dashboard::getSlug()],
        'access_subpage' => [],
        'access_widget' => [],
    ]);
    $allocation = UserAllocation::query()->create([
        'role_id' => $role->id,
        'management_scope' => 'all_program',
        'max_users' => 10,
    ]);
    $staff = User::factory()->create(['user_allocation_id' => $allocation->id]);
    $campus = Campus::factory()->create();
    $program = Program::factory()->for($campus)->create(['is_active' => true]);
    $alumni = Alumni::factory()->for($program)->create([
        'user_id' => null,
        'email' => 'ALEX@EXAMPLE.TEST',
        'student_number' => 123456,
    ]);
    $emailMatch = User::factory()->create(['name' => 'Alex Email', 'email' => 'alex@example.test']);
    $studentIdMatch = User::factory()->create(['name' => 'Alex Student ID', 'email' => 'different@example.test']);
    $studentIdMatch->forceFill(['student_id' => '123456'])->save();
    $unmatched = User::factory()->create(['name' => 'Unmatched User', 'email' => 'unmatched@example.test']);
    $alreadyLinked = User::factory()->create(['name' => 'Already Linked', 'email' => 'linked@example.test']);
    $alreadyLinked->forceFill(['student_id' => '123456'])->save();
    Alumni::factory()->for($program)->create(['user_id' => $alreadyLinked->id])->delete();

    Filament::setCurrentPanel('online');

    Livewire::actingAs($staff)
        ->test(ListAlumnis::class)
        ->assertTableActionVisible('linkAccount', $alumni)
        ->mountTableAction('linkAccount', $alumni)
        ->assertFormFieldExists('user_id', fn (Select $field): bool => array_keys($field->getOptions()) === [$emailMatch->id, $studentIdMatch->id])
        ->setTableActionData(['user_id' => $emailMatch->id])
        ->callMountedTableAction()
        ->assertHasNoFormErrors();

    expect($alumni->fresh()->user_id)->toBe($emailMatch->id);
    $this->assertDatabaseMissing('alumnis', ['id' => $alumni->id, 'user_id' => $unmatched->id]);
});

it('rejects an account that does not match an alumni record', function () {
    $role = Role::query()->create([
        'name' => 'Registry Staff',
        'action' => ['edit'],
        'access_page' => [AlumniResource::getSlug(), Dashboard::getSlug()],
        'access_subpage' => [],
        'access_widget' => [],
    ]);
    $allocation = UserAllocation::query()->create([
        'role_id' => $role->id,
        'management_scope' => 'all_program',
        'max_users' => 10,
    ]);
    $staff = User::factory()->create(['user_allocation_id' => $allocation->id]);
    $campus = Campus::factory()->create();
    $program = Program::factory()->for($campus)->create(['is_active' => true]);
    $alumni = Alumni::factory()->for($program)->create(['user_id' => null]);
    $unmatched = User::factory()->create(['email' => 'not-a-match@example.test']);

    Filament::setCurrentPanel('online');

    Livewire::actingAs($staff)
        ->test(ListAlumnis::class)
        ->callTableAction('linkAccount', $alumni, data: ['user_id' => $unmatched->id])
        ->assertHasFormErrors(['user_id']);

    expect($alumni->fresh()->user_id)->toBeNull();
});

it('hides account linking from users without edit permission', function () {
    $role = Role::query()->create([
        'name' => 'Registry Creator',
        'action' => ['create'],
        'access_page' => [AlumniResource::getSlug(), Dashboard::getSlug()],
        'access_subpage' => [],
        'access_widget' => [],
    ]);
    $allocation = UserAllocation::query()->create([
        'role_id' => $role->id,
        'management_scope' => 'all_program',
        'max_users' => 10,
    ]);
    $staff = User::factory()->create(['user_allocation_id' => $allocation->id]);
    $campus = Campus::factory()->create();
    $program = Program::factory()->for($campus)->create(['is_active' => true]);
    $alumni = Alumni::factory()->for($program)->create(['user_id' => null]);

    Filament::setCurrentPanel('online');

    Livewire::actingAs($staff)
        ->test(ListAlumnis::class)
        ->assertTableActionHidden('linkAccount', $alumni);

    expect($alumni->fresh()->user_id)->toBeNull();
});
