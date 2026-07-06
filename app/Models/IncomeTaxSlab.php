<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class IncomeTaxSlab
 *
 * @property int $its_id
 * @property int|null $its_b_id
 * @property float $its_income_from
 * @property float|null $its_income_to
 * @property float $its_tax_rate
 *
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class IncomeTaxSlab extends Model
{
	protected $table = 'income_tax_slabs';
	protected $primaryKey = 'its_id';
	public $timestamps = false;

	protected $casts = [
		'its_b_id' => 'int',
		'its_income_from' => 'float',
		'its_income_to' => 'float',
		'its_tax_rate' => 'float',
        'its_fy_id' => 'int',
	];

	protected $fillable = [
		'its_b_id',
        'its_fy_id',
        'its_regime',
		'its_income_from',
		'its_income_to',
		'its_tax_rate',
        'its_is_active'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'its_b_id');
	}

    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class, 'its_fy_id', 'fy_id');

    }
}
