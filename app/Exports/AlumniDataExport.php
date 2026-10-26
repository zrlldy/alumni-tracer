<?php

namespace App\Exports;

use App\Models\Alumni;
use App\Models\AlumniEducation;
use App\Models\AlumniEmployment;
use Illuminate\Database\Eloquent\Builder;
use LogicException;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlumniDataExport implements FromQuery, ShouldAutoSize, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private readonly ?int $graduationYear = null,
        private readonly ?int $programId = null,
        private readonly ?string $employmentStatus = null,
    ) {}

    public function query(): Builder
    {
        $query = Alumni::query()
            ->with(['program', 'alumniEducation', 'alumniEmployment'])
            ->orderBy('id');

        if ($this->graduationYear !== null) {
            $query->graduatedInYear($this->graduationYear);
        }

        if ($this->programId !== null) {
            $query->forProgram($this->programId);
        }

        if ($this->employmentStatus !== null) {
            $query->withEmploymentStatus($this->employmentStatus);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Student Number',
            'First Name',
            'Middle Name',
            'Last Name',
            'Email',
            'Phone Number',
            'Current Address',
            'Program',
            'Graduation Date',
            'Employment Status',
            'Remarks',
            'Date Traced',
            'Traced By',
            'Education History',
            'Employment History',
        ];
    }

    public function map(mixed $row): array
    {
        if (! $row instanceof Alumni) {
            throw new LogicException('Alumni data exports may only map alumni records.');
        }

        return [
            $row->student_number,
            $row->first_name,
            $row->middle_name,
            $row->last_name,
            $row->email,
            $row->phone_number,
            $row->current_address,
            $row->program?->program_name,
            $row->graduation_year,
            $row->employment_status,
            $row->remarks,
            $row->date_traced,
            $row->trace_by,
            implode(PHP_EOL, $row->alumniEducation->map($this->formatEducation(...))->all()),
            implode(PHP_EOL, $row->alumniEmployment->map($this->formatEmployment(...))->all()),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 18,
            'C' => 18,
            'D' => 18,
            'E' => 28,
            'F' => 18,
            'G' => 35,
            'H' => 28,
            'I' => 18,
            'J' => 18,
            'K' => 35,
            'L' => 18,
            'M' => 22,
            'N' => 60,
            'O' => 75,
        ];
    }

    public function styles(Worksheet $sheet): ?array
    {
        return [
            'A1:O1' => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '800000'],
                ],
            ],
            'N:O' => [
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => 'top',
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $worksheet = $event->sheet->getDelegate();
                $lastDataRow = $worksheet->getHighestDataRow();

                if ($lastDataRow < 2) {
                    return;
                }

                $worksheet
                    ->getStyle("A2:O{$lastDataRow}")
                    ->setConditionalStyles([
                        $this->statusHighlight('$J2="unemployed"', 'FFF2CC'),
                        $this->statusHighlight('$J2="untraced"', 'F4CCCC'),
                    ]);
            },
        ];
    }

    private function formatEducation(AlumniEducation $education): string
    {
        return sprintf(
            'Institution: %s | Program: %s | Degree Level: %s | Units Completed: %s | Status: %s | Started: %s | Ended: %s',
            $education->instituion,
            $education->program,
            $education->degree_level,
            $education->units_completed,
            $education->status,
            $education->started_at,
            $education->ended_at,
        );
    }

    private function statusHighlight(string $condition, string $color): Conditional
    {
        $conditional = new Conditional;

        $conditional
            ->setConditionType(Conditional::CONDITION_EXPRESSION)
            ->addCondition($condition);

        $fill = $conditional->getStyle()->getFill();

        $fill->setFillType(Fill::FILL_SOLID);
        $fill->getStartColor()->setRGB($color);
        $fill->getEndColor()->setRGB($color);

        return $conditional;
    }

    private function formatEmployment(AlumniEmployment $employment): string
    {
        return sprintf(
            'Company: %s | Position: %s | Address: %s | Industry: %s | Type: %s | Course Related: %s | Date Hired: %s | Starting Date: %s | Ended: %s | Documents: %s | Current: %s',
            $employment->company_name,
            $employment->position ?? '-',
            $employment->company_address,
            $employment->industry ?? '-',
            $employment->employment_type,
            $employment->is_course_related === null ? 'Not specified' : ($employment->is_course_related ? 'Yes' : 'No'),
            $employment->date_hired,
            $employment->starting_date ?? '-',
            $employment->ended_at ?? '-',
            $employment->supported_documents,
            $employment->is_current ? 'Yes' : 'No',
        );
    }
}
