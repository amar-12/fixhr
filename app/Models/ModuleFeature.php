<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhModuleFeature
 *
 * @property int $mdf_id
 * @property int|null $mdf_mdl_id
 * @property string|null $mdf_name
 * @property string|null $mdf_code
 * @property string|null $mdf_description
 * @property float|null $mdf_price
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Module|null $fh_module
 * @property Collection|SubscriptionFeature[] $fh_subscription_features
 *
 * @package App\Models
 */
class ModuleFeature extends Model
{
	protected $table = 'module_features';
	protected $primaryKey = 'mdf_id';
	public $incrementing = false;

	protected $casts = [
		'mdf_id' => 'int',
		'mdf_mdl_id' => 'int',
		'mdf_price' => 'float'
	];

	protected $fillable = [
		'mdf_mdl_id',
		'mdf_name',
		'mdf_code',
		'mdf_description',
		'mdf_price'
	];

	public function fh_module()
	{
		return $this->belongsTo(Module::class, 'mdf_mdl_id');
	}

	public function fh_subscription_features()
	{
		return $this->hasMany(SubscriptionFeature::class, 'sbf_mdf_id');
	}
}
