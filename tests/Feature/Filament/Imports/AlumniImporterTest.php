<?php

use App\Exports\AlumniImportTemplateExport;
use App\Filament\Imports\AlumniEducationImporter;
use App\Filament\Imports\AlumniEmploymentImporter;
use App\Filament\Imports\AlumniImporter;
use App\Models\Alumni;
use App\Models\AlumniEducation;
use App\Models\AlumniEmployment;
use App\Models\Campus;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

it('resolves the department within its selected campus', function () {
    $campus = Campus::factory()->create(['name' => 'North Campus']);
    $program = Program::factory()->for($campus)->create(['program_name' => 'Nursing', 'is_active' => true]);

    $row = (new AlumniImporter)->prepareForValidation([
        'campus' => ' North Campus ',
        'department' => 'Nursing',
    ], 2);

    expect($row['program_id'])->toBe($program->id);
});

it('does not resolve a department from a different campus', function () {
    $campus = Campus::factory()->create(['name' => 'North Campus']);
    $program = Program::factory()->for($campus)->create(['program_name' => 'Nursing', 'is_active' => true]);

    $row = (new AlumniImporter)->prepareForValidation([
        'campus' => 'South Campus',
        'department' => 'Nursing',
    ], 2);

    expect($row['program_id'])->toBeNull();
});

it('does not choose between duplicate campus and department matches', function () {
    $firstCampus = Campus::factory()->create(['name' => 'North Campus']);
    $secondCampus = Campus::factory()->create(['name' => 'North Campus']);
    Program::factory()->for($firstCampus)->create(['program_name' => 'Nursing', 'is_active' => true]);
    Program::factory()->for($secondCampus)->create(['program_name' => 'Nursing', 'is_active' => true]);

    $row = (new AlumniImporter)->prepareForValidation([
        'campus' => 'North Campus',
        'department' => 'Nursing',
    ], 2);

    expect($row['program_id'])->toBeNull();
});

it('does not resolve inactive campuses or departments', function () {
    $activeCampus = Campus::factory()->create(['name' => 'North Campus', 'is_active' => true]);
    $inactiveCampus = Campus::factory()->create(['name' => 'Closed Campus', 'is_active' => false]);
    Program::factory()->for($activeCampus)->create(['program_name' => 'Inactive Nursing', 'is_active' => false]);
    Program::factory()->for($inactiveCampus)->create(['program_name' => 'Closed Nursing', 'is_active' => true]);
    $importer = new AlumniImporter;

    $inactiveDepartment = $importer->prepareForValidation([
        'campus' => 'North Campus',
        'department' => 'Inactive Nursing',
    ], 2);
    $inactiveCampusRow = $importer->prepareForValidation([
        'campus' => 'Closed Campus',
        'department' => 'Closed Nursing',
    ], 3);

    expect($inactiveDepartment['program_id'])->toBeNull()
        ->and($inactiveCampusRow['program_id'])->toBeNull();
});

it('rejects pasted campus and department values that do not match during import', function () {
    $northCampus = Campus::factory()->create(['name' => 'North Campus']);
    $southCampus = Campus::factory()->create(['name' => 'South Campus']);
    Program::factory()->for($northCampus)->create(['program_name' => 'Nursing', 'is_active' => true]);
    Program::factory()->for($southCampus)->create(['program_name' => 'Education', 'is_active' => true]);
    $path = sys_get_temp_dir().'/alumni-import-mismatch-'.Str::uuid().'.xlsx';
    $spreadsheet = null;

    try {
        file_put_contents($path, Excel::raw(new AlumniImportTemplateExport, ExcelWriter::XLSX));
        $spreadsheet = IOFactory::load($path);
        $spreadsheet->getSheetByName('Alumni')->fromArray([[
            200002, 'Taylor', '', 'Example', '', '', '', 'North Campus', 'Education',
            ExcelDate::PHPToExcel(new DateTimeImmutable('2021-06-01')), '', '', '', '',
        ]], null, 'A3');
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();
        $spreadsheet = null;
        $failure = null;

        try {
            Excel::import(new AlumniImporter, $path);
        } catch (ExcelValidationException $exception) {
            $failure = $exception;
        }
    } finally {
        $spreadsheet?->disconnectWorksheets();

        if (is_file($path)) {
            unlink($path);
        }
    }

    expect($failure)->toBeInstanceOf(ExcelValidationException::class)
        ->and($failure->failures()[0]->row())->toBe(3)
        ->and($failure->failures()[0]->values()['campus'])->toBe('North Campus')
        ->and($failure->failures()[0]->values()['department'])->toBe('Education')
        ->and(Alumni::query()->where('student_number', 200002)->exists())->toBeFalse();
});

it('skips current and previous sample rows while importing real rows and optional details', function () {
    $campus = Campus::factory()->create(['name' => 'North Campus']);
    $program = Program::factory()->for($campus)->create(['program_name' => 'Nursing', 'is_active' => true]);
    $sampleRow = (new AlumniImportTemplateExport)->sheets()[0]->array()[0];
    $previousSampleRow = $sampleRow;
    $previousSampleRow[0]--;
    $previousSampleRow[11] = 'SAMPLE DATA - DELETE THIS ROW BEFORE IMPORTING';

    $spreadsheet = new Spreadsheet;
    $alumniSheet = $spreadsheet->getActiveSheet();
    $alumniSheet->setTitle('Alumni');
    $alumniSheet->fromArray([
        [
            'student_number', 'first_name', 'middle_name', 'last_name', 'email', 'phone_number',
            'current_address', 'campus', 'department', 'graduation_year', 'employment_status',
            'remarks', 'date_traced', 'trace_by',
        ],
        $sampleRow,
        $previousSampleRow,
        ['200001', 'Taylor', '', 'Example', '', '', '', 'North Campus', 'Nursing', ExcelDate::PHPToExcel(new DateTimeImmutable('2021-06-01')), '', '', '', ''],
    ], null, 'A1');
    $alumniSheet->getStyle('A132:N132')->getFont()->setBold(true);

    $educationSheet = $spreadsheet->createSheet();
    $educationSheet->setTitle('Education');
    $educationSheet->fromArray([
        ['student_number', 'institution', 'program', 'degree_level', 'units_completed', 'status', 'started_at', 'ended_at'],
        ['200001', 'Sample University', '', '', '', '', '', ''],
    ], null, 'A1');
    $educationSheet->getStyle('A4:H4')->getFont()->setBold(true);

    $employmentSheet = $spreadsheet->createSheet();
    $employmentSheet->setTitle('Employment');
    $employmentSheet->fromArray([
        [
            'student_number', 'company_name', 'position', 'company_address', 'industry', 'employment_type',
            'is_course_related', 'date_hired', 'starting_date', 'ended_at', 'supported_documents', 'is_current',
        ],
        ['200001', 'Sample Employer', '', '', '', '', '', '2022-01-15', '', '', '', ''],
    ], null, 'A1');
    $employmentSheet->getStyle('A4:L4')->getFont()->setBold(true);

    $path = sys_get_temp_dir().'/alumni-import-'.Str::uuid().'.xlsx';

    try {
        (new Xlsx($spreadsheet))->save($path);

        $failure = null;

        try {
            Excel::import(new AlumniImporter, $path);
        } catch (Throwable $throwable) {
            $failure = $throwable;
        }

        expect($failure?->getMessage())->toBeNull();
    } finally {
        $spreadsheet->disconnectWorksheets();

        if (is_file($path)) {
            unlink($path);
        }
    }

    $alumni = Alumni::query()->where('student_number', 200001)->firstOrFail();

    expect(Alumni::query()->count())->toBe(1)
        ->and($alumni->middle_name)->toBeNull()
        ->and($alumni->email)->toBeNull()
        ->and($alumni->phone_number)->toBeNull()
        ->and($alumni->current_address)->toBeNull()
        ->and(Alumni::query()->whereIn('student_number', [$sampleRow[0], $previousSampleRow[0]])->exists())->toBeFalse();

    expect(AlumniEducation::query()->where('alumni_id', $alumni->id)->firstOrFail()->only([
        'program', 'degree_level', 'units_completed', 'status', 'started_at', 'ended_at',
    ]))->toBe([
        'program' => null,
        'degree_level' => null,
        'units_completed' => null,
        'status' => null,
        'started_at' => null,
        'ended_at' => null,
    ]);

    expect(AlumniEmployment::query()->where('alumni_id', $alumni->id)->firstOrFail()->only([
        'company_address', 'employment_type', 'supported_documents', 'is_current',
    ]))->toBe([
        'company_address' => null,
        'employment_type' => null,
        'supported_documents' => null,
        'is_current' => null,
    ]);

    $optionalFieldsMigration = require database_path('migrations/2026_11_14_121906_make_optional_excel_import_fields_nullable.php');
    $rollbackFailure = null;

    try {
        $optionalFieldsMigration->down();
    } catch (RuntimeException $exception) {
        $rollbackFailure = $exception;
    }

    expect($rollbackFailure)->toBeInstanceOf(RuntimeException::class);
});

it('still requires identifiers and primary labels on imported rows', function () {
    $alumniImporter = new AlumniImporter;
    $alumniErrors = Validator::make([], $alumniImporter->rules())->errors();
    $educationImporter = new AlumniEducationImporter($alumniImporter);
    $employmentImporter = new AlumniEmploymentImporter($alumniImporter);
    $educationErrors = Validator::make([], $educationImporter->rules())->errors();
    $employmentErrors = Validator::make([], $employmentImporter->rules())->errors();
    $educationEndErrors = Validator::make(['ended_at' => '2020-01-01'], $educationImporter->rules())->errors();
    $employmentEndErrors = Validator::make(['ended_at' => '2020-01-01'], $employmentImporter->rules())->errors();

    expect($alumniErrors->has('student_number'))->toBeTrue()
        ->and($alumniErrors->has('first_name'))->toBeTrue()
        ->and($alumniErrors->has('last_name'))->toBeTrue()
        ->and($educationErrors->has('student_number'))->toBeTrue()
        ->and($educationErrors->has('institution'))->toBeTrue()
        ->and($employmentErrors->has('student_number'))->toBeTrue()
        ->and($employmentErrors->has('company_name'))->toBeTrue()
        ->and($educationEndErrors->has('started_at'))->toBeTrue()
        ->and($employmentEndErrors->has('starting_date'))->toBeTrue();
});
