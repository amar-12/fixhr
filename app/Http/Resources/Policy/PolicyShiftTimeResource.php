<?php

namespace App\Http\Resources\Policy;

use App\Http\Resources\MasterTableResource;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Auth;

class PolicyShiftTimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $isEndBy = 0;
        // if ($this->pst_type_id == 245 && $this->pst_end_next_day == 1) {
        //     $currentTime = Carbon::now()->format('H:i:s');
        //     $isEndBy = $currentTime <= $this->pst_end_by ? 1 : 0;
        $status = null;
        if ($this->pst_type_id == 245 && $this->pst_auto_assign_shift == 1) {
            $now = Carbon::now();

            $attendance = AttendanceRecord::where('atd_emp_id', Auth::user()->emp_id)
                // ->where('atd_pst_id', $this->pst_id)
                ->where('atd_pst_id', $this->pst_id)
                ->whereDate('atd_date', Carbon::today()->format('Y-m-d'))
                ->first();

            $hasCheckin  = !empty($attendance?->atd_check_in_time);
            $hasCheckout = !empty($attendance?->atd_check_out_time);

            // Base times (today)
            $start  = Carbon::today()->setTimeFromTimeString($this->pst_start_time);
            $end    = Carbon::today()->setTimeFromTimeString($this->pst_end_time);
            // $expiry = Carbon::today()->setTimeFromTimeString($this->pst_end_by);
            $expiry = $end->copy()->addMinutes((int) $this->pst_mins_punch_end_after);
            $startWindow = $start->copy()->subMinutes($this->pst_mins_punch_begin_before ?? 0);

            if ($this->pst_end_next_day == 1) {
                $end->addDay();
                $expiry->addDay();
                if ($now->lessThan($start)) {
                    $now->addDay();
                }
            }

            if ($hasCheckout) {
                // dd('1', $this->pst_id);
                $status = "COMPLETED";
            } elseif ($hasCheckin && !$hasCheckout) {
                if ($now->lessThanOrEqualTo($expiry)) {
                    // dd('2');
                    $status = "CHECKOUT";
                } else {
                    // dd('3');
                    $status = "COMPLETED";
                }
            // } elseif (!$hasCheckin) {
            //     dd($now, $startWindow, $end);
            //     if ($now->betweenIncluded($startWindow, $end)) {
            //         $status = "CHECKIN";
            //         // dd('4');
            //     } else if($now->betweenIncluded($end, $expiry)) {
            //         // dd('5');
            //         $status = "CHECKOUT";
            //     } else {
            //         // dd('6');
            //         $status = "COMPLETED";
            //     }
            // }

            } elseif (!$hasCheckin) {
                // Check-in window
                if ($now->greaterThanOrEqualTo($startWindow) && $now->lessThanOrEqualTo($end)) {
                    $status = "CHECKIN";
                }

                // Checkout window (agar check-in nahi hua aur end cross ho gaya)
                elseif ($now->greaterThanOrEqualTo($end) && $now->lessThanOrEqualTo($expiry)) {
                    $status = "CHECKOUT";
                }

                // Shift expire
                else {
                    $status = "COMPLETED";
                }
            }

        }
        return [
            'policy_id' => $this->pst_id,
            'policy_name' => $this->pst_name,
            'policy_shift_type' => $this->pst_type_id,
            'policy_auto_shift' => $this->pst_auto_assign_shift,
            'policy_shift_type_id' => $this->pst_type_id ? MasterTableResource::collection($this->fh_master_table()->get()) : [],
            'policy_shift_start_time' => $this->pst_start_time ? Carbon::parse($this->pst_start_time)->format('H:i') : '',
            'policy_shift_end_time' => $this->pst_end_time? Carbon::parse($this->pst_end_time)->format('H:i') : '',
            'policy_mins_punch_begin_before' => $this->pst_mins_punch_begin_before,
            'policy_mins_punch_end_after' => $this->pst_mins_punch_end_after,
            'policy_allow_grace_time' => $this->pst_grace_time,
            'is_end_by' => $this->pst_end_next_day,
            'end_by' => $this->pst_end_by ? Carbon::parse($this->pst_end_by)->format('H:i') : '',
            'button_status' => $status ?? null,
        ];
    }
}
