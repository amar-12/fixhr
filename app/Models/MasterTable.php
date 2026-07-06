<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MasterTable
 *
 * @property int $m_id
 * @property string|null $m_group
 * @property string|null $m_name
 * @property string|null $m_type
 * @property string|null $m_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property Collection|ActionUponRejection[] $_action_upon_rejections
 * @property Collection|ApprovalLog[] $_approval_logs
 * @property Collection|ApprovalModule[] $_approval_modules
 * @property Collection|Business[] $_businesses
 * @property Collection|City[] $_cities
 * @property Collection|EmployeeQualification[] $_employee_qualifications
 * @property Collection|Employee[] $_employees
 * @property Collection|Menu[] $_menus
 * @property Collection|PolicyTadaDailyAllowanceLodging[] $_policy_tada_daily_allowance_lodgings
 * @property Collection|PolicyTadaMiscellaneou[] $_policy_tada_miscellaneous
 * @property Collection|PolicyTadaTravelMode[] $_policy_tada_travel_modes
 * @property Collection|PolicyTadaTravelType[] $_policy_tada_travel_types
 * @property Collection|PolicyTadaTravelVehicle[] $_policy_tada_travel_vehicles
 * @property Collection|ProcessApprover[] $_process_approvers
 * @property Collection|RuleCriterion[] $_rule_criteria
 * @property Collection|TadaClaim[] $_tada_claims
 * @property Collection|TadaExpense[] $_tada_expenses
 * @property Collection|TadaRequestPlan[] $_tada_request_plans
 *
 * @package App\Models
 */
class MasterTable extends Model
{
	protected $table = 'master_table';
	protected $primaryKey = 'm_id';

	protected $fillable = [
		'm_group',
		'm_name',
		'm_type',
		'm_description',
        'm_other',
        'm_alias_name',
	];

	public function fh_action_upon_rejections()
	{
		return $this->hasMany(ActionUponRejection::class, 'aur_status_id');
	}

	public function fh_approval_logs()
	{
		return $this->hasMany(ApprovalLog::class, 'log_status');
	}

	public function fh_approval_modules()
	{
		return $this->hasMany(ApprovalModule::class, 'am_module_id');
	}

	public function fh_businesses()
	{
		return $this->hasMany(Business::class, 'b_type_id');
	}

	public function fh_cities()
	{
		return $this->hasMany(City::class, 'ct_type_id');
	}

	public function fh_employee_qualifications()
	{
		return $this->hasMany(EmployeeQualification::class, 'eq_qualification_id');
	}

	public function fh_employees()
	{
		return $this->hasMany(Employee::class, 'emp_marital_status_id');
	}

	public function fh_menus()
	{
		return $this->hasMany(Menu::class, 'menu_route_type_id');
	}

	public function fh_policy_tada_daily_allowance_lodgings()
	{
		return $this->hasMany(PolicyTadaDailyAllowanceLodging::class, 'ptdal_ct_type_id');
	}

	public function fh_policy_tada_miscellaneous()
	{
		return $this->hasMany(Miscellaneous::class, 'pm_ct_type_id');
	}

	public function fh_policy_tada_travel_modes()
	{
		return $this->hasMany(PolicyTadaTravelMode::class, 'pttm_by_mode_id');
	}

	public function fh_policy_tada_travel_types()
	{
		return $this->hasMany(PolicyTadaTravelType::class, 'pttt_type_id');
	}

	public function fh_policy_tada_travel_vehicles()
	{
		return $this->hasMany(PolicyTadaTravelVehicle::class, 'pttv_claim_type_id');
	}

	public function fh_process_approvers()
	{
		return $this->hasMany(ProcessApprover::class, 'pa_status_id');
	}

	public function fh_rule_criteria()
	{
		return $this->hasMany(RuleCriterion::class, 'rc_condition_option_id');
	}

	public function fh_tada_claims()
	{
		return $this->hasMany(TadaClaim::class, 'tc_status');
	}

	public function fh_tada_expenses()
	{
		return $this->hasMany(TadaExpense::class, 'te_type_id');
	}

	public function fh_tada_request_plans()
	{
		return $this->hasMany(TadaRequestPlan::class, 'trp_module_id');
	}

    public function fh_checkin_method()
	{
		return $this->hasMany(Employee::class, 'emp_checkin_method_id');
	}
    public function fh_leave_cat_type()
	{
		return $this->hasMany(LeaveType::class, 'lvt_cat_type_id');
	}

    public function fh_approval_modules2(){
        return $this->hasMany(EmployeeApprovalMapping::class, 'eam_module_id');
    }
    public function fh_employee_exit_requests()
    {
        return $this->hasMany(EmployeeExitRequest::class, 'er_exit_type_id', 'm_id');
    }
}
