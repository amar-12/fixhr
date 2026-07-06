<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhStatutoryDeduction
 *
 * @property int $std_id
 * @property int $std_b_id
 * @property int|null $std_deduction_type_id
 * @property int|null $std_deduction_cycle_id
 * @property float $std_employee_contri_rate_amount
 * @property float $std_employer_contri_rate_amount
 * @property float|null $std_threshold
 * @property bool|null $std_status
 * @property Carbon|null $std_created_at
 * @property Carbon|null $std_updated_at
 *
 * @property Business $fh_business
 * @property MasterTable|null $fh_master_table
 *
 * @package App\Models
 */
class StatutoryDeduction extends Model
{
	protected $table = 'statutory_deductions';
	protected $primaryKey = 'std_id';
	public $timestamps = false;

	protected $casts = [
		'std_b_id' => 'int',
		'std_deduction_type_id' => 'int',
		'std_deduction_cycle_id' => 'int',
		'std_employee_contri_rate_amount' => 'float',
		'std_employer_contri_rate_amount' => 'float',
		'std_threshold' => 'float',
		'std_status' => 'bool',
		'std_created_at' => 'datetime',
		'std_updated_at' => 'datetime'
	];

	protected $fillable = [
		'std_b_id',
		'std_deduction_type_id',
		'std_deduction_cycle_id',
		'std_employee_contri_rate_amount',
		'std_employer_contri_rate_amount',
		'std_threshold',
		'std_status',
		'std_created_at',
		'std_updated_at'
	];

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'std_b_id');
	}

	public function fh_deduction_type()
	{
		return $this->belongsTo(MasterTable::class, 'std_deduction_type_id');
	}

    public function fh_deduction_cycle()
	{
		return $this->belongsTo(MasterTable::class, 'std_deduction_cycle_id');
	}
}
