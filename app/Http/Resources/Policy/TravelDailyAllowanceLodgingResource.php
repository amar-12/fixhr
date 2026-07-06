<?php

namespace App\Http\Resources\Policy;

use App\Http\Resources\MasterTableResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TravelDailyAllowanceLodgingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'category_id' => $this->fh_policy_tada_category->ptc_id ?? 0,
            'travel_category_name' => $this->fh_policy_tada_category->ptc_name ?? '',
            'city_type' => $this->fh_city_type->m_name ?? '',
            'eligibility' => $this->ptdal_da_per_day_elig,
            'same_day_eligibility' => $this->ptdal_da_same_day_ret_elig,
            'same_day_remark' => $this->ptdal_same_day_remark ?? '',
            'eligibility_bill' => $this->ptdal_lodg_sngl_w_bill_elig,
            'eligibility_no_bill' => $this->ptdal_lodg_sngl_wo_bill_elig
        ];
    }
}
