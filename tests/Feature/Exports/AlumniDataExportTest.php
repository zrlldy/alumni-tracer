<?php

use App\Exports\AlumniDataExport;
use App\Models\Alumni;
use App\Models\AlumniEducation;
use App\Models\AlumniEmployment;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Conditional;

uses(RefreshDatabase::class);

it('exports alumni data with education and employment history', function () {
    $program = Program::query()->create([
        'program_name' => 'Bachelor of Science in Information Technology',
        'is_active' => true,
    ]);

    $alumni = Alumni::query()->create([
        'student_number' => 2024001,
        'first_name' => 'Maria',
        'middle_name' => 'Santos',
        'last_name' => 'Cruz',
        'email' => 'maria.cruz@example.test',
        'phone_number' => '09171234567',
        'current_address' => 'Quezon City',
        'program_id' => $program->id,
        'graduation_year' => '2024-06-01',
        'employment_status' => 'employed',
        'remarks' => 'Verified by phone call.',
        'date_traced' => '2026-10-07',
        'trace_by' => 'Admin User',
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
        'ended_at' => null,
        'supported_documents' => 'employment-certificate.pdf',
        'is_current' => true,
    ]);

    $export = new AlumniDataExport;
    $record = $export->query()->firstOrFail();

    expect($record->relationLoaded('program'))->toBeTrue()
        ->and($record->relationLoaded('alumniEducation'))->toBeTrue()
        ->and($record->relationLoaded('alumniEmployment'))->toBeTrue()
        ->and($export->map($record))->toBe([
            2024001,
            'Maria',
            'Santos',
            'Cruz',
            'maria.cruz@example.test',
            '09171234567',
            'Quezon City',
            'Bachelor of Science in Information Technology',
            '2024-06-01',
            'employed',
            'Verified by phone call.',
            '2026-10-07',
            'Admin User',
            'Institution: Example University | Program: Master of Science in Information Technology | Degree Level: Masters | Units Completed: 30 | Status: Completed | Started: 2024-08-01 | Ended: 2026-06-01',
            'Company: Example Technologies | Position: Software Engineer | Address: Makati City | Industry: Information Technology | Type: Full-time | Course Related: Yes | Date Hired: 2024-07-01 | Starting Date: 2024-07-15 | Ended: - | Documents: employment-certificate.pdf | Current: Yes',
        ]);
});

it('builds a valid xlsx workbook', function () {
    $workbook = Excel::raw(new AlumniDataExport, ExcelWriter::XLSX);

    expect($workbook)->toStartWith('PK');
});

it('highlights unemployed and untraced alumni rows in the workbook', function () {
    $program = Program::query()->create([
        'program_name' => 'Bachelor of Science in Information Technology',
        'is_active' => true,
    ]);

    Alumni::factory()->for($program)->create([
        'employment_status' => 'unemployed',
    ]);
    Alumni::factory()->for($program)->create([
        'employment_status' => 'untraced',
    ]);
    Alumni::factory()->for($program)->create([
        'employment_status' => 'employed',
    ]);

    $temporaryFile = tempnam(sys_get_temp_dir(), 'alumni-data-report-');

    if ($temporaryFile === false) {
        throw new RuntimeException('Unable to create a temporary XLSX file.');
    }

    file_put_contents($temporaryFile, Excel::raw(new AlumniDataExport, ExcelWriter::XLSX));

    try {
        $conditionalStyles = IOFactory::load($temporaryFile)
            ->getActiveSheet()
            ->getConditionalStyles('A2:O4');

        expect($conditionalStyles)->toHaveCount(2);
        expect($conditionalStyles[0]->getConditionType())->toBe(Conditional::CONDITION_EXPRESSION);
        expect($conditionalStyles[0]->getConditions())->toBe(['$J2="unemployed"']);
        expect($conditionalStyles[0]->getStyle()->getFill()->getStartColor()->getRGB())->toBe('FFF2CC');
        expect($conditionalStyles[1]->getConditions())->toBe(['$J2="untraced"']);
        expect($conditionalStyles[1]->getStyle()->getFill()->getStartColor()->getRGB())->toBe('F4CCCC');
    } finally {
        unlink($temporaryFile);
    }
});

it('exports only alumni who graduated in the selected year', function () {
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

    $exportedAlumniIds = (new AlumniDataExport(2024))->query()->pluck('id')->all();

    expect($exportedAlumniIds)->toBe([$alumniWhoGraduatedIn2024->id]);
});

it('exports only alumni in the selected department with the selected employment status', function () {
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
    Alumni::factory()->for($businessAdministration)->create([
        'employment_status' => 'unemployed',
    ]);
    Alumni::factory()->for($informationTechnology)->create([
        'employment_status' => 'employed',
    ]);

    $exportedAlumniIds = (new AlumniDataExport(
        programId: $informationTechnology->id,
        employmentStatus: 'unemployed',
    ))->query()->pluck('id')->all();

    expect($exportedAlumniIds)->toBe([$matchingAlumni->id]);
});

it('downloads the alumni report as an xlsx file', function () {
    Excel::fake();

    Excel::download(new AlumniDataExport, 'alumni-data-report.xlsx');

    Excel::assertDownloaded(
        'alumni-data-report.xlsx',
        fn (AlumniDataExport $export): bool => $export->query()->count() === 0
    );
});
