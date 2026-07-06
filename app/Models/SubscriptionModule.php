<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhSubscriptionModule
 *
 * @property int $sbm_id
 * @property int $sbm_sbc_id
 * @property int $sbm_mdl_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Subscription $fh_subscription
 * @property Module $fh_module
 *
 * @package App\Models
 */
class SubscriptionModule extends Model
{
	protected $table = 'fh_subscription_modules';
	protected $primaryKey = 'sbm_id';

	protected $casts = [
		'sbm_sbc_id' => 'int',
		'sbm_mdl_id' => 'int'
	];

	protected $fillable = [
		'sbm_sbc_id',
		'sbm_mdl_id'
	];

	public function fh_subscription()
	{
		return $this->belongsTo(Subscription::class, 'sbm_sbc_id');
	}

	public function fh_module()
	{
		return $this->belongsTo(Module::class, 'sbm_mdl_id');
	}
}
