<?php

namespace App\Exports\BusinessEmployee;

use App\Models\Employee;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BusinessEmployeeExport extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles,
    WithCustomValueBinder
{
    protected $businessId;
    protected $selectedEmployees;

    public function __construct($businessId, $selectedEmployees = [])
    {
        $this->businessId = $businessId;
        $this->selectedEmployees = $selectedEmployees;
    }

    public function collection()
    {
        $query = Employee::where('emp_b_id', $this->businessId)
            ->where('emp_role_id', '<>', 1);

        if (!empty($this->selectedEmployees)) {
            $query->whereIn('emp_id', $this->selectedEmployees);
        }

        return $query->get();
    }

    public function map($employee): array
    {
        $str = fn($val) => $val === null || $val === '' ? '' : trim($val);

        $employeeCode = $str($employee->emp_code.'B'.$this->businessId);
        
        $employeeId = $str($employee->emp_code);
        if (empty($employeeId) || !is_numeric($employeeId)) {
            $employeeId = 10000 + $employee->emp_id;
        }

        $cardNo = $employeeId;
        $doj = $str($employee->emp_date_of_joining);

        $validityEnd = '';
        if ($doj && strlen($doj) >= 10) {
            try {
                $validityEnd = Carbon::createFromFormat('Y-m-d', $doj)
                    ->addYears(2)
                    ->format('Y-m-d');
            } catch (\Exception $e) {
                $validityEnd = $doj;
            }
        }

        return [
            $employeeCode,
            $str($employee->emp_fname),
            $str($employee->emp_lname ?? ''),
            '1',
            'HR',
            $str($employee->emp_dg_id ?? '1'),
            $str($employee->designation?->name ?? 'Staff'),
            'Male / Female',
            $doj,
            $cardNo,
            2,
            $doj,
            $validityEnd,
        ];
    }

    public function headings(): array
    {
        return [
            'Employee Id',
            'First Name',
            'Last Name',
            'Department Id',
            'Department Name',
            'Position Code',
            'Position Name',
            'Gender',
            'Date of Joining',
            'Card No.',
            'Area Code',
            'Validity Start Date',
            'Validity End Date',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE4E1E1'],
            ],
        ]);
    }

    public function bindValue(Cell $cell, $value)
    {
        $col = $cell->getColumn();

        if ($col === 'H') {
            $cell->setValueExplicit('Male / Female', DataType::TYPE_STRING);
            return true;
        }

        if ($col === 'J' && is_numeric($value)) {
            $cell->setValueExplicit((int)$value, DataType::TYPE_NUMERIC);
            return true;
        }

        $cell->setValueExplicit($value ?? '', DataType::TYPE_STRING);
        return true;
    }
}