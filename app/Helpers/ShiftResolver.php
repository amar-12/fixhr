<?php

namespace App\Helpers;

use App\Models\Employee;
use App\Models\ShiftCalendar;
use App\Models\PolicyShiftTiming;
use Carbon\Carbon;

class ShiftResolver
{
	/**
	 * Resolve employee shift with source info
	 *
	 * @param Employee $employee
	 * @param string   $date
	 * @return object|null
	 *   ->shift (PolicyShiftTiming)
	 *   ->resolved_from (string: calendar|batch|department|employee)
	 *   ->source (the actual model object that provided the shift)
	 */
	public static function resolveEmployeeShift(
	    Employee $employee,
	    string $date,
	    ?string $inTime = null,
	    ?string $outTime = null
	): ?object
	{
		$date = Carbon::parse($date)->toDateString();
		if ($inTime) {
			$inTime = Carbon::parse($inTime)->format('H:i');
		}
		if ($outTime) {
			$outTime = Carbon::parse($outTime)->format('H:i');
		}
		// 1) Shift Calendar
		$calendar = ShiftCalendar::where('sc_emp_id', $employee->emp_id)
			->whereDate('sc_start_date', '<=', $date)
			->whereDate('sc_end_date', '>=', $date)
			->with('shift')
			->orderByDesc('sc_id')
			->first();

		if ($calendar && $calendar->shift) {
			return (object)[
				// 'shift' => $calendar->shift,
				'shift' => PolicyShiftTiming::find($calendar->shift->pst_id),
				'resolved_from' => 'calendar',
				'source' => $calendar, // <-- return full model
			];
		}

		if ($employee->fh_department && $employee->fh_department->d_pst_id) {
		    $shiftIds = is_array($employee->fh_department->d_pst_id)
		        ? $employee->fh_department->d_pst_id
		        : explode(',', $employee->fh_department->d_pst_id);

		    $shiftIds = array_map('intval', $shiftIds);
		    $shiftIds = array_filter($shiftIds);

		    if (!empty($shiftIds)) {

		        $shifts = PolicyShiftTiming::whereIn('pst_id', $shiftIds)
		            ->orderByRaw('FIELD(pst_id, ' . implode(',', $shiftIds) . ')')
		            ->get();

		        if ($shifts->isNotEmpty()) {

		            // 🔥 punch time resolve (fallback: shift start matching)
		            // $punchTime = request()->in_time ?? request()->out_time;
		            if ($inTime && $outTime) {
		            	$punchTime = $inTime ?? $outTime;
		            } else {
		            	$punchTime = request()->in_time ?? request()->out_time;
		            }

		            if ($punchTime) {
		                $checkIn = Carbon::parse($date . ' ' . $punchTime);
		                $bestShift = (new self)->pickBestShiftFromMultiple(
		                    $shifts,
		                    $checkIn,
		                    Carbon::parse($date)
		                );
		            } else {
		                // fallback → first priority shift
		                $bestShift = $shifts->first();
		            }

		            if ($bestShift) {
		                return (object)[
		                    'shift' => $bestShift, // ✅ SINGLE shift
		                    'resolved_from' => 'department',
		                    'source' => $employee->fh_department,
		                ];
		            }
		        }
		    }
		}

		// 4) Employee default shift
		if ($employee->fh_shift_type) {
			return (object)[
				// 'shift' => $employee->fh_shift_type,
				'shift' => PolicyShiftTiming::find($employee->fh_shift_type->pst_id),
				'resolved_from' => 'employee',
				'source' => $employee, // <-- employee itself
			];
		}

		return null;
	}

	private function pickBestShiftFromMultiple($shifts, Carbon $checkInTime, Carbon $date)
	{
	    $bestShift = null;
	    $bestScore = null;

	    foreach ($shifts as $shift) {

	        $startTime = $shift->pst_start_time->format('H:i');
	        $endTime   = $shift->pst_end_time->format('H:i');

	        $shiftStart = Carbon::parse($date->format('Y-m-d') . ' ' . $startTime);
	        $shiftEnd   = Carbon::parse($date->format('Y-m-d') . ' ' . $endTime);

	        $isNightShift = $endTime < $startTime;

	        if ($isNightShift) {
	            // night shift always ends next day
	            $shiftEnd->addDay();

	            /**
	             * ⭐ CRITICAL FIX ⭐
	             * 18:00 ke baad ka punch = upcoming night shift
	             */
	            if ($checkInTime->hour >= 18) {
	                // shiftStart stays same day (21:00 today)
	            } else {
	                // morning punches (01:00, 02:00 etc)
	                $shiftStart->subDay();
	                $shiftEnd->subDay();
	            }
	        }

	        // ⏱️ 2 hour tolerance window
	        $windowStart = $shiftStart->copy()->subHours(2);
	        $windowEnd   = $shiftEnd->copy()->addHours(2);

	        if (! $checkInTime->between($windowStart, $windowEnd)) {
	            continue;
	        }

	        // diff from shift start
	        $diff = abs($checkInTime->diffInMinutes($shiftStart));

	        // night shift priority
	        if ($isNightShift) {
	            $diff -= 240; // strong bias
	        }

	        if ($bestScore === null || $diff < $bestScore) {
	            $bestScore = $diff;
	            $bestShift = $shift;
	        }
	    }

	    return $bestShift;
	}

	public static function resolveEmployeeShiftforUser(
	    Employee $employee,
	    string $date,
	): ?object
	{
		$currentTime = now()->format('H:i');
		$date = Carbon::parse($date)->toDateString();

		// 1) Shift Calendar
		$calendar = ShiftCalendar::where('sc_emp_id', $employee->emp_id)
			->whereDate('sc_start_date', '<=', $date)
			->whereDate('sc_end_date', '>=', $date)
			->with('shift')
			->orderByDesc('sc_id')
			->first();

		if ($calendar && $calendar->shift) {
			return (object)[
				// 'shift' => $calendar->shift,
				'shift' => PolicyShiftTiming::find($calendar->shift->pst_id),
				'resolved_from' => 'calendar',
				'source' => $calendar, // <-- return full model
			];
		}

		if ($employee->fh_department && $employee->fh_department->d_pst_id) {
		    $shiftIds = is_array($employee->fh_department->d_pst_id)
		        ? $employee->fh_department->d_pst_id
		        : explode(',', $employee->fh_department->d_pst_id);

		    $shiftIds = array_map('intval', $shiftIds);
		    $shiftIds = array_filter($shiftIds);
		    if (!empty($shiftIds)) {

		        $shifts = PolicyShiftTiming::whereIn('pst_id', $shiftIds)
		            ->orderByRaw('FIELD(pst_id, ' . implode(',', $shiftIds) . ')')
		            ->get();

		        if ($shifts->isNotEmpty()) {

		            if ($currentTime) {
		                $checkIn = Carbon::parse($date . ' ' . $currentTime);
		                $bestShift = (new self)->pickBestShiftToUser(
		                    $shifts,
		                    $checkIn,
		                    $date
		                );
		            } else {
		                // fallback → first priority shift
		                $bestShift = $shifts->first();
		            }

		            if ($bestShift) {
		                return (object)[
		                    'shift' => $bestShift, // ✅ SINGLE shift
		                    'resolved_from' => 'department',
		                    'source' => $employee->fh_department,
		                ];
		            }
		        }
		    }
		}

		// 4) Employee default shift
		if ($employee->fh_shift_type) {
			return (object)[
				// 'shift' => $employee->fh_shift_type,
				'shift' => PolicyShiftTiming::find($employee->fh_shift_type->pst_id),
				'resolved_from' => 'employee',
				'source' => $employee, // <-- employee itself
			];
		}

		return null;
	}

	// private function pickBestShiftToUser($shifts, $checkInTime, $date)
	// {
	//     $bestShift = null;
	//     $bestScore = null;

	//     // Handle string or Carbon date
	//     $dateObj = $date instanceof Carbon
	//         ? $date
	//         : Carbon::parse($date);

	//     foreach ($shifts as $shift) {

	//         $startTime = $shift->pst_start_time->format('H:i');
	//         $endTime   = $shift->pst_end_time->format('H:i');

	//         $shiftStart = Carbon::parse($dateObj->format('Y-m-d') . ' ' . $startTime);
	//         $shiftEnd   = Carbon::parse($dateObj->format('Y-m-d') . ' ' . $endTime);

	//         $isNightShift = $endTime < $startTime;

	//         if ($isNightShift) {
	//             $shiftEnd->addDay();

	//             if ($checkInTime->hour < 12) {
	//                 $shiftStart->subDay();
	//                 $shiftEnd->subDay();
	//             }
	//         }

	//         /*
	//         |--------------------------------------------------------------------------
	//         | Auto Assign Shift Logic
	//         |--------------------------------------------------------------------------
	//         */
	//         if (
	//             $shift->pst_auto_assign_shift == 1 &&
	//             $shift->pst_allow_punch_begin_before == 1
	//         ) {
	//             $allowedMinutes = (int) ($shift->pst_mins_punch_begin_before ?? 0);

	//             // Example:
	//             // Shift Start = 14:00
	//             // Allowed Before = 60 mins
	//             // Window = 13:00 to 14:00
	//             $autoAssignStart = $shiftStart->copy()->subMinutes($allowedMinutes);

	//             if ($checkInTime->between($autoAssignStart, $shiftStart, true)) {
	//                 return $shift;
	//             }
	//         }

	//         // Existing fallback logic
	//         $windowStart = $shiftStart->copy()->subHours(2);
	//         $windowEnd   = $shiftEnd->copy()->addHours(2);

	//         if (! $checkInTime->between($windowStart, $windowEnd)) {
	//             continue;
	//         }

	//         $diff = abs($checkInTime->diffInMinutes($shiftStart));

	//         if ($isNightShift) {
	//             $diff -= 240; // Night shift priority
	//         }

	//         if ($bestScore === null || $diff < $bestScore) {
	//             $bestScore = $diff;
	//             $bestShift = $shift;
	//         }
	//     }

	//     return $bestShift;
	// }

	private function pickBestShiftToUser($shifts, $checkInTime, $date)
	{
	    $bestShift = null;
	    $bestScore = null;
	    $bestIsAutoAssign = false;

	    $dateObj = $date instanceof Carbon
	        ? $date
	        : Carbon::parse($date);

	    foreach ($shifts as $shift) {

	        $startTime = $shift->pst_start_time->format('H:i');
	        $endTime   = $shift->pst_end_time->format('H:i');

	        $shiftStart = Carbon::parse($dateObj->format('Y-m-d') . ' ' . $startTime);
	        $shiftEnd   = Carbon::parse($dateObj->format('Y-m-d') . ' ' . $endTime);

	        $isNightShift = $endTime < $startTime;

	        if ($isNightShift) {
	            $shiftEnd->addDay();

	            if ($checkInTime->hour < 12) {
	                $shiftStart->subDay();
	                $shiftEnd->subDay();
	            }
	        }

	        /*
	        |--------------------------------------------------------------------------
	        | Auto Assign Shift Logic
	        |--------------------------------------------------------------------------
	        */
	        $isAutoAssignMatch = false;

	        if (
	            $shift->pst_auto_assign_shift == 1 &&
	            $shift->pst_allow_punch_begin_before == 1
	        ) {
	            $allowedMinutes = (int) ($shift->pst_mins_punch_begin_before ?? 0);

	            // Example:
	            // Shift Start = 14:00
	            // Allowed Before = 60 mins
	            // Window = 13:00 to 14:00
	            $autoAssignStart = $shiftStart->copy()->subMinutes($allowedMinutes);

	            if ($checkInTime->between($autoAssignStart, $shiftStart, true)) {
	                $isAutoAssignMatch = true;
	            }
	        }

	        if ($isAutoAssignMatch) {
	            // Punch time se shift-start tak ki closeness measure karo
	            $diff = abs($checkInTime->diffInMinutes($shiftStart));

	            // Auto-assign matches ko hamesha non-auto-assign se priority milegi,
	            // aur unke beech, jiska start time punch ke sabse paas ho wahi jeetega
	            if (
	                !$bestIsAutoAssign ||
	                $diff < $bestScore
	            ) {
	                $bestScore = $diff;
	                $bestShift = $shift;
	                $bestIsAutoAssign = true;
	            }

	            continue; // ab fallback logic se compare karne ki zaroorat nahi
	        }

	        if ($bestIsAutoAssign) {
	            // Already ek auto-assign match mil chuka hai, fallback shifts usse override nahi karenge
	            continue;
	        }

	        // Existing fallback logic
	        $windowStart = $shiftStart->copy()->subHours(2);
	        $windowEnd   = $shiftEnd->copy()->addHours(2);

	        if (! $checkInTime->between($windowStart, $windowEnd)) {
	            continue;
	        }

	        $diff = abs($checkInTime->diffInMinutes($shiftStart));

	        if ($isNightShift) {
	            $diff -= 240; // Night shift priority
	        }

	        if ($bestScore === null || $diff < $bestScore) {
	            $bestScore = $diff;
	            $bestShift = $shift;
	        }
	    }

	    return $bestShift;
	}
}
