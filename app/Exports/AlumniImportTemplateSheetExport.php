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
     * @param  array<array{heading: string, options: array<string>, label: string, allow_blank?: bool}>  $dropdowns
     * @param  array<array<mixed>>  $rows
     */
    public function __construct(
        private string $worksheetTitle,
        private array $headings,
        private array $dropdowns = [],
        private array $rows = [],
    ) {}

    public function title(): string
    {
        return $this->worksheetTitle;
    }

    /** @return array<array<mixed>> */
    public function array(): array
    {
        return $this->rows;
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
        foreach (['student_number', 'phone_number'] as $textHeading) {
            $textColumnIndex = array_search($textHeading, $this->headings, true);

            if ($textColumnIndex === false) {
                continue;
            }

            $textColumn = Coordinate::stringFromColumnIndex($textColumnIndex + 1);

            $sheet->getStyle("{$textColumn}2")
                ->getNumberFormat()
                ->setFormatCode('@');
            $sheet->getStyle("{$textColumn}2")->setQuotePrefix(true);
        }

        foreach (['graduation_year', 'date_traced'] as $dateHeading) {
            $dateColumnIndex = array_search($dateHeading, $this->headings, true);

            if ($dateColumnIndex === false) {
                continue;
            }

            $dateColumn = Coordinate::stringFromColumnIndex($dateColumnIndex + 1);

            $sheet->getStyle("{$dateColumn}2")
                ->getNumberFormat()
                ->setFormatCode('yyyy-mm-dd');
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
                ->setAllowBlank($dropdown['allow_blank'] ?? false)
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

        $styles = [
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

        if ($this->rows !== []) {
            $styles['A2:'.Coordinate::stringFromColumnIndex(count($this->headings)).'2'] = [
                'font' => [
                    'italic' => true,
                    'color' => ['rgb' => '7F6000'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF2CC'],
                ],
            ];
        }

        return $styles;
    }
}
