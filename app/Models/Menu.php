<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * class Menu
 *
 * @property int $menu_id
 * @property int|null $menu_p_id
 * @property string|null $menu_name
 * @property string|null $menu_icon
 * @property bool|null $menu_status
 * @property string|null $menu_sub_status
 * @property string|null $menu_route
 * @property bool|null $menu_route_id_status
 * @property string|null $menu_type
 * @property int|null $menu_sequence
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Menu|null $fh_menu
 * @property Collection|FhMenu[] $fh_menus
 *
 * @package App\Models
 */
class Menu extends Model
{
	protected $table = 'menus';
	protected $primaryKey = 'menu_id';

	protected $casts = [
		'menu_p_id' => 'int',
		'menu_route_type_id' => 'int',
		'menu_status' => 'bool',
		'menu_sequence' => 'int',
	];

	protected $fillable = [
		'menu_p_id',
		'menu_name',
		'menu_icon',
		'menu_status',
		'menu_sub_status',
		'menu_route',
		'menu_route_type_id',
		'menu_group',
		'menu_sequence',
        'menu_mdl_ids',
        'menu_is_hidden',
	];

	public function fh_menu()
	{
		return $this->belongsTo(Menu::class, 'menu_p_id');
	}

	public function fh_menu_route_type()
	{
		return $this->belongsTo(MasterTable::class, 'menu_route_type_id')->where('m_group','MENU_ROUTE_TYPE');
	}

	public function fh_menus()
	{
		return $this->hasMany(Menu::class, 'menu_p_id');
	}

    public function fh_modules()
    {
        return $this->belongsToMany(Module::class, 'menu_modules', 'mm_menu_id', 'mm_mdl_id');
    }
}
