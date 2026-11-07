<?php

namespace App\Filament\Imports;

use App\Models\Alumni;
use App\Models\AlumniEmployment;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AlumniEmploymentImporter implements ToModel, WithHeadingRow, WithValidation
{
    public function __construct(private AlumniImporter $alumniImporter) {}

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function prepareForValidation(array $row, int $index): array
    {
        $row['student_number'] = trim((string) ($row['student_number'] ?? ''));
        $row['date_hired'] = $this->normalizeExcelDate($row['date_hired'] ?? null);
        $row['starting_date'] = $this->normalizeExcelDate($row['starting_date'] ?? null);
        $row['ended_at'] = $this->normalizeExcelDate($row['ended_at'] ?? null);
        $row['is_course_related'] = $this->normalizeBoolean($row['is_course_related'] ?? null);
        $row['is_current'] = $this->normalizeBoolean($row['is_current'] ?? null);

        return $row;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): AlumniEmployment
    {
        $alumni = Alumni::query()
            ->where('student_number', $row['student_number'])
            ->firstOrFail();

        return new AlumniEmployment([
            'alumni_id' => $alumni->id,
            'company_name' => $row['company_name'],
            'position' => $row['position'] ?? null,
            'company_address' => $row['company_address'],
            'industry' => $row['industry'] ?? null,
            'employment_type' => $row['employment_type'],
            'is_course_related' => $row['is_course_related'] ?? null,
            'date_hired' => $row['date_hired'],
            'starting_date' => $row['starting_date'] ?? null,
            'ended_at' => $row['ended_at'] ?? null,
            'supported_documents' => $row['supported_documents'],
            'is_current' => $row['is_current'],
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'student_number' => [
                'required',
                'integer',
                Rule::in($this->alumniImporter->importedStudentNumbers()),
                Rule::exists('alumnis', 'student_number'),
            ],
            'company_name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'company_address' => ['required', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['required', 'string', 'max:255'],
            'is_course_related' => ['nullable', 'boolean'],
            'date_hired' => ['required', 'date'],
            'starting_date' => ['nullable', 'date'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:date_hired', 'after_or_equal:starting_date'],
            'supported_documents' => ['required', 'string', 'max:255'],
            'is_current' => ['required', 'boolean'],
        ];
    }

    private function normalizeExcelDate(mixed $value): mixed
    {
        if (! is_numeric($value) || (float) $value < 1 || (float) $value > 2958465) {
            return $value;
        }

        return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
    }

    private function normalizeBoolean(mixed $value): mixed
    {
        $value = strtolower(trim((string) $value));

        if ($value === '') {
            return null;
        }

        return match ($value) {
            'yes', 'true', '1' => 1,
            'no', 'false', '0' => 0,
            default => $value,
        };
    }
}
