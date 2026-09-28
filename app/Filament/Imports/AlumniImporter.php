<?php

namespace App\Filament\Imports;

use App\Models\Alumni;
use App\Models\Program;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AlumniImporter implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row): Alumni
    {
        $program = Program::where('program_name', $row['program'])
            ->where('is_active', true)
            ->first();

        $dateTraced = ! empty($row['date_traced'])
            ? Date::excelToDateTimeObject($row['date_traced'])->format('Y-m-d')
            : null;

        $graduationYear = ! empty($row['graduation_year'])
            ? Date::excelToDateTimeObject($row['graduation_year'])->format('Y-m-d')
            : null;

        // IMPORTANT:
        // Do not use Alumni::create() here.
        return new Alumni([
            'student_number' => $row['student_number'],
            'first_name' => $row['first_name'],
            'middle_name' => $row['middle_name'] ?? null,
            'last_name' => $row['last_name'],
            'email' => $row['email'] ?? null,
            'phone_number' => $row['phone_number'] ?? null,
            'current_address' => $row['current_address'] ?? null,
            'program_id' => $program->id,
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

            'program' => [
                'required',
                'exists:programs,program_name',
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
        ];
    }
}
