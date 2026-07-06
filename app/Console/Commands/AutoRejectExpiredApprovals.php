<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\ApprovalExpiryRejectDay;
use App\Models\ApprovalModule;
use App\Models\Employee;
use App\Models\Business;
use App\Models\TadaRequestPlan;
use App\Models\TadaClaim;
use App\Models\AdvanceLog;
use App\Models\LeaveRequest;
use App\Models\AttendanceException;
use App\Models\GatePass;
use App\Models\LoanRequest;
use App\Models\OtApprovalStatus;
use ChandraHemant\HtkcUtils\FirebaseNotification;
use App\Helpers\NotificationHelper;

class AutoRejectExpiredApprovals extends Command
{
    protected $signature = 'approvals:auto-reject';

    protected $description = 'Auto reject expired approvals and send notifications';

    public function handle()
    {
        try {

            $now = Carbon::now();

            // $businesses = Business::where('b_status', 1)->get();
            $businesses = Business::where('b_status', 1)->where('b_id', 66)->get();

            foreach ($businesses as $business) {

                $businessId = $business->b_id;

                // =====================================================
                // SYSTEM EMPLOYEE
                // =====================================================

                $systemEmp = Employee::where('emp_b_id', $businessId)
                    ->where('emp_role_id', 1)
                    ->first();

                if (!$systemEmp) {
                    continue;
                }

                // =====================================================
                // EMPLOYEE CUSTOM RULES
                // =====================================================

                $empRules = ApprovalExpiryRejectDay::where('aer_b_id', $businessId)
                    ->get();

                foreach ($empRules as $rule) {

                    if (empty($rule->aer_day)) {
                        continue;
                    }

                    $cutoffDate = $now->copy()->subDays((int)$rule->aer_day);

                    $this->processModule(
                        $businessId,
                        $rule->aer_m_id,
                        $cutoffDate,
                        $systemEmp->emp_id
                    );
                }

                // =====================================================
                // MODULE DEFAULT RULES
                // =====================================================

                $modules = ApprovalModule::where('am_b_id', $businessId)
                    ->where('am_status', 1)
                    ->get();

                foreach ($modules as $module) {

                    // Skip if custom rule already exists
                    $exists = ApprovalExpiryRejectDay::where('aer_b_id', $businessId)
                        ->where('aer_m_id', $module->am_module_id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    if (empty($module->am_exp_rej_day)) {
                        continue;
                    }

                    $cutoffDate = $now->copy()->subDays((int)$module->am_exp_rej_day);

                    $this->processModule(
                        $businessId,
                        $module->am_module_id,
                        $cutoffDate,
                        $systemEmp->emp_id
                    );
                }
            }

            \Log::info("Auto reject cron completed at " . now());

        } catch (\Throwable $th) {

            \Log::error("Auto reject cron failed : " . $th->getMessage());
        }
    }

    private function processModule($businessId, $moduleId, $cutoffDate, $systemEmpId)
    {
        // =====================================================
        // TRAVEL REQUEST
        // =====================================================

        if ($moduleId == 145) {

            $records = TadaRequestPlan::where('trp_b_id', $businessId)
                ->where('created_at', '<=', $cutoffDate)
                ->whereNotIn('trp_request_status', [157, 170])
                ->where('trp_stage_completed', '!=', 1)
                ->get();

            foreach ($records as $record) {

                $record->update([
                    'trp_request_status' => 170,
                    'trp_stage_completed' => 1,
                ]);

                $this->sendNotification(
                    $record->trp_emp_id ?? null,
                    $record->trp_approved_by ?? null,
                    $systemEmpId,
                    "Travel Request Auto Rejected",
                    "Your travel request was automatically rejected due to approval timeout.",
                    145,
                    $record->trp_id
                );
            }

            \Log::info("Travel Auto Rejected Count : " . count($records));
        }

        // =====================================================
        // CLAIM
        // =====================================================

        elseif ($moduleId == 146) {

            $records = TadaClaim::where('tc_b_id', $businessId)
                ->where('created_at', '<=', $cutoffDate)
                ->whereNotIn('tc_status', [157, 170])
                ->where('tc_stage_completed', '!=', 1)
                ->get();

            foreach ($records as $record) {

                $record->update([
                    'tc_status' => 170,
                    'tc_stage_completed' => 1,
                ]);

                $this->sendNotification(
                    $record->tc_emp_id ?? null,
                    $record->tc_approved_by ?? null,
                    $systemEmpId,
                    "Claim Request Auto Rejected",
                    "Your claim request was automatically rejected due to approval timeout.",
                    146,
                    $record->tc_id
                );
            }

            \Log::info("Claim Auto Rejected Count : " . count($records));
        }

        // =====================================================
        // ADVANCE
        // =====================================================

        elseif ($moduleId == 199) {

            $records = AdvanceLog::where('adl_b_id', $businessId)
                ->where('created_at', '<=', $cutoffDate)
                ->whereNotIn('adl_request_status', [157, 170])
                ->where('adl_stage_completed', '!=', 1)
                ->get();

            foreach ($records as $record) {

                $record->update([
                    'adl_request_status' => 170,
                    'adl_stage_completed' => 1,
                ]);

                $this->sendNotification(
                    $record->adl_emp_id ?? null,
                    $record->adl_approved_by ?? null,
                    $systemEmpId,
                    "Advance Request Auto Rejected",
                    "Your advance request was automatically rejected due to approval timeout.",
                    199,
                    $record->adl_id
                );
            }

            \Log::info("Advance Auto Rejected Count : " . count($records));
        }

        // =====================================================
        // LEAVE
        // =====================================================

        elseif ($moduleId == 250) {

            $records = LeaveRequest::where('lvr_b_id', $businessId)
                ->where('created_at', '<=', $cutoffDate)
                ->whereNotIn('lvr_status', [157, 170])
                ->where('lvr_stage_completed', '!=', 1)
                ->get();

            foreach ($records as $record) {

                $record->update([
                    'lvr_status' => 170,
                    'lvr_stage_completed' => 1,
                    'lvr_approved_by' => $systemEmpId,
                ]);

                $this->sendNotification(
                    $record->lvr_emp_id ?? null,
                    $record->lvr_approved_by ?? null,
                    $systemEmpId,
                    "Leave Request Auto Rejected",
                    "Your leave request was automatically rejected due to approval timeout.",
                    250,
                    $record->lvr_id
                );
            }

            \Log::info("Leave Auto Rejected Count : " . count($records));
        }

        // =====================================================
        // MISSED PUNCH
        // =====================================================

        elseif ($moduleId == 229) {

            $records = AttendanceException::where('ae_b_id', $businessId)
                ->where('ae_module_id', 229)
                ->where('created_at', '<=', $cutoffDate)
                ->whereNotIn('ae_status', [157, 170])
                ->where('ae_stage_completed', '!=', 1)
                ->get();

            foreach ($records as $record) {

                $record->update([
                    'ae_status' => 170,
                    'ae_stage_completed' => 1,
                    'ae_approved_by' => $systemEmpId,
                ]);

                $this->sendNotification(
                    $record->ae_emp_id ?? null,
                    $record->ae_approved_by ?? null,
                    $systemEmpId,
                    "Missed Punch Auto Rejected",
                    "Your missed punch request was automatically rejected due to approval timeout.",
                    229,
                    $record->ae_id
                );
            }

            \Log::info("Missed Punch Auto Rejected Count : " . count($records));
        }

        // =====================================================
        // GATE PASS
        // =====================================================

        elseif ($moduleId == 339) {

            $records = GatePass::where('gtp_b_id', $businessId)
                ->where('gtp_module_id', 339)
                ->where('created_at', '<=', $cutoffDate)
                ->whereNotIn('gtp_status', [157, 170])
                ->where('gtp_stage_completed', '!=', 1)
                ->get();

            foreach ($records as $record) {

                $record->update([
                    'gtp_status' => 170,
                    'gtp_stage_completed' => 1,
                    'gtp_approved_by' => $systemEmpId,
                ]);

                $this->sendNotification(
                    $record->gtp_emp_id ?? null,
                    $record->gtp_approved_by ?? null,
                    $systemEmpId,
                    "Gate Pass Auto Rejected",
                    "Your gate pass request was automatically rejected due to approval timeout.",
                    339,
                    $record->gtp_id
                );
            }

            \Log::info("Gate Pass Auto Rejected Count : " . count($records));
        }

        // =====================================================
        // LOAN
        // =====================================================

        elseif ($moduleId == 442) {

            $records = LoanRequest::where('lnr_b_id', $businessId)
                ->where('lnr_module_id', 442)
                ->where('created_at', '<=', $cutoffDate)
                ->whereNotIn('lnr_request_status', [157, 170])
                ->where('lnr_stage_completed', '!=', 1)
                ->get();

            foreach ($records as $record) {

                $record->update([
                    'lnr_request_status' => 170,
                    'lnr_stage_completed' => 1,
                ]);

                $this->sendNotification(
                    $record->lnr_emp_id ?? null,
                    $record->lnr_approved_by ?? null,
                    $systemEmpId,
                    "Loan Request Auto Rejected",
                    "Your loan request was automatically rejected due to approval timeout.",
                    442,
                    $record->lnr_id
                );
            }

            \Log::info("Loan Auto Rejected Count : " . count($records));
        }

        // =====================================================
        // OT APPROVAL
        // =====================================================

        elseif ($moduleId == 562) {

            $records = OtApprovalStatus::where('ot_b_id', $businessId)
                ->where('created_at', '<=', $cutoffDate)
                ->whereNotIn('ot_requested_status', [157, 170])
                ->where('ot_stage_completed', '!=', 1)
                ->get();

            foreach ($records as $record) {

                $record->update([
                    'ot_requested_status' => 170,
                    'ot_stage_completed' => 1,
                ]);

                $this->sendNotification(
                    $record->ot_emp_id ?? null,
                    $record->ot_approved_by ?? null,
                    $systemEmpId,
                    "OT Request Auto Rejected",
                    "Your OT request was automatically rejected due to approval timeout.",
                    562,
                    $record->ot_id
                );
            }

            \Log::info("OT Auto Rejected Count : " . count($records));
        }
    }

    private function sendNotification(
        $employeeId,
        $approverId,
        $systemEmpId,
        $title,
        $body,
        $moduleId,
        $referenceId
    ) {

        $additionalData = [
            'module_id' => $moduleId,
            'reference_id' => $referenceId,
            'type' => 'auto_reject'
        ];

        // =====================================================
        // EMPLOYEE NOTIFICATION
        // =====================================================

        if ($employeeId) {

            $employee = Employee::where('emp_id', $employeeId)->first();

            if (
                $employee &&
                $employee->emp_is_notification_enabled == '1' &&
                !empty($employee->emp_fcm_token)
            ) {

                $serviceAccountPath = public_path('fixhr-app-firebase.json');

                FirebaseNotification::sendPushNotification(
                    $title,
                    $body,
                    $employee->emp_fcm_token,
                    $serviceAccountPath,
                    config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                    $additionalData
                );

                NotificationHelper::saveNotification(
                    $systemEmpId,
                    $employee->emp_id,
                    $title,
                    $body,
                    $additionalData
                );
            }
        }

        // =====================================================
        // APPROVER NOTIFICATION
        // =====================================================

        if ($approverId) {

            $approver = Employee::where('emp_id', $approverId)->first();

            if (
                $approver &&
                $approver->emp_is_notification_enabled == '1' &&
                !empty($approver->emp_fcm_token)
            ) {

                $approverTitle = "Pending Approval Auto Rejected";

                $approverBody = "A pending approval was automatically rejected due to timeout.";

                $serviceAccountPath = public_path('fixhr-app-firebase.json');

                FirebaseNotification::sendPushNotification(
                    $approverTitle,
                    $approverBody,
                    $approver->emp_fcm_token,
                    $serviceAccountPath,
                    config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                    $additionalData
                );

                NotificationHelper::saveNotification(
                    $systemEmpId,
                    $approver->emp_id,
                    $approverTitle,
                    $approverBody,
                    $additionalData
                );
            }
        }
    }
}
