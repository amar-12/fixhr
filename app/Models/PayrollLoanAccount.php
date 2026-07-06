<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PayrollLoanAccount
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property bool $is_active
 * @property string $type
 * @property string $title
 * @property float $loan_amount
 * @property Carbon $provided_date
 * @property string|null $description
 * @property bool $is_fixed
 * @property float $rate
 * @property float|null $installment_amount
 * @property int $installments
 * @property Carbon $installment_start_date
 * @property string $apply_on
 * @property bool $settled
 * @property Carbon|null $settled_date
 * @property int|null $allowance_id_id
 * @property int|null $asset_id_id
 * @property int|null $created_by_id
 * @property int $employee_id_id
 * @property int|null $modified_by_id
 *
 * @package App\Models
 */
class PayrollLoanAccount extends Model
{
	protected $table = 'payroll_loan_account';
	public $timestamps = false;

	protected $casts = [
		'is_active' => 'bool',
		'loan_amount' => 'float',
		'provided_date' => 'datetime',
		'is_fixed' => 'bool',
		'rate' => 'float',
		'installment_amount' => 'float',
		'installments' => 'int',
		'installment_start_date' => 'datetime',
		'settled' => 'bool',
		'settled_date' => 'datetime',
		'allowance_id_id' => 'int',
		'asset_id_id' => 'int',
		'created_by_id' => 'int',
		'pla_emp_id' => 'int',
		'modified_by_id' => 'int'
	];

	protected $fillable = [
        'pla_emp_id',
        'pla_b_id',
		'is_active',
		'type',
		'title',
		'loan_amount',
		'provided_date',
		'description',
		'is_fixed',
		'rate',
		'installment_amount',
		'installments',
		'installment_start_date',
		'apply_on',
		'settled',
		'settled_date',
		'allowance_id_id',
		'asset_id_id',
		'created_by_id',
		'modified_by_id'
	];

    public function fh_business()
	{
		return $this->belongsTo(Business::class, 'pla_b_id');
	}

    public function fh_employee()
    {
        return $this->belongsTo(Employee::class, 'pla_emp_id', 'emp_id');
    }

    public function fh_master_type()
	{
		return $this->belongsTo(MasterTable::class, 'type');
	}

	public function fh_employee_salary()
	{
		return $this->hasOne(SalaryEmployeeSalary::class, 'es_emp_id', 'pla_emp_id');
	}


}
