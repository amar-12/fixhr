<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TadaExpenseSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'tes_id' => $this->tes_id,
            'tes_expense_type_id' => $this->tes_expense_type_id ?? 0,
            'tes_code' => $this->tes_code ?? 0,
            'tes_head' => $this->tes_head ?? '',
            'tes_is_fixed' => $this->tes_is_fixed ? true : false,
            'tes_fixed_amount' => $this->tes_fixed_amount ?? null,
        ];
    }
}
