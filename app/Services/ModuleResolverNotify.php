<?php

namespace App\Services;

use App\Models\AdvanceLog;
use App\Models\ApprovalModule;
use App\Models\AttendanceException;
use App\Models\AttendanceRecord;
use App\Models\EmployeeApprovalMapping;
use App\Models\GatePass;
use App\Models\LeaveRequest;
use App\Models\LoanRequest;
use App\Models\TadaClaim;
use App\Models\TadaRequestPlan;
use Illuminate\Support\Collection;

/**
 * ModuleResolverNotify
 *
 * Provides a simple map from module id -> model + primary fields used by
 * approval notification logic. Also exposes helpers to fetch pending items.
 */
class ModuleResolverNotify
{
    /**
     * modules configuration
     * fields array format: [next_approver_col, emp_id_col, am_id_col, id_col, stage_col]
     */
    protected array $modules = [
        146 => [
            'model'  => TadaClaim::class,
            'fields' => ['tc_next_approver', 'tc_emp_id', 'tc_am_id', 'tc_trp_id', 'tc_stage_completed'],
            'stage'  => 'tc_stage_completed',
            'b_id' => 'tc_b_id',
        ],
        145 => [
            'model'  => TadaRequestPlan::class,
            'fields' => ['trp_next_approver', 'trp_emp_id', 'trp_am_id', 'trp_id', 'trp_stage_completed'],
            'stage'  => 'trp_stage_completed',
            'b_id' => 'trp_b_id',
        ],
        199 => [
            'model'  => AdvanceLog::class,
            'fields' => ['adl_next_approver', 'adl_approver_id', 'adl_am_id', 'adl_id', 'adl_stage_completed'],
            'stage'  => 'adl_stage_completed',
            'b_id' => 'adl_b_id',
        ],
        229 => [
            'model'  => AttendanceException::class,
            'fields' => ['ae_next_approver', 'ae_emp_id', 'ae_am_id', 'ae_id', 'ae_stage_completed'],
            'stage'  => 'ae_stage_completed',
            'b_id' => 'ae_b_id',
        ],
        339 => [
            'model'  => GatePass::class,
            'fields' => ['gtp_next_approver', 'gtp_emp_id', 'gtp_am_id', 'gtp_id', 'gtp_stage_completed'],
            'stage'  => 'gtp_stage_completed',
            'b_id' => 'gtp_b_id',
        ],
        250 => [
            'model'  => LeaveRequest::class,
            'fields' => ['lvr_next_approver', 'lvr_emp_id', 'lvr_am_id', 'lvr_id', 'lvr_stage_completed'],
            'stage'  => 'lvr_stage_completed',
            'b_id' => 'lvr_b_id',
        ],
        249 => [
            'model'  => AttendanceRecord::class,
            'fields' => ['atd_next_approver', 'atd_emp_id', 'atd_am_id', 'atd_id', 'atd_stage_completed'],
            'stage'  => 'atd_stage_completed',
            'b_id' => 'atd_b_id',
        ],
        442 => [
            'model'  => LoanRequest::class,
            'fields' => ['lnr_next_approver', 'lnr_emp_id', 'lnr_am_id', 'lnr_id', 'lnr_stage_completed'],
            'stage'  => 'lnr_stage_completed',
            'b_id' => 'lnr_b_id',
        ],
    ];

    /**
     * Resolve module config by id.
     *
     * @param int $moduleId
     * @return array|null
     */
    public static function resolve(int $moduleId): ?array
    {
        $resolver = new self();
        return $resolver->modules[$moduleId] ?? null;
    }

    /**
     * Return pending requests for a business (and optional employee).
     * Excludes clearly non-pending rows (next_approver=0 & stage=0) and
     * (next_approver=1 & stage=1) as per previous logic.
     *
     * @param int $moduleId
     * @param int $b_id
     * @param int|null $emp_id
     * @return Collection|null
     */
    public static function getPendingRequests(int $moduleId, int $b_id, ?int $emp_id = null): ?Collection
    {
        $module = self::resolve($moduleId);
        if (is_null($module)) {
            return null;
        }

        $model = $module['model'];
        $stageCol = $module['stage'];
        $bIdCol = $module['b_id'];
        $nextApproverCol = $module['fields'][0];
        $empIdCol = $module['fields'][1];

        $query = $model::where($bIdCol, $b_id)->where($stageCol, '!=', 1);

        if (!is_null($emp_id)) {
            $query->where($empIdCol, $emp_id);
        }

        $query->whereNot(function ($q) use ($stageCol, $nextApproverCol) {
            $q->where(function ($q1) use ($stageCol, $nextApproverCol) {
                $q1->where($nextApproverCol, 0)->where($stageCol, 0);
            })->orWhere(function ($q2) use ($stageCol, $nextApproverCol) {
                $q2->where($nextApproverCol, 1)->where($stageCol, 1);
            });
        });

        return $query->get();
    }
}
