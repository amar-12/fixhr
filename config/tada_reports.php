<?php

return [
	// Centralized TaDA report definitions
	'reports' => [
		193 => [
			'slug' => 'travel-report',
			'name' => 'Travel Report',
			'group' => 'travel',
			'export' => \App\Exports\TaDa\TravelReport::class,
			'with_tap_location' => false,
		],
		194 => [
			'slug' => 'travel-advance-report',
			'name' => 'Travel Advance Report',
			'group' => 'travel',
			'export' => \App\Exports\TaDa\TravelAdvanceReport::class,
			'with_tap_location' => false,
		],
		195 => [
			'slug' => 'travel-application-detailed-report',
			'name' => 'Travel Application Detailed Report',
			'group' => 'travel',
			'export' => \App\Exports\TaDa\TravelDetailedReport::class,
			'with_tap_location' => true,
		],
		567 => [
			'slug' => 'claim-basic-report',
			'name' => 'Claim Basic Report',
			'group' => 'claim',
			'export' => \App\Exports\TaDa\ClaimBasicReport::class,
			'with_tap_location' => false,
		],
		568 => [
			'slug' => 'claim-detailed-report',
			'name' => 'Claim Detailed Report',
			'group' => 'claim',
			'export' => \App\Exports\TaDa\ClaimDetailReport::class,
			'with_tap_location' => true,
		],
		569 => [
			'slug' => 'claim-summary-report',
			'name' => 'Claim Summary Report',
			'group' => 'claim',
			'export' => \App\Exports\TaDa\ClaimSummaryReport::class,
			'with_tap_location' => false,
		],
		626 => [
			'slug' => 'travel-attendance-report',
			'name' => 'Travel Attendance Report',
			'group' => 'travel',
			'export' => \App\Exports\TaDa\AttendanceGeofenceReport::class,
			'with_tap_location' => false,
		],
	],

	// Convenience maps
	'slug_map' => [
		'travel-report' => 193,
		'travel-advance-report' => 194,
		'travel-application-detailed-report' => 195,
		'claim-basic-report' => 567,
		'claim-detailed-report' => 568,
		'claim-summary-report' => 569,
		'travel-attendance-report' => 626,
	],

	'group_ids' => [
		'travel' => [193, 194, 195, 626],
		'claim' => [567, 568, 569],
	],
];


