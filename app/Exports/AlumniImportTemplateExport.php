<?php

namespace App\Exports;

use App\Models\Alumni;
use App\Models\Campus;
use App\Models\Program;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlumniImportTemplateExport implements Export, WithMultipleSheets
{
    /**
     * @return array<AlumniImportTemplateSheetExport>
     */
    public function sheets(): array
    {
        return [
            $this->alumniSheet(),
        ];
    }

    /**
     * @return array<string>
     */
    public function headings(): array
    {
        return [
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
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return $this->alumniSheet()->styles($sheet);
    }

    private function alumniSheet(): AlumniImportTemplateSheetExport
    {
        $activePrograms = Program::query()
            ->where('is_active', true)
            ->whereHas('campus', fn (Builder $query): Builder => $query->where('is_active', true))
            ->with('campus')
            ->orderBy('program_name')
            ->get();
        $sampleProgram = $activePrograms->first(fn (Program $program): bool => $activePrograms
            ->where('campus.name', $program->campus->name)
            ->where('program_name', $program->program_name)
            ->count() === 1);

        if ($sampleProgram === null) {
            throw new \RuntimeException('Cannot generate an importable sample without an unambiguous active campus and department.');
        }

        return new AlumniImportTemplateSheetExport('Alumni', $this->headings(), [
            ['heading' => 'campus', 'options' => Campus::query()->where('is_active', true)->orderBy('name')->pluck('name')->all(), 'label' => 'Campus'],
            ['heading' => 'department', 'options' => $activePrograms->pluck('program_name')->all(), 'label' => 'Department'],
        ], [$this->sampleAlumniRow($sampleProgram)]);
    }

    /**
     * @return array<mixed>
     */
    private function sampleAlumniRow(Program $sampleProgram): array
    {
        return [
            $this->sampleStudentNumber(),
            'Alex',
            'B.',
            'Example',
            'alex@example.com',
            '00000000000',
            '123 Example Street',
            $sampleProgram->campus->name,
            $sampleProgram->program_name,
            Date::PHPToExcel(new \DateTimeImmutable('2023-06-15')),
            'employed',
            'SAMPLE DATA - DELETE THIS ROW BEFORE IMPORTING',
            Date::PHPToExcel(new \DateTimeImmutable('2024-01-15')),
            'Sample Tracer',
        ];
    }

    private function sampleStudentNumber(): int
    {
        $studentNumber = 999_999_999;

        while ($studentNumber > 0 && Alumni::withTrashed()->where('student_number', $studentNumber)->exists()) {
            $studentNumber--;
        }

        if ($studentNumber === 0) {
            throw new \RuntimeException('No unused student number is available for the sample row.');
        }

        return $studentNumber;
    }
}
