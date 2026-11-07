<?php

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\GetStarted;
use App\Filament\Pages\MyInformation;
use App\Filament\Resources\Alumnis\AlumniResource;
use App\Filament\Widgets\RecentlyTraced;
use App\Livewire\AlumniInformationForm;
use App\Models\Alumni;
use App\Models\AlumniEmployment;
use App\Models\Campus;
use App\Models\Program;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAllocation;
use App\Support\SystemEncrypter;
use Filament\Facades\Filament;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
    Filament::setCurrentPanel('online');
});

function createInformationFormUser(): User
{
    $role = Role::query()->create([
        'name' => 'Alumni',
        'action' => ['view', 'create', 'edit'],
        'access_page' => [Dashboard::getSlug(), MyInformation::getSlug(), AlumniResource::getSlug()],
        'access_subpage' => [],
        'access_widget' => [],
    ]);
    $allocation = UserAllocation::query()->create([
        'role_id' => $role->id,
        'management_scope' => 'my_records',
        'max_users' => 10,
    ]);

    return User::factory()->create([
        'email' => 'alumni@example.test',
        'student_id' => null,
        'user_allocation_id' => $allocation->id,
    ]);
}

function createInformationFormProgram(): Program
{
    $campus = Campus::query()->create([
        'name' => 'Synthetic Campus',
        'is_active' => true,
    ]);

    return Program::factory()->create([
        'campus_id' => $campus->id,
        'program_name' => 'Sample Program',
        'is_active' => true,
    ]);
}

/** @return array<string, mixed> */
function informationFormData(Program $program): array
{
    return [
        'student_number' => 20240001,
        'first_name' => 'Alex',
        'middle_name' => 'Sample',
        'last_name' => 'Graduate',
        'email' => 'alumni@example.test',
        'phone_number' => '09123456789',
        'current_address' => 'Sample City',
        'campus_id' => $program->campus_id,
        'program_id' => $program->id,
        'graduation_year' => '2024-06-15',
        'employment_status' => 'employed',
        'education' => [],
        'employments' => [],
    ];
}

/** @return array<string, mixed> */
function informationEducationData(): array
{
    return [
        'instituion' => 'Synthetic Graduate School',
        'program' => 'Sample Graduate Program',
        'degree_level' => 'Masters',
        'units_completed' => 30,
        'status' => 'Completed',
        'started_at' => '2024-08-01',
        'ended_at' => '2026-06-01',
    ];
}

/** @return array<string, mixed> */
function informationEmploymentData(): array
{
    return [
        'company_name' => 'Synthetic Employer',
        'position' => 'Analyst',
        'company_address' => 'Sample Business District',
        'industry' => 'Information Technology',
        'employment_type' => 'Full-time',
        'is_course_related' => true,
        'date_hired' => '2024-07-01',
        'starting_date' => '2024-07-15',
        'ended_at' => null,
        'supported_documents' => 'Employment certificate available on request',
        'is_current' => true,
    ];
}

/** @param array<string, mixed> $attributes */
function createInformationFormAlumni(User $user, Program $program, array $attributes = []): Alumni
{
    $data = informationFormData($program);
    unset($data['campus_id'], $data['education'], $data['employments']);

    return Alumni::factory()->create([
        ...$data,
        'user_id' => $user->id,
        ...$attributes,
    ]);
}

it('creates the signed-in alumnus with education and employment information', function () {
    $user = createInformationFormUser();
    $data = informationFormData(createInformationFormProgram());
    $data['education'] = [informationEducationData()];
    $data['employments'] = [informationEmploymentData()];

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->set('data', $data)
        ->call('save')
        ->assertHasNoErrors()
        ->assertFormFieldIsDisabled('student_number');

    $this->assertDatabaseHas('alumnis', [
        'user_id' => $user->id,
        'student_number' => 20240001,
        'first_name' => 'Alex',
        'current_address' => 'Sample City',
    ]);
    $alumni = Alumni::query()->where('user_id', $user->id)->sole();
    $this->assertDatabaseHas('alumni_education', [
        'alumni_id' => $alumni->id,
        'instituion' => 'Synthetic Graduate School',
    ]);
    $this->assertDatabaseHas('alumni_employments', [
        'alumni_id' => $alumni->id,
        'company_name' => 'Synthetic Employer',
    ]);
});

it('updates permitted contacts and owned history without changing official identity', function () {
    $user = createInformationFormUser();
    $program = createInformationFormProgram();
    $alumni = createInformationFormAlumni($user, $program);
    $education = $alumni->alumniEducation()->create(informationEducationData());
    $employment = $alumni->alumniEmployment()->create(informationEmploymentData());
    $data = informationFormData($program);
    $data['student_number'] = 99999999;
    $data['first_name'] = 'Unapproved';
    $data['middle_name'] = 'Identity';
    $data['last_name'] = 'Change';
    $data['program_id'] = Program::factory()->for($program->campus)->create()->id;
    $data['graduation_year'] = '2025-06-15';
    $data['current_address'] = 'Updated Sample City';
    $data['phone_number'] = '09987654321';
    $data['email'] = 'updated-contact@example.test';
    $data['employment_status'] = 'unemployed';
    $data['education'] = [[...informationEducationData(), 'id' => $education->id, 'units_completed' => 36]];
    $data['employments'] = [[...informationEmploymentData(), 'id' => $employment->id, 'position' => 'Senior Analyst']];

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->set('data', $data)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('alumnis', [
        'id' => $alumni->id,
        'student_number' => 20240001,
        'first_name' => 'Alex',
        'middle_name' => 'Sample',
        'last_name' => 'Graduate',
        'program_id' => $program->id,
        'graduation_year' => '2024-06-15',
        'current_address' => 'Updated Sample City',
        'phone_number' => '09987654321',
        'email' => 'updated-contact@example.test',
        'employment_status' => 'unemployed',
    ]);
    $this->assertDatabaseHas('alumni_education', ['id' => $education->id, 'units_completed' => 36]);
    $this->assertDatabaseHas('alumni_employments', ['id' => $employment->id, 'position' => 'Senior Analyst']);
    $this->assertDatabaseHas('users', ['id' => $user->id, 'email' => 'alumni@example.test']);
});

it('does not insert duplicate alumni or history records on repeated save', function () {
    $user = createInformationFormUser();
    $data = informationFormData(createInformationFormProgram());
    $data['education'] = [informationEducationData()];
    $data['employments'] = [informationEmploymentData()];

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->set('data', $data)
        ->call('save')
        ->assertHasNoErrors()
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseCount('alumnis', 1);
    $this->assertDatabaseCount('alumni_education', 1);
    $this->assertDatabaseCount('alumni_employments', 1);
});

it('appends histories without deleting omitted existing histories', function () {
    $user = createInformationFormUser();
    $program = createInformationFormProgram();
    $alumni = createInformationFormAlumni($user, $program);
    $education = $alumni->alumniEducation()->create(informationEducationData());
    $employment = $alumni->alumniEmployment()->create(informationEmploymentData());
    $data = informationFormData($program);
    $data['education'] = [[...informationEducationData(), 'program' => 'Second Graduate Program']];
    $data['employments'] = [[...informationEmploymentData(), 'company_name' => 'Second Synthetic Employer']];

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->set('data', $data)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertModelExists($education);
    $this->assertModelExists($employment);
    $this->assertDatabaseHas('alumni_education', ['id' => $education->id, 'deleted_at' => null]);
    $this->assertDatabaseHas('alumni_employments', ['id' => $employment->id, 'deleted_at' => null]);
    $this->assertDatabaseHas('alumni_education', ['alumni_id' => $alumni->id, 'program' => 'Second Graduate Program']);
    $this->assertDatabaseHas('alumni_employments', ['alumni_id' => $alumni->id, 'company_name' => 'Second Synthetic Employer']);
});

it('ignores forged owner and staff-controlled attributes', function () {
    $user = createInformationFormUser();
    $otherUser = User::factory()->create(['email' => 'foreign@example.test']);
    $data = informationFormData(createInformationFormProgram());
    $data['id'] = 900;
    $data['user_id'] = $otherUser->id;
    $data['alumni_id'] = 900;
    $data['user_allocation_id'] = null;
    $data['role'] = 'Super Administrator';
    $data['date_traced'] = '2026-01-01';
    $data['trace_by'] = 'Self-approved tracer';
    $data['remarks'] = 'Self-approved official remark';
    $data['deleted_at'] = '2026-01-01';
    $data['education'] = [[...informationEducationData(), 'alumni_id' => 900]];
    $data['employments'] = [[...informationEmploymentData(), 'alumni_id' => 900]];

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->set('data', $data)
        ->call('save')
        ->assertHasNoErrors();

    $alumni = Alumni::query()->where('user_id', $user->id)->sole();
    $this->assertDatabaseHas('alumnis', [
        'id' => $alumni->id,
        'user_id' => $user->id,
        'trace_by' => null,
        'date_traced' => null,
        'remarks' => null,
        'deleted_at' => null,
    ]);
    $this->assertDatabaseHas('users', ['id' => $user->id, 'user_allocation_id' => $user->user_allocation_id]);
    $this->assertDatabaseHas('alumni_education', ['alumni_id' => $alumni->id, 'instituion' => 'Synthetic Graduate School']);
    $this->assertDatabaseHas('alumni_employments', ['alumni_id' => $alumni->id, 'company_name' => 'Synthetic Employer']);
});

it('does not claim an existing alumni record by matching student ID or email', function (array $matchingAttributes, ?string $deletedAt) {
    $user = createInformationFormUser();
    $otherUser = User::factory()->create(['email' => 'foreign@example.test']);
    $program = createInformationFormProgram();
    $foreignAlumni = createInformationFormAlumni($otherUser, $program, [
        'student_number' => 20249999,
        'email' => 'foreign@example.test',
        'current_address' => 'Foreign private address',
        'deleted_at' => $deletedAt,
        ...$matchingAttributes,
    ]);

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->assertDontSee('Foreign private address')
        ->set('data', informationFormData($program))
        ->call('save')
        ->assertHasErrors(['data.student_number']);

    $this->assertDatabaseCount('alumnis', 1);
    $this->assertDatabaseHas('alumnis', ['id' => $foreignAlumni->id, 'user_id' => $otherUser->id]);
})->with([
    'active student ID match' => [['student_number' => 20240001], null],
    'active email match' => [['email' => 'alumni@example.test'], null],
    'email match with different case' => [['email' => 'ALUMNI@EXAMPLE.TEST'], null],
    'archived student ID match' => [['student_number' => 20240001], '2026-01-01 00:00:00'],
    'archived email match' => [['email' => 'alumni@example.test'], '2026-01-01 00:00:00'],
]);

it('rejects another alumnus education ID without saving any changes', function () {
    $user = createInformationFormUser();
    $otherUser = User::factory()->create(['email' => 'foreign@example.test']);
    $program = createInformationFormProgram();
    $ownAlumni = createInformationFormAlumni($user, $program);
    $foreignAlumni = createInformationFormAlumni($otherUser, $program, ['student_number' => 20249999, 'email' => 'foreign@example.test']);
    $foreignEducation = $foreignAlumni->alumniEducation()->create(informationEducationData());
    $data = informationFormData($program);
    $data['current_address'] = 'Should not be saved';
    $data['education'] = [[...informationEducationData(), 'id' => $foreignEducation->id, 'units_completed' => 99]];

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->set('data', $data)
        ->call('save')
        ->assertHasErrors();

    $this->assertDatabaseHas('alumnis', ['id' => $ownAlumni->id, 'current_address' => 'Sample City']);
    $this->assertDatabaseHas('alumni_education', ['id' => $foreignEducation->id, 'alumni_id' => $foreignAlumni->id, 'units_completed' => 30]);
});

it('rejects another alumnus employment ID without saving any changes', function () {
    $user = createInformationFormUser();
    $otherUser = User::factory()->create(['email' => 'foreign@example.test']);
    $program = createInformationFormProgram();
    $ownAlumni = createInformationFormAlumni($user, $program);
    $foreignAlumni = createInformationFormAlumni($otherUser, $program, ['student_number' => 20249999, 'email' => 'foreign@example.test']);
    $foreignEmployment = $foreignAlumni->alumniEmployment()->create(informationEmploymentData());
    $data = informationFormData($program);
    $data['current_address'] = 'Should not be saved';
    $data['employments'] = [[...informationEmploymentData(), 'id' => $foreignEmployment->id, 'company_name' => 'Should not be saved']];

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->set('data', $data)
        ->call('save')
        ->assertHasErrors();

    $this->assertDatabaseHas('alumnis', ['id' => $ownAlumni->id, 'current_address' => 'Sample City']);
    $this->assertDatabaseHas('alumni_employments', ['id' => $foreignEmployment->id, 'alumni_id' => $foreignAlumni->id, 'company_name' => 'Synthetic Employer']);
});

it('does not restore or update archived histories supplied in a forged payload', function () {
    $user = createInformationFormUser();
    $program = createInformationFormProgram();
    $alumni = createInformationFormAlumni($user, $program);
    $education = $alumni->alumniEducation()->create([
        ...informationEducationData(),
        'deleted_at' => '2026-01-01 00:00:00',
    ]);
    $data = informationFormData($program);
    $data['education'] = [[...informationEducationData(), 'id' => $education->id, 'units_completed' => 99]];

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->set('data', $data)
        ->call('save')
        ->assertHasErrors();

    $this->assertDatabaseHas('alumni_education', [
        'id' => $education->id,
        'units_completed' => 30,
        'deleted_at' => '2026-01-01 00:00:00',
    ]);
});

it('requires authenticated access to the alumni form', function () {
    Livewire::test(AlumniInformationForm::class)->assertForbidden();
});

it('denies the alumni form to accounts without an allocation', function () {
    $user = User::factory()->create(['email' => 'pending@example.test', 'user_allocation_id' => null]);

    Livewire::actingAs($user)->test(AlumniInformationForm::class)->assertForbidden();
});

it('denies the alumni form outside the self-record scope', function () {
    $user = createInformationFormUser();
    $user->userAllocation->update(['management_scope' => 'all_program']);

    Livewire::actingAs($user)->test(AlumniInformationForm::class)->assertForbidden();
});

it('rechecks a revoked role before saving a mounted form', function () {
    $user = createInformationFormUser();
    $data = informationFormData(createInformationFormProgram());
    $component = Livewire::actingAs($user)->test(AlumniInformationForm::class)->set('data', $data);
    $user->userAllocation->role->delete();

    $component->call('save')->assertForbidden();

    $this->assertDatabaseCount('alumnis', 0);
});

it('rechecks a revoked allocation before saving a mounted form', function () {
    $user = createInformationFormUser();
    $data = informationFormData(createInformationFormProgram());
    $component = Livewire::actingAs($user)->test(AlumniInformationForm::class)->set('data', $data);
    $user->userAllocation->delete();

    $component->call('save')->assertForbidden();

    $this->assertDatabaseCount('alumnis', 0);
});

it('requires the create grant when saving a new alumni record', function () {
    $user = createInformationFormUser();
    $data = informationFormData(createInformationFormProgram());
    $component = Livewire::actingAs($user)->test(AlumniInformationForm::class)->set('data', $data);
    $user->userAllocation->role->update(['action' => ['view', 'edit']]);

    $component->call('save')->assertForbidden();

    $this->assertDatabaseCount('alumnis', 0);
});

it('requires the edit grant when saving existing alumni information', function () {
    $user = createInformationFormUser();
    $program = createInformationFormProgram();
    $alumni = createInformationFormAlumni($user, $program);
    $component = Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->set('data.current_address', 'Should not be saved');
    $user->userAllocation->role->update(['action' => ['view', 'create']]);

    $component->call('save')->assertForbidden();

    $this->assertDatabaseHas('alumnis', ['id' => $alumni->id, 'current_address' => 'Sample City']);
});

it('denies an archived owned alumni record instead of recreating it', function () {
    $user = createInformationFormUser();
    createInformationFormAlumni($user, createInformationFormProgram())->delete();

    Livewire::actingAs($user)->test(AlumniInformationForm::class)->assertForbidden();

    $this->assertDatabaseCount('alumnis', 1);
});

it('denies ambiguous multiple alumni records for the same account', function () {
    $user = createInformationFormUser();
    $program = createInformationFormProgram();
    createInformationFormAlumni($user, $program);
    createInformationFormAlumni($user, $program, ['student_number' => 20240002, 'email' => 'second-record@example.test']);

    Livewire::actingAs($user)->test(AlumniInformationForm::class)->assertForbidden();
});

it('validates required profile and history fields before persisting', function () {
    $user = createInformationFormUser();
    $data = informationFormData(createInformationFormProgram());
    $data['first_name'] = '';
    $data['education'] = [[...informationEducationData(), 'ended_at' => null]];
    $data['employments'] = [[...informationEmploymentData(), 'supported_documents' => '']];

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->set('data', $data)
        ->call('save')
        ->assertHasErrors(['data.first_name', 'data.education.0.ended_at', 'data.employments.0.supported_documents']);

    $this->assertDatabaseCount('alumnis', 0);
    $this->assertDatabaseCount('alumni_education', 0);
    $this->assertDatabaseCount('alumni_employments', 0);
});

it('rejects education completion before its start date', function () {
    $user = createInformationFormUser();
    $data = informationFormData(createInformationFormProgram());
    $data['education'] = [[...informationEducationData(), 'ended_at' => '2023-06-01']];

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->set('data', $data)
        ->call('save')
        ->assertHasErrors(['data.education.0.ended_at']);

    $this->assertDatabaseCount('alumnis', 0);
});

it('rolls back the profile and education when employment persistence fails', function () {
    $user = createInformationFormUser();
    $data = informationFormData(createInformationFormProgram());
    $data['education'] = [informationEducationData()];
    $data['employments'] = [informationEmploymentData()];
    $event = 'eloquent.creating: '.AlumniEmployment::class;
    Event::listen($event, function (): void {
        throw ValidationException::withMessages(['data.employments' => 'Synthetic persistence failure.']);
    });

    try {
        Livewire::actingAs($user)->test(AlumniInformationForm::class)
            ->set('data', $data)
            ->call('save')
            ->assertHasErrors(['data.employments']);
    } finally {
        Event::forget($event);
    }

    $this->assertDatabaseCount('alumnis', 0);
    $this->assertDatabaseCount('alumni_education', 0);
    $this->assertDatabaseCount('alumni_employments', 0);
});

it('shows the alumni form on the shared dashboard without administrative widgets', function () {
    config()->set('app.system_key', 'synthetic-test-key');
    config()->set('app.super_admin_emails_encrypted', SystemEncrypter::encryptSuperAdminData(''));
    config()->set('system.seal', SystemEncrypter::getSeal());
    config()->set('app.developer_host', 'developer.example.test');
    $user = createInformationFormUser();
    $user->update(['email' => 'ctu.synthetic-form-test@gmail.com']);
    $user->userAllocation->role->update(['access_widget' => [RecentlyTraced::class]]);

    $this->actingAs($user)->get('/online')
        ->assertOk()
        ->assertSeeLivewire(AlumniInformationForm::class)
        ->assertSee('Complete my information')
        ->assertDontSeeLivewire(RecentlyTraced::class);
});

it('shows an empty alumni overview and opens the owned editor on request', function () {
    $user = createInformationFormUser();

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->assertSee('0%')
        ->assertSee('Employment status not recorded')
        ->assertSee('No work history recorded')
        ->assertSee('No education history recorded')
        ->assertSee('Complete my information')
        ->assertDontSee('wire:submit="save"', escape: false)
        ->call('openEditor')
        ->assertSee('wire:submit="save"', escape: false);
});

it('shows only owned work and education history without inferring employment status', function () {
    $user = createInformationFormUser();
    $program = createInformationFormProgram();
    $alumni = createInformationFormAlumni($user, $program, ['employment_status' => 'unemployed']);
    $alumni->alumniEmployment()->create([
        ...informationEmploymentData(),
        'company_name' => 'Current Synthetic Employer',
        'supported_documents' => '<script>private-reference</script>',
    ]);
    $alumni->alumniEmployment()->create([
        ...informationEmploymentData(),
        'company_name' => 'Past Synthetic Employer',
        'is_current' => false,
        'ended_at' => '2025-01-01',
    ]);
    $alumni->alumniEducation()->create(informationEducationData());
    $otherUser = User::factory()->create(['email' => 'foreign@example.test']);
    $foreignAlumni = createInformationFormAlumni($otherUser, $program, [
        'student_number' => 20249999,
        'email' => 'foreign@example.test',
    ]);
    $foreignAlumni->alumniEmployment()->create([
        ...informationEmploymentData(),
        'company_name' => 'Foreign Private Employer',
    ]);

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->assertSee('100%')
        ->assertSee('Unemployed')
        ->assertSee('Current Synthetic Employer')
        ->assertSee('Past Synthetic Employer')
        ->assertSee('Synthetic Graduate School')
        ->assertSee('&lt;script&gt;private-reference&lt;/script&gt;', escape: false)
        ->assertDontSee('<script>private-reference</script>', escape: false)
        ->assertDontSee('Foreign Private Employer');
});

it('shows an incomplete milestone when a saved core profile field is blank', function () {
    $user = createInformationFormUser();
    createInformationFormAlumni($user, createInformationFormProgram(), ['current_address' => '']);

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->assertSee('0%')
        ->assertSee('Core profile needs attention')
        ->assertDontSee('Core profile information recorded')
        ->assertSee('Review my information')
        ->call('openEditor')
        ->assertSee('Current Address');
});

it('does not offer update actions to an alumni account with view-only access', function () {
    $user = createInformationFormUser();
    createInformationFormAlumni($user, createInformationFormProgram());
    $user->userAllocation->role->update(['action' => ['view']]);

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->assertSee('100%')
        ->assertDontSee('Update work')
        ->assertDontSee('Update education')
        ->assertSee('View my information')
        ->call('openEditor')
        ->assertSee('Current Address')
        ->assertSet('data.current_address', 'Sample City')
        ->assertFormFieldIsDisabled('email')
        ->call('save')
        ->assertForbidden();
});

it('refreshes the overview after saving an alumni profile and histories', function () {
    $user = createInformationFormUser();
    $data = informationFormData(createInformationFormProgram());
    $data['education'] = [informationEducationData()];
    $data['employments'] = [informationEmploymentData()];

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->assertSee('0%')
        ->call('openEditor')
        ->set('data', $data)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('100%')
        ->assertSee('Employed')
        ->assertSee('Synthetic Employer')
        ->assertSee('Synthetic Graduate School');
});

it('redirects roleless dashboard users to get started without alumni records', function () {
    $user = User::factory()->create(['user_allocation_id' => null]);

    Livewire::actingAs($user)->test(Dashboard::class)
        ->assertRedirect(GetStarted::getUrl())
        ->assertDontSeeLivewire(AlumniInformationForm::class)
        ->assertDontSee('class="fi-wg"', escape: false);
});

it('redirects guests from the shared dashboard', function () {
    $this->get('/online')->assertRedirect('/online/login');
});

it('renders the shared login page for guests', function () {
    $this->get('/online/login')->assertOk();
});

it('keeps the staff alumni directory inaccessible to an alumni form account', function () {
    config()->set('allowed-email.domains', ['example.test']);
    config()->set('app.developer_host', 'developer.example.test');

    $user = createInformationFormUser();
    $program = createInformationFormProgram();
    $foreignUser = User::factory()->create(['email' => 'other@example.test']);
    $foreignAlumni = Alumni::factory()->create([
        'user_id' => $foreignUser->id,
        'program_id' => $program->id,
        'email' => 'other@example.test',
    ]);

    $this->actingAs($user)->get('/online')
        ->assertOk()
        ->assertSeeLivewire(AlumniInformationForm::class)
        ->assertDontSee(AlumniResource::getUrl(), escape: false);

    foreach (['/online/alumnis', '/online/alumnis/create', "/online/alumnis/{$foreignAlumni->id}", "/online/alumnis/{$foreignAlumni->id}/edit"] as $url) {
        $this->get($url)->assertForbidden();
    }
});

it('keeps the alumni directory available to a permitted staff scope', function () {
    config()->set('allowed-email.domains', ['example.test']);
    config()->set('app.developer_host', 'developer.example.test');

    $user = createInformationFormUser();
    $user->userAllocation->update(['management_scope' => 'all_program']);

    $this->actingAs($user)->get('/online/alumnis')->assertOk();
});

it('preserves configured bootstrap access to the alumni directory', function () {
    config()->set('app.system_key', 'synthetic-test-key');
    config()->set('app.super_admin_emails_encrypted', SystemEncrypter::encryptSuperAdminData('alumni@example.test'));
    config()->set('system.seal', SystemEncrypter::getSeal());
    config()->set('app.developer_host', 'developer.example.test');

    $user = createInformationFormUser();

    $this->actingAs($user)->get('/online/alumnis')->assertOk();
});

it('redirects a roleless account to get started through the shared dashboard route', function () {
    config()->set('app.system_key', 'synthetic-test-key');
    config()->set('app.super_admin_emails_encrypted', SystemEncrypter::encryptSuperAdminData(''));
    config()->set('system.seal', SystemEncrypter::getSeal());
    config()->set('app.developer_host', 'developer.example.test');
    $user = User::factory()->create([
        'email' => 'ctu.synthetic-roleless-test@gmail.com',
        'user_allocation_id' => null,
    ]);

    $this->actingAs($user)->get('/online')
        ->assertRedirect(GetStarted::getUrl());

    $this->get(GetStarted::getUrl())
        ->assertSee('Waiting for a role')
        ->assertDontSeeLivewire(AlumniInformationForm::class)
        ->assertDontSee('class="fi-wg"', escape: false);
});

it('denies the shared dashboard route when an allocated account lacks its page grant', function () {
    config()->set('allowed-email.domains', ['example.test']);
    config()->set('app.developer_host', 'developer.example.test');

    $user = createInformationFormUser();
    $user->userAllocation->role->update(['access_page' => [AlumniResource::getSlug()]]);

    $this->actingAs($user)->get('/online')->assertForbidden();
});

it('keeps the shared dashboard route available to staff with its page grant', function () {
    config()->set('allowed-email.domains', ['example.test']);
    config()->set('app.developer_host', 'developer.example.test');

    $user = createInformationFormUser();
    $user->userAllocation->update(['management_scope' => 'all_program']);

    $this->actingAs($user)->get('/online')
        ->assertOk()
        ->assertDontSeeLivewire(AlumniInformationForm::class);
});

it('denies the alumni dashboard component without its page grant', function () {
    $user = createInformationFormUser();
    $user->userAllocation->role->update(['access_page' => [AlumniResource::getSlug()]]);

    Livewire::actingAs($user)->test(Dashboard::class)
        ->assertForbidden();
});

it('denies direct alumni form access without the dashboard page grant', function () {
    $user = createInformationFormUser();
    $user->userAllocation->role->update(['access_page' => [AlumniResource::getSlug()]]);

    Livewire::actingAs($user)->test(AlumniInformationForm::class)->assertForbidden();
});

it('accepts a finished job when its optional work start date is unknown', function () {
    $user = createInformationFormUser();
    $data = informationFormData(createInformationFormProgram());
    $data['employments'] = [[
        ...informationEmploymentData(),
        'starting_date' => null,
        'ended_at' => '2025-01-01',
        'is_current' => false,
    ]];

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->set('data', $data)->call('save')->assertHasNoErrors();

    $this->assertDatabaseHas('alumni_employments', ['starting_date' => null, 'ended_at' => '2025-01-01']);
});

it('does not show private dashboard records after the alumni allocation is revoked', function () {
    $user = createInformationFormUser();
    $component = Livewire::actingAs($user)->test(Dashboard::class);
    $user->userAllocation->delete();

    $component->call('$refresh')->call('$refresh')
        ->assertDontSee('class="fi-wg"', escape: false)
        ->assertDontSeeLivewire(RecentlyTraced::class);
});

it('keeps the archived program label visible for an existing alumni record', function () {
    $user = createInformationFormUser();
    $program = createInformationFormProgram();
    createInformationFormAlumni($user, $program);
    $program->delete();

    Livewire::actingAs($user)->test(AlumniInformationForm::class)
        ->call('openEditor')
        ->assertSee('Sample Program');
});

it('shows an access notice instead of staff widgets for a no-access scope', function () {
    $user = createInformationFormUser();
    $user->userAllocation->update(['management_scope' => 'no_access']);

    Livewire::actingAs($user)->test(Dashboard::class)
        ->assertSee('Ask the administrator to enable access')
        ->assertDontSee('class="fi-wg"', escape: false);
});

it('preserves the shared staff dashboard for staff management scopes', function () {
    $user = createInformationFormUser();
    $user->userAllocation->update(['management_scope' => 'all_program']);

    Livewire::actingAs($user)->test(Dashboard::class)
        ->assertSee('class="fi-wg"', escape: false)
        ->assertDontSeeLivewire(AlumniInformationForm::class);
});

it('reports database failures safely and leaves all three tables unchanged', function () {
    $user = createInformationFormUser();
    $data = informationFormData(createInformationFormProgram());
    $data['education'] = [informationEducationData()];
    $data['employments'] = [informationEmploymentData()];
    Log::spy();
    $event = 'eloquent.creating: '.AlumniEmployment::class;
    Event::listen($event, function (): void {
        throw new QueryException('sqlite', 'synthetic SQL', ['synthetic-private-value'], new RuntimeException('Synthetic database failure'));
    });

    try {
        Livewire::actingAs($user)->test(AlumniInformationForm::class)
            ->call('openEditor')
            ->set('data', $data)->call('save')
            ->assertHasErrors(['data'])
            ->assertSee('Your information could not be saved.')
            ->assertDontSee('synthetic-private-value');
    } finally {
        Event::forget($event);
    }

    $this->assertDatabaseCount('alumnis', 0);
    $this->assertDatabaseCount('alumni_education', 0);
    $this->assertDatabaseCount('alumni_employments', 0);
    Log::shouldHaveReceived('warning')->with('Alumni information save failed.', ['user_id' => $user->id])->once();
});
