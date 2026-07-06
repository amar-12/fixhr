<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhModule
 *
 * @property int $mdl_id
 * @property string $mdl_name
 * @property string $mdl_code
 * @property string|null $mdl_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|BusinessModuleAccess[] $fh_business_module_accesses
 * @property Collection|MenuModule[] $fh_menu_modules
 *
 * @package App\Models
 */
class Module extends Model
{
	protected $table = 'modules';
	protected $primaryKey = 'mdl_id';

	protected $fillable = [
		'mdl_name',
		'mdl_code',
		'mdl_description'
	];

	public function fh_business_module_accesses()
	{
		return $this->hasMany(BusinessModuleAccess::class, 'bma_mdl_id');
	}

	public function fh_menu_modules()
	{
		return $this->hasMany(MenuModule::class, 'mm_mdl_id');
	}
}
