<?php

namespace App\Services\DemoSetup\Steps;

use App\Models\Business;
use App\Models\PolicyAttendance;
use App\Models\PolicyShiftTiming;
use App\Services\DemoSetup\Contracts\DemoSetupStep;
use App\Services\DemoSetup\DemoSetupService;

class ShiftPolicySetup implements DemoSetupStep
{
    public function key(): string
    {
        return 'shift_policy';
    }

    public function shouldRun(Business $business): bool
    {
        return !PolicyShiftTiming::where('pst_b_id', $business->b_id)->exists();
    }

    public function run(Business $business, DemoSetupService $service): void
    {
        $attendancePolicy = PolicyAttendance::where('ap_b_id', $business->b_id)->first();

        if (!$attendancePolicy) {
            return;
        }

        $input = $service->getInput('shift_policy') ?? [];

        // Defaults (safe, boring, reliable)
        $typeId        = $input['shift_type_id']        ?? 244; // Fixed
        $name          = $input['name']           ?? 'Default General Shift';
        $code          = $input['code']           ?? 'GS';
        $startTime     = $input['start_time']     ?? '10:00';
        $endTime       = $input['end_time']       ?? '18:30';
        $graceMinutes  = $input['grace']  ?? 10;

        // Break inputs. If only duration is provided, derive start/end centered in the shift.
        $rawBreakDuration = isset($input['break_duration']) ? (int)$input['break_duration'] : null; // minutes

        $allowBreakInput = $input['allow_break'] ?? true;
        $breakStartInput = $input['break_start'] ?? '13:30';
        $breakEndInput   = $input['break_end']   ?? '14:00';
        $isBreakPaidInput = $input['is_break_paid'] ?? true;

        $allowBreak = $allowBreakInput;
        $isBreakPaid = $isBreakPaidInput;
        $breakDuration = $rawBreakDuration;
        $breakStart = $breakStartInput;
        $breakEnd = $breakEndInput;

        if ($rawBreakDuration !== null) {
            $allowBreak = true;
            $isBreakPaid = true;
            $breakDuration = $rawBreakDuration;

            $toMinutes = function (string $t): int {
                [$h, $m] = explode(':', $t);
                return ((int)$h) * 60 + ((int)$m);
            };

            $fromMinutes = function (int $mins): string {
                $mins = $mins % (24 * 60);
                $h = intdiv($mins, 60);
                $m = $mins % 60;
                return sprintf('%02d:%02d', $h, $m);
            };

            $shiftStartMin = $toMinutes($startTime);
            $shiftEndMin = $toMinutes($endTime);
            if ($shiftEndMin <= $shiftStartMin) {
                $shiftEndMin += 24 * 60; // assume next day
            }

            $shiftDuration = $shiftEndMin - $shiftStartMin;

            // Place break centered in the shift
            $mid = $shiftStartMin + intdiv($shiftDuration, 2);
            $half = intdiv($breakDuration, 2);
            $candidateStart = $mid - $half;
            $candidateEnd = $candidateStart + $breakDuration;

            // Ensure break fits inside shift bounds
            if ($candidateStart < $shiftStartMin) {
                $candidateStart = $shiftStartMin;
                $candidateEnd = $candidateStart + $breakDuration;
            }
            if ($candidateEnd > $shiftEndMin) {
                $candidateEnd = $shiftEndMin;
                $candidateStart = max($shiftStartMin, $candidateEnd - $breakDuration);
            }

            $breakStart = $fromMinutes($candidateStart);
            $breakEnd = $fromMinutes($candidateEnd);
        } else {
            // normalize duration when not provided
            $breakDuration = $allowBreak ? 30 : 0;
        }

        PolicyShiftTiming::create([
            'pst_b_id'  => $business->b_id,
            'pst_ap_id' => $attendancePolicy->ap_id,
            'pst_type_id' => $typeId,

            'pst_name' => $name,
            'pst_code' => $code,

            'pst_start_time' => $startTime . ':00',
            'pst_end_time'   => $endTime . ':00',

            'pst_allow_grace_time' => $graceMinutes > 0 ? 1 : 0,
            'pst_grace_time'       => $graceMinutes,

            // Minimum work hour = shift duration (simple default logic)
            'pst_min_work_hour' => '16:00:00',

            // Break logic
            'pst_allow_break1' => $allowBreak ? 1 : 0,
            'pst_break_begin_time1' => $allowBreak ? $breakStart . ':00' : null,
            'pst_break_end_time1'   => $allowBreak ? $breakEnd . ':00' : null,
            'pst_break1_duration'   => $allowBreak ? $breakDuration : 30,
            'pst_is_break_paid'     => $isBreakPaid ? 1 : 0,
        ]);
    }
}
