<?php

use App\Exports\AlumniDataExport;
use App\Exports\AlumniDataPdfExport;
use App\Filament\Pages\AlumniDataReport;
use App\Models\Alumni;
use App\Models\AlumniEducation;
use App\Models\AlumniEmployment;
use App\Models\Campus;
use App\Models\Program;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAllocation;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

function createAlumniReportPdfUser(array $actions = ['view', 'export']): User
{
    $role = Role::query()->create([
        'name' => 'PDF Report Staff',
        'action' => $actions,
        'access_page' => [AlumniDataReport::getSlug()],
    ]);
    $allocation = UserAllocation::query()->create([
        'role_id' => $role->id,
        'management_scope' => 'all_program',
        'max_users' => 10,
    ]);

    return User::factory()->create(['user_allocation_id' => $allocation->id]);
}

describe('PDF exports', function () {
    beforeEach(function () {
        $this->withoutVite();
        Filament::setCurrentPanel('online');
    });

    it('downloads the report as a PDF alongside the existing Excel action', function () {
        $this->actingAs(createAlumniReportPdfUser());

        $page = Livewire::test(AlumniDataReport::class)
            ->assertActionVisible('exportXlsx')
            ->assertActionVisible('exportPdf')
            ->callAction('exportPdf')
            ->assertFileDownloaded('alumni-data-report.pdf', contentType: 'application/pdf');

        expect(base64_decode($page->effects['download']['content']))->toStartWith('%PDF-');
    });

    it('downloads the PDF using all report filters and the latest active record', function () {
        $program = Program::factory()->create(['is_active' => true]);
        $otherProgram = Program::factory()->create(['is_active' => true]);
        $account = User::factory()->create();
        Alumni::factory()->for($program)->create([
            'user_id' => $account->id,
            'graduation_year' => '2024-06-01',
            'employment_status' => 'employed',
            'updated_at' => '2026-11-01 10:00:00',
        ]);
        $matchingAlumni = Alumni::factory()->for($program)->create([
            'user_id' => $account->id,
            'graduation_year' => '2024-06-01',
            'employment_status' => 'employed',
            'updated_at' => '2026-11-02 10:00:00',
        ]);
        Alumni::factory()->for($program)->create(['graduation_year' => '2025-06-01', 'employment_status' => 'employed']);
        Alumni::factory()->for($program)->create(['graduation_year' => '2024-06-01', 'employment_status' => 'untraced']);
        Alumni::factory()->for($otherProgram)->create(['graduation_year' => '2024-06-01', 'employment_status' => 'employed']);
        $this->actingAs(createAlumniReportPdfUser());
        Excel::fake();

        Livewire::test(AlumniDataReport::class)
            ->filterTable('campus', ['campus_id' => (string) $program->campus_id])
            ->filterTable('graduation_year', ['year' => '2024'])
            ->filterTable('department', ['program_id' => (string) $program->id])
            ->filterTable('employment_status', ['status' => 'employed'])
            ->callAction('exportPdf');

        Excel::assertDownloaded('alumni-data-report-2024.pdf', function (AlumniDataPdfExport $export) use ($matchingAlumni, $program): bool {
            return $export->query()->pluck('id')->all() === [$matchingAlumni->id]
                && $export->headings()[0][0] === $program->campus->name;
        });
    });

    it('does not allow a PDF download without export permission', function () {
        $this->actingAs(createAlumniReportPdfUser(['view']));

        Livewire::test(AlumniDataReport::class)
            ->assertActionExists('exportPdf')
            ->assertActionHidden('exportPdf')
            ->call('mountAction', 'exportPdf')
            ->assertNoFileDownloaded();
    });

    it('does not allow a PDF download after export permission is revoked', function () {
        $staff = createAlumniReportPdfUser();
        $this->actingAs($staff);
        $page = Livewire::test(AlumniDataReport::class)->assertActionVisible('exportPdf');
        $staff->userAllocation->role->update(['action' => ['view']]);
        $this->actingAs($staff->fresh());

        $page->call('mountAction', 'exportPdf')->assertNoFileDownloaded();
    });
});

it('filters the report and download by linked campus', function () {
    $firstCampus = Campus::query()->create(['name' => 'North Campus']);
    $secondCampus = Campus::query()->create(['name' => 'South Campus']);
    $firstProgram = Program::query()->create(['campus_id' => $firstCampus->id, 'program_name' => 'Information Technology']);
    $secondProgram = Program::query()->create(['campus_id' => $secondCampus->id, 'program_name' => 'Business Administration']);
    $role = Role::query()->create(['name' => 'Report Staff', 'action' => ['view', 'export'], 'access_page' => [AlumniDataReport::getSlug()]]);
    $allocation = UserAllocation::query()->create(['role_id' => $role->id, 'management_scope' => 'all_program', 'max_users' => 10]);
    $user = User::factory()->create(['user_allocation_id' => $allocation->id]);
    $matchingAlumni = Alumni::factory()->for($firstProgram)->create(['user_id' => $user->id]);
    $otherAlumni = Alumni::factory()->for($secondProgram)->create([
        'user_id' => User::factory()->create(['email' => 'other-report-alumni@example.test'])->id,
    ]);

    Excel::fake();
    $this->withoutVite();
    $this->actingAs($user);
    Filament::setCurrentPanel('online');

    Livewire::test(AlumniDataReport::class)
        ->filterTable('campus', ['campus_id' => (string) $firstCampus->id])
        ->assertCanSeeTableRecords([$matchingAlumni])
        ->assertCanNotSeeTableRecords([$otherAlumni])
        ->callAction('exportXlsx');

    Excel::assertDownloaded('alumni-data-report.xlsx', fn (AlumniDataExport $export): bool => $export->query()->pluck('id')->all() === [$matchingAlumni->id]
        && $export->headings()[0][0] === 'North Campus'
    );
});

it('shows and exports only the latest active alumni record per account', function () {
    $campus = Campus::query()->create(['name' => 'North Campus']);
    $program = Program::query()->create([
        'campus_id' => $campus->id,
        'program_name' => 'Information Technology',
    ]);
    $role = Role::query()->create([
        'name' => 'Report Staff',
        'action' => ['view', 'export'],
        'access_page' => [AlumniDataReport::getSlug()],
    ]);
    $allocation = UserAllocation::query()->create([
        'role_id' => $role->id,
        'management_scope' => 'all_program',
        'max_users' => 10,
    ]);
    $staff = User::factory()->create([
        'email' => 'report-staff@example.test',
        'user_allocation_id' => $allocation->id,
    ]);
    $account = User::factory()->create(['email' => 'alumni-owner@example.test']);

    $older = Alumni::factory()->for($program)->create([
        'user_id' => $account->id,
        'employment_status' => 'unemployed',
        'updated_at' => '2026-10-01 10:00:00',
    ]);
    $sameTimeOlder = Alumni::factory()->for($program)->create([
        'user_id' => $account->id,
        'employment_status' => 'unemployed',
        'updated_at' => '2026-10-02 10:00:00',
    ]);
    $latestActive = Alumni::factory()->for($program)->create([
        'user_id' => $account->id,
        'employment_status' => 'employed',
        'updated_at' => '2026-10-02 10:00:00',
    ]);
    $archivedNewer = Alumni::factory()->for($program)->create([
        'user_id' => $account->id,
        'employment_status' => 'untraced',
        'updated_at' => '2026-10-03 10:00:00',
    ]);
    $archivedNewer->delete();

    Excel::fake();
    $this->withoutVite();
    $this->actingAs($staff);
    Filament::setCurrentPanel('online');

    Livewire::test(AlumniDataReport::class)
        ->assertCanSeeTableRecords([$latestActive])
        ->assertCanNotSeeTableRecords([$older, $sameTimeOlder, $archivedNewer])
        ->callAction('exportXlsx');

    Excel::assertDownloaded('alumni-data-report.xlsx', function (AlumniDataExport $export) use ($latestActive): bool {
        $record = $export->query()->sole();

        return $record->is($latestActive)
            && $export->map($record)[4] === 'Employed';
    });
});

it('shows only alumni who graduated in the selected year', function () {
    $program = Program::query()->create([
        'program_name' => 'Bachelor of Science in Information Technology',
        'is_active' => true,
    ]);

    $alumniWhoGraduatedIn2024 = Alumni::factory()->for($program)->create([
        'graduation_year' => '2024-06-01',
    ]);
    $alumniWhoGraduatedIn2025 = Alumni::factory()->for($program)->create([
        'graduation_year' => '2025-06-01',
    ]);

    Filament::setCurrentPanel('online');

    Livewire::test(AlumniDataReport::class)
        ->filterTable('graduation_year', ['year' => '2024'])
        ->assertCanSeeTableRecords([$alumniWhoGraduatedIn2024])
        ->assertCanNotSeeTableRecords([$alumniWhoGraduatedIn2025]);
});

it('shows only alumni in the selected department with the selected employment status', function () {
    $informationTechnology = Program::query()->create([
        'program_name' => 'Bachelor of Science in Information Technology',
        'is_active' => true,
    ]);
    $businessAdministration = Program::query()->create([
        'program_name' => 'Bachelor of Science in Business Administration',
        'is_active' => true,
    ]);

    $matchingAlumni = Alumni::factory()->for($informationTechnology)->create([
        'employment_status' => 'unemployed',
    ]);
    $alumniInOtherDepartment = Alumni::factory()->for($businessAdministration)->create([
        'employment_status' => 'unemployed',
    ]);
    $alumniWithOtherEmploymentStatus = Alumni::factory()->for($informationTechnology)->create([
        'employment_status' => 'employed',
    ]);

    Filament::setCurrentPanel('online');

    Livewire::test(AlumniDataReport::class)
        ->filterTable('department', ['program_id' => (string) $informationTechnology->id])
        ->filterTable('employment_status', ['status' => 'unemployed'])
        ->assertCanSeeTableRecords([$matchingAlumni])
        ->assertCanNotSeeTableRecords([$alumniInOtherDepartment, $alumniWithOtherEmploymentStatus]);
});

it('downloads only alumni who graduated in the filtered year', function () {
    $program = Program::query()->create([
        'program_name' => 'Bachelor of Science in Information Technology',
        'is_active' => true,
    ]);

    $alumniWhoGraduatedIn2024 = Alumni::factory()->for($program)->create([
        'graduation_year' => '2024-06-01',
    ]);
    Alumni::factory()->for($program)->create([
        'graduation_year' => '2025-06-01',
    ]);

    Excel::fake();
    Filament::setCurrentPanel('online');

    Livewire::test(AlumniDataReport::class)
        ->filterTable('graduation_year', ['year' => '2024'])
        ->callAction('exportXlsx');

    Excel::assertDownloaded(
        'alumni-data-report-2024.xlsx',
        fn (AlumniDataExport $export): bool => $export->query()->pluck('id')->all() === [$alumniWhoGraduatedIn2024->id]
    );
});

it('downloads only alumni in the filtered department with the filtered employment status', function () {
    $informationTechnology = Program::query()->create([
        'program_name' => 'Bachelor of Science in Information Technology',
        'is_active' => true,
    ]);
    $businessAdministration = Program::query()->create([
        'program_name' => 'Bachelor of Science in Business Administration',
        'is_active' => true,
    ]);

    $matchingAlumni = Alumni::factory()->for($informationTechnology)->create([
        'employment_status' => 'unemployed',
        'graduation_year' => '2024-06-01',
    ]);
    Alumni::factory()->for($businessAdministration)->create([
        'employment_status' => 'unemployed',
        'graduation_year' => '2024-06-01',
    ]);
    Alumni::factory()->for($informationTechnology)->create([
        'employment_status' => 'employed',
        'graduation_year' => '2024-06-01',
    ]);

    Excel::fake();
    Filament::setCurrentPanel('online');

    Livewire::test(AlumniDataReport::class)
        ->filterTable('graduation_year', ['year' => '2024'])
        ->filterTable('department', ['program_id' => (string) $informationTechnology->id])
        ->filterTable('employment_status', ['status' => 'unemployed'])
        ->callAction('exportXlsx');

    Excel::assertDownloaded(
        'alumni-data-report-2024.xlsx',
        fn (AlumniDataExport $export): bool => $export->query()->pluck('id')->all() === [$matchingAlumni->id]
    );
});

it('exports all graduation years when no graduation year is selected', function () {
    $program = Program::query()->create([
        'program_name' => 'Bachelor of Science in Information Technology',
        'is_active' => true,
    ]);

    $alumniWhoGraduatedIn2024 = Alumni::factory()->for($program)->create([
        'graduation_year' => '2024-06-01',
    ]);
    $alumniWhoGraduatedIn2025 = Alumni::factory()->for($program)->create([
        'graduation_year' => '2025-06-01',
    ]);

    Excel::fake();
    Filament::setCurrentPanel('online');

    Livewire::test(AlumniDataReport::class)
        ->callAction('exportXlsx');

    Excel::assertDownloaded(
        'alumni-data-report.xlsx',
        fn (AlumniDataExport $export): bool => $export->query()->pluck('id')->all() === [
            $alumniWhoGraduatedIn2024->id,
            $alumniWhoGraduatedIn2025->id,
        ]
    );
});

it('opens a compact report row as a details modal', function () {
    $program = Program::query()->create([
        'program_name' => 'Bachelor of Science in Information Technology',
        'is_active' => true,
    ]);
    $alumni = Alumni::factory()->for($program)->create([
        'current_address' => 'Quezon City',
        'remarks' => 'Verified by phone call.',
    ]);

    AlumniEducation::query()->create([
        'alumni_id' => $alumni->id,
        'instituion' => 'Example University',
        'program' => 'Master of Science in Information Technology',
        'degree_level' => 'Masters',
        'units_completed' => 30,
        'status' => 'Completed',
        'started_at' => '2024-08-01',
        'ended_at' => '2026-06-01',
    ]);
    AlumniEmployment::query()->create([
        'alumni_id' => $alumni->id,
        'company_name' => 'Example Technologies',
        'position' => 'Software Engineer',
        'company_address' => 'Makati City',
        'industry' => 'Information Technology',
        'employment_type' => 'Full-time',
        'is_course_related' => true,
        'date_hired' => '2024-07-01',
        'starting_date' => '2024-07-15',
        'supported_documents' => 'employment-certificate.pdf',
        'is_current' => true,
    ]);

    Filament::setCurrentPanel('online');

    Livewire::test(AlumniDataReport::class)
        ->assertTableActionVisible('viewDetails', $alumni)
        ->mountTableAction('viewDetails', $alumni)
        ->assertSet('mountedActions.0.name', 'viewDetails')
        ->assertSet('mountedActions.0.context.recordKey', (string) $alumni->id);

    $this->view('filament.pages.alumni-report-details', [
        'alumni' => $alumni->load(['program', 'alumniEducation', 'alumniEmployment']),
    ])
        ->assertSee('Quezon City')
        ->assertSee('Master of Science in Information Technology')
        ->assertSee('Example Technologies')
        ->assertSee('employment-certificate.pdf');
});
