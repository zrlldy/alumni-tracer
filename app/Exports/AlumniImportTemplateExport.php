<?php

namespace App\Exports;

use App\Models\Program;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Override;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlumniImportTemplateExport implements
    FromArray,
    WithHeadings,
    ShouldAutoSize,
    WithStyles
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
            'program',
            'graduation_year',
            'current_address',
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        $programs = Program::query()->where('is_active', true)->orderBy('program_name')->pluck('program_name')->all();

        // Text format for student_number, including A2 and all rows below.
        $sheet->getStyle('A:A')
            ->getNumberFormat()
            ->setFormatCode('@');

        // Enable Excel's quote-prefix style.
        $sheet->getStyle('A:A')->setQuotePrefix(true);



        if ($programs !== []) {
            $range = 'G2:G1048576';


            $validation = new DataValidation();

            $validation
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP)
                ->setAllowBlank(false)
                ->setShowDropDown(true)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setPromptTitle('Select Program')
                ->setPrompt('Choose a program from the dropdown.')
                ->setErrorTitle('Invalid Program')
                ->setError('Please select a program from the list.')
                ->setFormula1(
                    '"' . str_replace('"', '""', implode(',', $programs)) . '"'
                )
                ->setSqref($range);

            $sheet->setDataValidation($range, $validation);
        }
        return [
            'A1:M1' => [
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
