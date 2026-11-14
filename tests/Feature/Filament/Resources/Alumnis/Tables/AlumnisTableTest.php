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
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;

uses(RefreshDatabase::class);

function createExcelImportStaff(): User
{
    $role = Role::query()->create([
        'name' => 'Import Staff',
        'action' => ['view', 'import'],
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

it('shows one concise summary per invalid Excel row', function () {
    $staff = createExcelImportStaff();
    Filament::setCurrentPanel('online');

    $values = ['student_number' => ''];
    $failure = new ExcelValidationException(
        new LaravelValidationException(Validator::make([], [])),
        [
            new Failure(132, 'student_number', ['Student number is required.'], $values),
            new Failure(132, 'first_name', ['The first_name field is required.'], $values),
            new Failure(132, 'last_name', ['The last_name field is required.'], $values),
            new Failure(132, 'campus', ['The campus field is required.'], $values),
            new Failure(132, 'department', ['The department field is required.'], $values),
            new Failure(132, 'department', ['The selected department must belong to the selected campus.'], $values),
            new Failure(132, 'graduation_year', ['The graduation_year field is required.'], $values),
        ],
    );

    Excel::shouldReceive('import')->once()->andThrow($failure);

    Livewire::actingAs($staff)
        ->test(ListAlumnis::class)
        ->callAction(TestAction::make('import')->table(), data: ['file' => [UploadedFile::fake()->create('synthetic.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')]])
        ->assertHasNoFormErrors()
        ->assertHasNoActionErrors()
        ->assertNotified(Notification::make()
            ->title('Check the Excel file')
            ->body("Some rows could not be imported. Check the listed fields, then upload the file again:\n\nRow 132: Check these fields: Student Number, First Name, Last Name, Campus, Department, Graduation Year. Department must match the selected campus.")
            ->danger()
            ->persistent());
});

it('hides technical exception details from Excel import notifications', function () {
    $staff = createExcelImportStaff();
    Filament::setCurrentPanel('online');
    Log::spy();
    $exception = new RuntimeException('Typed property PhpOffice\\PhpSpreadsheet\\Worksheet\\Worksheet::$cellCollection must not be accessed before initialization');

    Excel::shouldReceive('import')->once()->andThrow($exception);

    Livewire::actingAs($staff)
        ->test(ListAlumnis::class)
        ->callAction(TestAction::make('import')->table(), data: ['file' => [UploadedFile::fake()->create('synthetic.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')]])
        ->assertHasNoFormErrors()
        ->assertHasNoActionErrors()
        ->assertNotified(Notification::make()
            ->title('Import failed')
            ->body('We could not import this file. Check that it uses the provided XLSX template, then try again.')
            ->danger()
            ->persistent());

    Log::shouldHaveReceived('error')->once()->with('Alumni Excel import failed.', [
        'exception_class' => RuntimeException::class,
    ]);
});

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
