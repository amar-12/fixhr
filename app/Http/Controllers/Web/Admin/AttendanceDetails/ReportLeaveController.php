<?php

namespace App\Http\Controllers\Web\Admin\AttendanceDetails;

use App\Models\Business;
use App\Models\Employee;
use App\Models\LeaveBalance;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use App\Helpers\CentralLogics;
use Illuminate\Support\Carbon;
use App\Models\PolicyHolidayList;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ReportLeaveController extends Controller
{


    public function leaveBalance()
    {
        return view('admin.setting.attendance-details.leave-balance-export');
    }



    public function employeeLeaveBalanceReport(Request $request)
    {
        $user = Auth::user();
        $month = $request->month ?? now()->format('m'); // Default to the current month if not provided
        $year = $request->year ?? now()->format('Y'); // Default to the current year if not provided
        $businessId = auth()->user()->emp_b_id;

        $business = Business::find($businessId);


        $employees = Employee::select('emp_id', 'emp_code', 'emp_full_name', 'emp_email', 'emp_b_id', 'emp_pwo_id','emp_shift_type_id')
        ->where('emp_b_id', $businessId)
        ->where('emp_status', 71)
        ->get();

        if ($employees->isEmpty()) {
            return redirect()->back()->with('error', 'No employees found for this period.');
        }

        // dd($employees);
        $leaveBalances = $this->getLeaveBalances($businessId);


        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $dateRange = $startDate->toPeriod($endDate);

        $holiday_record_exits = PolicyHolidayList::where('phl_b_id', $user->emp_b_id)->where('phl_day_type_id', 201)->where('phl_day_type_id', 201)->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('phl_start_date', [$startDate, $endDate])
              ->orWhereBetween('phl_end_date', [$startDate, $endDate]);
        })->get();
        $holidaysByDate = collect();
        foreach ($holiday_record_exits as $holiday) {
            $start = Carbon::parse($holiday->phl_start_date);
            $end = Carbon::parse($holiday->phl_end_date);

            while ($start->lte($end)) {
                $holidaysByDate->put($start->toDateString(), $holiday);
                $start->addDay();
            }
        }



        $attendanceDetails = [];
        $daysInMonth = Carbon::createFromFormat('Y-m', "{$year}-{$month}")->daysInMonth;

        foreach ($employees as $employee) {
            $weekOfDates = CentralLogics::getWeekOffDates($employee, $year, $month);

            $attendanceData = [];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::createFromFormat('Y-m-d', "{$year}-{$month}-{$day}");
                $dailyDetails = CentralLogics::newGetMonthlyAttendanceDetails($employee, $month, $year, $holidaysByDate, $weekOfDates);
                $dailyDetails['date'] = $date->format('Y-m-d');
                $attendanceData[] = $dailyDetails;
            }

            // Fetch leave balance for the employee
            $employeeLeaveBalance = collect($leaveBalances)->firstWhere('leave_bal_id', $employee->emp_id);

            $attendanceDetails[] = [
                'employee' => $employee,
                'attendanceDetails' => $attendanceData,
                'leaveBalance' => $employeeLeaveBalance,
            ];
        }



        if ($request->has('export_excel')) {
            return $this->exportAttendanceDataExcel($attendanceDetails, $month, $year, $business);
        }

        return view('admin.setting.attendance-details.muster-roll', compact('attendanceDetails', 'month', 'year'));
    }

    public function getLeaveBalances($businessId)
    {


        $leaveBalances = DB::table('leave_balance')
            ->join('employees', 'leave_balance.lb_emp_id', '=', 'employees.emp_id') // Removed trailing space
            ->select(
                'employees.emp_id',
                'employees.emp_code',
                'employees.emp_full_name',
                'leave_balance.lb_emp_id as leave_bal_id',
                'leave_balance.lb_alloted_leave',
                'leave_balance.lb_taken_leave',
                'leave_balance.lb_balance_remaining_leave',
                'leave_balance.lb_cat_type_id'
            )
            ->where('employees.emp_b_id', $businessId)
            ->get();
            // dd($leaveBalances);

        return $leaveBalances->toArray();
    }

    public function exportAttendanceDataExcel($attendanceDetails, $month, $year, $business)
    {
        set_time_limit(0);

        $exportData = [];
        $daysInMonth = Carbon::createFromFormat('Y-m', "{$year}-{$month}")->daysInMonth;

        $logoFileName = $business->b_logo;
        $logoPath = public_path($logoFileName);

        if (!file_exists($logoPath)) {
            throw new \Exception("Logo file not found at: {$logoPath}");
        }

        foreach ($attendanceDetails as $entry) {
            $employee = $entry['employee'];
            $leaveBalances = $entry['leaveBalance'];

            $alloted_207 = $taken_207 = $remaining_207 = 0;
            $alloted_211 = $taken_211 = $remaining_211 = 0;
            $alloted_208 = $taken_208 = $remaining_208 = 0;

            foreach ($leaveBalances as $leaveBalance) {
                if (is_int($leaveBalance)) {
                    $leaveBalance = LeaveBalance::find($leaveBalance);
                }

                if ($leaveBalance && is_object($leaveBalance)) {
                    if ($leaveBalance->lb_cat_type_id == '207') {
                        $alloted_207 = isset($leaveBalance->lb_alloted_leave) ? $leaveBalance->lb_alloted_leave : 0;
                        $taken_207 = isset($leaveBalance->lb_taken_leave) ? $leaveBalance->lb_taken_leave : 0;
                        $remaining_207 = isset($leaveBalance->lb_balance_remaining_leave) ? $leaveBalance->lb_balance_remaining_leave : 0;
                    } elseif ($leaveBalance->lb_cat_type_id == '211') {
                        $alloted_211 = isset($leaveBalance->lb_alloted_leave) ? $leaveBalance->lb_alloted_leave : 0;
                        $taken_211 = isset($leaveBalance->lb_taken_leave) ? $leaveBalance->lb_taken_leave : 0;
                        $remaining_211 = isset($leaveBalance->lb_balance_remaining_leave) ? $leaveBalance->lb_balance_remaining_leave : 0;
                    } elseif ($leaveBalance->lb_cat_type_id == '208') {
                        $alloted_208 = isset($leaveBalance->lb_alloted_leave) ? $leaveBalance->lb_alloted_leave : 0;
                        $taken_208 = isset($leaveBalance->lb_taken_leave) ? $leaveBalance->lb_taken_leave : 0;
                        $remaining_208 = isset($leaveBalance->lb_balance_remaining_leave) ? $leaveBalance->lb_balance_remaining_leave : 0;
                    }
                }
            }


            $row = [
                'Emp ID' => $employee->emp_code ?: 'N/A',  // Ensure 'N/A' if emp_code is null
                'Emp Name' => $employee->emp_full_name ?: 'Unknown',  // Ensure 'Unknown' if emp_name is null
                'Alloted_207' => $alloted_207,
                'Taken_207' => $taken_207,
                'Remaining_207' => $remaining_207,
                'Alloted_211' => $alloted_211,
                'Taken_211' => $taken_211,
                'Remaining_211' => $remaining_211,
                'Alloted_208' => $alloted_208,
                'Taken_208' => $taken_208,
                'Remaining_208' => $remaining_208,
            ];

            // Add the row to export data
            $exportData[] = $row;
        }

        $fileName = "Employee_Leave_Balance_Report_{$month}_{$year}.xlsx";

        return Excel::download(new class ($exportData, $daysInMonth, $logoPath, $business) implements
            \Maatwebsite\Excel\Concerns\FromArray,
            \Maatwebsite\Excel\Concerns\WithEvents {

            private $data;
            private $daysInMonth;
            private $logoPath;
            private $business;

            public function __construct($data, $daysInMonth, $logoPath, $business)
            {
                $this->data = $data;
                $this->daysInMonth = $daysInMonth;
                $this->logoPath = $logoPath;
                $this->business = $business;
            }

            public function array(): array
            {
                return $this->data; // Return raw data only
            }

            public function registerEvents(): array
            {
                return [
                    \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                        $sheet = $event->sheet;
                        $worksheet = $sheet->getDelegate();

                        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                        $drawing->setPath($this->logoPath);
                        $drawing->setHeight(100);
                        $drawing->setCoordinates('A1');
                        $drawing->setWorksheet($worksheet);

                        $sheet->mergeCells('C2:F2');
                        $sheet->setCellValue('C2', $this->business->b_name);
                        $sheet->getStyle('C2')->applyFromArray([
                            'font' => ['bold' => true, 'size' => 14],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                        ]);

                        $sheet->mergeCells('C3:F3');
                        $sheet->setCellValue('C3', 'Employee Leave Balance Muster Roll Report');
                        $sheet->getStyle('C3')->applyFromArray([
                            'font' => ['bold' => true, 'size' => 12],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                        ]);

                        $sheet->mergeCells('C4:F4');
                        $sheet->setCellValue('C4', 'Branch: FixingDots HO');
                        $sheet->getStyle('C4')->applyFromArray([
                            'font' => ['bold' => true, 'size' => 12],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                        ]);

                        $sheet->mergeCells('A6:B6');
                        $sheet->setCellValue('B6', '');
                        $sheet->getStyle('B6')->applyFromArray([
                            'font' => ['bold' => true, 'size' => 12],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                        ]);

                        $sheet->mergeCells('C6:E6');
                        $sheet->setCellValue('C6', 'Casual Leave');
                        $sheet->getStyle('C6')->applyFromArray([
                            'font' => ['bold' => true, 'size' => 12],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                                ]
                            ],
                        ]);

                        // Apply border to the entire row from column C to E (if you want the borders on the surrounding columns as well)
                        $sheet->getStyle('C6:E6')->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                                ]
                            ]
                        ]);

                        $headings = ['Emp ID', 'Emp Name', 'Alloted 207', 'Taken 207', 'Remaining 207', 'Alloted 211', 'Taken 211', 'Remaining 211', 'Alloted 208', 'Taken 208', 'Remaining 208'];
                        $worksheet->fromArray([$headings], null, 'A7'); // Add headings at A7




                        $sheet->getStyle('A7:' . $worksheet->getHighestColumn() . '7')->applyFromArray([
                            'font' => ['bold' => true],
                            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => 'D9EAD3']],
                        ]);

                        foreach (range('A', $worksheet->getHighestColumn()) as $col) {
                            $sheet->getColumnDimension($col)->setAutoSize(true);
                        }

                        $rowIndex = 8;
                        foreach ($this->data as $employeeData) {
                            foreach (range('A', $worksheet->getHighestColumn()) as $col) {
                                $worksheet->getStyle("{$col}{$rowIndex}")->applyFromArray([
                                    'alignment' => [
                                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                                        'wrapText' => true,
                                    ],
                                ]);
                            }
                            $rowIndex++;
                        }

                        foreach (range(8, $worksheet->getHighestRow()) as $row) {
                            $worksheet->getRowDimension($row)->setRowHeight(25); // Set a standard row height
                            foreach (range('A', $worksheet->getHighestColumn()) as $col) {
                                $worksheet->getStyle("{$col}{$row}")->applyFromArray([
                                    'borders' => [
                                        'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                                    ],
                                ]);
                            }
                        }
                    },
                ];
            }
        }, $fileName);
    }







}
