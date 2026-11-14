<?php

namespace App\Filament\Imports;

use App\Models\Alumni;
use App\Models\Program;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AlumniImporter implements SkipsEmptyRows, SkipsUnknownSheets, ToModel, WithHeadingRow, WithMultipleSheets, WithValidation
{
    /** @var array<string, true> */
    private array $importedStudentNumbers = [];

    /**
     * @return array<array-key, mixed>
     */
    public function sheets(): array
    {
        return [
            0 => $this,
            'Education' => new AlumniEducationImporter($this),
            'Employment' => new AlumniEmploymentImporter($this),
        ];
    }

    public function onUnknownSheet(string|int $sheetName): void
    {
        // Older import files may not include the optional history worksheets.
    }

    /**
     * @return array<string>
     */
    public function importedStudentNumbers(): array
    {
        return array_keys($this->importedStudentNumbers);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function isEmptyWhen(array $row): bool
    {
        return in_array(strtoupper(trim((string) ($row['remarks'] ?? ''))), [
            'SAMPLE DATA - KEEP AS A GUIDE; THIS ROW WILL NOT BE IMPORTED',
            'SAMPLE DATA - DELETE THIS ROW BEFORE IMPORTING',
        ], true);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function prepareForValidation(array $row, int $index): array
    {
        $campusName = trim((string) ($row['campus'] ?? ''));
        $departmentName = trim((string) ($row['department'] ?? ''));
        $programIds = Program::query()
            ->where('program_name', $departmentName)
            ->where('is_active', true)
            ->whereHas('campus', fn (Builder $query): Builder => $query
                ->where('name', $campusName)
                ->where('is_active', true))
            ->limit(2)
            ->pluck('id');

        return [
            ...$row,
            'campus' => $campusName,
            'department' => $departmentName,
            'program_id' => $programIds->count() === 1 ? $programIds->first() : null,
        ];
    }

    public function model(array $row): Alumni
    {
        $dateTraced = ! empty($row['date_traced'])
            ? Date::excelToDateTimeObject($row['date_traced'])->format('Y-m-d')
            : null;

        $graduationYear = ! empty($row['graduation_year'])
            ? Date::excelToDateTimeObject($row['graduation_year'])->format('Y-m-d')
            : null;

        $this->importedStudentNumbers[(string) $row['student_number']] = true;

        return new Alumni([
            'student_number' => $row['student_number'],
            'first_name' => $row['first_name'],
            'middle_name' => $row['middle_name'] ?? null,
            'last_name' => $row['last_name'],
            'email' => $row['email'] ?? null,
            'phone_number' => $row['phone_number'] ?? null,
            'current_address' => $row['current_address'] ?? null,
            'program_id' => $row['program_id'],
            'graduation_year' => $graduationYear,
            'employment_status' => $row['employment_status'] ?? null,
            'remarks' => $row['remarks'] ?? null,
            'date_traced' => $dateTraced,
            'trace_by' => $row['trace_by'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'student_number' => [
                'bail',
                'required',
                'integer',
                'distinct',
                Rule::unique('alumnis', 'student_number'),
            ],

            'first_name' => [
                'required',
                'string',
                'max:255',
            ],

            'middle_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'last_name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'phone_number' => [
                'nullable',
                'numeric',
            ],

            'current_address' => [
                'nullable',
                'string',
                'max:255',
            ],

            'campus' => [
                'required',
                'exists:campuses,name',
            ],

            'department' => [
                'required',
                'exists:programs,program_name',
            ],

            'program_id' => [
                'required',
                'exists:programs,id',
            ],

            'graduation_year' => [
                'required',
            ],

            'employment_status' => [
                'nullable',
                'string',
                'max:255',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:255',
            ],

            'date_traced' => [
                'nullable',
            ],

            'trace_by' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'student_number.unique' => 'This student number already exists in the database.',

            'student_number.distinct' => 'This student number appears more than once in the Excel file.',

            'student_number.required' => 'Student number is required.',
            'program_id.required' => 'The selected department must belong to the selected campus.',
        ];
    }

    public function customValidationAttributes(): array
    {
        return ['program_id' => 'department'];
    }
}
