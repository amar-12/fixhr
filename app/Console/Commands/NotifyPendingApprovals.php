<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ModuleResolverNotify;
use App\Models\Employee;
use App\Models\TadaClaim;
use App\Models\AppNotification;
use App\Models\EmployeeApprovalMapping;
use App\Models\EmployeeApprovalStatus;
use App\Helpers\NotificationHelper;
use ChandraHemant\HtkcUtils\FirebaseNotification;
use Illuminate\Support\Facades\Log;

class NotifyPendingApprovals extends Command
{
    protected $signature = 'notify:pending-approvals';
    protected $description = 'Send daily notifications to approvers who have pending approvals';

    public function handle(): int
    {
        $modules = [145, 146, 199];

        foreach ($modules as $moduleId) {
            $resolver = ModuleResolverNotify::resolve($moduleId);
            if (!$resolver) {
                Log::warning("NotifyPendingApprovals: No resolver for module {$moduleId}");
                continue;
            }

            $model  = $resolver['model'];
            $fields = $resolver['fields'];
            [$nextField, $empField, $amField, $idField, $stageField] = $fields;

            try {
                $items = $model::where($stageField, '!=', 1)
                    ->whereNotNull($nextField)
                    ->where($nextField, '!=', 0)
                    ->get();

                foreach ($items as $item) {
                    $requesterEmpId = $item->{$empField} ?? null;
                    
                    if($moduleId == 199){
                        $requesterEmpId = $item->adl_emp_id ?? null;
                    }
                    $nextSequence   = (int) ($item->{$nextField} ?? 0);

                    if (!$requesterEmpId) {
                        continue;
                    }

                    $approverEmpId = $this->resolveApproverFromMapping(
                        $requesterEmpId,
                        $moduleId,
                        $nextSequence
                    );

                    if (!$approverEmpId) {
                        Log::debug("NotifyPendingApprovals: Could not resolve approver", [
                            'module'    => $moduleId,
                            'record_id' => $item->{$idField},
                            'sequence'  => $nextSequence,
                        ]);
                        continue;
                    }

                    $approver = Employee::find($approverEmpId);
                    if (!$approver) {
                        continue;
                    }

                    $requester     = $this->resolveRequester($item, $empField);
                    $requesterName = $this->formatEmployeeName($requester);
                    $requesterCode = $requester->emp_code ?? '';
                    $moduleLabel   = $this->getModuleLabel($moduleId);
                    $currentStatus = $this->getStatusLabel($item->{$stageField} ?? null);
                    $requestId     = $item->{$idField};
                    $amount        = $this->getAmountForModule($item, $moduleId);

                    $title = "ðŸ”” {$moduleLabel} Pending Approval";
                    $body  = ($requesterName ?: 'An employee')
                        . ($requesterCode ? $requesterCode : '')
                        . " has submitted a {$this->getModuleBodyLabel($moduleId)} request"
                        . ($amount ? " for â‚¹{$amount}" : '')
                        . ". Your approval is required.";

                    $additional = [
                        'notification_type' => 'pending_approval',
                        'module_id'         => $moduleId,
                        'module_label'      => $moduleLabel,
                        'request_ref'       => $requestId,
                        'current_status'    => $currentStatus,
                        'requester_id'      => $requesterEmpId,
                        'requester_name'    => $requesterName,
                        'requester_code'    => $requesterCode,
                    ];

                    $this->dispatchNotification($approver, $title, $body, $additional);
                }

            } catch (\Throwable $e) {
                Log::error("NotifyPendingApprovals error for module {$moduleId}: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            // ------------------------------------------------------------------
            // TadaClaim : Deduction pending â€” remind employee
            // ------------------------------------------------------------------
            // if ($moduleId === 146) {
            //     try {
            //         $claims = $model::whereHas('fh_deduction_log', function ($q) {
            //                 $q->whereNull('dlog_requester_action');
            //             })
            //             ->where($stageField, '!=', 1)
            //             ->get();

            //         foreach ($claims as $claim) {
            //             $employee = $claim->fh_employee;
            //             if (!$employee) {
            //                 continue;
            //             }

            //             $alreadyNotified = AppNotification::where('user_id', $employee->emp_id)
            //                 ->whereJsonContains('additional_data->notification_type', 'deduction_pending')
            //                 ->whereJsonContains('additional_data->request_ref', $claim->tc_id)
            //                 ->whereDate('created_at', now()->toDateString())
            //                 ->exists();

            //             if ($alreadyNotified) {
            //                 continue;
            //             }

            //             $deductionLog  = $claim->fh_deduction_log
            //                 ->whereNull('dlog_requester_action')
            //                 ->first();
 
            //             $deductedBy     = $deductionLog?->fh_employee?->emp_full_name ?? 'Approver';
            //             $deductedAmount = $deductionLog?->dlog_deduction_amount ?? null;

            //             // $body  = "A deduction has been added to your Travel Claim (ID #{$claim->tc_id}). Your approval is required.";

            //             $title = 'ðŸ”” Acceptance Pending Approval';
            //             $body  = 'Amount of â‚¹' . ($deductedAmount ? number_format((float)$deductedAmount, 2) : '__')
            //                 . " has been deducted by {$deductedBy}"
            //                 . " for Claim ID #{$claim->tc_unique_id}."
            //                 . " Your acceptance is required.";

            //             $additional = [
            //                 'notification_type' => 'deduction_pending',
            //                 'module_id'         => 146,
            //                 'module_label'      => 'Travel Claim',
            //                 'request_ref'       => $claim->tc_id,
            //             ];

            //             $this->dispatchNotification($employee, $title, $body, $additional);
            //         }

            //     } catch (\Throwable $e) {
            //         Log::error('NotifyPendingApprovals (deduction notify) error: ' . $e->getMessage(), [
            //             'trace' => $e->getTraceAsString(),
            //         ]);
            //     }
            // }
            
            if ($moduleId === 146) {
                try {
                    $claims = $model::whereHas('fh_deduction_log', function ($q) {
                            $q->whereNull('dlog_requester_action');
                        })
                        ->where($stageField, '!=', 1)
                        ->get();
            
                    foreach ($claims as $claim) {
                        $employee = $claim->fh_employee;
                        if (!$employee) {
                            continue;
                        }

                        $pendingDeductions = $claim->fh_deduction_log
                            ->whereNull('dlog_requester_action');
            
                        foreach ($pendingDeductions as $deductionLog) {
                            $alreadyNotified = AppNotification::where('user_id', $employee->emp_id)
                                ->whereJsonContains('additional_data->notification_type', 'deduction_pending')
                                ->whereJsonContains('additional_data->dlog_id', $deductionLog->dlog_id)
                                ->whereDate('created_at', now()->toDateString())
                                ->exists();
            
                            if ($alreadyNotified) {
                                continue;
                            }
            
                            $deductedBy     = $deductionLog->fh_employee?->emp_full_name ?? 'Approver';
                            $deductedAmount = $deductionLog->dlog_deduction_amount ?? null;
            
                            $title = '🔔 Acceptance Pending Approval';
                            $body  = 'Amount of ₹' . ($deductedAmount ? number_format((float)$deductedAmount, 2) : '__')
                                . " has been deducted by {$deductedBy}"
                                . " for Claim ID #{$claim->tc_unique_id}."
                                . " Your acceptance is required.";
            
                            $additional = [
                                'notification_type' => 'deduction_pending',
                                'module_id'         => 146,
                                'module_label'      => 'Travel Claim',
                                'request_ref'       => $claim->tc_id,
                                'dlog_id'           => $deductionLog->dlog_id,  // har deduction alag track hogi
                            ];
            
                            $this->dispatchNotification($employee, $title, $body, $additional);
                        }
                    }
            
                } catch (\Throwable $e) {
                    Log::error('NotifyPendingApprovals (deduction notify) error: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        $this->info('Pending approval notifications processed.');
        return 0;
    }

    private function resolveApproverFromMapping(
        int $requesterEmpId,
        int $moduleId,
        int $nextSequence
    ): ?int {
        try {
            $mapping = EmployeeApprovalMapping::where('eam_emp_id', $requesterEmpId)
                ->where('eam_module_id', $moduleId)
                ->first();

            if (!$mapping) {
                return null;
            }

            $status = EmployeeApprovalStatus::where('eas_eam_id', $mapping->eam_id)
                ->orderBy('eas_id', 'asc')
                ->offset($nextSequence - 1)
                ->limit(1)
                ->first();

            return $status?->eas_approvel_id ?? null;

        } catch (\Throwable $e) {
            Log::warning("resolveApproverFromMapping failed: " . $e->getMessage());
            return null;
        }
    }

    private function resolveRequester($item, string $empField): ?Employee
    {
        if (isset($item->fh_employee) && $item->fh_employee instanceof Employee) {
            return $item->fh_employee;
        }

        $empId = $item->{$empField} ?? null;
        return $empId ? Employee::find($empId) : null;
    }

    private function formatEmployeeName(?Employee $employee): string
    {
        if (!$employee) {
            return '';
        }

        if (!empty($employee->emp_full_name)) {
            return $employee->emp_full_name;
        }

        return trim(($employee->emp_fname ?? '') . ' ' . ($employee->emp_lname ?? ''));
    }

    private function getModuleLabel(int $moduleId): string
    {
        return match ($moduleId) {
            145 => 'Travel',
            146 => 'Claim',
            199 => 'Advance',
            default => "Module #{$moduleId}",
        };
    }

    private function getModuleBodyLabel(int $moduleId): string
    {
        return match ($moduleId) {
            145 => 'travel',
            146 => 'claim',
            199 => 'advance',
            default => 'approval',
        };
    }

    private function getAmountForModule($item, int $moduleId): ?string
    {
        $amount = match ($moduleId) {
            145 => null,
            146 => $item->tc_amount ?? null,
            199 => $item->adl_requested_amount ?? null,
            default => null,
        };

        return ($amount !== null && $amount > 0)
            ? number_format((float) $amount, 2)
            : null;
    }

    private function getStatusLabel(?int $statusId): string
    {
        return match ($statusId) {
            140  => 'Requested',
            141  => 'Confirmed',
            156  => 'Processing',
            157  => 'Approved',
            170  => 'Rejected',
            171  => 'Auto Approved',
            172  => 'Claimed',
            174  => 'Reviewed',
            175  => 'Deduction Added',
            192  => 'Recalled',
            200  => 'Reimbursed',
            412  => 'Paid',
            451  => 'Finance Reviewed',
            452  => 'Finance Approved',
            default => 'Pending',
        };
    }

    private function dispatchNotification(
        Employee $employee,
        string $title,
        string $body,
        array $additional
    ): void {
        try {
            if (!empty($employee->emp_fcm_token) && $employee->emp_is_notification_enabled == '1') {
                $serviceAccountPath = public_path('fixhr-app-firebase.json');
                FirebaseNotification::sendPushNotification(
                    $title,
                    $body,
                    $employee->emp_fcm_token,
                    $serviceAccountPath,
                    config('credentials')['FIREBASE_MESSAGING_CONFIG'],
                    $additional
                );
            }

            NotificationHelper::saveNotification(null, $employee->emp_id, $title, $body, $additional);

            Log::info('Notification dispatched', [
                'emp_id'            => $employee->emp_id,
                'notification_type' => $additional['notification_type'] ?? 'unknown',
                'module_id'         => $additional['module_id'] ?? null,
                'request_ref'       => $additional['request_ref'] ?? null,
            ]);

        } catch (\Throwable $e) {
            Log::warning("dispatchNotification failed for emp {$employee->emp_id}: " . $e->getMessage());
        }
    }
}
