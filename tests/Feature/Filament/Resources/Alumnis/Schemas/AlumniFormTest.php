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
use Filament\Actions\Testing\TestAction;
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

it('creates an unlinked alumni record with education and employment history', function () {
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
    $campus = Campus::factory()->create();
    $program = Program::factory()->for($campus)->create(['is_active' => true]);
    Filament::setCurrentPanel('online');

    Livewire::actingAs($user)
        ->test(ListAlumnis::class)
        ->callAction(TestAction::make('CreateAlumni')->table(), data: [
            'student_number' => 23456789,
            'first_name' => 'Alex',
            'middle_name' => 'M',
            'last_name' => 'Graduate',
            'email' => 'alex.graduate@example.test',
            'phone_number' => '09123456789',
            'current_address' => 'Test Address',
            'campus_id' => $campus->id,
            'program_id' => $program->id,
            'graduation_year' => '2020-01-01',
            'alumniEducation' => [[
                'instituion' => 'Example University',
                'program' => 'Information Technology',
                'degree_level' => 'Bachelor',
                'units_completed' => 120,
                'status' => 'Completed',
                'started_at' => '2016-08-01',
                'ended_at' => '2020-05-30',
            ]],
            'alumniEmployment' => [[
                'company_name' => 'Example Company',
                'position' => 'Analyst',
                'company_address' => 'Example City',
                'industry' => 'Technology',
                'employment_type' => 'Full-time',
                'is_course_related' => 1,
                'date_hired' => '2021-01-15',
                'starting_date' => '2021-01-15',
                'ended_at' => null,
                'supported_documents' => 'Certificate ref 123',
                'is_current' => null,
            ]],
        ])
        ->assertHasNoFormErrors();

    $alumni = Alumni::query()->where('student_number', 23456789)->sole();

    $this->assertNull($alumni->user_id);
    $this->assertSame(1, User::query()->count());
    $this->assertDatabaseHas('alumni_education', [
        'alumni_id' => $alumni->id,
        'instituion' => 'Example University',
        'program' => 'Information Technology',
    ]);
    $this->assertDatabaseHas('alumni_employments', [
        'alumni_id' => $alumni->id,
        'company_name' => 'Example Company',
        'position' => 'Analyst',
        'is_current' => null,
    ]);
});

it('saves education and employment records from the alumni edit form', function () {
    $role = Role::query()->create([
        'name' => 'Registry Staff',
        'action' => ['edit'],
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
    $campus = Campus::factory()->create();
    $program = Program::factory()->for($campus)->create(['is_active' => true]);
    $alumni = Alumni::factory()->for($program)->create(['phone_number' => '09123456789']);

    Filament::setCurrentPanel('online');

    Livewire::actingAs($user)
        ->test(ListAlumnis::class)
        ->callTableAction('edit', $alumni, data: [
            'alumniEducation' => [[
                'instituion' => 'Example University',
                'program' => 'Information Technology',
                'degree_level' => 'Bachelor',
                'units_completed' => 120,
                'status' => 'Completed',
                'started_at' => '2016-08-01',
                'ended_at' => '2020-05-30',
            ]],
            'alumniEmployment' => [[
                'company_name' => 'Example Company',
                'position' => 'Analyst',
                'company_address' => 'Example City',
                'industry' => 'Technology',
                'employment_type' => 'Full-time',
                'is_course_related' => 1,
                'date_hired' => '2021-01-15',
                'starting_date' => '2021-01-15',
                'ended_at' => null,
                'supported_documents' => 'Certificate ref 123',
                'is_current' => true,
            ]],
        ])
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('alumni_education', [
        'alumni_id' => $alumni->id,
        'instituion' => 'Example University',
        'program' => 'Information Technology',
    ]);
    $this->assertDatabaseHas('alumni_employments', [
        'alumni_id' => $alumni->id,
        'company_name' => 'Example Company',
        'position' => 'Analyst',
    ]);
});

it('loads and updates existing education and employment entries in collapsed sections without duplicating them', function () {
    $role = Role::query()->create([
        'name' => 'Registry Staff',
        'action' => ['edit'],
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
    $campus = Campus::factory()->create();
    $program = Program::factory()->for($campus)->create(['is_active' => true]);
    $alumni = Alumni::factory()->for($program)->create(['phone_number' => '09123456789']);
    $education = $alumni->alumniEducation()->create([
        'instituion' => 'Example University',
        'program' => 'Information Technology',
        'degree_level' => 'Bachelor',
        'units_completed' => 120,
        'status' => 'Completed',
        'started_at' => '2016-08-01',
        'ended_at' => '2020-05-30',
    ]);
    $employment = $alumni->alumniEmployment()->create([
        'company_name' => 'Example Company',
        'position' => 'Analyst',
        'company_address' => 'Example City',
        'industry' => 'Technology',
        'employment_type' => 'Full-time',
        'is_course_related' => true,
        'date_hired' => '2021-01-15',
        'starting_date' => '2021-01-15',
        'ended_at' => null,
        'supported_documents' => 'Certificate ref 123',
        'is_current' => true,
    ]);

    Filament::setCurrentPanel('online');

    Livewire::actingAs($user)
        ->test(ListAlumnis::class)
        ->callTableAction('edit', $alumni, data: [
            'alumniEducation' => ["record-{$education->id}" => [
                'instituion' => 'Example University',
                'program' => 'Information Technology',
                'degree_level' => 'Bachelor',
                'units_completed' => 128,
                'status' => 'Completed',
                'started_at' => '2016-08-01',
                'ended_at' => '2020-05-30',
            ]],
            'alumniEmployment' => ["record-{$employment->id}" => [
                'company_name' => 'Example Company',
                'position' => 'Senior Analyst',
                'company_address' => 'Example City',
                'industry' => 'Technology',
                'employment_type' => 'Full-time',
                'is_course_related' => true,
                'date_hired' => '2021-01-15',
                'starting_date' => '2021-01-15',
                'ended_at' => null,
                'supported_documents' => 'Certificate ref 123',
                'is_current' => true,
            ]],
        ])
        ->assertHasNoFormErrors();

    $this->assertDatabaseCount('alumni_education', 1);
    $this->assertDatabaseHas('alumni_education', ['id' => $education->id, 'units_completed' => 128]);
    $this->assertDatabaseCount('alumni_employments', 1);
    $this->assertDatabaseHas('alumni_employments', ['id' => $employment->id, 'position' => 'Senior Analyst']);
});
