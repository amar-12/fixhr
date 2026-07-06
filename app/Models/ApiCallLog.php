<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhApiCallLog
 *
 * @property int $acl_id
 * @property int|null $acl_b_id
 * @property int|null $acl_apc_id
 * @property int|null $acl_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business|null $fh_business
 * @property ApiCredential|null $fh_api_credential
 *
 * @package App\Models
 */
class ApiCallLog extends Model
{
	protected $table = 'api_call_logs';
	protected $primaryKey = 'acl_id';

	protected $casts = [
		'acl_b_id' => 'int',
		'acl_apc_id' => 'int',
		'acl_count' => 'int'
	];

	protected $fillable = [
		'acl_b_id',
		'acl_apc_id',
		'acl_count'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'acl_b_id');
	}

	public function fh_api_credential()
	{
		return $this->belongsTo(ApiCredential::class, 'acl_apc_id');
	}
}
