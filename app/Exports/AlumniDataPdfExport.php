<?php

namespace App\Exports;

use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AlumniDataPdfExport extends AlumniDataExport
{
    public const string WRITER_TYPE = 'AlumniDataPdf';

    public function registerEvents(): array
    {
        return [
            ...parent::registerEvents(),
            BeforeWriting::class => function (): void {
                IOFactory::registerWriter(self::WRITER_TYPE, AlumniDataPdfWriter::class);
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getPageSetup()
            ->setPaperSize(PageSetup::PAPERSIZE_A3)
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

        return parent::styles($sheet);
    }
}
