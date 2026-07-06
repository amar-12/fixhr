<?php

namespace App\Imports;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\PolicyShiftTiming;
use App\Models\PolicyHolidayList;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Helpers\CentralLogics;
use App\Models\AutomationRule;
use App\Models\PayrollPeriod;
use App\Models\ShiftCalendar;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class EmployeeCalendarImport implements OnEachRow, WithHeadingRow, WithChunkReading, WithValidation
{
	protected $user;
	protected $rowNumber = 1;
	protected $errorMessages = [];
	protected $successfulCount = 0;
	protected $skippedCount = 0;
	protected $failedCount = 0;

	public function __construct($user)
	{
		$this->user = $user;
	}

	public function chunkSize(): int
	{
		return 1000;
	}

	public function rules(): array
	{
		return [
			'*.Emp Code*' => 'required',
			'*.Shift Code*' => 'required',
			'*.Start Date(YYYY-MM-DD)*' => 'required',
			'*.End Date(YYYY-MM-DD)*' => 'required',
		];
	}

	public function onRow(Row $row)
	{
		$this->rowNumber++;
		$row = $row->toArray();

		$empCode   = trim($row['emp_code'] ?? '');
		$shiftCode = trim($row['shift_code'] ?? '');
		$start     = $row['start_dateyyyy_mm_dd'] ?? null;
		$end       = $row['end_dateyyyy_mm_dd'] ?? null;

		$errors = [];

		try {
			$startDate = is_numeric($start) ? Carbon::instance(ExcelDate::excelToDateTimeObject($start)) : Carbon::parse($start);
		} catch (\Exception $e) {
			$startDate = null;
			$errors[] = "Invalid Start Date.";
		}

		try {
			$endDate = is_numeric($end) ? Carbon::instance(ExcelDate::excelToDateTimeObject($end)) : Carbon::parse($end);
		} catch (\Exception $e) {
			$endDate = null;
			$errors[] = "Invalid End Date.";
		}

		if ($startDate && $endDate && $startDate->gt($endDate)) {
			$errors[] = "Start Date cannot be after End Date.";
		}

		$employee = Employee::where('emp_code', $empCode)
			->where('emp_b_id', $this->user->emp_b_id)
			->first();

		if (!$employee) {
			$errors[] = "Employee Code '$empCode' not found.";
		} elseif ($employee->emp_date_of_joining && $startDate && Carbon::parse($employee->emp_date_of_joining)->gt($startDate)) {
			$errors[] = "Employee DOJ is after the start date.";
		}

		$shift = PolicyShiftTiming::where('pst_code', $shiftCode)
			->where('pst_b_id', $this->user->emp_b_id)
			->first();

		if (!$shift) {
			$errors[] = "Shift Code - '$shiftCode' not found.";
		}

		if (!empty($errors)) {
			$this->failedCount++;
			$this->errorMessages[] = "Row {$this->rowNumber}: " . implode(' ', $errors);
			return;
		}

		$newShiftCalendar = ShiftCalendar::where([['sc_b_id', $this->user->emp_b_id], ['sc_emp_id', $employee->emp_id], ['sc_start_date', $startDate], ['sc_end_date', $endDate]])->first();
		if (!$newShiftCalendar) {
			ShiftCalendar::create([
				'sc_b_id' => $this->user->emp_b_id,
				'sc_emp_id' => $employee->emp_id,
				'sc_pst_id' => $shift->pst_id,
				'sc_start_date' => $startDate,
				'sc_end_date' => $endDate,
			]);
		} else {
			$newShiftCalendar->update([
		        'sc_pst_id' => $shift->pst_id,
		    ]);
		}
		$this->successfulCount++;
	}

	public function getErrorMessages(): array
	{
		return $this->errorMessages;
	}

	public function getSummary(): array
	{
		return [
			'success' => $this->successfulCount,
			'failed' => $this->failedCount,
			'skipped' => $this->skippedCount,
		];
	}
}
