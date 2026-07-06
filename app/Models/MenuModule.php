<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class FhMenuModule
 *
 * @property int $mm_id
 * @property int|null $mm_menu_id
 * @property int|null $mm_mdl_id
 *
 * @property Menu|null $fh_menu
 * @property Module|null $fh_module
 *
 * @package App\Models
 */
class MenuModule extends Model
{
	protected $table = 'menu_modules';
	protected $primaryKey = 'mm_id';
	public $timestamps = false;

	protected $casts = [
		'mm_menu_id' => 'int',
		'mm_mdl_id' => 'int'
	];

	protected $fillable = [
		'mm_menu_id',
		'mm_mdl_id'
	];

	public function fh_menu()
	{
		return $this->belongsTo(Menu::class, 'mm_menu_id');
	}

	public function fh_module()
	{
		return $this->belongsTo(Module::class, 'mm_mdl_id');
	}
}
