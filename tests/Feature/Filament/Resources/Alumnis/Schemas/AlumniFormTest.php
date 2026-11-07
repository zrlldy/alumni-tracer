<?php

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Alumnis\AlumniResource;
use App\Filament\Resources\Alumnis\Pages\ListAlumnis;
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

it('filters alumni departments by the selected campus', function () {
    $role = Role::query()->create([
        'name' => 'Registry Staff',
        'action' => ['create'],
        'access_page' => [AlumniResource::getSlug(), Dashboard::getSlug()],
        'access_subpage' => [],
        'access_widget' => [],
    ]);
    $allocation = UserAllocation::query()->create([
        'role_id' => $role->id,
        'management_scope' => 'program',
        'max_users' => 10,
    ]);
    $user = User::factory()->create(['user_allocation_id' => $allocation->id]);
    $campus = Campus::factory()->create(['name' => 'North Campus']);
    $otherCampus = Campus::factory()->create(['name' => 'South Campus']);
    $program = Program::factory()->for($campus)->create(['program_name' => 'Nursing', 'is_active' => true]);
    Program::factory()->for($otherCampus)->create(['program_name' => 'Information Technology', 'is_active' => true]);

    Filament::setCurrentPanel('online');

    Livewire::actingAs($user)
        ->test(ListAlumnis::class)
        ->mountTableAction('CreateAlumni')
        ->assertFormFieldExists('campus_id')
        ->setTableActionData(['campus_id' => $campus->id])
        ->assertFormFieldExists('program_id', fn (Select $field): bool => $field->getOptions() === [
            $program->id => 'Nursing',
        ]);
});
