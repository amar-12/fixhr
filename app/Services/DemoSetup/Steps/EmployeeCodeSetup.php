<?php

namespace App\Services\DemoSetup\Steps;

use App\Models\Business;
use App\Services\DemoSetup\Contracts\DemoSetupStep;
use App\Services\DemoSetup\DemoSetupService;

class EmployeeCodeSetup implements DemoSetupStep
{
	public function key(): string
	{
		return 'employee_code';
	}

	public function shouldRun(Business $business): bool
	{
		return !empty($business->b_emp_code_type);
	}

	public function run(Business $business, DemoSetupService $service): void
	{
		$input = $service->getInput($this->key());

		$mode = $input['type'] ?? 190;
		$prefix = $input['prefix'] ?? '';

		if ($mode === 191) {
			$business->update([
				'b_emp_code_type' => 191, // manual
				'b_emp_code' => null,
				'b_dashboard_id' => 168, // Attendance Dashboard
			]);
			return;
		}

		$business->update([
			'b_emp_code_type' => 190, // auto
			'b_emp_code' => strtoupper($prefix),
		]);
	}
}
