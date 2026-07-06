<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhApiCredential
 *
 * @property int $apc_id
 * @property string|null $apc_type
 * @property string $apc_key
 * @property string $apc_secret
 * @property string $apc_region
 * @property string $apc_version
 * @property bool|null $apc_is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|ApiCallLog[] $fh_api_call_logs
 *
 * @package App\Models
 */
class ApiCredential extends Model
{
	protected $table = 'api_credentials';
	protected $primaryKey = 'apc_id';

	protected $casts = [
		'apc_is_active' => 'bool'
	];

	protected $hidden = [
		'apc_secret'
	];

	protected $fillable = [
		'apc_type',
		'apc_key',
		'apc_secret',
		'apc_region',
		'apc_version',
		'apc_is_active'
	];

	public function fh_api_call_logs()
	{
		return $this->hasMany(ApiCallLog::class, 'acl_apc_id');
	}
}
