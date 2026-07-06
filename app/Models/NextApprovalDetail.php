<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class NextApprovalDetail
 * 
 * @property int $nxt_id
 * @property int|null $nxt_tc_id
 * @property int|null $nxt_approver_sequence
 * @property int|null $nxt_is_last
 * @property int|null $nxt_am_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * 
 * @property TadaClaim|null $fh_tada_claim
 *
 * @package App\Models
 */
class NextApprovalDetail extends Model
{
	protected $table = 'next_approval_details';
	protected $primaryKey = 'nxt_id';

	protected $casts = [
		'nxt_tc_id' => 'int',
		'nxt_approver_sequence' => 'int',
		'nxt_is_last' => 'int',
		'nxt_am_id' => 'int'
	];

	protected $fillable = [
		'nxt_tc_id',
		'nxt_approver_sequence',
		'nxt_is_last',
		'nxt_am_id',
		'nxt_approval_type'
	];

	public function fh_tada_claim()
	{
		return $this->belongsTo(TadaClaim::class, 'nxt_tc_id');
	}
}
