<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhSalaryAllowance
 *
 * @property int $sa_id
 * @property int|null $sa_b_id
 * @property string $sa_title
 * @property string|null $sa_description
 * @property int|null $sa_calculation_type
 * @property float|null $sa_threshold_value
 * @property bool|null $sa_consider_for_pf
 * @property int|null $sa_consider_for_pf_condition
 * @property bool|null $sa_consider_for_esic
 * @property bool|null $sa_calculate_on_prorata_basis
 * @property bool|null $sa_is_taxable
 * @property string|null $sa_name_in_payslip
 * @property bool|null $sa_show_in_payslip
 * @property bool|null $sa_is_active
 * @property Carbon $sa_created_at
 * @property Carbon $sa_updated_at
 *
 * @property MasterTable|null $fh_master_table
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class SalaryAllowance extends Model
{
	protected $table = 'salary_allowances';
	protected $primaryKey = 'sa_id';
	public $timestamps = false;

	protected $casts = [
		'sa_b_id' => 'int',
		'sa_calculation_type' => 'int',
		'sa_threshold_value' => 'float',
		'sa_consider_for_pf' => 'bool',
		'sa_consider_for_pf_condition' => 'int',
		'sa_consider_for_esic' => 'bool',
		'sa_calculate_on_prorata_basis' => 'bool',
		'sa_is_taxable' => 'bool',
		'sa_show_in_payslip' => 'bool',
		'sa_is_active' => 'bool',
        'sa_payroll_heading_id' => 'int',
		'sa_created_at' => 'datetime',
		'sa_updated_at' => 'datetime',
	];

	protected $fillable = [
		'sa_b_id',
		'sa_title',
		'sa_description',
		'sa_calculation_type',
		'sa_threshold_value',
		'sa_earning_type_id',
        'sa_payroll_heading_id',
		'sa_consider_for_pf',
		'sa_consider_for_pf_condition',
		'sa_consider_for_esic',
		'sa_calculate_on_prorata_basis',
		'sa_is_taxable',
		'sa_name_in_payslip',
		'sa_show_in_payslip',
		'sa_is_active',
        'sa_sequence_valu',
		'sa_created_at',
		'sa_updated_at'
	];

	public function fh_pf_condition()
	{
		return $this->belongsTo(MasterTable::class, 'sa_consider_for_pf_condition')->where('m_group','PF_CONDITION');;
	}

    public function fh_allowance_calculation_type()
	{
		return $this->belongsTo(MasterTable::class, 'sa_calculation_type')->where('m_group','ALLOWANCE_CAL_TYPE');;
	}

    public function fh_earning_type(){
        return $this->belongsTo(MasterTable::class, 'sa_earning_type_id')->where('m_group','PAYROLL_EARNING');
    }

    public function fh_payroll_heading(){
        return $this->belongsTo(MasterTable::class, 'sa_payroll_heading_id')->where('m_group','PAYROLL_HEADINGS');
    }

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'sa_b_id');
	}
}
