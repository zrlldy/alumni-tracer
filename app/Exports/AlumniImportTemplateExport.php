<?php

namespace App\Exports;

use App\Models\Campus;
use App\Models\Program;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlumniImportTemplateExport implements WithMultipleSheets
{
    /**
     * @return array<AlumniImportTemplateSheetExport>
     */
    public function sheets(): array
    {
        return [
            $this->alumniSheet(),
            new AlumniImportTemplateSheetExport('Education', [
                'student_number',
                'institution',
                'program',
                'degree_level',
                'units_completed',
                'status',
                'started_at',
                'ended_at',
            ]),
            new AlumniImportTemplateSheetExport('Employment', [
                'student_number',
                'company_name',
                'position',
                'company_address',
                'industry',
                'employment_type',
                'is_course_related',
                'date_hired',
                'starting_date',
                'ended_at',
                'supported_documents',
                'is_current',
            ], [
                ['heading' => 'is_course_related', 'options' => ['Yes', 'No'], 'label' => 'Course relation'],
                ['heading' => 'is_current', 'options' => ['Yes', 'No'], 'label' => 'Current employment'],
            ]),
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
        return new AlumniImportTemplateSheetExport('Alumni', $this->headings(), [
            ['heading' => 'campus', 'options' => Campus::query()->where('is_active', true)->orderBy('name')->pluck('name')->all(), 'label' => 'Campus'],
            ['heading' => 'department', 'options' => Program::query()->where('is_active', true)
                ->whereHas('campus', fn (Builder $query): Builder => $query->where('is_active', true))
                ->orderBy('program_name')->pluck('program_name')->all(), 'label' => 'Department'],
        ]);
    }
}
