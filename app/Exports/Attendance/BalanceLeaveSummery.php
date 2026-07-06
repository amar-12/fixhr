<?php

namespace App\Exports\Attendance;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class BalanceLeaveSummery implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents, WithStartRow
{
    protected $data, $fromDate, $toDate, $filters, $leaveCategories = [];

    public function __construct($records, $filters = null, $fromDate = null, $toDate = null)
    {
        $this->data = $records;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->filters = $filters;
        $this->extractLeaveCategories();
    }

    protected function extractLeaveCategories()
    {
        $categories = [];
        foreach ($this->data as $record) {
            if (isset($record['fh_leave_cat_type'])) {
                $cat = $record['fh_leave_cat_type'];
                $categories[$cat['m_id']] = [
                    'name' => $cat['m_name'],
                    'type' => $cat['m_type'] ?? substr($cat['m_name'], 0, 2),
                    'color' => $this->extractColor($cat['m_other'] ?? '')
                ];
            }
            if (isset($record['fh_employees_details']['leaveBalances'])) {
                foreach ($record['fh_employees_details']['leaveBalances'] as $balance) {
                    if (isset($balance['fh_leave_type'])) {
                        $cat = $balance['fh_leave_type'];
                        $categories[$cat['m_id']] = [
                            'name' => $cat['m_name'],
                            'type' => $cat['m_type'] ?? substr($cat['m_name'], 0, 2),
                            'color' => $this->extractColor($cat['m_other'] ?? '')
                        ];
                    }
                }
            }
        }
        $this->leaveCategories = array_column($categories, null, 'type');
    }

    protected function extractColor($mOther)
    {
        $data = json_decode($mOther, true);
        return $data['color'] ?? 'FFFFFF';
    }

    public function startRow(): int
    {
        return 10;
    }

    public function collection()
    {
        $summary = [];

        foreach ($this->data as $record) {
            $emp = $record['fh_employees_details'];
            $empId = $record['lvr_emp_id'];

            if (!isset($summary[$empId])) {
                $summary[$empId] = [
                    'Emp Code' => $emp['emp_code'] ?? '',
                    'Employee Name' => $emp['emp_full_name'] ?? '',
                    'Department' => $emp['fh_department']['d_name'] ?? '',
                    'Designation' => $emp['fh_designation']['dg_name'] ?? '',
                    'Dealership' => $emp['fh_dealership']['dlr_name'] ?? '',
                    'Date of Joining' => isset($emp['emp_date_of_joining']) ? Carbon::parse($emp['emp_date_of_joining'])->format('d-M-Y') : '',
                    'Month' => $this->fromDate->format('M-Y') . ' to ' . $this->toDate->format('M-Y'),
                ];

                foreach ($this->leaveCategories as $type => $cat) {
                    $summary[$empId][$type . ' Opening'] = 0;
                    $summary[$empId][$type . ' Used'] = 0;
                    $summary[$empId][$type . ' Remaining'] = 0;
                }
            }

            if (!empty($emp['leaveBalances'])) {
                foreach ($emp['leaveBalances'] as $balance) {
                    $cat = $balance['fh_leave_type'] ?? null;
                    if (!$cat) continue;
                    $type = $cat['m_type'] ?? substr($cat['m_name'], 0, 2);

                    $summary[$empId][$type . ' Opening'] += (float) ($balance['lb_alloted_leave'] ?? 0) + (float) ($balance['lb_carried_forward'] ?? 0);
                    $summary[$empId][$type . ' Used'] += (float) ($balance['lb_taken_leave'] ?? 0);
                    $summary[$empId][$type . ' Remaining'] = max(0, $summary[$empId][$type . ' Opening'] - $summary[$empId][$type . ' Used']);
                }
            }
        }

        $result = [];
        $serial = 1;
        foreach ($summary as $empData) {
            $result[] = array_merge(['S.No' => $serial++], $empData);
        }

        return collect($result);
    }

    public function headings(): array
    {
        return [];
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 6, 'B' => 12, 'C' => 25, 'D' => 20, 'E' => 20, 'F' => 20, 'G' => 15, 'H' => 20
        ];

        $colIndex = 9;
        foreach ($this->leaveCategories as $type => $cat) {
            for ($i = 0; $i < 3; $i++) {
                $col = Coordinate::stringFromColumnIndex($colIndex++);
                $widths[$col] = 15;
            }
        }

        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->insertNewRowBefore(1, 8);
                $sheet->setCellValue('A1', $this->data[0]['fh_business']['b_name'] ?? 'Business Name');
                $sheet->setCellValue('A2', 'Employee Leave Summary Report');
                $sheet->setCellValue('A3', 'Period: ' . $this->fromDate->format('F Y') . ' to ' . $this->toDate->format('F Y'));
                $sheet->setCellValue('A4', 'Printed on: ' . Carbon::now()->format('d-M-Y'));
                $sheet->setCellValue('A5', 'As of: ' . $this->fromDate->format('d-M-Y'));
                $sheet->getStyle('A1:A5')->getFont()->setBold(true);

                $mainHeaderRow = 7;
                $subHeaderRow = 8;
                $headers = ['S.No', 'Emp Code', 'Employee Name', 'Department', 'Designation', 'Dealership', 'Date of Joining', 'Month'];

                foreach ($headers as $i => $header) {
                    $col = Coordinate::stringFromColumnIndex($i + 1);
                    $sheet->setCellValue("{$col}{$mainHeaderRow}", $header);
                    $sheet->mergeCells("{$col}{$mainHeaderRow}:{$col}{$subHeaderRow}");
                }

                $startColIndex = count($headers) + 1;
                foreach ($this->leaveCategories as $type => $cat) {
                    $start = Coordinate::stringFromColumnIndex($startColIndex);
                    $end = Coordinate::stringFromColumnIndex($startColIndex + 2);
                    $sheet->mergeCells("$start{$mainHeaderRow}:$end{$mainHeaderRow}");
                    $sheet->setCellValue("{$start}{$mainHeaderRow}", $cat['name']);
                    $sheet->setCellValue("{$start}{$subHeaderRow}", 'Opening');
                    $sheet->setCellValue("{$end}{$subHeaderRow}", 'Remaining');
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($startColIndex + 1) . $subHeaderRow, 'Used');
                    $startColIndex += 3;
                }

                $sheet->freezePane('A9');
                $sheet->getStyle('A7:' . $sheet->getHighestColumn() . $sheet->getHighestRow())
                    ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                foreach ($this->columnWidths() as $column => $width) {
                    $sheet->getColumnDimension($column)->setWidth($width);
                }
            }
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }
}
