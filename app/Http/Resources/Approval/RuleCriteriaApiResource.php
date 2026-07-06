<?php

namespace App\Http\Resources\Approval;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RuleCriteriaApiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'rc_id'=> $this-> rc_id,
            'rc_b_id'=> $this-> rc_b_id,
		    'rc_am_id'=> $this-> rc_am_id ?? 0,
		    'rc_approval_rule_id'=> $this-> rc_approval_rule_id ?? 0,
		    'rc_rule_condition_id'=> $this-> rc_rule_condition_id ?? 0,
		    'rc_condition_option_id'=> $this-> rc_condition_option_id ?? 0,
		    'rc_custom_value'=> $this-> rc_custom_value ?? '',
        ];
    }
}
