<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FhLoanRequest
 *
 * @property int $lnr_id
 * @property float|null $lnr_requested_amount
 * @property float|null $lnr_received_amount
 * @property string|null $lnr_description
 * @property int|null $lnr_module_id
 * @property int|null $lnr_am_id
 * @property int|null $lnr_request_status
 * @property int|null $lnr_stage_completed
 * @property int|null $lnr_next_approver
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property MasterTable|null $fh_master_table
 * @property ApprovalModule|null $fh_approval_module
 *
 * @package App\Models
 */
class LoanRequest extends Model
{
	protected $table = 'loan_requests';
	protected $primaryKey = 'lnr_id';

	protected $casts = [
		'lnr_b_id' => 'int',
		'lnr_emp_id' => 'int',
		'lnr_requested_amount' => 'decimal:2',
		'lnr_description' => 'string',
		'lnr_module_id' => 'int',
		'lnr_am_id' => 'int',
		'lnr_installment_amount' => 'decimal:2',
		'lnr_installments' => 'int',
		'lnr_start_date' => 'date:Y-m-d',
		'lnr_request_status' => 'int',
		'lnr_stage_completed' => 'int',
		'lnr_next_approver' => 'int'
	];



	protected $fillable = [
		'lnr_b_id',
		'lnr_unique_id',
		'lnr_emp_id',
        'lnr_advance_type',
        'lnr_request_subject',
		'lnr_requested_amount',
		'lnr_description',
		'lnr_module_id',
		'lnr_am_id',
		'lnr_rate',
		'lnr_installment_amount',
		'lnr_installments',
		'lnr_start_date',
		'lnr_request_status',
		'lnr_stage_completed',
		'lnr_next_approver'
	];

	public function fh_employee()
    {
        return $this->belongsTo(Employee::class, 'lnr_emp_id');
    }

	public function fh_master_table()
	{
		return $this->belongsTo(MasterTable::class, 'lnr_request_status');
	}


	public function fh_plan_approval_log()
	{
		return $this->hasMany(ApprovalLog::class, 'log_am_id', 'lnr_am_id')->where('log_request_id', $this->lnr_id)->orderBy('updated_at','desc');
	}

	public function fh_approval_log2(){
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'lnr_id')->where('log_module_id', 442);
    }


	public function fh_approval_status()
	{
		return $this->belongsTo(MasterTable::class, 'lnr_request_status')->where('m_group','APPROVAL_STATUS');
	}

	public function fh_approval_module()
	{
		return $this->belongsTo(ApprovalModule::class, 'lnr_am_id');
	}


	public function fh_process_approvers()
	{
		return $this->hasMany(ProcessApprover::class, 'pa_am_id' , 'lnr_am_id');
	}

	public function filteredProcessApprovers($emp_d_id)
    {
        return $this->hasMany(ProcessApprover::class, 'pa_am_id', 'lnr_am_id')
            ->where(function ($query) use ($emp_d_id) {
                $query->where('pa_flow', 'business')
                    ->orWhere(function ($query) use ($emp_d_id) {
                        $query->where('pa_flow', 'department')
                            ->where('pa_d_id', $emp_d_id);
                    });
            });
    }


    public function canApprove()
    {
        return $this->belongsTo(EmployeeApprovalMapping::class,'lnr_emp_id'  ,'eam_emp_id');
    }

    public function getApproverIds()
    {
        return array_filter([$this->eam_approver_manager_1, $this->eam_approver_manager_2]);
    }

	public function fh_business()
	{
		return $this->belongsTo(Business::class, 'lnr_b_id');
	}


	public function fh_module(){
        return $this->belongsTo(MasterTable::class, 'lnr_module_id','m_id')->where('m_group', 'MODULE');
    }

	public function fh_employee_salary()
	{
		return $this->hasOne(SalaryEmployeeSalary::class, 'es_emp_id', 'lnr_emp_id');
	}

	public function fh_payroll_periods()
	{
		return $this->hasMany(PayrollPeriod::class, 'pp_b_id', 'lnr_b_id');
	}


	public function fh_payroll_loan_installments()
	{
		return $this->hasMany(PayrollLoanInstallment::class, 'pli_loan_id', 'lnr_id');
	}


}
