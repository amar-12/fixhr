<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class AppMenu
 *
 * @property int $menu_id
 * @property int|null $menu_p_id
 * @property int|null $menu_route_type_id
 * @property string|null $menu_name
 * @property string|null $menu_icon
 * @property bool|null $menu_status
 * @property int|null $menu_sub_status
 * @property string|null $menu_route
 * @property string|null $menu_group
 * @property int|null $menu_sequence
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @package App\Models
 */
class AppMenu extends Model
{
	protected $table = 'app_menus';
	protected $primaryKey = 'menu_id';

	protected $casts = [
		'menu_p_id' => 'int',
		'menu_route_type_id' => 'int',
		'menu_status' => 'bool',
		'menu_sub_status' => 'int',
		'menu_sequence' => 'int'
	];

	protected $fillable = [
		'menu_p_id',
		'menu_route_type_id',
		'menu_name',
		'menu_icon',
		'menu_status',
		'menu_sub_status',
		'menu_route',
		'menu_group',
		'menu_sequence',
		'menu_description'
	];
}
