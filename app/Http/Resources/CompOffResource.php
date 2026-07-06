<?php

namespace App\Http\Resources;

use App\Models\CompOffBalance;
use App\Models\MasterTable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompOffResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @return array<string, mixed>
	 */
	public function toArray(Request $request): array
	{
		$user = Auth::user();

		$balance = CompOffBalance::where('cb_emp_id', $user->emp_id)
			->where('cb_year', now()->year)
			->where('cb_month', now()->month)
			->orderByDesc('created_at')
			->first();

		return [
			'lvt_id' => $this->m_id,
			'cat_type_id' => $this->m_id ? MasterTableResource::collection(MasterTable::where('m_id', $this->m_id)->get()) : [],
			'leave_balance_details' => $balance ?
				[[
					'category_master_detail' => $this->m_id ? MasterTableResource::collection(MasterTable::where('m_id', $this->m_id)->get()) : [],
					'total_alloted_leave' => (string) ($balance->cb_alloted - $balance->cb_expired) ?? '0',
					'total_taken_leave' => $balance->cb_taken ?? '0',
					'total_balance_remaining_leave' => $balance->cb_balance_remaining ?? '0',
					'total_carried_forward' => '0',
				]]
				: [[
					'category_master_detail' => [],
					'total_alloted_leave' => '0',
					'total_taken_leave' => '0',
					'total_balance_remaining_leave' => '0',
					'total_carried_forward' => '0',
				]],
		];
	}
}
