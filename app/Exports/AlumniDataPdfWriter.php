<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf;

class AlumniDataPdfWriter extends Dompdf
{
    public function generateHtmlAll(): string
    {
        $rows = $this->spreadsheet->getActiveSheet()->toArray(null, false, false);
        [$title, $headings] = array_splice($rows, 0, 2);
        $headings[0] = 'Campus / Department';

        return view('filament.pages.alumni-data-report-pdf', [
            'title' => $title[0],
            'headings' => $headings,
            'columns' => array_keys(array_filter($headings, fn (mixed $heading): bool => filled($heading))),
            'rows' => $rows,
        ])->render();
    }
}
