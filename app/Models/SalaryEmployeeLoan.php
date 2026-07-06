<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * class SalaryEmployeeLoan
 * 
 * @property int $el_id
 * @property int|null $el_b_id
 * @property int $el_emp_id
 * @property float|null $el_loan_amount
 * @property float|null $el_loan_balance
 * @property float|null $el_interest_rate
 * @property float|null $el_monthly_installment
 * @property string|null $el_loan_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * 
 * @property Employee $fh_employee
 * @property Business|null $fh_business
 *
 * @package App\Models
 */
class SalaryEmployeeLoan extends Model
{
	protected $table = 'employee_loans';
	protected $primaryKey = 'el_id';

	protected $casts = [
		'el_b_id' => 'int',
		'el_emp_id' => 'int',
		'el_loan_amount' => 'float',
		'el_loan_balance' => 'float',
		'el_interest_rate' => 'float',
		'el_monthly_installment' => 'float'
	];

	protected $fillable = [
		'el_b_id',
		'el_emp_id',
		'el_loan_amount',
		'el_loan_balance',
		'el_interest_rate',
		'el_monthly_installment',
		'el_loan_status'
	];

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'el_emp_id');
	}

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'el_b_id');
	}
}
