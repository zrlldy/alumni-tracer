<?php

use App\Exports\AlumniDataExport;
use App\Filament\Pages\AlumniDataReport;
use App\Models\Alumni;
use App\Models\AlumniEducation;
use App\Models\AlumniEmployment;
use App\Models\Program;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

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
