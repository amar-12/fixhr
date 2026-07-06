<?php

namespace App\Http\Controllers;

use App\Models\MasterTable;
use App\Services\DemoSetup\DemoSetupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoSetupController extends Controller
{
	public function start()
	{
		$shiftType = MasterTable::where('m_group', 'ATTENDANCE_SHIFT_TYPE')->get();
		$leaveCats = MasterTable::where('m_group', 'LEAVE_CATEGORY')->whereIn('m_id', [207, 208, 209, 213])->get();
		return view('admin.onboarding.onboarding', compact('shiftType', 'leaveCats'));
	}

	protected function rules(): array
	{
		return [
			// Employee Code
			'b_emp_code_type' => 'required|in:190,191',
			'b_emp_code'      => 'nullable|string|max:5',

			// Shift
			'shift_type_id' => 'required|in:244,245,246',
			'start_time'    => 'required|date_format:H:i',
			'end_time'      => 'required|date_format:H:i',
			'grace_minutes' => 'nullable|integer|min:0|max:120',
			'break_duration' => 'nullable|integer|min:0|max:240',

			// Weekly Policy
			'paidWeekOff' => 'nullable|in:on',
			'dayIds'      => 'required|array|min:1',
			'dayIds.*'    => 'numeric',

			// Leave Policy (ONLY when present)
			'leaveCatIds'   => 'nullable|array',
			'leaveCatIds.*' => 'numeric',
			'leave_days'    => 'nullable|array',
			'leave_days.*'  => 'numeric',

		];
	}


	public function store(Request $request)
	{
		// dd($request->all());
		$user = Auth::user();

		if (!$user) {
			return response()->json([
				'message' => 'Unauthenticated'
			], 401);
		}

		$business = $user->fh_business;

		if (!$business) {
			return response()->json([
				'message' => 'Business not found'
			], 404);
		}

		if ($business->demo_setup_completed) {
			return response()->json([
				'message' => 'Business setup already completed for ' . $business->b_name . " business",
			], 404);
		}

		$request->merge([
			'leaveCatIds' => array_values(array_filter(
				$request->leaveCatIds ?? [],
				fn($v) => !is_null($v)
			)),
			'leave_days' => array_values(array_filter(
				$request->leave_days ?? [],
				fn($v) => !is_null($v)
			)),
		]);

		// 1️⃣ Validate
		$data = $request->validate($this->rules());

		$leaves = [];

		$catIds  = array_map('intval', $data['leaveCatIds']) ?? [];
		$daysArr = array_map('floatval', $data['leave_days']) ?? [];

		foreach ($catIds as $index => $catId) {
			if (!isset($daysArr[$index])) {
				continue;
			}

			$days = (float) $daysArr[$index];

			$leaves[] = [
				'cat_type_id'   => (int) $catId,
				'cycle_id'      => 219,
				'days'          => $days,
				'carry_forward' => $days,
			];
		}

		// 2️⃣ Normalize inputs for DemoSetupService
		$dayIds = array_map('intval', $data['dayIds']);
		$recurrenceValues = [334, 335, 336, 337, 338];
		$recurrence = [];
		foreach ($dayIds as $dayId) {
			$recurrence[$dayId] = $recurrenceValues;
		}

		$inputs = [
			// Employee Code
			'employee_code' => [
				'type'   => (int) $data['b_emp_code_type'],
				'prefix' => $data['b_emp_code'] ?? null,
			],

			// Shift Policy
			'shift_policy' => [
				'type_id' => (int) $data['shift_type_id'],
				'start_time' => $data['start_time'],
				'end_time' => $data['end_time'],
				'grace' => (int) ($data['grace_minutes'] ?? 0),
				'break_duration' => (int) ($data['break_duration'] ?? 0),
			],

			// Weekly Policy
			'weekly_policy' => [
				'unpaid_days' => isset($data['paidWeekOff']) ? [] : $dayIds,
				'day_ids' => $dayIds,
				'recurrence' => $recurrence,
			],

			// Leave Policy
			'leave_policy' => [
				'categories' => $leaves
			],
		];

		// 3️⃣ Run Business Setup
		$result = DemoSetupService::forBusiness(
			$business,
			$inputs
		)->run();

		if ($result->alreadyCompleted) {
			return response()->json([
				'status'  => 'info',
				'message' => 'Business setup is already completed for this business.',
			]);
		}

		return response()->json([
			'status' => 'success',
			'message' => 'Business setup completed successfully.',
			'steps' => $result->executedSteps,
		]);
	}
}
