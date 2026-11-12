<?php

use App\Exports\AlumniDataPdfExport;
use App\Exports\AlumniDataPdfWriter;
use App\Models\Alumni;
use App\Models\AlumniEmployment;
use App\Models\Campus;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

uses(RefreshDatabase::class);

it('renders the existing report fields and escapes alumni text for PDF', function () {
    $campus = Campus::factory()->create(['name' => 'North Campus']);
    $program = Program::factory()->for($campus)->create([
        'program_name' => 'Information Technology',
        'is_active' => true,
    ]);
    $alumni = Alumni::factory()->for($program)->create([
        'student_number' => 2024001,
        'first_name' => 'Ana',
        'last_name' => 'Reyes',
        'graduation_year' => '2024-06-01',
        'employment_status' => 'employed',
        'phone_number' => '09171234567',
        'remarks' => '<script>alert("report")</script>',
    ]);
    AlumniEmployment::query()->create([
        'alumni_id' => $alumni->id,
        'company_name' => 'Example Company',
        'company_address' => 'Example City',
        'employment_type' => 'Full-time',
        'date_hired' => '2024-07-01',
        'supported_documents' => 'employment-certificate.pdf',
        'is_current' => true,
    ]);

    $export = new AlumniDataPdfExport(campusId: $campus->id);
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray([
        ...$export->headings(),
        $export->map($export->query()->sole()),
    ]);

    $html = (new AlumniDataPdfWriter($spreadsheet))->generateHtmlAll();

    expect($html)->toContain(
        'North Campus - Information Technology',
        'Ana',
        'Reyes',
        '2024001',
        '2024-06-01',
        '09171234567',
        'Example Company',
        'employment-certificate.pdf',
        'TRACER',
        '&lt;script&gt;',
    )->not->toContain('<script>alert("report")</script>');
});

it('generates a valid PDF for an empty or multi-page report', function (int $recordCount) {
    $program = Program::factory()->create([
        'program_name' => 'Information Technology',
        'is_active' => true,
    ]);
    $records = Alumni::factory()->count($recordCount)->for($program)->create([
        'graduation_year' => '2024-06-01',
        'date_traced' => '2026-11-01',
    ])->each(function (Alumni $alumni): void {
        $alumni->alumniEmployment()->create([
            'company_name' => 'Example Technologies Corporation',
            'company_address' => '123 Example Avenue, Example City',
            'employment_type' => 'Full-time',
            'date_hired' => '2024-07-01',
            'supported_documents' => 'employment-certificate.pdf',
            'is_current' => true,
        ]);
    });
    $records->first()?->alumniEmployment()->update([
        'supported_documents' => 'https://example.test/documents/'.str_repeat('abcdefghij', 40).'/reference-end.pdf',
    ]);
    Storage::fake('local');

    $stored = Excel::store(new AlumniDataPdfExport, 'alumni-data-report.pdf', 'local', AlumniDataPdfExport::WRITER_TYPE);

    expect($stored)->toBeTrue();
    Storage::disk('local')->assertExists('alumni-data-report.pdf');
    expect(Storage::disk('local')->get('alumni-data-report.pdf'))->toStartWith('%PDF-');
})->with(['empty report' => 0, 'multi-page report' => 35]);
