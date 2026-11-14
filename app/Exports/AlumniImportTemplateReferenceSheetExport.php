<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlumniImportTemplateReferenceSheetExport implements FromArray, WithEvents, WithTitle
{
    /**
     * @param  array<string, array<string>>  $departmentsByCampus
     */
    public function __construct(private array $departmentsByCampus) {}

    public function title(): string
    {
        return '_Lists';
    }

    /** @return array<array<string|null>> */
    public function array(): array
    {
        $departmentCount = max(array_map('count', $this->departmentsByCampus));
        $rows = [[
            'campus',
            ...array_map(fn (int $index): string => 'department_'.$index, range(1, $departmentCount)),
        ]];

        foreach ($this->departmentsByCampus as $campus => $departments) {
            $rows[] = [$campus, ...array_pad($departments, $departmentCount, null)];
        }

        return $rows;
    }

    /** @return array<class-string, callable> */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $worksheet = $event->sheet->getDelegate();
                $worksheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
                $spreadsheet = $worksheet->getParent();
                $lastCampusRow = count($this->departmentsByCampus) + 1;

                $spreadsheet->addNamedRange(new NamedRange('_CampusList', $worksheet, '$A$2:$A$'.$lastCampusRow));

                foreach (array_values($this->departmentsByCampus) as $index => $departments) {
                    $row = $index + 2;
                    $lastDepartmentColumn = Coordinate::stringFromColumnIndex(count($departments) + 1);
                    $range = '$B$'.$row.':$'.$lastDepartmentColumn.'$'.$row;

                    $spreadsheet->addNamedRange(new NamedRange('Department_'.($index + 1), $worksheet, $range));
                }
            },
        ];
    }
}
