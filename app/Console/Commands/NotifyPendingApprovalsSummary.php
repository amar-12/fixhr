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

class NotifyPendingApprovalsSummary extends Command
{
    protected $signature = 'notify:pending-approvals-summary';
    protected $description = 'Send a single grouped summary notification to each approver/employee for all their pending requests';

    public function handle(): int
    {
        $modules = [145, 146, 199];

        $approverGroups = [];
        $deductionGroups = [];

        foreach ($modules as $moduleId) {
            $resolver = ModuleResolverNotify::resolve($moduleId);
            if (!$resolver) {
                Log::warning("NotifyPendingApprovalsSummary: No resolver for module {$moduleId}");
                continue;
            }

            $model  = $resolver['model'];
            $fields = $resolver['fields'];
            [$nextField, $empField, $amField, $idField, $stageField] = $fields;

            // ------------------------------------------------------------------
            // PART 1 — Approver-wise pending approvals
            // ------------------------------------------------------------------
            try {
                $items = $model::where($stageField, '!=', 1)
                    ->whereNotNull($nextField)
                    ->where($nextField, '!=', 0)
                    ->get();

                foreach ($items as $item) {
                    $requesterEmpId = $item->{$empField} ?? null;

                    if ($moduleId == 199) {
                        $requesterEmpId = $item->adl_emp_id ?? null;
                    }

                    $nextSequence = (int) ($item->{$nextField} ?? 0);

                    if (!$requesterEmpId || !$nextSequence) {
                        continue;
                    }

                    $approverEmpId = $this->resolveApproverFromMapping(
                        $requesterEmpId,
                        $moduleId,
                        $nextSequence
                    );

                    if (!$approverEmpId) {
                        continue;
                    }

                    $amount = $this->getAmountForModule($item, $moduleId);

                    if (!isset($approverGroups[$approverEmpId][$moduleId])) {
                        $approverGroups[$approverEmpId][$moduleId] = [
                            'count'  => 0,
                            'amount' => null,
                        ];
                    }

                    $approverGroups[$approverEmpId][$moduleId]['count']++;

                    if ($amount !== null) {
                        $approverGroups[$approverEmpId][$moduleId]['amount'] =
                            ($approverGroups[$approverEmpId][$moduleId]['amount'] ?? 0) + $amount;
                    }
                }

            } catch (\Throwable $e) {
                Log::error("NotifyPendingApprovalsSummary error for module {$moduleId}: " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            // ------------------------------------------------------------------
            // PART 2 — Module 146: Deduction pending — employee-wise
            // ------------------------------------------------------------------
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
                                ->whereJsonContains('additional_data->notification_type', 'deduction_pending_summary')
                                ->whereJsonContains('additional_data->dlog_ids', $deductionLog->dlog_id)
                                ->whereDate('created_at', now()->toDateString())
                                ->exists();

                            if ($alreadyNotified) {
                                continue;
                            }

                            $deductionGroups[$employee->emp_id][] = [
                                'claim_unique_id' => $claim->tc_unique_id ?? $claim->tc_id,
                                'claim_id'        => $claim->tc_id,
                                'dlog_id'         => $deductionLog->dlog_id,
                                'amount'          => $deductionLog->dlog_deduction_amount ?? null,
                                'deducted_by'     => $deductionLog->fh_employee?->emp_full_name ?? 'Approver',
                            ];
                        }
                    }

                } catch (\Throwable $e) {
                    Log::error('NotifyPendingApprovalsSummary (deduction group) error: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        // ------------------------------------------------------------------
        // PART 3 — Send a grouped summary notification to each approver.
        // ------------------------------------------------------------------
        foreach ($approverGroups as $approverEmpId => $moduleSummary) {
            $approver = Employee::find($approverEmpId);
            if (!$approver) {
                continue;
            }

            $totalCount  = array_sum(array_column($moduleSummary, 'count'));
            $totalAmount = array_sum(array_filter(array_column($moduleSummary, 'amount')));

            $lines = [];
            foreach ($moduleSummary as $moduleId => $data) {
                $label = $this->getModuleLabel($moduleId);
                $count = $data['count'];
                $amt   = $data['amount'];

                $line = "• {$label} ({$count} " . ($count > 1 ? 'requests' : 'request') . ")";
                if ($amt !== null && $amt > 0) {
                    // $line .= " – Total ₹" . number_format($amt, 2);
                    $line .= "";
                }
                $lines[] = $line;
            }

            $title = '🔔 Pending Approvals Summary';
            $body  = "You have {$totalCount} pending " . ($totalCount > 1 ? 'approvals' : 'approval') . ":\n"
                . implode("\n", $lines)
                . ($totalAmount > 0 ? "\nCombined Amount: ₹" . number_format($totalAmount, 2) : '')
                . "\nPlease review and take action.";

            $additional = [
                'notification_type' => 'pending_approvals_summary',
                'total_count'       => $totalCount,
                'total_amount'      => $totalAmount > 0 ? number_format($totalAmount, 2) : null,
                'module_summary'    => $moduleSummary,
            ];

            $this->dispatchNotification($approver, $title, $body, $additional);
        }

        // ------------------------------------------------------------------
        // PART 4 — Send a grouped deduction summary notification to each employee.
        // ------------------------------------------------------------------
        foreach ($deductionGroups as $empId => $deductions) {
            $employee = Employee::find($empId);
            if (!$employee) {
                continue;
            }

            $totalDeductions = count($deductions);
            $totalAmount     = array_sum(array_filter(array_column($deductions, 'amount')));
            $dlogIds         = array_column($deductions, 'dlog_id');

            // Multiple deductions ho to summary format, single ho to detail format
            if ($totalDeductions === 1) {
                $d     = $deductions[0];
                $title = '🔔 Acceptance Pending Approval';
                $body  = 'Amount of ₹' . ($d['amount'] ? number_format((float)$d['amount'], 2) : '__')
                    . " has been deducted by {$d['deducted_by']}"
                    . " for Claim ID #{$d['claim_unique_id']}."
                    . " Your acceptance is required.";
            } else {
                $lines = [];
                foreach ($deductions as $d) {
                    $line = "• Claim #{$d['claim_unique_id']} – ₹"
                        . ($d['amount'] ? number_format((float)$d['amount'], 2) : '__')
                        . " by {$d['deducted_by']}";
                    $lines[] = $line;
                }

                $title = '🔔 Acceptance Pending Approval';
                $body  = "You have {$totalDeductions} pending deduction acceptances:\n"
                    . implode("\n", $lines)
                    . ($totalAmount > 0 ? "\nTotal Deducted: ₹" . number_format($totalAmount, 2) : '')
                    . "\nPlease Accept or Reject.";
            }

            $additional = [
                'notification_type' => 'deduction_pending_summary',
                'module_id'         => 146,
                'module_label'      => 'Travel Claim',
                'dlog_ids'          => $dlogIds,   // dedup ke liye array of dlog_ids
                'total_deductions'  => $totalDeductions,
                'total_amount'      => $totalAmount > 0 ? number_format($totalAmount, 2) : null,
            ];

            $this->dispatchNotification($employee, $title, $body, $additional);
        }

        $this->info('Pending approvals summary notifications processed.');
        return 0;
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

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

    private function getModuleLabel(int $moduleId): string
    {
        return match ($moduleId) {
            145 => 'Travel',
            146 => 'Claim',
            199 => 'Advance',
            default => "Module #{$moduleId}",
        };
    }

    private function getAmountForModule($item, int $moduleId): ?float
    {
        $amount = match ($moduleId) {
            145 => null,
            146 => $item->tc_amount            ?? null,
            199 => $item->adl_requested_amount ?? null,
            default => null,
        };

        return ($amount !== null && $amount > 0) ? (float) $amount : null;
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

            Log::info('Summary notification dispatched', [
                'emp_id'            => $employee->emp_id,
                'notification_type' => $additional['notification_type'] ?? 'unknown',
            ]);

        } catch (\Throwable $e) {
            Log::warning("dispatchNotification failed for emp {$employee->emp_id}: " . $e->getMessage());
        }
    }
}
