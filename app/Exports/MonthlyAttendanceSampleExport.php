<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MonthlyAttendanceSampleExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        $cu_month_year = Carbon::now()->format('m/Y');
        $monthName = Carbon::now()->format('F');
        return [
            // Sample data rows with different scenarios
            [1, 'ABS02', '01/' . $cu_month_year, '31/' . $cu_month_year, '09:50:00', '18:40:00', 'Regular attendance for ' . $monthName],
            [2, 'ABS03', '18/' . $cu_month_year, '18/' . $cu_month_year, '09:50:00', '18:40:00', 'Regular attendance for ' . $monthName],
            [3, 'ABS04', '25/' . $cu_month_year, '25/' . $cu_month_year, '09:50:00', '', 'Regular attendance for ' . $monthName],
            [4, 'ABS05', '26/' . $cu_month_year, '26/' . $cu_month_year, '', '18:40:00', 'Regular attendance for ' . $monthName],
        ];
    }

    public function headings(): array
    {
        return [
            'S. No.*',
            'Emp Code*',
            'Attendance Start Date(DD/MM/YYYY)*',
            'Attendance End Date(DD/MM/YYYY)*',
            'Attendance Check In Time',
            'Attendance Check Out Time',
            'Remark*',
        ];
    }
}
