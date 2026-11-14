<?php

use App\Exports\AlumniImportTemplateExport;
use App\Models\Alumni;
use App\Models\Campus;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

uses(RefreshDatabase::class);

it('exports the alumni fields with campus and department headings', function () {
    expect((new AlumniImportTemplateExport)->headings())->toBe([
        'student_number',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'phone_number',
        'current_address',
        'campus',
        'department',
        'graduation_year',
        'employment_status',
        'remarks',
        'date_traced',
        'trace_by',
    ]);
});

it('exports one alumni sheet with sample values for every field', function () {
    $campus = Campus::factory()->create(['name' => 'North Campus']);
    $program = Program::factory()->for($campus)->create(['program_name' => 'Nursing', 'is_active' => true]);
    Alumni::factory()->for($program)->create(['student_number' => 999_999_999]);
    $sheets = (new AlumniImportTemplateExport)->sheets();

    expect(array_map(fn ($sheet): string => $sheet->title(), $sheets))
        ->toBe(['Alumni']);

    $sampleRow = $sheets[0]->array()[0];

    expect($sampleRow)->toHaveCount(14)
        ->and($sampleRow)->not->toContain('')
        ->and(is_int($sampleRow[0]))->toBeTrue()
        ->and($sampleRow[0])->toBe(999_999_998)
        ->and($sampleRow[4])->toBe('alex@example.com')
        ->and($sampleRow[5])->toBe('00000000000')
        ->and($sampleRow[7])->toBe('North Campus')
        ->and($sampleRow[8])->toBe('Nursing')
        ->and(is_numeric($sampleRow[9]))->toBeTrue()
        ->and($sampleRow[10])->toBe('employed')
        ->and($sampleRow[11])->toBe('SAMPLE DATA - KEEP AS A GUIDE; THIS ROW WILL NOT BE IMPORTED');
});

it('requires an unambiguous active campus and department pair for sample data', function () {
    $firstCampus = Campus::factory()->create(['name' => 'North Campus']);
    $secondCampus = Campus::factory()->create(['name' => 'North Campus']);
    Program::factory()->for($firstCampus)->create(['program_name' => 'Nursing', 'is_active' => true]);
    Program::factory()->for($secondCampus)->create(['program_name' => 'Nursing', 'is_active' => true]);
    $failure = null;

    try {
        (new AlumniImportTemplateExport)->sheets();
    } catch (RuntimeException $exception) {
        $failure = $exception;
    }

    expect($failure)->toBeInstanceOf(RuntimeException::class);
});

it('places campus and department dropdowns under their headings', function () {
    $campus = Campus::factory()->create(['name' => 'North Campus']);
    Program::factory()->for($campus)->create(['program_name' => 'Nursing', 'is_active' => true]);
    $sheet = (new Spreadsheet)->getActiveSheet();

    $styles = (new AlumniImportTemplateExport)->styles($sheet);

    expect(array_key_first($styles))->toBe('A1:N1')
        ->and($sheet->dataValidationExists('H1048576'))->toBeTrue()
        ->and($sheet->dataValidationExists('I1048576'))->toBeTrue()
        ->and($sheet->getDataValidation('H2')->getAllowBlank())->toBeFalse()
        ->and($sheet->getDataValidation('I2')->getAllowBlank())->toBeFalse();
});

it('marks the sample row and formats phone and date examples correctly', function () {
    $campus = Campus::factory()->create(['name' => 'North Campus']);
    Program::factory()->for($campus)->create(['program_name' => 'Nursing', 'is_active' => true]);
    $spreadsheet = new Spreadsheet;
    $sheet = (new AlumniImportTemplateExport)->sheets()[0];

    $styles = $sheet->styles($spreadsheet->getActiveSheet());

    expect($styles['A2:N2']['fill']['startColor']['rgb'])->toBe('FFF2CC')
        ->and($spreadsheet->getActiveSheet()->getStyle('A2')->getNumberFormat()->getFormatCode())->toBe('@')
        ->and($spreadsheet->getActiveSheet()->getStyle('F2')->getNumberFormat()->getFormatCode())->toBe('@')
        ->and($spreadsheet->getActiveSheet()->getStyle('J2')->getNumberFormat()->getFormatCode())->toBe('yyyy-mm-dd')
        ->and($spreadsheet->getActiveSheet()->getStyle('M2')->getNumberFormat()->getFormatCode())->toBe('yyyy-mm-dd');
});
