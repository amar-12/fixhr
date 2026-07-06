<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class MonthlyAttendanceStatusExport implements FromArray, WithHeadings
{
    protected $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'S. No.*',
            'Emp Code',
            'Emp Name',
            'Attendance Start Date(DD/MM/YYYY)*',
            'Attendance End Date(DD/MM/YYYY)*',
            'Attendance Check In Time',
            'Attendance Check Out Time',
            'Remark*',
            'Status',
        ];
    }
}
