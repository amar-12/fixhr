<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Reader\Xls\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


class MonthlyAttendanceBasicReport implements FromArray, WithHeadings, WithEvents
{
    protected $exportData;
    protected $daysInMonth;
    protected $month;
    protected $year;
    protected $logoPath;
    protected $business;

    protected $summaryHeader;



    public function __construct($exportData, $daysInMonth, $month, $year, $logoPath, $business, $summaryHeader)
    {
        $this->exportData = $exportData;
        $this->daysInMonth = $daysInMonth;
        $this->month = $month;
        $this->year = $year;
        $this->logoPath = $logoPath;
        $this->business = $business;
        $this->summaryHeader = $summaryHeader;
    }

    // Export data
    public function array(): array
    {
        foreach ($this->exportData as &$row) {

            foreach ($this->summaryHeader as $key) {
                if (!isset($row[$key]) || $row[$key] === null) {
                    $row[$key] = 0;
                }
            }
        }
        return $this->exportData;
    }

    // Set headings
    public function headings(): array
    {
        $headings = ['Emp Code', 'Emp Name'];
        for ($day = 1; $day <= $this->daysInMonth; $day++) {
            $headings[] = "$day";
        }
        return array_merge($headings, $this->summaryHeader);
    }



    public function registerEvents(): array
    {
      return [
        AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet;
            $worksheet = $sheet->getDelegate();

            $sheet->insertNewRowBefore(1, 4);

            $sheet->mergeCells('D2:I2');
            $sheet->setCellValue('D2', $this->business->b_name);
            $sheet->getStyle('D2')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Report title
            $sheet->mergeCells('D3:I3');
            $sheet->setCellValue('D3', 'Monthly Attendance Report');
            $sheet->getStyle('D3')->applyFromArray([
                'font' => ['bold' => false, 'size' => 14],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Date range
            $startDate = date('d-m-Y', strtotime("$this->year-$this->month-01"));
            $endDate = date('d-m-Y', strtotime("$this->year-$this->month-" . $this->daysInMonth));
            $sheet->mergeCells('D4:I4');
            $sheet->setCellValue('D4', "Date (From: $startDate To: $endDate)");
            $sheet->getStyle('D4')->applyFromArray([
                'font' => ['bold' => false, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Insert logo
            $worksheet->mergeCells('B1:B4');
            if ($this->logoPath) {
                $drawing = new Drawing();
                $drawing->setPath($this->logoPath);
                $drawing->setHeight(40);
                $drawing->setWidth(50);
                $drawing->setOffsetX(50);
                $drawing->setOffsetY(20);
                $drawing->setCoordinates('B1');
                $drawing->setWorksheet($worksheet);
            }

            // Freeze header row
            $sheet->freezePane('A6');

            // Adjust column width
            // foreach (range('A', $worksheet->getHighestColumn()) as $col) {
            //     $sheet->getColumnDimension($col)->setWidth(20);
            // }
            $highestColumn = $worksheet->getHighestColumn(); // e.g. 'AA'
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $sheet->getColumnDimension($colLetter)->setWidth(20);
            }

            // Align header row (Row 5 after inserting 4 rows above)
            $headerRow = 5;
            $lastColumn = $worksheet->getHighestColumn();
            $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);

            // Align all data rows (below header)
            $dataStartRow = $headerRow + 1;
            $dataEndRow = $worksheet->getHighestRow();
            $sheet->getStyle("A{$dataStartRow}:{$lastColumn}{$dataEndRow}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
        },
     ];
   }

}
