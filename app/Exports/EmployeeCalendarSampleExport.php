<?php

namespace App\Exports;

use App\Models\PolicyShiftTiming;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeeCalendarSampleExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            // Sample data rows with different scenarios
            [1, 'ABS02', 'GP-SP-101', '2025-08-04', '2025-08-09'],
            [2, 'ABS03', 'GP-SP-101', '2025-08-04', '2025-08-09'],
            [3, 'ABS04', 'GP-SP-101', '2025-08-04', '2025-08-09'],
            [4, 'ABS05', 'GP-SP-101', '2025-08-04', '2025-08-09'],
        ];
    }

    public function headings(): array
    {
        return [
            'S. No.*',
            'Emp Code*',
            'Shift Code*',
            'Start Date(YYYY-MM-DD)*',
            'End Date(YYYY-MM-DD)*',
        ];
    }
}
