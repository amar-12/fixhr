<?php

namespace App\Services\DemoSetup;

use App\Models\Business;
use Illuminate\Support\Facades\DB;
use App\Services\DemoSetup\Steps\EmployeeCodeSetup;
use App\Services\DemoSetup\Steps\AttendancePolicySetup;
use App\Services\DemoSetup\Steps\ShiftPolicySetup;
use App\Services\DemoSetup\Steps\WeeklyOffSetup;
use App\Services\DemoSetup\Steps\LeavePolicySetup;
use App\Services\DemoSetup\Steps\ApprovalFlowSetup;
use App\Services\DemoSetup\DemoSetupResult;

class DemoSetupService
{
	protected Business $business;
	protected array $steps = [];
	protected array $input = [];

	public static function forBusiness(Business $business, array $input = []): self
	{
		return new self($business, $input);
	}

	public function __construct(Business $business, array $input = [])
	{
		$this->business = $business;
		$this->input    = $input;

		$this->steps = [
			EmployeeCodeSetup::class,
			AttendancePolicySetup::class,
			ShiftPolicySetup::class,
			WeeklyOffSetup::class,
			LeavePolicySetup::class,
			ApprovalFlowSetup::class,
		];
	}

	public function getInput(string $key, $default = null)
	{
		return data_get($this->input, $key, $default);
	}

	public function run(): DemoSetupResult
	{
		// 🚫 Demo setup already completed
		if ($this->business->demo_setup_completed) {
			return new DemoSetupResult([], true);
		}

		$executed = [];

		DB::transaction(function () use (&$executed) {
			foreach ($this->steps as $stepClass) {
				$step = app($stepClass);

				if ($step->shouldRun($this->business)) {
					$step->run($this->business, $this);
					$executed[] = $step->key();
				}
			}

			// ✅ Mark demo setup as completed
			$this->business->update([
				'demo_setup_completed' => 1,
			]);
		});

		return new DemoSetupResult($executed, false);
	}
}
