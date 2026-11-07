<?php

namespace App\Exports;

use App\Models\Campus;
use App\Models\Program;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlumniImportTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles
{
    public function array(): array
    {
        return [];
    }

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

    public function styles(Worksheet $sheet): ?array
    {
        $campuses = Campus::query()->where('is_active', true)->orderBy('name')->pluck('name')->all();
        $programs = Program::query()->where('is_active', true)->orderBy('program_name')->pluck('program_name')->all();

        // Text format for student_number, including A2 and all rows below.
        $sheet->getStyle('A:A')
            ->getNumberFormat()
            ->setFormatCode('@');

        // Enable Excel's quote-prefix style.
        $sheet->getStyle('A:A')->setQuotePrefix(true);

        foreach ([['H', $campuses, 'Campus'], ['I', $programs, 'Department']] as [$column, $options, $label]) {
            if ($options === []) {
                continue;
            }

            $range = "{$column}2:{$column}1048576";

            $validation = new DataValidation;

            $validation
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP)
                ->setAllowBlank(false)
                ->setShowDropDown(true)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setPromptTitle("Select {$label}")
                ->setPrompt('Choose a '.$label.' from the dropdown.')
                ->setErrorTitle("Invalid {$label}")
                ->setError('Please select a '.$label.' from the list.')
                ->setFormula1(
                    '"'.str_replace('"', '""', implode(',', $options)).'"'
                )
                ->setSqref($range);

            $sheet->setDataValidation($range, $validation);
        }

        return [
            'A1:'.Coordinate::stringFromColumnIndex(count($this->headings())).'1' => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '800000'],
                ],
            ],
        ];
    }
}
