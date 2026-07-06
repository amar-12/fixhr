<?php

namespace App\Exports;

use App\Models\MasterTable;
use App\Models\PolicyLeave;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SampleLeaveOpeningBalanceExport implements WithHeadings
{
    protected $user;

    /**
     * Constructor to accept authenticated user
     * @param Employee $user
     */
    public function __construct(Employee $user)
    {
        $this->user = $user;
    }

    public function headings(): array
    {
        $headingArr = [
            'S. No.*',
            'Emp Code*',
        ];

        // Fetch leave policies for the business and get their related leave types
        $leavePolicies = PolicyLeave::where('pl_b_id', $this->user->emp_b_id)->get();
        
        $leaveTypeNames = [];
        foreach ($leavePolicies as $policy) {
            // Get leave types for this policy
            $leaveTypes = $policy->fh_leave_type()->get();
            foreach ($leaveTypes as $leaveType) {
                // Get the leave category name using the relation
                $leaveCatName = $leaveType->fh_leave_cat_type()?->first()?->m_name ?? 'Unknown';
                if ($leaveCatName && !in_array($leaveCatName, $leaveTypeNames)) {
                    $leaveTypeNames[] = $leaveCatName;
                }
            }
        }

        // If no leave types found, fallback to all leave categories
        if (empty($leaveTypeNames)) {
            $leaveTypeNames = MasterTable::where('m_group', 'LEAVE_CATEGORY')->pluck('m_name')->toArray();
        }

        $headingArr = array_merge($headingArr, $leaveTypeNames);
        return $headingArr;
    }
}
