<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProfessionalTaxSlab
 *
 * @property int $pts_id
 * @property int|null $pts_b_id
 * @property string|null $pts_state_id
 * @property float|null $pts_income_from
 * @property float|null $pts_income_to
 * @property float|null $pts_tax_amount
 *
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class ProfessionalTaxSlab extends Model
{
	protected $table = 'professional_tax_slabs';
	protected $primaryKey = 'pts_id';
	public $timestamps = false;

	protected $casts = [
		'pts_b_id' => 'int',
		'pts_br_id' => 'int',
		'pts_income_from' => 'float',
		'pts_income_to' => 'float',
		'pts_tax_amount' => 'float'
	];

	protected $fillable = [
		'pts_b_id',
		'pts_br_id',
		'pts_income_from',
		'pts_income_to',
		'pts_tax_amount'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pts_b_id');
	}

	public function fh_branch()
	{
		return $this->belongsTo(Branch::class, 'pts_br_id');
	}
}
