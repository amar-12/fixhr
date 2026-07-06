<?php

namespace App\Services\DemoSetup\Steps;

use App\Models\Business;
use App\Models\MasterTable;
use App\Models\ApprovalModule;
use App\Models\ProcessApprover;
use App\Models\ActionUponRejection;
use App\Models\RuleCriterion;
use App\Services\DemoSetup\Contracts\DemoSetupStep;
use App\Services\DemoSetup\DemoSetupService;

class ApprovalFlowSetup implements DemoSetupStep
{
    public function key(): string
    {
        return 'approval_flow';
    }

    public function shouldRun(Business $business): bool
    {
        return !ApprovalModule::where('am_b_id', $business->b_id)->exists();
    }

    public function run(Business $business, DemoSetupService $service): void
    {
        $superAdmin = $business->fh_employee; // creator / owner
        if (!$superAdmin) {
            return;
        }

        $modules = MasterTable::where('m_group', 'MODULE')
            ->where('m_status', 1)
            ->get();

        foreach ($modules as $module) {

            // 🔒 Skip if already created
            if (
                ApprovalModule::where('am_b_id', $business->b_id)
                    ->where('am_module_id', $module->m_id)
                    ->exists()
            ) {
                continue;
            }

            /**
             * 1️⃣ Approval Module
             */
            $approvalModule = ApprovalModule::create([
                'am_b_id'       => $business->b_id,
                'am_module_id'  => $module->m_id,
                'am_name'       => $module->m_name . ' Approval',
                'am_description'=> $module->m_name . ' Approval',
                'am_exe_on'     => json_encode([166, 167]),
                'am_status'     => 1,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            /**
             * 2️⃣ Process Approver (Super Admin)
             */
            ProcessApprover::create([
                'pa_b_id'     => $business->b_id,
                'pa_am_id'    => $approvalModule->am_id,
                'pa_flow'     => 'business',
                'pa_d_id'     => null,
                'pa_type'     => 'single',
                'pa_sequence' => 1,
                'pa_role_id'  => 1, // superadmin
                'pa_emp_id'   => $superAdmin->emp_id,
                'pa_last'     => 1,
                'pa_status_id'=> 157,
                'pa_message'  => 'Super Admin - Approved',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            /**
             * 3️⃣ Rule Criteria (same for all)
             */
            RuleCriterion::create([
                'rc_b_id'                 => $business->b_id,
                'rc_am_id'                => $approvalModule->am_id,
                'rc_approval_rule_id'     => 131,
                'rc_rule_condition_id'    => 137,
                'rc_condition_option_id'  => 140,
                'rc_custom_value'         => null,
                'created_at'              => now(),
                'updated_at'              => now(),
            ]);

            /**
             * 4️⃣ Action Upon Rejection
             */
            ActionUponRejection::create([
                'aur_b_id'     => $business->b_id,
                'aur_emp_id'   => $superAdmin->emp_id,
                'aur_am_id'    => $approvalModule->am_id,
                'aur_group_ids'=> json_encode([152, 153]),
                'aur_status_id'=> 170,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
