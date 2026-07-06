<?php

namespace App\Exports\Attendance;

use App\Models\Employee;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DepartmentWiseForecastReport implements
    FromCollection,
    WithHeadings,
    WithColumnWidths,
    WithEvents,
    WithCustomStartCell
{
    protected Collection $records;
    protected array $filters;
    protected Carbon $fromDate;
    protected Carbon $toDate;
    protected Collection $finalRows;

    public function __construct($records, $filters, $fromDate, $toDate)
    {
        $this->records = collect($records);
        $this->filters = $filters ?? [];

        $this->fromDate = Carbon::parse($fromDate);
        $this->toDate   = Carbon::parse($toDate);

        $this->applyFilters();
        $this->prepareData();
    }

    /* =========================================================
       APPLY 4 FILTERS
    ========================================================= */

    private function applyFilters()
    {
        $this->records = $this->records->filter(function ($leave) {

            $emp = $leave->fh_employees_details;

            if (!$emp) return false;

            // Department filter
            if (!empty($this->filters['department_id']) &&
                $emp->emp_d_id != $this->filters['department_id']) {
                return false;
            }

            // Employee filter
            if (!empty($this->filters['employee_id']) &&
                $leave->lvr_emp_id != $this->filters['employee_id']) {
                return false;
            }

            // Dealer filter
            if (!empty($this->filters['dealer_id']) &&
                $emp->emp_dlr_id != $this->filters['dealer_id']) {
                return false;
            }

            // Designation filter
            if (!empty($this->filters['designation_id']) &&
                $emp->emp_dg_id != $this->filters['designation_id']) {
                return false;
            }

            return true;
        });
    }

    /* =========================================================
       PREPARE DATA
    ========================================================= */

    private function prepareData()
    {
        $rows = [];
        $serial = 1;

        $departmentCounts = Employee::where('emp_b_id', auth()->user()->emp_b_id)
            ->selectRaw('emp_d_id, COUNT(*) as total')
            ->groupBy('emp_d_id')
            ->pluck('total', 'emp_d_id');

        $shown = [];

        foreach ($this->records as $leave) {

            $period = CarbonPeriod::create(
                Carbon::parse($leave->lvr_start_date),
                Carbon::parse($leave->lvr_end_date)
            );

            $dept = $leave->fh_employees_details->fh_department ?? null;
            $deptName = $dept->d_name ?? 'Unknown';
            $deptId   = $dept->d_id ?? null;

            foreach ($period as $date) {

                if ($date->lt($this->fromDate) || $date->gt($this->toDate)) {
                    continue;
                }

                $dateKey = $date->toDateString();
                $uniqueKey = $deptId . '_' . $dateKey;

                if (isset($shown[$uniqueKey])) continue;
                $shown[$uniqueKey] = true;

                $dayDeptLeaves = $this->records->filter(function ($l) use ($date, $deptId) {

                    $dept = $l->fh_employees_details->fh_department ?? null;

                    return $dept &&
                        $dept->d_id == $deptId &&
                        $date->between(
                            Carbon::parse($l->lvr_start_date),
                            Carbon::parse($l->lvr_end_date)
                        );
                });

                $approved = $dayDeptLeaves->where('lvr_status', 157)->count();
                $pending  = $dayDeptLeaves->where('lvr_status', 140)->count();

                $totalEmployees = $departmentCounts[$deptId] ?? 0;

                $rows[] = [
                    $serial++,
                    $date->format('d-M-Y'),
                    $deptName,
                    $totalEmployees,
                    $approved,
                    $pending,
                    $totalEmployees - $approved,
                    $totalEmployees - ($approved + $pending),
                ];
            }
        }

        usort($rows, fn($a,$b) => strtotime($a[1]) <=> strtotime($b[1]));

        $this->finalRows = collect($rows);
    }

    public function collection()
    {
        return $this->finalRows;
    }

    public function headings(): array
    {
        return [
            'S#',
            'Attendance Date',
            'Department',
            'Total Employees',
            'Approved',
            'Pending',
            'Remaining (Strict)',
            'Remaining (Planning)',
        ];
    }

    public function startCell(): string
    {
        return 'A6';
    }

    public function columnWidths(): array
    {
        return [
            'A'=>5,'B'=>15,'C'=>25,'D'=>18,'E'=>12,'F'=>12,'G'=>20,'H'=>20
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex(count($this->headings()));
                $dataCount  = $this->finalRows->count();
                $lastRow    = 6 + $dataCount;

                $sheet->setShowGridlines(false);

                $sheet->setCellValue('A1', $this->records->first()->fh_business->b_name ?? 'Business Name');
                $sheet->setCellValue('A2', 'Department Wise Forecast Report');
                $sheet->setCellValue('A3', "Period: ".$this->fromDate->format('d-M-Y')." to ".$this->toDate->format('d-M-Y'));
                $sheet->setCellValue('A4', 'Printed on: '.Carbon::now()->format('d-M-Y h:i A'));

                foreach (range(1,4) as $r) {
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setBold(true);
                }

                if ($dataCount > 0) {
                    $sheet->freezePane('A7');
                }
            }
        ];
    }
}
