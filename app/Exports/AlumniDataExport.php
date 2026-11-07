<?php

namespace App\Exports;

use App\Models\Alumni;
use App\Models\Campus;
use Illuminate\Database\Eloquent\Builder;
use LogicException;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlumniDataExport implements FromQuery, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithStyles
{
    private int $rowNumber = 0;

    private ?int $lastProgramId = null;

    public function __construct(
        private readonly ?int $graduationYear = null,
        private readonly ?int $programId = null,
        private readonly ?string $employmentStatus = null,
        private readonly ?int $campusId = null,
    ) {}

    public function query(): Builder
    {
        $query = Alumni::query()
            ->latestPerAccount()
            ->with([
                'program.campus',
                'alumniEmployment',
            ])
            ->orderBy('program_id')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('id');

        if ($this->graduationYear !== null) {
            $query->graduatedInYear($this->graduationYear);
        }

        if ($this->programId !== null) {
            $query->forProgram($this->programId);
        }

        if ($this->campusId !== null) {
            $query->whereHas('program', fn (Builder $programQuery): Builder => $programQuery->where('campus_id', $this->campusId));
        }

        if ($this->employmentStatus !== null) {
            $query->withEmploymentStatus(
                $this->employmentStatus
            );
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            [
                $this->campusId === null ? 'All Campuses' : (Campus::query()->find($this->campusId)?->name ?? 'Campus'),
            ],
            [
                '',
                'NO.',
                'Lastname',
                'Firstname',
                'STATUS',
                '',
                '',
                'Supporting Documents',
                'Remarks (e.g. Deceased)',
                'Name of Company',
                'Address of the Company',
                'Date Hired',
                'DATE OF GRADUATION',
                'STUDENT ID NUMBER',
                'CLASSIFICATION (PT, FT, J.O)',
                'DATE TRACED',
                'PHONE NUMBER',
                'TRACER',
            ],
        ];
    }

    public function map($row): array
    {
        if (! $row instanceof Alumni) {
            throw new LogicException(
                'Alumni data exports may only map alumni records.'
            );
        }

        $this->rowNumber++;

        $programId = $row->program_id;

        $programName = '';

        if ($this->lastProgramId !== $programId) {
            $programName = collect([$row->program?->campus?->name, $row->program?->program_name])->filter()->implode(' - ');
            $this->lastProgramId = $programId;
        }

        // Prefer the current employment record.
        // Otherwise use the most recent available record.
        $employment = $row->alumniEmployment
            ->sortByDesc(
                fn ($item) => (string) ($item->date_hired ?? '')
            )
            ->firstWhere('is_current', true);

        if ($employment === null) {
            $employment = $row->alumniEmployment
                ->sortByDesc(
                    fn ($item) => (string) ($item->date_hired ?? '')
                )
                ->first();
        }

        return [
            // A - Program group
            $programName,

            // B - Sequential number
            $this->rowNumber,

            // C - Lastname
            $row->last_name,

            // D - Firstname
            $row->first_name,

            // E - Employment status
            ucfirst((string) $row->employment_status),

            // F-G - Reserved columns
            '',
            '',

            // H - Supporting documents
            $employment?->supported_documents,

            // I - Remarks
            $row->remarks,

            // J - Company name
            $employment?->company_name,

            // K - Company address
            $employment?->company_address,

            // L - Date hired
            $employment?->date_hired,

            // M - Graduation year fallback
            $row->graduation_year,

            // N - Student number
            $row->student_number,

            // O - Classification
            $employment?->employment_type,

            // P - Date traced
            $row->date_traced,

            // Q - Phone number
            $row->phone_number,

            // R - Traced by
            $row->trace_by,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 48,
            'B' => 10,
            'C' => 22,
            'D' => 24,
            'E' => 18,
            'F' => 12,
            'G' => 12,
            'H' => 45,
            'I' => 35,
            'J' => 35,
            'K' => 50,
            'L' => 18,
            'M' => 23,
            'N' => 22,
            'O' => 32,
            'P' => 20,
            'Q' => 20,
            'R' => 25,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '800000'],
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                ],
            ],

            2 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '800000'],
                ],
                'alignment' => [
                    'horizontal' => 'center',
                    'vertical' => 'center',
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                // Report title
                $sheet->mergeCells('A1:R1');

                $sheet->getRowDimension(1)
                    ->setRowHeight(32);

                $sheet->getRowDimension(2)
                    ->setRowHeight(42);

                // Keep headers visible when scrolling.
                $sheet->freezePane('A3');

                // Filters across all template columns.
                $lastRow = $sheet->getHighestDataRow();

                $sheet->setAutoFilter(
                    "A2:R{$lastRow}"
                );

                if ($lastRow < 3) {
                    return;
                }

                // Wrap long text.
                $sheet->getStyle("A3:R{$lastRow}")
                    ->getAlignment()
                    ->setWrapText(true);

                $sheet->getStyle("A3:R{$lastRow}")
                    ->getAlignment()
                    ->setVertical('top');

                // Preserve IDs and phone numbers as text.
                $sheet->getStyle("N3:N{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('@');

                $sheet->getStyle("Q3:Q{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('@');

                // Highlight employment status.
                $sheet->getStyle("A3:R{$lastRow}")
                    ->setConditionalStyles([
                        $this->statusHighlight(
                            '$E3="Unemployed"',
                            'FFF2CC'
                        ),
                        $this->statusHighlight(
                            '$E3="Untraced"',
                            'F4CCCC'
                        ),
                    ]);
            },
        ];
    }

    private function statusHighlight(
        string $condition,
        string $color
    ): Conditional {
        $conditional = new Conditional;

        $conditional
            ->setConditionType(
                Conditional::CONDITION_EXPRESSION
            )
            ->addCondition($condition);

        $fill = $conditional
            ->getStyle()
            ->getFill();

        $fill->setFillType(Fill::FILL_SOLID);
        $fill->getStartColor()->setRGB($color);
        $fill->getEndColor()->setRGB($color);

        return $conditional;
    }
}
