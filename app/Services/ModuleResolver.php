<?php

namespace App\Services;

use App\Models\AdvanceLog;
use App\Models\ApprovalModule;
use App\Models\AttendanceException;
use App\Models\AttendanceRecord;
use App\Models\EmployeeApprovalMapping;
use App\Models\EmployeeExitRequest;
use App\Models\GatePass;
use App\Models\LeaveRequest;
use App\Models\LoanRequest;
use App\Models\OtApprovalStatus;
use App\Models\TadaClaim;
use App\Models\TadaRequestPlan;
use Illuminate\Support\Facades\DB;

class ModuleResolver
{
    protected array $modules = [
        146 => [
            'model'  => TadaClaim::class,
            'fields' => ['tc_next_approver as next_approver', 'tc_emp_id', 'tc_am_id', 'tc_trp_id', 'tc_stage_completed'],
            'stage'  => 'tc_stage_completed',
            'b_id' => 'tc_b_id',
        ],
        145 => [
            'model'  => TadaRequestPlan::class,
            'fields' => ['trp_next_approver as next_approver', 'trp_emp_id', 'trp_am_id', 'trp_id', 'trp_stage_completed'],
            'stage'  => 'trp_stage_completed',
            'b_id' => 'trp_b_id',
        ],
        199 => [
            'model'  => AdvanceLog::class,
            'fields' => ['adl_next_approver as next_approver', 'adl_approver_id', 'adl_am_id', 'adl_id', 'adl_stage_completed'],
            'stage'  => 'adl_stage_completed',
            'b_id' => 'adl_b_id',
        ],
        229 => [
            'model'  => AttendanceException::class,
            'fields' => ['ae_next_approver as next_approver', 'ae_emp_id', 'ae_am_id', 'ae_id', 'ae_stage_completed'],
            'stage'  => 'ae_stage_completed',
            'b_id' => 'ae_b_id',
        ],
        339 => [
            'model'  => GatePass::class,
            'fields' => ['gtp_next_approver as next_approver', 'gtp_emp_id', 'gtp_am_id', 'gtp_id', 'gtp_stage_completed'],
            'stage'  => 'gtp_stage_completed',
            'b_id' => 'gtp_b_id',
        ],
        250 => [
            'model'  => LeaveRequest::class,
            'fields' => ['lvr_next_approver as next_approver', 'lvr_emp_id', 'lvr_am_id', 'lvr_id', 'lvr_stage_completed'],
            'stage'  => 'lvr_stage_completed',
            'b_id' => 'lvr_b_id',
        ],
        249 => [
            'model'  => AttendanceRecord::class,
            'fields' => ['atd_next_approver as next_approver', 'atd_emp_id', 'atd_am_id', 'atd_id', 'atd_stage_completed'],
            'stage'  => 'atd_stage_completed',
            'b_id' => 'atd_b_id',
        ],
        442 => [
            'model'  => LoanRequest::class,
            'fields' => ['lnr_next_approver as next_approver', 'lnr_emp_id', 'lnr_am_id', 'lnr_id', 'lnr_stage_completed'],
            'stage'  => 'lnr_stage_completed',
            'b_id' => 'lnr_b_id',
        ],
        562 => [
            'model'  => OtApprovalStatus::class,
            'fields' => ['ot_next_approver as next_approver', 'ot_emp_id', 'oot_am_id', 'ot_id', 'ot_stage_completed'],
            'stage'  => 'ot_stage_completed',
            'b_id' => 'ot_b_id',
        ],

        // for FNF 

        5888 => [
            'model'  => EmployeeExitRequest::class,
            'fields' => ['er_next_approver', 'er_emp_id', 'er_am_id', 'er_id', 'er_stage_completed'],
            'stage'  => 'er_stage_completed',
            'b_id' => 'er_b_id',
        ],

        5889 => [
            'model'  => EmployeeExitRequest::class,
            'fields' => ['er_next_approver', 'er_emp_id', 'er_am_id', 'er_id', 'er_stage_completed'],
            'stage'  => 'er_stage_completed',
            'b_id' => 'er_b_id',
        ],

        5890 => [
            'model'  => EmployeeExitRequest::class,
            'fields' => ['er_next_approver', 'er_emp_id', 'er_am_id', 'er_id', 'er_stage_completed'],
            'stage'  => 'er_stage_completed',
            'b_id' => 'er_b_id',
        ],

        5891 => [
            'model'  => EmployeeExitRequest::class,
            'fields' => ['er_next_approver', 'er_emp_id', 'er_am_id', 'er_id', 'er_stage_completed'],
            'stage'  => 'er_stage_completed',
            'b_id' => 'er_b_id',
        ],


    ];

    /**
     * Get module config (model + fields + stage column)
     */
    public function resolve(int $moduleId): ?array
    {
        return $this->modules[$moduleId] ?? null;
    }

    /**
     * Get pending requests (stage != 1)
     */
    public function getPendingRequests(int $moduleId, int $b_id)
    {
        $module = $this->resolve($moduleId);

        if (!$module) {
            return null;
        }

        $model  = $module['model'];
        $stage  = $module['stage'];
        $b_id_col = $module['b_id'];

        return $model::where($stage, '!=', 1)->where($b_id_col, $b_id)->get();
    }
}
