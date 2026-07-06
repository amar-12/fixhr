<?php

namespace App\Http\Resources\Request;

use App\Http\Resources\MasterTableResource;
use App\Http\Resources\TadaTravelTypeResource;
use App\Http\Resources\TravelPurposeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use DateTime;
use Illuminate\Support\Carbon;

class PlanRequestApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'trp_id'=> $this->trp_id ?? 0,
            'trp_unique_id'=> $this->trp_unique_id ?? '',
            'trp_b_id' => $this->trp_b_id ?? 0,
            'trp_br_id' => $this->trp_br_id ?? 0,
            'trp_emp_id' => $this->trp_emp_id ?? 0,
            'trp_pttt_id' => $this->trp_pttt_id ?? 0,
            'trp_pttt_details' => TadaTravelTypeResource::collection([$this->fh_policy_tada_travel_type])->all(),
            'trp_ptc_id' => $this->trp_ptc_id ?? 0,
            'trp_name' => $this->trp_name ?? '',
            'trp_destination'=> $this->trp_destination ?? '',
            'trp_start_date' => $this->trp_start_date ?  Carbon::parse($this->trp_start_date)->format('d M, Y') : null,
            'trp_end_date' => $this->trp_end_date ?  Carbon::parse($this->trp_end_date)->format('d M, Y') : null,
            'trp_start_time' => $this->trp_start_time ? Carbon::parse($this->trp_start_time)->format('h:i A') : null,
            'trp_end_time' => $this->trp_end_time ? Carbon::parse($this->trp_end_time)->format('h:i A') : null,
            'trp_purpose' => $this->fh_travel_purpose ? TravelPurposeResource::collection([$this->fh_travel_purpose])->all() : [],
            'trp_advance_allowance' => $this->trp_advance_allowance ?? 0,
            'trp_remark' => $this->trp_remarks ?? '',
            'trp_document' => $this->trp_document ?json_decode($this->trp_document, true) : [],
            'trp_request_status' => $this->fh_approval_status ? MasterTableResource::collection([$this->fh_approval_status]) : [] ,
            'trp_call_id' => $this->trp_call_id ?? '',
            'trp_created_at' => $this->created_at,
            'trp_is_expense_added' => $this->trp_is_expense_added ?? 0,
            'trp_is_details_added' => $this->trp_is_details_added ?? 0,
            'trp_details' => PlanRequestDetailResource::collection($this->fh_tada_request_details),
            'trp_total_distance' => number_format((float) $this->fh_tada_request_details->sum('trd_total_distance') + (float) $this->fh_tada_request_details->sum('trd_total_tolerance'),2,'.',''),
            'trp_total_amount'   => number_format((float) $this->fh_tada_request_details->sum('trd_net_amount'), 2, '.', ''),
        ];
    }
}
