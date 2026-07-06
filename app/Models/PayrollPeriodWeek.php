<?php
// app/Models/PayrollPeriodWeek.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriodWeek extends Model
{
    use HasFactory;

    protected $table = 'payroll_period_weeks';
    protected $primaryKey = 'ppw_id';
    public $timestamps = true;

    protected $fillable = [
        'ppw_pp_id',
        'ppw_b_id',
        'ppw_fy_id',
        'ppw_month_id',
        'ppw_week_number',
        'ppw_week_name',
        'ppw_start_date',
        'ppw_end_date',
        'ppw_status',
        'ppw_description',
        'ppw_metadata',
        'ppw_employee_count',
        'ppw_processed_count',
        'ppw_is_frozen',
        'ppw_is_processed',
        'ppw_processed_at',
        'ppw_created_by',
        'ppw_updated_by',
        'ppw_frozen_at',
        'ppw_frozen_by',
    ];

    protected $casts = [
        'ppw_start_date' => 'date',
        'ppw_end_date' => 'date',
        'ppw_metadata' => 'array',
        'ppw_is_frozen' => 'boolean',
        'ppw_is_processed' => 'boolean',
        'ppw_processed_at' => 'datetime',
        'ppw_frozen_at' => 'datetime',
    ];

    // Status Constants - Aligned with PayrollPeriod
    const STATUS_OPEN = 'open';                    // Initial state - ready for processing
    const STATUS_PENDING_APPROVAL = 'pending_approval';  // Waiting for approval
    const STATUS_UNDER_REVIEW = 'under_review';    // Attendance under review
    const STATUS_FROZEN = 'frozen';                // Attendance frozen
    const STATUS_PROCESSING = 'processing';        // Salary processing
    const STATUS_PROCESSED = 'processed';          // Salary processed - ready for verification
    const STATUS_VERIFICATION = 'verification';    // Under verification
    const STATUS_COMPLETED = 'completed';          // Completed
    const STATUS_FINALIZED = 'finalized';          // Finalized and locked

    // Alias constants for backward compatibility
    const STATUS_PENDING = 'open';
    const STATUS_CONFIGURED = 'open';
    const STATUS_PAYROLL_LOCKED = 'finalized';

    protected $attributes = [
        'ppw_status' => self::STATUS_OPEN,
        'ppw_is_frozen' => false,
        'ppw_is_processed' => false,
    ];

    // Relationships
    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class, 'ppw_pp_id', 'pp_id');
    }

    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class, 'ppw_fy_id', 'fy_id');
    }

    public function month()
    {
        return $this->belongsTo(MasterTable::class, 'ppw_month_id', 'm_id');
    }

    public function attendanceSummaries()
    {
        return $this->hasMany(AttendanceSummary::class, 'as_week_id', 'ppw_id');
    }

    public function processedSalaries()
    {
        return $this->hasMany(ProcessedEmployeeSalary::class, 'ps_week_id', 'ppw_id');
    }

    // ============================================
    // STATUS CONFIGURATION FOR UI
    // ============================================
    
    public static function getStatusConfig(): array
    {
        return [
            self::STATUS_OPEN => [
                'label' => 'Open',
                'description' => 'Week is open for processing',
                'color' => 'primary',
                'icon' => 'unlock',
                'step' => 'STEP1',
                'can_process' => true,
                'can_edit' => true,
            ],
            self::STATUS_PENDING_APPROVAL => [
                'label' => 'Pending Approval',
                'description' => 'Waiting for approval',
                'color' => 'warning',
                'icon' => 'clock',
                'step' => 'STEP1',
                'can_process' => false,
                'can_edit' => false,
            ],
            self::STATUS_UNDER_REVIEW => [
                'label' => 'Under Review',
                'description' => 'Attendance under review',
                'color' => 'info',
                'icon' => 'eye',
                'step' => 'STEP2',
                'can_process' => true,
                'can_edit' => true,
            ],
            self::STATUS_FROZEN => [
                'label' => 'Attendance Frozen',
                'description' => 'Attendance data frozen',
                'color' => 'success',
                'icon' => 'lock',
                'step' => 'STEP2',
                'can_process' => true,
                'can_edit' => false,
            ],
            self::STATUS_PROCESSING => [
                'label' => 'Salary Processing',
                'description' => 'Salary processing in progress',
                'color' => 'primary',
                'icon' => 'loader',
                'step' => 'STEP3',
                'can_process' => true,
                'can_edit' => false,
            ],
            self::STATUS_PROCESSED => [
                'label' => 'Processed',
                'description' => 'Salary processed, ready for verification',
                'color' => 'success',
                'icon' => 'check-circle',
                'step' => 'STEP3',
                'can_process' => false,
                'can_edit' => false,
            ],
            self::STATUS_VERIFICATION => [
                'label' => 'Verification',
                'description' => 'Under verification',
                'color' => 'warning',
                'icon' => 'check-circle',
                'step' => 'STEP5',
                'can_process' => false,
                'can_edit' => false,
            ],
            self::STATUS_COMPLETED => [
                'label' => 'Completed',
                'description' => 'Week completed',
                'color' => 'success',
                'icon' => 'check',
                'step' => 'STEP5',
                'can_process' => false,
                'can_edit' => false,
            ],
            self::STATUS_FINALIZED => [
                'label' => 'Finalized',
                'description' => 'Week finalized and locked',
                'color' => 'dark',
                'icon' => 'lock',
                'step' => 'STEP6',
                'can_process' => false,
                'can_edit' => false,
            ],
        ];
    }

    public function getStatusDetails(): array
    {
        return self::getStatusConfig()[$this->ppw_status] ?? [
            'label' => 'Unknown',
            'description' => 'Unknown status',
            'color' => 'secondary',
            'icon' => 'help-circle',
            'step' => 'STEP1',
            'can_process' => false,
            'can_edit' => false,
        ];
    }

    // ============================================
    // PERMISSION CHECK METHODS
    // ============================================
    
    public function canProcessAttendance(): bool
    {
        return in_array($this->ppw_status, [self::STATUS_OPEN, self::STATUS_UNDER_REVIEW]) && !$this->ppw_is_processed;
    }

    public function canFreezeAttendance(): bool
    {
        return $this->ppw_status === self::STATUS_UNDER_REVIEW && !$this->ppw_is_frozen && !$this->ppw_is_processed;
    }

    public function canProcessSalary(): bool
    {
        return $this->ppw_status === self::STATUS_FROZEN && !$this->ppw_is_processed;
    }

    public function canUnfreezeAttendance(): bool
    {
        return $this->ppw_status === self::STATUS_FROZEN && !$this->ppw_is_processed;
    }

    public function canEditAttendance(): bool
    {
        return in_array($this->ppw_status, [self::STATUS_OPEN, self::STATUS_UNDER_REVIEW]) && !$this->ppw_is_frozen;
    }

    public function canVerify(): bool
    {
        return $this->ppw_status === self::STATUS_PROCESSED;
    }

    public function canFinalize(): bool
    {
        return $this->ppw_status === self::STATUS_COMPLETED || $this->ppw_status === self::STATUS_VERIFICATION;
    }

    // ============================================
    // ACTION METHODS
    // ============================================
    
    public function submitForApproval(?int $userId = null): void
    {
        $this->ppw_status = self::STATUS_PENDING_APPROVAL;
        $this->ppw_updated_by = $userId ?? auth()->id();
        $this->save();
        $this->updatePayrollPeriodStatus();
    }

    public function approve(?int $userId = null): void
    {
        $this->ppw_status = self::STATUS_UNDER_REVIEW;
        $this->ppw_updated_by = $userId ?? auth()->id();
        $this->save();
        $this->updatePayrollPeriodStatus();
    }

    public function reject(?int $userId = null): void
    {
        $this->ppw_status = self::STATUS_OPEN;
        $this->ppw_updated_by = $userId ?? auth()->id();
        $this->save();
        $this->updatePayrollPeriodStatus();
    }

    public function freezeAttendance(int $employeeCount, ?int $userId = null): void
    {
        $this->ppw_is_frozen = true;
        $this->ppw_status = self::STATUS_FROZEN;
        $this->ppw_frozen_at = now();
        $this->ppw_frozen_by = $userId ?? auth()->id();
        $this->ppw_employee_count = $employeeCount;
        $this->save();
        $this->updatePayrollPeriodStatus();
    }

    public function unfreezeAttendance(?int $userId = null): void
    {
        $this->ppw_is_frozen = false;
        $this->ppw_status = self::STATUS_UNDER_REVIEW;
        $this->ppw_frozen_at = null;
        $this->ppw_frozen_by = null;
        $this->ppw_employee_count = 0;
        $this->ppw_processed_count = 0;
        $this->ppw_is_processed = false;
        $this->save();
        $this->updatePayrollPeriodStatus();
    }

    public function markAsProcessing(?int $userId = null): void
    {
        $this->ppw_status = self::STATUS_PROCESSING;
        $this->ppw_updated_by = $userId ?? auth()->id();
        $this->save();
        $this->updatePayrollPeriodStatus();
    }

    public function markAsProcessed(int $processedCount, ?int $userId = null): void
    {
        $this->ppw_is_processed = true;
        $this->ppw_status = self::STATUS_PROCESSED;
        $this->ppw_processed_count = $processedCount;
        $this->ppw_processed_at = now();
        $this->ppw_updated_by = $userId ?? auth()->id();
        $this->save();
        $this->updatePayrollPeriodStatus();
    }

    public function markForVerification(?int $userId = null): void
    {
        $this->ppw_status = self::STATUS_VERIFICATION;
        $this->ppw_updated_by = $userId ?? auth()->id();
        $this->save();
        $this->updatePayrollPeriodStatus();
    }

    public function markAsCompleted(?int $userId = null): void
    {
        $this->ppw_status = self::STATUS_COMPLETED;
        $this->ppw_updated_by = $userId ?? auth()->id();
        $this->save();
        $this->updatePayrollPeriodStatus();
    }

    public function markAsFinalized(?int $userId = null): void
    {
        $this->ppw_status = self::STATUS_FINALIZED;
        $this->ppw_is_frozen = true;
        $this->ppw_updated_by = $userId ?? auth()->id();
        $this->save();
        $this->updatePayrollPeriodStatus();
    }

    // ============================================
    // UPDATE MAIN PAYROLL PERIOD STATUS
    // ============================================
    
    public function updatePayrollPeriodStatus(): void
    {
        $payrollPeriod = $this->payrollPeriod;
        if (!$payrollPeriod) {
            return;
        }

        $allWeeks = $payrollPeriod->weeks;
        
        // Check week statuses
        $hasPendingApproval = $allWeeks->contains('ppw_status', self::STATUS_PENDING_APPROVAL);
        $hasUnderReview = $allWeeks->contains('ppw_status', self::STATUS_UNDER_REVIEW);
        $hasFrozen = $allWeeks->contains('ppw_status', self::STATUS_FROZEN);
        $hasProcessing = $allWeeks->contains('ppw_status', self::STATUS_PROCESSING);
        $hasProcessed = $allWeeks->contains('ppw_status', self::STATUS_PROCESSED);
        $hasVerification = $allWeeks->contains('ppw_status', self::STATUS_VERIFICATION);
        
        $allCompleted = $allWeeks->every(function($week) {
            return $week->ppw_status === self::STATUS_COMPLETED;
        });
        
        $allFinalized = $allWeeks->every(function($week) {
            return $week->ppw_status === self::STATUS_FINALIZED;
        });
        
        // Determine overall status
        if ($allFinalized) {
            $newStatus = PayrollPeriod::STATUS_COMPLETED;
        } elseif ($allCompleted) {
            $newStatus = PayrollPeriod::STATUS_COMPLETED;
        } elseif ($hasVerification) {
            $newStatus = PayrollPeriod::STATUS_VERIFICATION;
        } elseif ($hasProcessed) {
            $newStatus = PayrollPeriod::STATUS_VERIFICATION;
        } elseif ($hasProcessing) {
            $newStatus = PayrollPeriod::STATUS_SALARY_PROCESSING;
        } elseif ($hasFrozen) {
            $newStatus = PayrollPeriod::STATUS_ATTENDANCE_FROZEN;
        } elseif ($hasUnderReview) {
            $newStatus = PayrollPeriod::STATUS_UNDER_REVIEW;
        } elseif ($hasPendingApproval) {
            $newStatus = PayrollPeriod::STATUS_PENDING_APPROVAL;
        } else {
            $newStatus = PayrollPeriod::STATUS_PROCESSING;
        }
        
        if ($payrollPeriod->pp_status_code !== $newStatus) {
            $payrollPeriod->pp_status_code = $newStatus;
            $payrollPeriod->save();
            
            \Log::info('Payroll period status updated from weeks', [
                'period_id' => $payrollPeriod->pp_id,
                'new_status' => $newStatus,
                'week_statuses' => $allWeeks->pluck('ppw_status')->toArray()
            ]);
        }
    }

    // ============================================
    // UI HELPER METHODS
    // ============================================
    
    public function getStepAttribute(): string
    {
        $config = $this->getStatusDetails();
        return $config['step'];
    }

    public function getCurrentStepAttribute(): string
    {
        return $this->getStepAttribute();
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->getStatusDetails()['label'];
    }

    public function getStatusColorAttribute(): string
    {
        return $this->getStatusDetails()['color'];
    }

    public function getStatusIconAttribute(): string
    {
        return $this->getStatusDetails()['icon'];
    }

    public function getDateRangeAttribute(): string
    {
        return $this->ppw_start_date->format('d M Y') . ' - ' . $this->ppw_end_date->format('d M Y');
    }

    // ============================================
    // CONVENIENCE METHODS
    // ============================================
    
    public function isOpen(): bool
    {
        return $this->ppw_status === self::STATUS_OPEN;
    }

    public function isPendingApproval(): bool
    {
        return $this->ppw_status === self::STATUS_PENDING_APPROVAL;
    }

    public function isUnderReview(): bool
    {
        return $this->ppw_status === self::STATUS_UNDER_REVIEW;
    }

    public function isFrozen(): bool
    {
        return $this->ppw_status === self::STATUS_FROZEN;
    }

    public function isProcessing(): bool
    {
        return $this->ppw_status === self::STATUS_PROCESSING;
    }

    public function isProcessed(): bool
    {
        return $this->ppw_status === self::STATUS_PROCESSED;
    }

    public function isVerification(): bool
    {
        return $this->ppw_status === self::STATUS_VERIFICATION;
    }

    public function isCompleted(): bool
    {
        return $this->ppw_status === self::STATUS_COMPLETED;
    }

    public function isFinalized(): bool
    {
        return $this->ppw_status === self::STATUS_FINALIZED;
    }
}