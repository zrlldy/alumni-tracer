<?php

namespace App\Exports;

use App\Models\Alumni;
use App\Models\Program;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlumniImportTemplateExport implements Export, WithMultipleSheets
{
    /**
     * @return array<AlumniImportTemplateReferenceSheetExport|AlumniImportTemplateSheetExport>
     */
    public function sheets(): array
    {
        return $this->sheetExports();
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
        return $this->sheetExports()[0]->styles($sheet);
    }

    /**
     * @return array<AlumniImportTemplateReferenceSheetExport|AlumniImportTemplateSheetExport>
     */
    private function sheetExports(): array
    {
        $activePrograms = Program::query()
            ->where('is_active', true)
            ->whereHas('campus', fn (Builder $query): Builder => $query->where('is_active', true))
            ->with('campus')
            ->orderBy('program_name')
            ->get();
        $departmentsByCampus = $this->departmentsByCampus($activePrograms);
        $sampleProgram = $activePrograms->first(fn (Program $program): bool => in_array(
            $program->program_name,
            $departmentsByCampus[$program->campus->name] ?? [],
            true,
        ));

        if ($sampleProgram === null) {
            throw new \RuntimeException('Cannot generate an importable sample without an unambiguous active campus and department.');
        }

        return [
            new AlumniImportTemplateSheetExport('Alumni', $this->headings(), [
                ['heading' => 'campus', 'formula' => '=_CampusList', 'label' => 'Campus'],
                ['heading' => 'department', 'formula' => '=INDIRECT("Department_"&MATCH($H2,_CampusList,0))', 'label' => 'Department'],
                ['heading' => 'employment_status', 'options' => ['employed', 'unemployed', 'untraced'], 'label' => 'Employment Status', 'allow_blank' => true],
            ], [$this->sampleAlumniRow($sampleProgram)]),
            new AlumniImportTemplateReferenceSheetExport($departmentsByCampus),
        ];
    }

    /**
     * @param  Collection<int, Program>  $activePrograms
     * @return array<string, array<string>>
     */
    private function departmentsByCampus(Collection $activePrograms): array
    {
        return $activePrograms
            ->groupBy(fn (Program $program): string => $program->campus->name)
            ->map(fn (Collection $programs, string $campusName): array => $programs
                ->filter(fn (Program $program): bool => $activePrograms
                    ->where('campus.name', $campusName)
                    ->where('program_name', $program->program_name)
                    ->count() === 1)
                ->pluck('program_name')
                ->unique()
                ->sort()
                ->values()
                ->all())
            ->filter(fn (array $departments): bool => $departments !== [])
            ->sortKeys()
            ->all();
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
            'SAMPLE DATA - KEEP AS A GUIDE; THIS ROW WILL NOT BE IMPORTED',
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
