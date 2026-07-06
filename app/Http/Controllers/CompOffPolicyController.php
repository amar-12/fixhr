<?php

namespace App\Http\Controllers;

use App\Helpers\CentralLogics;
use App\Models\CompOffDurationCondition;
use App\Models\CompOffPolicy;
use App\Models\Employee;
use App\Models\MasterTable;
use Carbon\Carbon;
use ChandraHemant\ServerSideDatatable\DynamicModelDataTableHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CompOffPolicyController extends Controller
{
	/**
	 * Display a listing of the resource.
	 */
	public function index(Request $request)
	{
		$user = Auth::user();
		if ($user) {
			$businessId = $user->emp_b_id;
			$approval_flow = MasterTable::where("m_group", "LIKE", "APPROVAL_TYPE")->get();

			if ($request->ajax()) {

				$dynamicConditions = [
					[
						'method' => 'where',
						'args' => ['cop_b_id', $businessId],
					],
					[
						'method' => 'select',
						'args' => ['cop_id', 'cop_b_id', 'co_policy_name', 'carry_forward', 'validity', 'cop_effective_date', 'cop_status'],
						'relation' => ['duration_conditions', 'fh_business:b_id'],
					],
					[
						'method' => 'sortBy',
						'args' => ['co_policy_name', 'carry_forward', 'validity', 'cop_effective_date', 'cop_status']
					],
				];

				$searchColumns = ['co_policy_name', 'carry_forward', 'validity', 'cop_effective_date', 'cop_status'];

				$list = (new DynamicModelDataTableHelper(eloquentModel: new CompOffPolicy(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns))->getServerSideDataTable();

				$rowData = [];
				$i = 0;
				foreach ($list as $key => $value) {
					$i++;
					$row = [];
					$row[] = $i;
					$row[] = $value->co_policy_name;
					$row[] = '<span class="fs-11 fw-bold">W.E.F. </span>
                    <span class="with-effect-from-badge fs-10">' . Carbon::parse($value->cop_effective_date)->format('d-M-Y') . '</span>';

					$editUrl = route('compoff-policy.edit', $value->cop_id);
					$deleteUrl = route('compoff-policy.destroy', $value->cop_id);
					$editData = json_encode([
						'id' => md5($value->cop_id),
						'co_policy_name' => $value->co_policy_name,
						'carry_forward' => $value->carry_forward,
						'cop_status' => $value->cop_status,
						'validity' => $value->validity,
						'cop_effective_date' => Carbon::parse($value->cop_effective_date)->format('Y-m-d'),
						'conditions' => $value->duration_conditions,
					]);

					$row[] = '
						<div class="btn-list ms-3">
							<div class="dropdown">
								<button class="btn btn-light btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
									<i class="fa fa-ellipsis-v"></i> <!-- Three-dot icon -->
								</button>
								<ul class="dropdown-menu p-2" style="min-width: 180px;">
									<li>
										<button class="dropdown-item text-primary fw-semibold d-flex align-items-center gap-2 edit-button edit-policy"
											type="button" data-edit-data="' . htmlspecialchars($editData, ENT_QUOTES, 'UTF-8') . '">
											<i class="feather feather-edit"></i> Edit
										</button>
									</li>
									<li>
										<button class="dropdown-item text-danger fw-semibold d-flex align-items-center gap-2 delete-button"
											type="button"
											data-id="' . $value->cop_id . '"
											data-url="' . $deleteUrl . '"
											title="Delete">
											<i class="feather feather-trash"></i> Delete
										</button>
									</li>
								</ul>
							</div>
						</div>
					';

					$rowData[] = $row;
				}

				$output = [
					"draw" => $request->input('draw'),
					"recordsTotal" => sizeof($list),
					"recordsFiltered" => (new DynamicModelDataTableHelper(eloquentModel: new CompOffPolicy(), dynamicConditions: $dynamicConditions, searchColumns: $searchColumns))->getServerSideDataTable(),
					"data" => $rowData,
				];

				return json_encode($output);
			}

			$columns = [
				'S. No.',
				'Policy Name',
				'With Effect From',
				'Action'
			];

			return view('admin.setting.attendance.compoff-policy', compact('approval_flow', 'columns'));
		}
	}

	private function compOffValidationRules(Request $request, $businessId, $ignoreId = null)
	{
		return [
			'co_policy_name' => [
				'required',
				'string',
				'max:255',
			],
			'carry_forward' => 'required|boolean',
			'validity' => 'required|integer',
			'cop_effective_date' => [
				'required',
				'date',
				Rule::unique('compoff_policy')->where(function ($query) use ($businessId) {
					return $query->where('cop_b_id', $businessId);
				})->ignore($ignoreId, 'cop_id')
			],
			'conditions' => 'required|array'
		];
	}

	private function compOffValidationMessages()
	{
		return [
			'cop_effective_date.unique' => 'A policy with this effective date already exists for the selected business.'
		];
	}

	/**
	 * Show the form for creating a new resource.
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 */
	public function store(Request $request)
	{
		// dd($request->all());
		$user = Auth::user();
		$businessId = $user->emp_b_id;

		try {
			\Illuminate\Support\Facades\DB::beginTransaction();

			// Validate and store the data
			$request->validate(
				$this->compOffValidationRules($request, $businessId),
				$this->compOffValidationMessages()
			);

			$carryForward = $request->carry_forward ? 120 : 121;
			$cop_status = $request->cop_status;

			$policy = CompOffPolicy::create([
				'cop_b_id' => $businessId,
				'co_policy_name' => $request->co_policy_name,
				'carry_forward' => $carryForward,
				'cop_status' => $cop_status,
				'validity' => $request->validity,
				'cop_effective_date' => $request->cop_effective_date,
			]);

			foreach ($request->conditions as $condition) {
				if ($condition['work_hours'] && $condition['operator'] && $condition['cp_quantity']) {
					CompOffDurationCondition::create([
						'condition_b_id' => $businessId,
						'cop_id' => $policy->cop_id,
						'work_duration' => $condition['work_hours'],
						'operator' => $condition['operator'],
						'co_quantity' => $condition['cp_quantity'],
					]);
				}
			}

			\Illuminate\Support\Facades\DB::commit();

			return response()->json(['status' => 'success', 'message' => 'Comp Off Policy created successfully.']);
		} catch (\Exception $e) {
			\Illuminate\Support\Facades\DB::rollBack();
			return response()->json(['status' => 'error', 'message' => 'Failed to create Comp Off Policy.', 'error' => $e->getMessage()], 500);
		}
	}

	/**
	 * Display the specified resource.
	 */
	public function show(string $id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 */
	public function edit(string $id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 */
	public function update(Request $request, string $id)
	{
		$user = Auth::user();
		$businessId = $user->emp_b_id;

		try {
			\Illuminate\Support\Facades\DB::beginTransaction();

			$policy = CompOffPolicy::where('cop_b_id', $businessId)->whereRaw('md5(cop_id) = ?', [$id])->first();

			// Validate and store the data
			$request->validate(
				$this->compOffValidationRules($request, $businessId, $policy->cop_id),
				$this->compOffValidationMessages()
			);

			$carryForward = $request->carry_forward ? 120 : 121;

			$policy->update([
				'co_policy_name' => $request->co_policy_name,
				'validity' => $request->validity,
				'cop_effective_date' => $request->cop_effective_date,
				'cop_status' => $request->cop_status,
			]);

			CompOffDurationCondition::where('cop_id', $policy->cop_id)->delete();

			foreach ($request->conditions as $condition) {
				if ($condition['work_hours'] && $condition['operator'] && $condition['cp_quantity']) {
					CompOffDurationCondition::create([
						'condition_b_id' => $businessId,
						'cop_id' => $policy->cop_id,
						'work_duration' => $condition['work_hours'],
						'operator' => $condition['operator'],
						'co_quantity' => $condition['cp_quantity'],
					]);
				}
			}

			\Illuminate\Support\Facades\DB::commit();

			return response()->json(['status' => 'success', 'message' => 'Comp Off Policy updated successfully.']);
		} catch (\Exception $e) {
			\Illuminate\Support\Facades\DB::rollBack();
			return response()->json(['status' => 'error', 'message' => 'Failed to update Comp Off Policy.', 'error' => $e->getMessage()], 500);
		}
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(string $id)
	{
		$policy = CompOffPolicy::findOrFail($id);
		if ($policy) {
			$policy->delete();
			return response()->json(['status' => 'success', 'message' => 'Comp Off Policy deleted successfully.']);
		} else {
			return response()->json(['status' => 'error', 'message' => 'Comp Off Policy not found.'], 404);
		}
	}
}
