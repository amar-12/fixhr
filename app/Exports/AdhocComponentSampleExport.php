<?php

namespace App\Exports;

use App\Models\AdhocComponent;
use App\Models\PayrollPeriod;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class AdhocComponentSampleExport implements FromArray, WithStyles, WithColumnWidths, WithEvents
{
    protected $businessId;

    public function __construct($businessId)
    {
        $this->businessId = $businessId;
    }

    /**
     * Prepare export data
     */
    public function array(): array
    {
        $headings = $this->getHeadings();

        // Row 1 → Dropdowns (Payroll Period & Component Type)
        $row1 = array_fill(0, count($headings), '');
        $row1[0] = 'Payroll Period';
        $row1[2] = 'Component Type (E/D)';

        // Row 2 → Column headings
        $row2 = $headings;

        // Row 3+ → Empty data rows
        $emptyRow = array_fill(0, count($headings), '');
        return [
            $row1,
            $row2,
            $emptyRow,
            $emptyRow
        ];
    }

    /**
     * Get column headings dynamically from Adhoc Components
     */
    protected function getHeadings(): array
    {
        $components = AdhocComponent::where('ac_adhoc_business_id', $this->businessId)
            ->pluck('ac_adhoc_component_name')
            ->unique()
            ->values()
            ->toArray();

        return array_merge(['Employee Code', 'Employee Name'], $components, ['Remarks']);
    }

    /**
     * Apply styling
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Payroll Period Row
            2 => ['font' => ['bold' => true, 'italic' => true]], // Headings
        ];
    }

    /**
     * Set column widths
     */
    public function columnWidths(): array
    {
        $headings = $this->getHeadings();
        $widths = [];

        foreach (range(1, count($headings)) as $index) {
            $widths[Coordinate::stringFromColumnIndex($index)] = 20;
        }

        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                /** --- Payroll Period Dropdown (B1) --- */
                $periods = PayrollPeriod::where('pp_b_id', $this->businessId)
                    ->pluck('pp_name')
                    ->unique()
                    ->values()
                    ->toArray();

                if (!empty($periods)) {
                    array_unshift($periods, '-- Select Payroll Period --');

                    // Escape quotes and prevent too-long lists
                    $escaped = array_map(fn($v) => str_replace('"', '""', $v), $periods);
                    $escaped = array_slice($escaped, 0, 50);
                    $dropdownList = '"' . implode(',', $escaped) . '"';

                    $validation = $sheet->getCell('B1')->getDataValidation() ?? new DataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(false);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1($dropdownList);

                    $sheet->getCell('B1')->setDataValidation($validation);
                    $sheet->setCellValue('B1', '-- Select Payroll Period --');
                }

                /** --- Component Type Dropdown (C1) --- */
                $validation = $sheet->getCell('C1')->getDataValidation() ?? new DataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(false);
                $validation->setShowDropDown(true);
                $validation->setFormula1('"Earning,Deduction"');

                $sheet->getCell('C1')->setDataValidation($validation);

                if (empty($sheet->getCell('C1')->getValue())) {
                    $sheet->setCellValue('C1', 'Earning');
                }
            }
        ];
    }
}