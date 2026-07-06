<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhSchedulerProcessTrack
 *
 * @property int $spt_id
 * @property int $spt_b_id
 * @property string $spt_process_type
 * @property int $spt_total_items
 * @property int|null $spt_processed_count
 * @property string|null $spt_status
 * @property Carbon $spt_process_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Employee $fh_employee
 *
 * @package App\Models
 */
class SchedulerProcessTrack extends Model
{
	protected $table = 'scheduler_process_tracks';
	protected $primaryKey = 'spt_id';

	protected $casts = [
		'spt_b_id' => 'int',
		'spt_total_items' => 'int',
		'spt_processed_count' => 'int',
		'spt_process_date' => 'datetime'
	];

	protected $fillable = [
		'spt_b_id',
		'spt_process_type',
		'spt_total_items',
		'spt_processed_count',
		'spt_status',
		'spt_process_date'
	];

	public function fh_business()
	{
		return $this->belongsTo(Employee::class, 'spt_b_id', 'b_id');
	}
}
