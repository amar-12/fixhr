<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeExitRequest extends Model
{
    use HasFactory;

    // Table name
    protected $table = 'employee_exit_requests';

    // Primary key
    protected $primaryKey = 'er_id';

    // Mass-assignable fields
    protected $fillable = [
        'er_emp_id',
        'er_b_id',
        'er_exit_type_id',
        'er_reason',
        'er_resignation_date',
        'er_notice_period_days',
        'er_last_working_day',
        'er_manager_status',
        'er_manager_remark',
        'er_manager_action_at',
        'er_hr_status',
        'er_hr_remark',
        'er_hr_action_at',
        'er_finance_remark',
        'er_finance_action_at',
        'er_overall_status',
        'er_remark',
        'er_documentation_notes',
        'relieving_letter_path',
        'experience_letter_path',
        'noc_form_path',
        'settlement_docs_paths',
        'er_created_by',
        'er_updated_by',

        'er_am_id',
        'er_module_id',
        'er_status',
        'er_next_approver',
        'er_stage_completed',
        'er_module_stage',
        'er_request_status',
        'er_ref_no',
        'er_revert_remark'
    ];

    // Casts
    protected $casts = [
        'er_resignation_date' => 'date',
        'er_last_working_day' => 'date',
        'er_manager_action_at' => 'datetime',
        'er_hr_action_at' => 'datetime',
        'er_finance_action_at' => 'datetime',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'er_emp_id', 'emp_id');
    }

    public function fh_employee()
    {
        return $this->belongsTo(Employee::class, 'er_emp_id', 'emp_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'er_b_id', 'b_id');
    }

    public function exitType()
    {
        return $this->belongsTo(MasterTable::class, 'er_exit_type_id', 'm_id');
    }

    // Document URL helpers
    public function getRelievingLetterUrlAttribute()
    {
        return $this->relieving_letter_path ? asset('storage/' . $this->relieving_letter_path) : null;
    }

    public function getExperienceLetterUrlAttribute()
    {
        return $this->experience_letter_path ? asset('storage/' . $this->experience_letter_path) : null;
    }

    public function getNocFormUrlAttribute()
    {
        return $this->noc_form_path ? asset('storage/' . $this->noc_form_path) : null;
    }

    public function getSettlementDocsUrlsAttribute()
    {
        if (!$this->settlement_docs_paths) return [];
        $paths = json_decode($this->settlement_docs_paths, true);
        return array_map(fn($p) => asset('storage/' . $p), $paths);
    }

    // Workflow status helpers
    public function isManagerApproved()
    {
        return $this->er_overall_status === 'MANAGER_APPROVED';
    }

    public function isHrApproved()
    {
        return $this->er_overall_status === 'HR_APPROVED';
    }

    public function isFinanceCleared()
    {
        return $this->er_overall_status === 'CLEARANCE_IN_PROGRESS';
    }

    public function isRelieved()
    {
        return $this->er_overall_status === 'RELIEVED';
    }

    public function isDocumentsUploaded()
    {
        return $this->er_overall_status === 'DOCUMENTS_AND_RELIEVING';
    }

    public function fh_approval_log()
    {
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'er_id');
    }

    // Employee wise approver flow
    public function fh_approval_log1()
    {
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'er_id')->where('log_module_id', 603);
    }

    public function fh_approval_log2()
    {
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'er_id')->where('log_module_id', 604);
    }

    public function fh_approval_log3()
    {
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'er_id')->where('log_module_id', 605);
    }

    public function fh_approval_log4()
    {
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'er_id')->where('log_module_id', 606);
    }

    // hierarchy wise all module
    public function fh_approval_log_all_module_emp()
    {
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'er_id')->where('log_module_id', $this->er_module_id)->orderBy('updated_at', 'desc');
    }

    // hierarchy wise all module
    public function fh_approval_log_all_module()
    {
        return $this->hasMany(ApprovalLog::class, 'log_request_id', 'er_id')->where('log_am_id', $this->er_am_id)->where('log_module_id', $this->er_module_id)->orderBy('updated_at', 'desc');
    }

    public function fh_process_approvers()
    {
        return $this->hasMany(ProcessApprover::class, 'pa_am_id', 'er_am_id');
    }
}
