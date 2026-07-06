<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use App\Models\MasterTable;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveOpeningBalance;
use Illuminate\Support\Facades\DB;

class LeaveOpeningImport implements OnEachRow, WithHeadingRow, WithChunkReading, WithValidation
{
	protected $user;
	protected $rowNumber = 1;
	protected $errorMessages = [];
	protected $successfulCount = 0;
	protected $skippedCount = 0;
	protected $failedCount = 0;
	protected $leaveTypeMap = [];

	public function __construct($user)
	{
		$this->user = $user;

		// prepare a mapping of normalized heading => leave type id
		$this->leaveTypeMap = [];
		$leaveCats = MasterTable::where('m_group', 'LEAVE_CATEGORY')->get();
		foreach ($leaveCats as $cat) {
			$key = $this->normalizeHeading($cat->m_name);
			$this->leaveTypeMap[$key] = $cat->m_id;
		}
	}

	/**
	 * Normalize a heading string to the same format WithHeadingRow uses for keys
	 */
	protected function normalizeHeading(string $h): string
	{
		$h = mb_strtolower($h);
		// replace non-alphanumeric characters with underscore
		$h = preg_replace('/[^a-z0-9]+/u', '_', $h);
		$h = trim($h, '_');
		// collapse multiple underscores
		$h = preg_replace('/_+/', '_', $h);
		return $h;
	}

	public function chunkSize(): int
	{
		return 1000;
	}

	public function rules(): array
	{
		return [
			'Emp Code*' => 'required',
		];
	}

	public function onRow(Row $row)
	{
		$this->rowNumber++;
		$row = $row->toArray();

		$empCode = trim($row['emp_code'] ?? '');

		// find employee by code or email
		$employee = null;
		if (!empty($empCode)) {
			$employee = Employee::where('emp_code', $empCode)->first();
		}

		if (!$employee) {
			$this->skippedCount++;
			$this->errorMessages[] = "Row {$this->rowNumber}: Employee not found for code '{$empCode}";
			return;
		}

		// iterate over each column (leave types) except common columns
		$skipKeys = ['s_no', 's_no*', 's_no*', 'sno', 's.no', 'emp_code'];

		// use DB transaction per row to keep consistent
		DB::beginTransaction();
		try {
			foreach ($row as $colHeading => $colValue) {
				$colKey = $this->normalizeHeading($colHeading);
				if (in_array($colKey, $skipKeys, true) || $colKey === '' ) {
					continue;
				}

				// if empty or non-numeric, skip silently
				$val = trim((string)$colValue);
				if ($val === '' || !is_numeric($val)) {
					continue;
				}

				$updatedInput = (float) $val;

				$leaveTypeId = $this->leaveTypeMap[$colKey] ?? null;
				if (!$leaveTypeId) {
					$this->errorMessages[] = "Row {$this->rowNumber}: Leave type '{$colHeading}' not recognized.";
					$this->skippedCount++;
					continue;
				}

				$assignedLeaveTypes = $employee->fh_policy_leave->fh_leave_type->toArray();
				if (!in_array($leaveTypeId, array_column($assignedLeaveTypes, 'lvt_cat_type_id'))) {
					continue;
				}

				// fetch current leave balance for this employee and leave type for current year
				$current = LeaveBalance::where('lb_emp_id', $employee->emp_id)
					->where('lb_cat_type_id', $leaveTypeId)
					->where('lb_year', now()->year)
					->where('lb_month', now()->month)
					->where('lb_b_id', $employee->emp_b_id)
					->first();

				$previous = $current ? (float) $current->lb_balance_remaining_leave : 0.0;

				if ($current) {
					// Add the imported value to both allotted and remaining balances
					$existingAlloted = (float) ($current->lb_alloted_leave ?? 0);
					$existingRemaining = (float) ($current->lb_balance_remaining_leave ?? 0);
					$newAlloted = $existingAlloted + $updatedInput;
					$newRemaining = $existingRemaining + $updatedInput;

					// record opening balance entry (previous = previous remaining)
					LeaveOpeningBalance::create([
						'lob_b_id' => $employee->emp_b_id,
						'lob_emp_id' => $employee->emp_id,
						'lob_leave_type_id' => $leaveTypeId,
						'lob_previous' => $previous,
						'lob_updated' => $updatedInput,
						'lob_total' => $newRemaining,
						'updated_by' => $this->user->emp_id ?? null,
					]);

					// update leave balance by adding the imported amount
					$current->update([
						'lb_alloted_leave' => $newAlloted,
						'lb_balance_remaining_leave' => $newRemaining,
						'lb_year' => now()->year,
						'lb_month' => now()->month,
					]);
					$this->successfulCount++;
				} else {
					// create new leave balance
					$newAlloted = $updatedInput;
					$newRemaining = $updatedInput;

					LeaveOpeningBalance::create([
						'lob_b_id' => $employee->emp_b_id,
						'lob_emp_id' => $employee->emp_id,
						'lob_leave_type_id' => $leaveTypeId,
						'lob_previous' => $previous,
						'lob_updated' => $updatedInput,
						'lob_total' => $newAlloted,
						'updated_by' => $this->user->emp_id ?? null,
					]);

					LeaveBalance::create([
						'lb_b_id' => $employee->emp_b_id,
						'lb_emp_id' => $employee->emp_id,
						'lb_cat_type_id' => $leaveTypeId,
						'lb_month' => now()->month,
						'lb_year' => now()->year,
						'lb_alloted_leave' => $newAlloted,
						'lb_taken_leave' => 0,
						'lb_balance_remaining_leave' => $newRemaining,
					]);

					$this->successfulCount++;
				}
			}
			DB::commit();
		} catch (\Exception $e) {
			DB::rollBack();
			$this->failedCount++;
			$this->errorMessages[] = "Row {$this->rowNumber}: Exception - " . $e->getMessage();
		}
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
