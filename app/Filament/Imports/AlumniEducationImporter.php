<?php

namespace App\Filament\Imports;

use App\Models\Alumni;
use App\Models\AlumniEducation;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AlumniEducationImporter implements SkipsEmptyRows, ToModel, WithHeadingRow, WithValidation
{
    public function __construct(private AlumniImporter $alumniImporter) {}

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function prepareForValidation(array $row, int $index): array
    {
        $row['student_number'] = trim((string) ($row['student_number'] ?? ''));
        $row['started_at'] = $this->normalizeExcelDate($row['started_at'] ?? null);
        $row['ended_at'] = $this->normalizeExcelDate($row['ended_at'] ?? null);

        return $row;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function model(array $row): AlumniEducation
    {
        $alumni = Alumni::query()
            ->where('student_number', $row['student_number'])
            ->firstOrFail();

        return new AlumniEducation([
            'alumni_id' => $alumni->id,
            'instituion' => $row['institution'],
            'program' => $row['program'] ?? null,
            'degree_level' => $row['degree_level'] ?? null,
            'units_completed' => $row['units_completed'] ?? null,
            'status' => $row['status'] ?? null,
            'started_at' => $row['started_at'] ?? null,
            'ended_at' => $row['ended_at'] ?? null,
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
            'institution' => ['required', 'string', 'max:255'],
            'program' => ['nullable', 'string', 'max:255'],
            'degree_level' => ['nullable', 'string', 'max:255'],
            'units_completed' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'status' => ['nullable', 'string', 'max:255'],
            'started_at' => ['nullable', 'date', 'required_with:ended_at'],
            'ended_at' => ['nullable', 'date', 'after_or_equal:started_at'],
        ];
    }

    private function normalizeExcelDate(mixed $value): mixed
    {
        if (! is_numeric($value) || (float) $value < 1 || (float) $value > 2958465) {
            return $value;
        }

        return Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
    }
}
