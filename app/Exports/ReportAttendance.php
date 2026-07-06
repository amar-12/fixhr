<?php

namespace App\Exports;

use Illuminate\Support\Facades\Auth;
use App\Helpers\CentralLogics;
use App\Models\AttendanceException;
use App\Models\AttendanceRecord;
use App\Models\LeaveRequest;
use App\Models\PolicyHolidayList;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Illuminate\Support\Facades\Log;

class ReportAttendance implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    protected $EmpData;
    public $NoData = false;
    protected $length;
    protected $export_type;
    protected $month;
    protected $year;
    protected $day;
    protected $businessId;
   /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($EmpData, $length, $export_type, $month, $year, $day, $businessId)
    {
        $this->EmpData = $EmpData;
        $this->length = $length + 8;
        $this->export_type = $export_type;
        $this->businessId = $businessId;
        $this->month = $month;
        $this->year = $year;
        $this->day = $day;
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function collection()
    {
        if ($this->export_type == 9) {
            $data = [];
            foreach ($this->EmpData as $key => $item) {
                $monthDay = $this->month == date('m') ? date('j') : cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
                $dataWithActive = [
                    'S.no.' => ++$key,
                    'Name' => $item->emp_fname . ' ' . $item->emp_mname . ' ' . $item->emp_lname,
                    'Emp ID' => $item->emp_code,
                ];

                $present = $absent = $half_day = $leave = $holiday = $misPunch = $over_time = $late = $prt = 0;

                for ($count = 1; $count <= $monthDay; $count++) {
                    $recordStatus = AttendanceRecord::where('atd_emp_id', $item->emp_id)->where('atd_date', date($this->year . '-' . $this->month . '-' . $count))->first();
                    $attendanceData = CentralLogics::getAttendanceRecord($item->emp_id, date($this->year . '-' . $this->month . '-' . $count));
                    $status = $attendanceData['status'] ?? null;
                    $leave_name = $attendanceData['leave_name'] ?? null;
                    $holiday_name = $attendanceData['holiday_name'] ?? null;

                    if ($recordStatus && $recordStatus->atd_attendance_status == 251 || $status == 251) {
                        $dataWithActive[$count] = 'P';
                        $present++;
                    } elseif ($recordStatus && $recordStatus->atd_attendance_status == 252 || $status == 202) {
                        $dataWithActive[$count] = 'HD';
                        $half_day++;
                    } elseif ($recordStatus && $recordStatus->atd_attendance_status == 203 || $status == 203) {
                        $dataWithActive[$count] = 'A';
                    $absent++;
                    } elseif ($recordStatus && $recordStatus->atd_attendance_status == 204) {
                        $dataWithActive[$count] = 'PRT';
                        $prt++;
                    } elseif ($status == 205 || $status == 206) {
                        $dataWithActive[$count] = $holiday_name ? $holiday_name . '+'. 'HO' : 'HO';
                    $holiday++;
                    } elseif($recordStatus && $recordStatus->atd_attendance_status == 228 || $status == 228) {
                        $dataWithActive[$count] = 'MSP';
                        $misPunch++;
                    } elseif($recordStatus && $recordStatus->atd_attendance_status == 201 || $status == 201) {
                        $dataWithActive[$count] = $leave_name ? $leave_name . '+'. ' L': 'L';
                    $leave++;
                    } else {
                        $dataWithActive[$count] = '-';
                    }
                    $over_time = $recordStatus ? $recordStatus->where('atd_is_overtime', 1)->count() : 0;
                    $late = $recordStatus ? $recordStatus->where('atd_is_late', 1)->count() : 0;
                }

                $total = $present +  ($half_day * 0.5);
                $dataWithActive = array_merge($dataWithActive, [($present ?? '0'), ($absent ?? '0'), ($half_day ?? '0'), ($leave ?? '0'), ($leave ?? '0'), ($holiday ?? '0'), ($misPunch ?? '0'), ($over_time ?? '0'), ($late ?? '0'), ($prt ?? '0'), ($total <= 0 ? '0' : $total)]);

                $data[$key + 8] = $dataWithActive;
            }
            return collect($data);
        }
    }


    public function headings(): array
    {
        $load = [];
        if ($this->export_type == 9) {
            $monthDay = $this->month == date('m') ? date('j') : cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
            $load = ['S.No.', 'Name', 'Emp ID'];

            for ($count = 1; $count <= $monthDay; $count++) {
                $formattedDate = $count;
                $load[] = $formattedDate;
            }

            $load = array_merge($load, ['P', 'A', 'HD', 'L', 'WO', 'HO', 'MSP', 'OT', 'LE', 'EE', 'Total']);
            $this->endPosition = array_search('Total', $load);
        }
        return $load;

    }

    public function registerEvents(): array
    {
        $user = Auth::user();
        $logoPath = $user->fh_business->b_logo;

        if (!file_exists($logoPath)) {
            $logoPath = public_path(asset('assets/logo/logo_round.png')); // Adjust the path to your fallback image
        }

        return [
            AfterSheet::class => function (AfterSheet $event) use ($logoPath) {
                try {

                    $drawing = new Drawing();
                    $richText = new RichText();

                    $drawing->setName('Logo');
                    $drawing->setDescription('Logo Image');
                    $drawing->setPath($logoPath);
                    $drawing->setHeight(100);
                    $drawing->setWidth(100);

                    $drawing->setCoordinates('A1');

                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(5);
                    $drawing->setWorksheet($event->sheet->getDelegate());


                    // Set business name in C1 cell
                    $event->sheet->getDelegate()->mergeCells('C2:I2');
                    $event->sheet->getDelegate()->setCellValue('C2', $this->businessId);
                    $event->sheet->getDelegate()->mergeCells('C3:I3');
                    $event->sheet->getDelegate()->mergeCells('C4:I4');
                    $event->sheet->getDelegate()->setCellValue('C4', 'For the Month of ' . date('F', strtotime($this->year . '-' . $this->month . '-01')) . '-' . date('Y', strtotime($this->year . '-' . $this->month . '-01')));
                    $event->sheet->getDelegate()->getStyle('C2')->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);


                    $event->sheet->getDelegate()->mergeCells('C5:I5');
                    // $branchLabel = 'Branch: '; // Bold text
                    // $branchName = $this->BranchName; // Regular text

                    // $boldText = $richText->createTextRun($branchLabel);
                    // $boldText->getFont()->setBold(true);

                    // $normalText = $richText->createTextRun($this->BranchName);
                    $event->sheet->getDelegate()->getCell('C5')->setValue($richText);


                    $event->sheet->setShowGridlines(false);

                    $sheet = $event->sheet->getDelegate();

                    $businessNameStyle = $event->sheet->getDelegate()->getStyle('C1');
                    $businessNameStyle->getFont()->setSize(12);
                    $businessNameStyle->getFont()->setBold(true);


                    if ($this->export_type == 9) {
                        $user = Auth::user();
                        $event->sheet->getDelegate()->freezePane('A9');

                        $NumOfDay = $this->month == date('m') ? date('j') : cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);

                        if ($NumOfDay == 31) {
                            $endCell1 = 'AH';
                            $startCell1 = 'AI';
                            $endCell2 = 'AS';
                        } elseif ($NumOfDay == 30) {
                            $endCell1 = 'AG';
                            $startCell1 = 'AH';
                            $endCell2 = 'AR';
                        } else if ($NumOfDay == 29) {
                            $endCell1 = 'AF';
                            $startCell1 = 'AG';
                            $endCell2 = 'AQ';
                        } else if ($NumOfDay == 28) {
                            $endCell1 = 'AE';
                            $startCell1 = 'AF';
                            $endCell2 = 'AP';
                        } else {
                            // $endCell2 = $this->endPosition;
                            $endCell1 = Coordinate::stringFromColumnIndex($this->endPosition - 10);
                            $startCell1 = Coordinate::stringFromColumnIndex($this->endPosition - 9);
                            $endCell2 = Coordinate::stringFromColumnIndex($this->endPosition + 1);
                        }

                        $event->sheet->getDelegate()->setCellValue('C3', 'Attendance Muster Roll Report');
                        $event->sheet->getDelegate()->mergeCells('A7:A8');
                        $event->sheet->getDelegate()->setCellValue('A7', 'S.NO.');
                        $event->sheet->getDelegate()->getStyle('A7:A8')->getBorders()->getAllBorders()
                            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $event->sheet->getDelegate()->getStyle('A7')->getAlignment()
                            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);


                        $event->sheet->getDelegate()->mergeCells('B7:B8');
                        $event->sheet->getDelegate()->setCellValue('B7', 'Name');
                        $event->sheet->getDelegate()->getStyle('B7:B8')->getBorders()->getAllBorders()
                            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $event->sheet->getDelegate()->getStyle('B7')->getAlignment()
                            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);


                        $event->sheet->getDelegate()->mergeCells('C7:C8');
                        $event->sheet->getDelegate()->setCellValue('C7', 'Emp ID');
                        $event->sheet->getDelegate()->getStyle('C7:C8')->getBorders()->getAllBorders()
                            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $event->sheet->getDelegate()->getStyle('C7')->getAlignment()
                            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);


                        $event->sheet->getDelegate()->mergeCells('D7:' . $endCell1 . '7');
                        $event->sheet->getDelegate()->setCellValue('D7', date('F', strtotime($this->year . '-' . $this->month . '-01')) . '-' . date('Y', strtotime($this->year . '-' . $this->month . '-01')));
                        $event->sheet->getDelegate()->getStyle('D7:' . $endCell1 . '7')->getBorders()->getAllBorders()
                            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $event->sheet->getDelegate()->getStyle('D7')->getAlignment()
                            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);


                        $event->sheet->getDelegate()->mergeCells($startCell1 . '7:' . $endCell2 . '7');
                        $event->sheet->getDelegate()->setCellValue($startCell1 . '7', 'SUMMARY');
                        $event->sheet->getDelegate()->getStyle($startCell1 . '7:' . $endCell2 . '7')->getBorders()->getAllBorders()
                            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $event->sheet->getDelegate()->getStyle($startCell1 . '7')->getAlignment()
                            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER)
                            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);



                        $event->sheet->getDelegate()->getStyle('D8:' . $endCell2 . $this->length)->getAlignment()
                            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

                        $boldStyle = $event->sheet->getDelegate()->getStyle('A7:' . $endCell2 . '8');
                        $boldStyle->getFont()->setBold(true);

                        $boldBorderStyle = $event->sheet->getDelegate()->getStyle('A8:' . $endCell2 . $this->length)->getBorders();
                        $boldBorderStyle->getAllBorders()->setBorderStyle(Border::BORDER_THIN);


                        $event->sheet->getDelegate()->mergeCells('A' . ($this->length + 2) . ':' . $endCell2 . ($this->length + 2));
                        $event->sheet->getDelegate()->setCellValue('A' . ($this->length + 2), 'P => Present, A => Absent, HD => Halfday, L => Leave, WO => Week-Off, HO => Holiday, MSP => Mis-Punch, OT => Overtime, LE => Late Entry, EE => Early Exit');

                        $event->sheet->getDelegate()->mergeCells('A' . ($this->length + 3) . ':' . $endCell2 . ($this->length + 3));
                        $event->sheet->getDelegate()->setCellValue('A' . ($this->length + 3), 'Note: For today`s attendance, count would temporarily reflect in MSP till the punch out and the status shown would be `Present` ');
                        $event->sheet->getDelegate()->getStyle('A' . ($this->length + 3))->getFont()->setBold(true);

                        $event->sheet->getDelegate()->mergeCells('A' . ($this->length + 5) . ':' . $endCell2 . ($this->length + 5));
                        $event->sheet->getDelegate()->setCellValue('A' . ($this->length + 5), 'Exported By ' . $user->emp_full_name . ' at: ' . now());

                        $event->sheet->getColumnDimension('A')->setAutoSize(false);
                        $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(5);
                        $event->sheet->getColumnDimension('C')->setAutoSize(false);
                        $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(7);

                        $startColumn = 'D';
                        $endColumn = $endCell2;

                        $startIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($startColumn);
                        $endIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($endColumn);

                        $columns = [];
                        for ($i = $startIndex; $i <= $endIndex; $i++) {
                            $columns[] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
                        }

                        foreach ($columns as $column) {
                            $event->sheet->getColumnDimension($column)->setAutoSize(false);
                            $event->sheet->getColumnDimension($column)->setWidth(5);
                            $event->sheet->getDelegate()->getStyle($column)->getAlignment()->setWrapText(true);
                        }
                        $lastColumn = end($columns);
                        $event->sheet->getColumnDimension($lastColumn)->setAutoSize(false);
                        $event->sheet->getColumnDimension($lastColumn)->setWidth(7);
                        $event->sheet->getDelegate()->getStyle($lastColumn)->getAlignment()->setWrapText(true);



                        // printing properties
                        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
                        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);

                        // Set margins (units are inches)
                        $sheet->getPageMargins()->setTop(0.25);
                        $sheet->getPageMargins()->setBottom(0.25);
                        $sheet->getPageMargins()->setLeft(0.25);
                        $sheet->getPageMargins()->setRight(0.25);

                        // Set scaling options
                        $sheet->getPageSetup()->setFitToWidth(1);
                        $sheet->getPageSetup()->setFitToHeight(0);

                        $sheet->getHeaderFooter()->setOddFooter('&CPage &P of &N');
                    }
                } catch (\Exception $e) {
                    Log::error('Error adding image to Excel sheet: ' . $e->getMessage());
                    Log::error('Stack trace: ' . $e->getTraceAsString());
                }
            },
        ];
    }

    public function setSelfie($ImagePath, $cellKey, $cellNo, $delegate)
    {
        $drawing = new Drawing();

        if (file_exists(public_path('upload_image/' . $ImagePath))) {
            $drawing->setPath(public_path('upload_image/' . $ImagePath));
        } else {
            return;
        }
        $drawing->setHeight(90);
        $drawing->setCoordinates($cellKey . $cellNo);
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(5);
        $drawing->setWorksheet($delegate);

    }

    public function calculation()
    {
        $user = Auth::user();
        $empId = $this->EmpData->emp_id;
        $month = $this->month;
        $year = $this->year;
        $date = Carbon::parse("{$year}-{$month}")->format('Y-m');
        // Calculate total working hours and overtime
        $totalWorkHour = AttendanceRecord::selectRaw('SUM(TIME_TO_SEC(atd_total_worked_hours)) AS total_hours,SUM(AS total_hours(atd_overtime_hours)) AS total_minutes, SUM(atd_overtime_hours) AS total_overtime')
                                            ->where('atd_emp_id', $empId)
                                            ->whereMonth('atd_date', $month)
                                            ->whereYear('atd_date', $year)
                                            ->first();

        // Extract hours and minutes from total work hour
        $totalHours = $totalWorkHour->total_hours + floor($totalWorkHour->total_minutes / 60);
        $totalMinutes = $totalWorkHour->total_minutes % 60;
        $totalOvertimeHours = floor($totalWorkHour->total_overtime / 60);
        $totalOvertimeMinutes = $totalWorkHour->total_overtime % 60;

            // Attendance counts
        $attendanceCounts = AttendanceRecord::where('atd_emp_id', $empId)->groupBy('atd_attendance_status')->selectRaw('atd_attendance_status, COUNT(*) AS count')->pluck('count', 'atd_attendance_status');

        $presentCount = $attendanceCounts[251] ?? 0;
        $absentCount = $attendanceCounts[203] ?? 0;
        $halfDayCount = ($attendanceCounts[252] ?? 0) * 0.5;
        $misPunchCount = $attendanceCounts[228] ?? 0;

        // Mis-punch calculations
        $misPunchData = AttendanceException::whereHas('fh_attendance_policy', function ($query) {
            $query->whereColumn('ap_id', 'ae_ap_id');
        })
        ->where('ae_emp_id', $empId)
        ->whereMonth('ae_date', $month)
        ->whereYear('ae_date', $year)
        ->get();

        $approvedMisPunchCount = $misPunchData->where('ae_status', 'approved')->count();
        $pendingMisPunchCount = $misPunchData->whereNull('ae_status')->count();

        // Holiday calculations
        $totalHolidayDays = PolicyHolidayList::whereHas('fh_attendance_policy', function ($query) {
            $query->whereColumn('ap_id', 'phl_ap_id');
        })
        ->where('ph1_b_id', $user->emp_b_id)
        ->whereDate('phl_start_date', '<=', $date->endOfMonth())
        ->whereDate('phl_end_date', '>=', $date->startOfMonth())
        ->get()
        ->sum(function ($holiday) {
            $startDate = Carbon::parse($holiday->phl_start_date);
            $endDate = Carbon::parse($holiday->phl_end_date);
            return $startDate->diffInDays($endDate) + 1;
        });

        // Leave calculations
        $leaveData = LeaveRequest::where('lvr_emp_id', $empId)
            ->where(function ($query) use ($date) {
                $query->whereBetween('lvr_start_date', [$date->startOfMonth(), $date->endOfMonth()])
                    ->orWhereBetween('lvr_end_date', [$date->startOfMonth(), $date->endOfMonth()]);
            })
            ->get();

        $approvedFullLeaves = $leaveData->where('lvr_leave_day_type_id', 201)->where('lvr_status', 'approved')->sum('lvr_total_leave_days');
        $approvedHalfLeaves = $leaveData->where('lvr_leave_day_type_id', 202)->where('lvr_status', 'approved')->sum('lvr_total_leave_days') * 0.5;
        $pendingLeaves = $leaveData->whereIn('lvr_leave_day_type_id', [201, 202])->where('lvr_status', 'pending')->sum('lvr_total_leave_days');

        // Build the return string
        $stringValue = sprintf(
            'Total Duration: %d hours and %d mins, PresentDays = %d, HalfDays = %.1f, Leaves = %d, Week Off = %d, Holiday = %d, Absent + MSP (Deductible) = %d, MSP = %d, Total OverTime Duration: %d hours and %d mins',
            $totalHours, $totalMinutes,
            $presentCount + $approvedMisPunchCount,
            $halfDayCount + $approvedHalfLeaves,
            $approvedFullLeaves,
            $attendanceCounts[204] ?? 0, // Week Off
            $totalHolidayDays,
            $absentCount + $misPunchCount,
            $misPunchCount + $pendingMisPunchCount,
            $totalOvertimeHours, $totalOvertimeMinutes
        );

            return $stringValue;
    }

    public function getTimeLog($date)
    {
        $user = Auth::user();
        $attendance = AttendanceRecord::where('atd_emp_id', $this->EmpData->emp_id)
                            ->where('atd_b_id', $user->emp_b_id)
                            ->where('atd_date', $date)
                            ->get();

        return $attendance;
    }
}
