<?php

namespace App\Services\DemoSetup\Steps;

use App\Models\Business;
use App\Models\PolicyAttendance;
use App\Services\DemoSetup\Contracts\DemoSetupStep;
use App\Services\DemoSetup\DemoSetupService;

class AttendancePolicySetup implements DemoSetupStep
{
	public function key(): string
	{
		return 'attendance_policy';
	}

	public function shouldRun(Business $business): bool
	{
		return !PolicyAttendance::where('ap_b_id', $business->b_id)->exists();
	}

	public function run(Business $business, DemoSetupService $service): void
	{
		PolicyAttendance::create([
			'ap_b_id' => $business->b_id,
			'ap_name' => 'Default Attendance Policy',
			'ap_description' => 'System generated default attendance policy',
			'ap_checkin_method_ids' => json_encode([
				314, // Selfie
				316, // Face Detection
				317, // QR Scanner
			]),
			'ap_punch_duration' => 15,
		]);
	}
}
