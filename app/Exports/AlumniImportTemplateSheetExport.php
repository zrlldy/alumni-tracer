<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlumniImportTemplateSheetExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    /**
     * @param  array<string>  $headings
     * @param  array<array{heading: string, options: array<string>, label: string}>  $dropdowns
     */
    public function __construct(
        private string $worksheetTitle,
        private array $headings,
        private array $dropdowns = [],
    ) {}

    public function title(): string
    {
        return $this->worksheetTitle;
    }

    /** @return array<array<never>> */
    public function array(): array
    {
        return [];
    }

    /** @return array<string> */
    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        $studentNumberIndex = array_search('student_number', $this->headings, true);

        if ($studentNumberIndex !== false) {
            $studentNumberColumn = Coordinate::stringFromColumnIndex($studentNumberIndex + 1);

            $sheet->getStyle("{$studentNumberColumn}:{$studentNumberColumn}")
                ->getNumberFormat()
                ->setFormatCode('@');
            $sheet->getStyle("{$studentNumberColumn}:{$studentNumberColumn}")->setQuotePrefix(true);
        }

        foreach ($this->dropdowns as $dropdown) {
            if ($dropdown['options'] === []) {
                continue;
            }

            $columnIndex = array_search($dropdown['heading'], $this->headings, true);

            if ($columnIndex === false) {
                continue;
            }

            $column = Coordinate::stringFromColumnIndex($columnIndex + 1);
            $range = "{$column}2:{$column}1048576";
            $validation = new DataValidation;

            $validation
                ->setType(DataValidation::TYPE_LIST)
                ->setErrorStyle(DataValidation::STYLE_STOP)
                ->setAllowBlank(false)
                ->setShowDropDown(true)
                ->setShowInputMessage(true)
                ->setShowErrorMessage(true)
                ->setPromptTitle("Select {$dropdown['label']}")
                ->setPrompt('Choose a '.$dropdown['label'].' from the dropdown.')
                ->setErrorTitle("Invalid {$dropdown['label']}")
                ->setError('Please select a '.$dropdown['label'].' from the list.')
                ->setFormula1('"'.str_replace('"', '""', implode(',', $dropdown['options'])).'"')
                ->setSqref($range);

            $sheet->setDataValidation($range, $validation);
        }

        return [
            'A1:'.Coordinate::stringFromColumnIndex(count($this->headings)).'1' => [
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
