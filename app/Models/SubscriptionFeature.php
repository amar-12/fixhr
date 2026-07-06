<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhSubscriptionFeature
 *
 * @property int $sbf_id
 * @property int $sbf_sbc_id
 * @property int $sbf_mdl_id
 * @property int $sbf_mdf_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Subscription $fh_subscription
 * @property Module $fh_module
 * @property ModuleFeature $fh_module_feature
 *
 * @package App\Models
 */
class SubscriptionFeature extends Model
{
	protected $table = 'subscription_features';
	protected $primaryKey = 'sbf_id';

	protected $casts = [
		'sbf_sbc_id' => 'int',
		'sbf_mdl_id' => 'int',
		'sbf_mdf_id' => 'int'
	];

	protected $fillable = [
		'sbf_sbc_id',
		'sbf_mdl_id',
		'sbf_mdf_id'
	];

	public function fh_subscription()
	{
		return $this->belongsTo(Subscription::class, 'sbf_sbc_id');
	}

	public function fh_module()
	{
		return $this->belongsTo(Module::class, 'sbf_mdl_id');
	}

	public function fh_module_feature()
	{
		return $this->belongsTo(ModuleFeature::class, 'sbf_mdf_id');
	}
}
