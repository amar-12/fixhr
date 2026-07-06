<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\CompOffBalance;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\MasterTable;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompOffBalanceController extends Controller
{
	public function index(Request $request)
	{
		$user = Auth::user();
		$businessId = $user->emp_b_id;

		$branch = Branch::whereNull('br_b_id')->orWhere('br_b_id', $businessId)->get();
		$departments = Department::whereNull('d_b_id')->orWhere('d_b_id', $businessId)->get();
		$designations = Designation::whereNull('dg_b_id')->orWhere('dg_b_id', $businessId)->get();

		$branchFilter = $request->input('balance_branchFilter');
		$departmentFilter = $request->input('balance_departmentFilter');
		$designationFilter = $request->input('balance_designationFilter');
		$emp_monthFilter = request()->input('emp_monthFilter');
		$comp_off_monthFilter = request()->input('comp_off_monthFilter');
		$activeFilter = request()->input('balance_activeFilter');

		if (!empty($toDateFilter) && str_contains($toDateFilter, '-')) {
			$dateParts = explode('-', $toDateFilter);
			$year = count($dateParts) === 2 ? $dateParts[0] : null;
			$month = count($dateParts) === 2 ? $dateParts[1] : null;
		} else {
			$year = null;
			$month = null;
		}

		$columns = [
			'S. No.',
			'Emp. Name',
			'Emp. Code',
			'Opening Balance',
			'Accrued',
			'Taken',
			'Expired',
			'Balance',
		];

		if ($request->ajax()) {
			$dynamicConditions = [
				[
					'method' => 'where',
					'args' => ['emp_b_id', $businessId]
				],
				[
					'method' => 'where',
					'args' => ['emp_role_id', '!=', '1']
				],

				[
					'method' => 'select',
					'args' => ['emp_id', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_code', 'emp_br_id', 'emp_d_id', 'emp_dg_id', 'emp_date_of_joining'],
					'relation' => ['compOffBalances']
				],
				[
					'method' => 'sortBy',
					'args' => ['emp_id', 'emp_fname', 'emp_code'],
				]
			];

			if (!empty($branchFilter)) {
				$dynamicConditions[] = ['method' => 'where', 'args' => ['emp_br_id', $branchFilter]];
			}

			if (!empty($departmentFilter)) {
				$dynamicConditions[] = ['method' => 'where', 'args' => ['emp_d_id', $departmentFilter]];
			}

			if (!empty($designationFilter)) {
				$dynamicConditions[] = ['method' => 'where', 'args' => ['emp_dg_id', $designationFilter]];
			}

			if ($activeFilter != '') {
				$dynamicConditions[] = ['method' => 'where', 'args' => ['emp_status', $activeFilter]];
			}

			if ($emp_monthFilter != '') {
				$startDate = $emp_monthFilter . '-01';
				$endDate = date('Y-m-t', strtotime($startDate));
				$dynamicConditions[] = [
					'method' => 'whereBetween',
					'args' => ['emp_date_of_joining', [$startDate, $endDate]],
				];
			}

			if ($comp_off_monthFilter != '') {
				[$year, $month] = explode('-', $comp_off_monthFilter);
				$prev_year = Carbon::parse("{$year}-{$month}-01")->subMonth()->year;
				$prev_month = Carbon::parse("{$year}-{$month}-01")->subMonth()->month;
			} else {
				$year = Carbon::now()->year;
				$month = Carbon::now()->month;
				$prev_year = Carbon::now()->subMonth()->year;
				$prev_month = Carbon::now()->subMonth()->month;
			}


			$list = (new DynamicModelDataTableHelper(
				eloquentModel: new Employee(),
				dynamicConditions: $dynamicConditions,
				searchColumns: ['emp_id', 'emp_fname', 'emp_mname', 'emp_lname', 'emp_code'],
				searchRelationships: [
					'compOffBalances' => ['cb_emp_id', 'cb_year', 'cb_month', 'cb_balance_remaining']
				]
			))->getServerSideDataTable();

			$rowData = [];
			$i = 1;

			foreach ($list as $employee) {
				$row = [];
				$row[] = $i++;
				$row[] = trim("{$employee->emp_fname} {$employee->emp_mname} {$employee->emp_lname}");
				$row[] = $employee->emp_code;

				$opening_bal = CompOffBalance::where('cb_emp_id', $employee->emp_id)
				->where('cb_year', $prev_year)
				->where('cb_month', $prev_month)
				->orderBy('created_at', 'desc')
				->value('cb_balance_remaining');

				$row[] = $opening_bal ?? '0';

				$accrued = $employee->compOffBalances->where('cb_year', $year)->where('cb_month', $month)->value('cb_alloted');
				$taken = $employee->compOffBalances->where('cb_year', $year)->where('cb_month', $month)->value('cb_taken');
				$expired = $employee->compOffBalances->where('cb_year', $year)->where('cb_month', $month)->value('cb_expired');

				$row[] = $accrued ?? '0';
				$row[] = $taken ?? '0';
				$row[] = $expired ?? '0';
				$row[] = $employee->compOffBalances->where('cb_year', $year)->where('cb_month', $month)->value('cb_balance_remaining');

				$rowData[] = $row;
			}

			$output = [
				"draw" => intval($request->input('draw')),
				"recordsTotal" => $list->count(),
				"recordsFiltered" => (new DynamicModelDataTableHelper(
					eloquentModel: new Employee(),
					dynamicConditions: $dynamicConditions
				))->countFilteredServerSideDataTable(),
				"data" => $rowData,
			];

			return response()->json($output);
		}

		return view('admin.setting.attendance-details.comp-off-balance', compact(
			'departments',
			'designations',
			'branch',
			'columns'
		));
	}
}
