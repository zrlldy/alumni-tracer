<?php

use App\Exports\AlumniImportTemplateExport;
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

it('places campus and department dropdowns under their headings', function () {
    $campus = Campus::factory()->create(['name' => 'North Campus']);
    Program::factory()->for($campus)->create(['program_name' => 'Nursing', 'is_active' => true]);
    $sheet = (new Spreadsheet)->getActiveSheet();

    $styles = (new AlumniImportTemplateExport)->styles($sheet);

    expect(array_key_first($styles))->toBe('A1:N1')
        ->and($sheet->dataValidationExists('H1048576'))->toBeTrue()
        ->and($sheet->dataValidationExists('I1048576'))->toBeTrue();
});
