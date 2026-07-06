<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\GatePassConfirmation;
use Illuminate\Support\Facades\DB;

/**
 * Class Gatepass
 *
 * @property int $gtp_id
 * @property int|null $gtp_emp_id
 * @property Carbon $gtp_date
 * @property string $gtp_in_time
 * @property string $gtp_out_time
 * @property string $gtp_reason
 * @property string $gtp_destination
 * @property int $gtp_status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Employee|null $fh_employee
 * @property MasterTable $fh_master_table
 *
 * @package App\Models
 */
class GatePass extends Model
{
    use SoftDeletes;
	protected $table = 'gatepasses';
	protected $primaryKey = 'gtp_id';

	protected $casts = [
		'gtp_emp_id' => 'int',
		'gtp_date' => 'datetime',
		'gtp_status' => 'int'
	];

	protected $fillable = [
        'gtp_b_id',
		'gtp_emp_id',
		'gtp_date',
		'gtp_in_time',
		'gtp_out_time',
		'gtp_reason',
		'gtp_destination',
        'gtp_approved_by',
		'gtp_status',
        'gtp_stage_completed',
        'gtp_module_id',
        'gtp_am_id',
        'gtp_next_approver',
	];

	public function fh_employee()
	{
		return $this->belongsTo(Employee::class, 'gtp_emp_id');
	}

	public function fh_master_table()
	{
		return $this->belongsTo(MasterTable::class, 'gtp_status');
	}

    public function fh_plan_approval_log()
	{
		return $this->hasMany(ApprovalLog::class, 'log_am_id', 'gtp_am_id')->where('log_request_id', $this->gtp_id)->orderBy('updated_at','desc');
	}

    public function fh_approval_log2(){
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'gtp_id')->where('log_module_id', 339);
    }

        public function fh_employees_details()

    {

        return $this->belongsTo(Employee::class, 'gtp_emp_id', 'emp_id')

            ->with(['fh_department', 'fh_designation', 'fh_dealership', 'fh_branch','fh_grade','fh_shift_type','fh_employee_salary',]);

    }

    public function fh_approval_status()
	{
		return $this->belongsTo(MasterTable::class, 'gtp_status')->where('m_group','APPROVAL_STATUS');
	}

    public function fh_process_approvers()
	{
		return $this->hasMany(ProcessApprover::class, 'pa_am_id' , 'gtp_am_id');
	}

    public function filteredProcessApprovers($emp_d_id)
    {
        return $this->hasMany(ProcessApprover::class, 'pa_am_id', 'gtp_am_id')
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
        return $this->belongsTo(EmployeeApprovalMapping::class,'gtp_emp_id'  ,'eam_emp_id');
    }

    public function getApproverIds()
    {
        return array_filter([$this->eam_approver_manager_1, $this->eam_approver_manager_2]);
    }

    public function fh_module(){
        return $this->belongsTo(MasterTable::class, 'gtp_module_id','m_id')->where('m_group', 'MODULE');
    }

    public function fh_gatepass_confirmation()
    {
        return $this->hasOne(GatePassConfirmation::class, 'gcp_gtp_id', 'gtp_id');
    }

    public function scopeApprovableBy($query, $user)
    {
        $userId = (int) $user->emp_id;
        $businessId = (int) $user->emp_b_id;

        $gatePassModuleId = 339;

        $query->where(function($q) use ($userId, $businessId, $gatePassModuleId) {
            // 1) Hierarchy-wise approver: an applicable process_approver row must exist
            $q->orWhereExists(function($sub) use ($userId, $businessId, $gatePassModuleId) {
                $sub->select(DB::raw(1))
                    ->from('approval_modules as am')
                    ->join('process_approver as pa', 'am.am_id', '=', 'pa.pa_am_id')
                    ->where('am.am_module_id', $gatePassModuleId)
                    ->where('pa.pa_b_id', $businessId)
                    ->where('pa.pa_emp_id', $userId)
                    ->whereNull('am.deleted_at')
                    ->limit(1);
            });

            // 2) Employee-wise approver: check mapping & status table
            $q->orWhereExists(function($sub) use ($userId, $businessId, $gatePassModuleId) {
                $sub->select(DB::raw(1))
                    ->from('employee_approval_mappings as eam')
                    ->join('employee_approval_status as eas', 'eam.eam_id', '=', 'eas.eas_eam_id')
                    ->whereColumn('eam.eam_emp_id', 'gatepasses.gtp_emp_id')
                    ->where('eam.eam_b_id', $businessId)
                    ->where('eam.eam_module_id', $gatePassModuleId)
                    ->where('eas.eas_approvel_id', $userId)
                    ->whereNull('eam.deleted_at')
                    ->limit(1);
            });
        });

        return $query;
    }

}
