<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhBusinessModuleAccess
 *
 * @property int $bma_id
 * @property int $bma_b_id
 * @property int $bma_mdl_id
 * @property bool|null $bma_access
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Business $fh_business
 * @property Module $fh_module
 *
 * @package App\Models
 */
class BusinessModuleAccess extends Model
{
	protected $table = 'business_module_access';
	protected $primaryKey = 'bma_id';

	protected $casts = [
		'bma_b_id' => 'int',
		'bma_mdl_id' => 'int',
		'bma_access' => 'bool'
	];

	protected $fillable = [
		'bma_b_id',
		'bma_mdl_id',
		'bma_access'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'bma_b_id');
	}

	public function fh_module()
	{
		return $this->belongsTo(Module::class, 'bma_mdl_id');
	}
}
