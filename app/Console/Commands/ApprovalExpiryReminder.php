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
use App\Models\AppNotification;
use ChandraHemant\HtkcUtils\FirebaseNotification;
use App\Helpers\NotificationHelper;

class ApprovalExpiryReminder extends Command
{
    protected $signature = 'approvals:expiry-reminder';

    protected $description = 'Send reminder before auto rejection';

    public function handle()
    {
        try {

            $now = Carbon::now(config('app.timezone'));

            // $businesses = Business::where('b_status', 1)->get();
            $businesses = Business::where('b_status', 1)->where('b_id', 66)->get();

            foreach ($businesses as $business) {

                $businessId = $business->b_id;

                $systemEmp = Employee::where('emp_b_id', $businessId)
                    ->where('emp_role_id', 1)
                    ->first();

                if (!$systemEmp) {
                    continue;
                }

                // =====================================================
                // EMPLOYEE WISE CUSTOM RULES
                // =====================================================

                $empRules = ApprovalExpiryRejectDay::where('aer_b_id', $businessId)
                    ->get();

                foreach ($empRules as $rule) {

                    if (empty($rule->aer_day)) {
                        continue;
                    }

                    $rejectDays = (int) $rule->aer_day;
                    $notificationBeforeDays = (int) ($rule->aer_noti_day ?? 1);

                    // $rejectDays = 1;
                    // $notificationBeforeDays = 1;

                    $days = max(1, $rejectDays - $notificationBeforeDays);

                    $reminderDate = $now->copy()->subDays($days);

                    $this->processModule(
                        $businessId,
                        $rule->aer_m_id,
                        $reminderDate,
                        $systemEmp->emp_id
                    );
                }

                // =====================================================
                // HIERARCHY WISE DEFAULT RULES
                // =====================================================

                $modules = ApprovalModule::where('am_b_id', $businessId)
                    ->where('am_status', 1)
                    ->get();

                foreach ($modules as $module) {

                    $exists = ApprovalExpiryRejectDay::where('aer_b_id', $businessId)
                        ->where('aer_m_id', $module->am_module_id)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    if (empty($module->am_exp_rej_day)) {
                        continue;
                    }

                    $rejectDays = (int) $module->am_exp_rej_day;
                    $notificationBeforeDays = (int) ($module->am_noti_before_days ?? 1);

                    // $rejectDays = 1;
                    // $notificationBeforeDays = 1;

                    $days = max(1, $rejectDays - $notificationBeforeDays);

                    $reminderDate = $now->copy()->subDays($days);

                    $this->processModule(
                        $businessId,
                        $module->am_module_id,
                        $reminderDate,
                        $systemEmp->emp_id
                    );
                }
            }

            \Log::info("Approval expiry reminder cron completed");

        } catch (\Throwable $th) {

            \Log::error("Approval expiry reminder failed : " . $th->getMessage());
        }
    }

    private function processModule($businessId, $moduleId, $reminderDate, $systemEmpId)
    {
        // =====================================================
        // TRAVEL
        // =====================================================

        if ($moduleId == 145) {

            $records = TadaRequestPlan::where('trp_b_id', $businessId)
                ->whereDate('created_at', '=', $reminderDate->toDateString())
                ->whereNotIn('trp_request_status', [157, 170])
                ->where('trp_stage_completed', '!=', 1)
                ->get();

            foreach ($records as $record) {

                $this->sendReminderNotification(
                    $record->trp_emp_id ?? null,
                    $record->trp_approved_by ?? null,
                    $systemEmpId,
                    "Travel Request Expiry Reminder",
                    "Your travel request will be auto rejected tomorrow if not approved.",
                    145,
                    $record->trp_id
                );
            }
        }

        // =====================================================
        // CLAIM
        // =====================================================

        elseif ($moduleId == 146) {

            $records = TadaClaim::where('tc_b_id', $businessId)
                ->whereDate('created_at', '=', $reminderDate->toDateString())
                ->whereNotIn('tc_status', [157, 170])
                ->where('tc_stage_completed', '!=', 1)
                ->get();

            foreach ($records as $record) {

                $this->sendReminderNotification(
                    $record->tc_emp_id ?? null,
                    $record->tc_approved_by ?? null,
                    $systemEmpId,
                    "Claim Request Expiry Reminder",
                    "Your claim request will be auto rejected tomorrow if not approved.",
                    146,
                    $record->tc_id
                );
            }
        }

        // =====================================================
        // ADVANCE
        // =====================================================

        elseif ($moduleId == 199) {

            $records = AdvanceLog::where('adl_b_id', $businessId)
                ->whereDate('created_at', '=', $reminderDate->toDateString())
                ->whereNotIn('adl_request_status', [157, 170])
                ->where('adl_stage_completed', '!=', 1)
                ->get();

            foreach ($records as $record) {

                $this->sendReminderNotification(
                    $record->adl_emp_id ?? null,
                    $record->adl_approved_by ?? null,
                    $systemEmpId,
                    "Advance Request Expiry Reminder",
                    "Your advance request will be auto rejected tomorrow if not approved.",
                    199,
                    $record->adl_id
                );
            }
        }

        // =====================================================
        // LEAVE
        // =====================================================

        elseif ($moduleId == 250) {

            $records = LeaveRequest::where('lvr_b_id', $businessId)
                ->whereDate('created_at', '=', $reminderDate->toDateString())
                ->whereNotIn('lvr_status', [157, 170])
                ->where('lvr_stage_completed', '!=', 1)
                ->get();

            foreach ($records as $record) {

                $this->sendReminderNotification(
                    $record->lvr_emp_id ?? null,
                    $record->lvr_approved_by ?? null,
                    $systemEmpId,
                    "Leave Request Expiry Reminder",
                    "Your leave request will be auto rejected tomorrow if not approved.",
                    250,
                    $record->lvr_id
                );
            }
        }

        // =====================================================
        // MISSED PUNCH
        // =====================================================

        elseif ($moduleId == 229) {

            $records = AttendanceException::where('ae_b_id', $businessId)
                ->where('ae_module_id', 229)
                ->whereDate('created_at', '=', $reminderDate->toDateString())
                ->whereNotIn('ae_status', [157, 170])
                ->where('ae_stage_completed', '!=', 1)
                ->get();

            foreach ($records as $record) {

                $this->sendReminderNotification(
                    $record->ae_emp_id ?? null,
                    $record->ae_approved_by ?? null,
                    $systemEmpId,
                    "Missed Punch Expiry Reminder",
                    "Your missed punch request will be auto rejected tomorrow if not approved.",
                    229,
                    $record->ae_id
                );
            }
        }

        // =====================================================
        // GATE PASS
        // =====================================================

        elseif ($moduleId == 339) {

            $records = GatePass::where('gtp_b_id', $businessId)
                ->where('gtp_module_id', 339)
                ->whereDate('created_at', '=', $reminderDate->toDateString())
                ->whereNotIn('gtp_status', [157, 170])
                ->where('gtp_stage_completed', '!=', 1)
                ->get();

            foreach ($records as $record) {

                $this->sendReminderNotification(
                    $record->gtp_emp_id ?? null,
                    $record->gtp_approved_by ?? null,
                    $systemEmpId,
                    "Gate Pass Expiry Reminder",
                    "Your gate pass request will be auto rejected tomorrow if not approved.",
                    339,
                    $record->gtp_id
                );
            }
        }

        // =====================================================
        // LOAN
        // =====================================================

        elseif ($moduleId == 442) {

            $records = LoanRequest::where('lnr_b_id', $businessId)
                ->where('lnr_module_id', 442)
                ->whereDate('created_at', '=', $reminderDate->toDateString())
                ->whereNotIn('lnr_request_status', [157, 170])
                ->where('lnr_stage_completed', '!=', 1)
                ->get();

            foreach ($records as $record) {

                $this->sendReminderNotification(
                    $record->lnr_emp_id ?? null,
                    $record->lnr_approved_by ?? null,
                    $systemEmpId,
                    "Loan Request Expiry Reminder",
                    "Your loan request will be auto rejected tomorrow if not approved.",
                    442,
                    $record->lnr_id
                );
            }
        }

        // =====================================================
        // OT
        // =====================================================

        elseif ($moduleId == 562) {

            $records = OtApprovalStatus::where('ot_b_id', $businessId)
                ->whereDate('created_at', '=', $reminderDate->toDateString())
                ->whereNotIn('ot_requested_status', [157, 170])
                ->where('ot_stage_completed', '!=', 1)
                ->get();

            foreach ($records as $record) {

                $this->sendReminderNotification(
                    $record->ot_emp_id ?? null,
                    $record->ot_approved_by ?? null,
                    $systemEmpId,
                    "OT Request Expiry Reminder",
                    "Your OT request will be auto rejected tomorrow if not approved.",
                    562,
                    $record->ot_id
                );
            }
        }
    }

    private function sendReminderNotification(
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
            'type' => 'expiry_reminder'
        ];

        // =====================================================
        // EMPLOYEE
        // =====================================================

        if ($employeeId) {

            $employee = Employee::where('emp_id', $employeeId)->first();

            if (
                $employee &&
                $employee->emp_is_notification_enabled == '1' &&
                !empty($employee->emp_fcm_token)
            ) {

                $alreadySent = AppNotification::where('user_id', $employee->emp_id)
                    ->where('title', $title)
                    ->whereDate('created_at', Carbon::today())
                    ->exists();

                if (!$alreadySent) {
                    $serviceAccountPath = public_path('fixhr-app-firebase.json');
                    FirebaseNotification::sendPushNotification(
                        $title,
                        $body,
                        $employee->emp_fcm_token,
                        $serviceAccountPath,
                        config('credentials.FIREBASE_MESSAGING_CONFIG'),
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
        }

        // =====================================================
        // APPROVER
        // =====================================================

        if ($approverId) {

            $approver = Employee::where('emp_id', $approverId)->first();

            if (
                $approver &&
                $approver->emp_is_notification_enabled == '1' &&
                !empty($approver->emp_fcm_token)
            ) {

                $approverTitle = "Approval Expiry Reminder";

                $approverBody = "A pending approval assigned to you will be auto rejected tomorrow.";

                $alreadySent = AppNotification::where('user_id', $approver->emp_id)
                    ->where('title', $approverTitle)
                    ->whereDate('created_at', Carbon::today())
                    ->exists();

                if (!$alreadySent) {
                    $serviceAccountPath = public_path('fixhr-app-firebase.json');
                    FirebaseNotification::sendPushNotification(
                        $approverTitle,
                        $approverBody,
                        $approver->emp_fcm_token,
                        $serviceAccountPath,
                        config('credentials.FIREBASE_MESSAGING_CONFIG'),
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
}
