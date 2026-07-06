<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $table = 'payroll_periods';

    protected $primaryKey = 'pp_id';

    public $timestamps = true;

    protected $fillable = [
        'pp_b_id',
        'pp_fy_id',
        'pp_month_id',
        'pp_process_id',
        'pp_type_id',
        'pp_name',
        'pp_seq_no',
        'pp_start_date',
        'pp_end_date',
        'pp_cheque_no',
        'pp_description',
        'pp_payment_date',
        'pp_payslip_date',
        'pp_is_active',
        'pp_is_freezed',
        'pp_quarter_id',
        'pp_is_processed',
        'pp_is_finalized',
        'pp_finalized_by',
        'pp_finalized_at',
        'pp_status_code',
        'pp_status_details',
        'pp_approved_by',
        'pp_approved_at',
        'pp_verified_by',
        'pp_verified_at',
        'pp_salary_disbursement_date',
        'pp_tada_disbursement_date',
        'pp_locked_by',
        'pp_locked_at',
    ];

    protected $casts = [
        'pp_start_date' => 'date',
        'pp_end_date' => 'date',
        'pp_payment_date' => 'date',
        'pp_payslip_date' => 'date',
        'pp_is_active' => 'boolean',
        'pp_is_finalized' => 'boolean',
        'finalized_at' => 'datetime',
        'pp_approved_at' => 'datetime',
        'pp_verified_at' => 'datetime',
        'pp_locked_at' => 'datetime',
    ];

    // Relationships
    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class, 'pp_fy_id', 'fy_id');
    }

    public function month()
    {
        return $this->belongsTo(MasterTable::class, 'pp_month_id', 'm_id')
            ->where('m_group', 'MONTH');
    }

    public function fh_employee()
    {
        return $this->hasMany(Employee::class, 'emp_b_id', 'pp_b_id');
    }

    public function quarter_master()
    {
        return $this->belongsTo(MasterTable::class, 'pp_quarter_id', 'm_id')
            ->where('m_group', 'PAYROLL_QUARTER');
    }

    public function processedSalaries()
    {
        return $this->hasMany(ProcessedEmployeeSalary::class, 'ps_payroll_id', 'pp_id');
    }

    public function activeSalaryHolds()
    {
        return $this->hasMany(SalaryHold::class, 'sh_pp_id', 'pp_id')
            ->where('sh_status', 'HELD');
    }

    public function holdUntilRelease()
    {
        return $this->hasMany(SalaryHold::class, 'sh_pp_id', 'pp_id')
            ->where('sh_status', 'HELD')
            ->where('sh_hold_until_release', 1);
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'pp_approved_by', 'emp_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(Employee::class, 'pp_verified_by', 'emp_id');
    }

    public function lockedBy()
    {
        return $this->belongsTo(Employee::class, 'pp_locked_by', 'emp_id');
    }

    // Status Constants with detailed descriptions
    const STATUS_PROCESSING = 'PROCESSING'; // Step 1: Open for inputs
    const STATUS_PENDING_APPROVAL = 'PENDING_APPROVAL'; // Step 1: Waiting for approval
    const STATUS_UNDER_REVIEW = 'UNDER_REVIEW'; // Step 2: Attendance review
    const STATUS_ATTENDANCE_FROZEN = 'ATTENDANCE_FROZEN'; // Step 2: Attendance frozen
    const STATUS_SALARY_UNDER_PREVIEW = 'SALARY_UNDER_PREVIEW'; // Step 3: Salary preview
    const STATUS_SALARY_PROCESSING = 'SALARY_PROCESSING'; // Step 3: Salary processing
    const STATUS_VERIFICATION = 'VERIFICATION'; // Step 4: Verification stage
    const STATUS_COMPLETED = 'COMPLETED'; // Step 5: Completed
    const STATUS_PAYROLL_LOCKED = 'PAYROLL_LOCKED'; // Step 5: Locked

    // Step mapping
    const STEP_APPROVAL = 'STEP1';
    const STEP_FREEZE = 'STEP2';
    const STEP_PROCESS = 'STEP3';
    const STEP_VERIFY = 'STEP4';
    const STEP_LOCK = 'STEP5';

    protected $attributes = [
        'pp_status_code' => self::STATUS_PROCESSING,
    ];

    // Status descriptions for UI
    public static function getStatusConfig(): array
    {
        return [
            self::STATUS_PROCESSING => [
                'step' => self::STEP_APPROVAL,
                'label' => 'PROCESSING',
                'description' => 'Payroll period is processing for inputs and adjustments',
                'color' => 'info',
                'icon' => 'unlock',
                'actions' => ['submit_for_approval'],
            ],
            self::STATUS_PENDING_APPROVAL => [
                'step' => self::STEP_APPROVAL,
                'label' => 'Approval Pending',
                'description' => 'Waiting for manager approval to proceed',
                'color' => 'warning',
                'icon' => 'clock',
                'actions' => ['approve', 'reject'],
            ],
            self::STATUS_UNDER_REVIEW => [
                'step' => self::STEP_FREEZE,
                'label' => 'Attendance Under Review',
                'description' => 'Attendance data is being reviewed before freezing',
                'color' => 'primary',
                'icon' => 'eye',
                'actions' => ['freeze_attendance', 'return_to_approval'],
            ],
            self::STATUS_ATTENDANCE_FROZEN => [
                'step' => self::STEP_FREEZE,
                'label' => 'Attendance Frozen',
                'description' => 'Attendance data is frozen and cannot be modified',
                'color' => 'success',
                'icon' => 'lock',
                'actions' => ['proceed_to_salary'],
            ],
            self::STATUS_SALARY_UNDER_PREVIEW => [
                'step' => self::STEP_PROCESS,
                'label' => 'Salary Under Preview',
                'description' => 'Salary calculations are being previewed',
                'color' => 'info',
                'icon' => 'file-text',
                'actions' => ['process_salary', 'return_to_attendance'],
            ],
            self::STATUS_SALARY_PROCESSING => [
                'step' => self::STEP_PROCESS,
                'label' => 'Salary Processing',
                'description' => 'Salaries are being processed',
                'color' => 'processing',
                'icon' => 'loader',
                'actions' => ['complete_processing'],
            ],
            self::STATUS_VERIFICATION => [
                'step' => self::STEP_VERIFY,
                'label' => 'Verification',
                'description' => 'Processed salaries are under verification',
                'color' => 'warning',
                'icon' => 'check-circle',
                'actions' => ['verify', 'return_to_processing'],
            ],
            self::STATUS_COMPLETED => [
                'step' => self::STEP_LOCK,
                'label' => 'Completed',
                'description' => 'Payroll process completed successfully',
                'color' => 'success',
                'icon' => 'check',
                'actions' => ['lock_payroll'],
            ],
            self::STATUS_PAYROLL_LOCKED => [
                'step' => self::STEP_LOCK,
                'label' => 'Payroll Locked',
                'description' => 'Payroll is locked and cannot be modified',
                'color' => 'dark',
                'icon' => 'lock',
                'actions' => [],
            ],
        ];
    }

    // Get status details
    public function getStatusDetails(): array
    {
        return self::getStatusConfig()[$this->pp_status_code] ?? [
            'step' => 'UNKNOWN',
            'label' => 'Unknown',
            'description' => 'Unknown status',
            'color' => 'secondary',
            'icon' => 'help-circle',
            'actions' => [],
        ];
    }

    // Step access methods
    public function canAccessStep($step): bool
    {
        $currentStep = $this->getCurrentStep();

        // Allow access to current step and previous steps
        $stepOrder = [
            self::STEP_APPROVAL => 1,
            self::STEP_FREEZE => 2,
            self::STEP_PROCESS => 3,
            self::STEP_VERIFY => 4,
            self::STEP_LOCK => 5,
        ];

        $currentStepOrder = $stepOrder[$currentStep] ?? 0;
        $requestedStepOrder = $stepOrder[$step] ?? 0;

        return $requestedStepOrder <= $currentStepOrder;
    }

    public function getCurrentStep(): string
    {
        return $this->getStatusDetails()['step'];
    }

    // Specific step access methods
    public function canAccessStep1(): bool
    {
        return in_array($this->pp_status_code, [
            self::STATUS_PROCESSING,
            self::STATUS_PENDING_APPROVAL,
        ]);
    }

    public function canAccessStep2()
    {
        // Should allow both PROCESSING and ATTENDANCE_FROZEN
        return in_array($this->pp_status_code, ['PROCESSING', 'PENDING_APPROVAL', 'UNDER_REVIEW']);
    }


    public function canAccessStep3(): bool
    {
        return $this->pp_status_code === 'ATTENDANCE_FROZEN';

        // return in_array($this->pp_status_code, [
        //     self::STATUS_SALARY_UNDER_PREVIEW,
        //     self::STATUS_SALARY_PROCESSING,
        // ]);
    }

    public function canAccessStep4(): bool
    {
        return $this->pp_status_code === self::STATUS_VERIFICATION;
    }

    public function canAccessStep5(): bool
    {
        return in_array($this->pp_status_code, [
            self::STATUS_COMPLETED,
            self::STATUS_PAYROLL_LOCKED,
        ]);
    }

    // Status transition methods
    public function transitionTo(string $targetStatus, ?int $userId = null): bool
    {
        $allowedTransitions = [
            self::STATUS_PROCESSING => [self::STATUS_PENDING_APPROVAL],
            self::STATUS_PENDING_APPROVAL => [
                self::STATUS_PROCESSING, // rejected
                self::STATUS_UNDER_REVIEW, // approved
            ],
            self::STATUS_UNDER_REVIEW => [
                self::STATUS_PENDING_APPROVAL, // return
                self::STATUS_ATTENDANCE_FROZEN, // freeze
            ],
            self::STATUS_ATTENDANCE_FROZEN => [
                self::STATUS_SALARY_UNDER_PREVIEW,
                self::STATUS_UNDER_REVIEW, // unfreeze
            ],
            self::STATUS_SALARY_UNDER_PREVIEW => [
                self::STATUS_ATTENDANCE_FROZEN, // return
                self::STATUS_SALARY_PROCESSING, // process
            ],
            self::STATUS_SALARY_PROCESSING => [
                self::STATUS_SALARY_UNDER_PREVIEW, // return
                self::STATUS_VERIFICATION, // complete
            ],
            self::STATUS_VERIFICATION => [
                self::STATUS_SALARY_PROCESSING, // return
                self::STATUS_COMPLETED, // verify
            ],
            self::STATUS_COMPLETED => [
                self::STATUS_PAYROLL_LOCKED,
                self::STATUS_VERIFICATION, // reprocessing
            ],
            self::STATUS_PAYROLL_LOCKED => [
                self::STATUS_COMPLETED, // unlock (admin only)
            ],
        ];

        if (!isset($allowedTransitions[$this->pp_status_code]) ||
            !in_array($targetStatus, $allowedTransitions[$this->pp_status_code])) {
            return false;
        }

        $previousStatus = $this->pp_status_code;
        $this->pp_status_code = $targetStatus;

        // Set user who performed the action
        if ($userId) {
            $this->setActionUser($targetStatus, $userId);
        }

        // Log the status change
        $this->logStatusChange($previousStatus, $targetStatus, $userId);

        return true;
    }

    // Set user who performed action
    private function setActionUser(string $status, int $userId): void
    {
        switch ($status) {
            case self::STATUS_PENDING_APPROVAL:
                $this->pp_approved_by = $userId;
                $this->pp_approved_at = now();
                break;
            case self::STATUS_VERIFICATION:
                $this->pp_verified_by = $userId;
                $this->pp_verified_at = now();
                break;
            case self::STATUS_PAYROLL_LOCKED:
                $this->pp_locked_by = $userId;
                $this->pp_locked_at = now();
                break;
        }
    }

    // Log status change
    private function logStatusChange(string $from, string $to, ?int $userId = null): void
    {
        // You can create a PayrollStatusLog model or use activity log
        \Log::info('Payroll status changed', [
            'payroll_id' => $this->pp_id,
            'from' => $from,
            'to' => $to,
            'user_id' => $userId,
            'timestamp' => now(),
        ]);

        // Store in status_details field
        $this->pp_status_details = json_encode([
            'last_change' => [
                'from' => $from,
                'to' => $to,
                'user_id' => $userId,
                'time' => now()->toDateTimeString(),
            ],
            'history' => $this->getStatusHistory(),
        ]);
    }

    private function getStatusHistory(): array
    {
        $history = json_decode($this->pp_status_details, true)['history'] ?? [];
        $history[] = [
            'status' => $this->pp_status_code,
            'changed_at' => now()->toDateTimeString(),
        ];

        return array_slice($history, -10); // Keep last 10 entries
    }

    // Convenience methods
    public function isOpen(): bool
    {
        return $this->pp_status_code === self::STATUS_PROCESSING;
    }

    public function isApprovalPending(): bool
    {
        return $this->pp_status_code === self::STATUS_PENDING_APPROVAL;
    }

    public function isAttendanceUnderReview(): bool
    {
        return $this->pp_status_code === self::STATUS_UNDER_REVIEW;
    }

    public function isAttendanceFrozen(): bool
    {
        return $this->pp_status_code === self::STATUS_ATTENDANCE_FROZEN;
    }

    public function isSalaryUnderPreview(): bool
    {
        return $this->pp_status_code === self::STATUS_SALARY_UNDER_PREVIEW;
    }

    public function isProcessing(): bool
    {
        return $this->pp_status_code === self::STATUS_SALARY_PROCESSING;
    }

    public function isUnderVerification(): bool
    {
        return $this->pp_status_code === self::STATUS_VERIFICATION;
    }

    public function isCompleted(): bool
    {
        return $this->pp_status_code === self::STATUS_COMPLETED;
    }

    public function isLocked(): bool
    {
        return $this->pp_status_code === self::STATUS_PAYROLL_LOCKED;
    }

    // Check if can submit for approval
    public function canSubmitForApproval(): bool
    {
        return $this->pp_status_code === self::STATUS_PROCESSING;
    }

    // Check if can approve
    public function canApprove(): bool
    {
        return $this->pp_status_code === self::STATUS_PENDING_APPROVAL;
    }

    // Check if can freeze attendance
    public function canFreezeAttendance(): bool
    {
        return $this->pp_status_code === self::STATUS_UNDER_REVIEW;
    }

    // Check if can process salary
    public function canProcessSalary(): bool
    {
        return in_array($this->pp_status_code, [
            self::STATUS_SALARY_UNDER_PREVIEW,
            self::STATUS_SALARY_PROCESSING,
        ]);
    }

    // Check if can verify
    public function canVerify(): bool
    {
        return $this->pp_status_code === self::STATUS_VERIFICATION;

    }

    // Check if can lock
    public function canLock(): bool
    {
        return $this->pp_status_code === self::STATUS_COMPLETED;
    }


    
    public function weeks()
    {
        return $this->hasMany(PayrollPeriodWeek::class, 'ppw_pp_id', 'pp_id')
                    ->orderBy('ppw_week_number', 'asc');
    }

    public function getWeeksCountAttribute()
    {
        return $this->weeks()->count();
    }

    public function getConfiguredWeeksCountAttribute()
    {
        return $this->weeks()->where('ppw_status', PayrollPeriodWeek::STATUS_CONFIGURED)->count();
    }

    public function getProcessedWeeksCountAttribute()
    {
        return $this->weeks()->where('ppw_status', PayrollPeriodWeek::STATUS_PROCESSED)->count();
    }

    public function getWeeksForProcessing()
    {
        return $this->weeks()->whereIn('ppw_status', [
            PayrollPeriodWeek::STATUS_CONFIGURED,
            PayrollPeriodWeek::STATUS_PENDING
        ])->get();
    }

    public function getWeekByNumber($weekNumber)
    {
        return $this->weeks()->where('ppw_week_number', $weekNumber)->first();
    }

}
